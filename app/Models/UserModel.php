<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'email',
        'password',
        'username',
        'user_type',
        'where_from',
        'provider',
        'is_agency_account',
        'picture',
        'phone',
        'age',
        'sex',
        'job',
        'health_point',
        'note',
        'is_dormant',
        'dormant_at',
        'last_login_at',
        'last_logout_at',
        'last_activity_at',
        'oauth_token',
        'uid',
        'group_auth_code',
        'is_active',
    ];

    /** @var list<string> */
    protected $hidden = ['password', 'oauth_token'];

    /** @var array<string, string> */
    protected $validationRules = [
        'email'    => 'required|valid_email|max_length[255]',
        'password' => 'min_length[8]',
    ];

    // user_type 상수
    public const TYPE_USER           = 1;   // 일반 사용자
    public const TYPE_OPERATOR       = 2;   // 운영자
    public const TYPE_HOSPITAL_AD    = 201; // 광고주병원
    public const TYPE_HOSPITAL_GENE  = 202; // 일반병원
    public const TYPE_HOSPITAL_RECV  = 203; // 접수병원
    public const TYPE_ADMIN          = 401; // 관리자
    public const TYPE_STATS          = 402; // 통계운영자
    public const TYPE_GENERAL        = 403; // 일반운영자
    public const TYPE_INSTALL        = 404; // 접수설치
    public const TYPE_EXTERNAL       = 405; // 외부운영자

    /**
     * 어드민 로그인 인증용 — password 포함 조회 ($hidden 우회)
     *
     * @return array<string, mixed>|null
     */
    public function findAdminForAuth(string $email): ?array
    {
        return $this->db->table($this->table)
            ->select('id, email, username, user_type, password, is_active, created_at')
            ->where('email', $email)
            ->whereIn('user_type', [
                self::TYPE_OPERATOR,
                self::TYPE_ADMIN,
                self::TYPE_STATS,
                self::TYPE_GENERAL,
                self::TYPE_INSTALL,
                self::TYPE_EXTERNAL,
            ])
            ->where('is_active', 1)
            ->where('deleted_at IS NULL', null, false)
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    /** @var list<int> 포털(광고주) 로그인 허용 병원 유형 */
    public const PORTAL_HOSPITAL_TYPES = [
        self::TYPE_HOSPITAL_AD,
        self::TYPE_HOSPITAL_GENE,
        self::TYPE_HOSPITAL_RECV,
    ];

    /**
     * 포털 로그인 인증용 — password 포함 조회 ($hidden 우회)
     *
     * 허용 대상: 광고대행사(is_agency_account=1) 또는 병원 유형(201~203)
     *
     * @return array<string, mixed>|null
     */
    public function findPortalForAuth(string $email): ?array
    {
        return $this->db->table($this->table)
            ->select('id, email, username, user_type, is_agency_account, password, is_active, created_at')
            ->where('email', $email)
            ->groupStart()
                ->where('is_agency_account', 1)
                ->orWhereIn('user_type', self::PORTAL_HOSPITAL_TYPES)
            ->groupEnd()
            ->where('is_active', 1)
            ->where('deleted_at IS NULL', null, false)
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    /**
     * 광고주 계정(병원 유형) 이메일 조회 — 대행사 광고주 등록 시 owner 연결용
     *
     * @return array<string, mixed>|null
     */
    public function findHospitalUserByEmail(string $email): ?array
    {
        return $this->select('id, email, username, user_type')
            ->where('email', $email)
            ->whereIn('user_type', self::PORTAL_HOSPITAL_TYPES)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * 광고주(병원 유형) 포털 로그인 계정 생성 — 비밀번호 해시 처리 포함
     *
     * 어드민 광고주 등록 시 owner 계정을 함께 생성할 때 사용한다.
     *
     * @return int|false 생성된 user id, 실패 시 false
     */
    public function createHospitalOwner(string $email, string $plainPassword, ?string $username, ?string $phone): int|false
    {
        $data = [
            'email'             => $email,
            'password'          => password_hash($plainPassword, PASSWORD_DEFAULT),
            'username'          => $username ?: null,
            'user_type'         => self::TYPE_HOSPITAL_AD,
            'is_agency_account' => 0,
            'phone'             => $phone ?: null,
            'is_dormant'        => 1, // 1 = 활성 (반전 의미)
            'is_active'         => 1,
        ];

        $id = $this->insert($data, true);

        return $id === false ? false : (int) $id;
    }

    /**
     * 이메일 중복 여부 (soft delete 제외)
     */
    public function emailExists(string $email): bool
    {
        return $this->where('email', $email)->countAllResults() > 0;
    }

    /**
     * 목록 조회 (user_type 필터, 검색, 페이징)
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getList(array $params): array
    {
        $builder = $this->builder()
            ->where($this->table . '.deleted_at IS NULL', null, false);

        if (!empty($params['is_agency'])) {
            $builder->where('is_agency_account', 1);
        } else {
            // 대행사 계정은 '대행사' 탭에서만 노출 — 다른 탭에서는 제외
            $builder->where('is_agency_account', 0);

            if (!empty($params['user_types']) && is_array($params['user_types'])) {
                $builder->whereIn('user_type', array_map('intval', $params['user_types']));
            } elseif (!empty($params['user_type'])) {
                $builder->where('user_type', (int) $params['user_type']);
            }
        }

        // 주의: is_dormant 값은 반전 의미 — 1 = 활성, 0 = 휴면
        if (isset($params['is_dormant']) && $params['is_dormant'] !== '') {
            $builder->where('is_dormant', (int) $params['is_dormant']);
        }

        if (!empty($params['search_word'])) {
            $builder->groupStart()
                ->like('email', $params['search_word'])
                ->orLike('username', $params['search_word'])
                ->orLike('phone', $params['search_word'])
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = max(1, (int) ($params['limit'] ?? 20));

        $list = $builder
            ->select('id, email, username, user_type, where_from, provider, is_agency_account, picture, phone, age, sex, health_point, is_dormant, last_login_at, created_at')
            ->orderBy('id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => (int) $total];
    }
}
