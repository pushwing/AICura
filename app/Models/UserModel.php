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
        // 소셜 계정은 비밀번호가 없으므로(null) permit_empty — 값이 있을 때만 길이 검증
        'password' => 'permit_empty|min_length[8]',
    ];

    /** @var array<int, string> 가입 경로 라벨 (where_from) */
    public const WHERE_FROM_LABELS = [
        1 => '웹',
        2 => 'iOS',
        3 => 'Android',
        4 => '어드민',
    ];

    /** @var array<int, string> 로그인 방식 라벨 (provider) */
    public const PROVIDER_LABELS = [
        9 => '이메일',
        1 => 'Facebook',
        2 => 'Naver',
        3 => 'Kakao',
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
     * 본인 id로 password 포함 조회 ($hidden 우회) — 비밀번호 변경 검증용 (이슈 #49)
     *
     * @return array<string, mixed>|null
     */
    public function findWithPasswordById(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('id, email, username, phone, password')
            ->where('id', $id)
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
     * 외부 앱 내 프로필 조회 — 소비자 노출 컬럼만. (이슈 #97)
     *
     * @return array<string, mixed>|null
     */
    public function getProfile(int $id): ?array
    {
        return $this->db->table($this->table)
            ->select('id, email, username, picture, phone, age, sex, job, health_point, provider, where_from, created_at')
            ->where('id', $id)
            ->where('deleted_at IS NULL', null, false)
            ->get()
            ->getRowArray() ?: null;
    }

    /**
     * 외부 앱 내 프로필 수정 — 허용 필드만 갱신. (이슈 #97)
     *
     * @param array<string, mixed> $data
     */
    public function updateProfile(int $id, array $data): void
    {
        $allowed = ['username', 'phone', 'age', 'sex', 'job', 'picture'];
        $update  = array_intersect_key($data, array_flip($allowed));

        if ($update !== []) {
            $this->update($id, $update);
        }
    }

    /**
     * 외부 앱(소비자) 로그인 인증용 — password 포함 조회 ($hidden 우회) (이슈 #96)
     *
     * 일반 사용자(user_type=1) 한정. 운영자·병원·대행사 계정은 앱 로그인 불가.
     *
     * @return array<string, mixed>|null
     */
    public function findAppUserForAuth(string $email): ?array
    {
        return $this->db->table($this->table)
            ->select('id, email, username, password, provider, is_active')
            ->where('email', $email)
            ->where('user_type', self::TYPE_USER)
            ->where('deleted_at IS NULL', null, false)
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    /**
     * 소셜 로그인 계정 조회 — provider + uid 로 식별 (이슈 #96)
     *
     * @return array<string, mixed>|null
     */
    public function findAppUserByProviderUid(int $provider, string $uid): ?array
    {
        return $this->db->table($this->table)
            ->select('id, email, username, is_active')
            ->where('provider', $provider)
            ->where('uid', $uid)
            ->where('user_type', self::TYPE_USER)
            ->where('deleted_at IS NULL', null, false)
            ->limit(1)
            ->get()
            ->getRowArray() ?: null;
    }

    /**
     * 외부 앱(소비자) 계정 생성 — user_type=1 고정, 비밀번호 해시 처리 (이슈 #96)
     *
     * @param array<string, mixed> $data email·password(평문, 선택)·username·phone·age·sex·where_from·provider·uid·picture
     * @return int 생성된 user id
     */
    public function createAppUser(array $data): int
    {
        $plainPassword = $data['password'] ?? null;

        $row = [
            'email'      => $data['email'],
            'password'   => is_string($plainPassword) && $plainPassword !== ''
                ? password_hash($plainPassword, PASSWORD_DEFAULT)
                : null,
            'username'   => $data['username'] ?? null,
            'user_type'  => self::TYPE_USER,
            'where_from' => $data['where_from'] ?? 2,
            'provider'   => $data['provider'] ?? 9,
            'phone'      => $data['phone'] ?? null,
            'age'        => $data['age'] ?? null,
            'sex'        => $data['sex'] ?? null,
            'picture'    => $data['picture'] ?? null,
            'uid'        => $data['uid'] ?? null,
            'is_dormant' => 1, // 1 = 활성 (반전 의미)
            'is_active'  => 1,
        ];

        $id = $this->insert($row, true);

        if ($id === false) {
            throw new \RuntimeException('앱 계정 생성에 실패했습니다: ' . implode(' ', $this->errors()));
        }

        return (int) $id;
    }

    /**
     * 로그인 시각 갱신 (이슈 #96)
     */
    public function touchLogin(int $id): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table($this->table)
            ->where('id', $id)
            ->update(['last_login_at' => $now, 'last_activity_at' => $now]);
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

    /**
     * 사용자 상태 변경 — 휴면 상태(is_dormant)·계정 활성(is_active)을 선택적으로 갱신. (이슈 #90)
     *
     * - is_dormant: 1 활성 · 0 휴면 (반전 의미). 휴면 전환 시 dormant_at 기록, 활성 복귀 시 해제.
     * - is_active : 1 활성 · 0 비활성 (로그인 허용 여부).
     * - null 로 전달된 항목은 변경하지 않는다.
     *
     * @throws \RuntimeException 사용자 없음·유효하지 않은 값·변경 항목 없음
     */
    public function updateStatus(int $id, ?int $isDormant, ?int $isActive): void
    {
        if ($this->find($id) === null) {
            throw new \RuntimeException('사용자를 찾을 수 없습니다.');
        }

        $update = [];

        if ($isDormant !== null) {
            if (!in_array($isDormant, [0, 1], true)) {
                throw new \RuntimeException('유효하지 않은 휴면 상태입니다.');
            }
            $update['is_dormant'] = $isDormant;
            // 0 = 휴면 전환 시점 기록, 1 = 활성 복귀 시 해제
            $update['dormant_at'] = $isDormant === 0 ? date('Y-m-d H:i:s') : null;
        }

        if ($isActive !== null) {
            if (!in_array($isActive, [0, 1], true)) {
                throw new \RuntimeException('유효하지 않은 계정 활성 상태입니다.');
            }
            $update['is_active'] = $isActive;
        }

        if ($update === []) {
            throw new \RuntimeException('변경할 상태가 없습니다.');
        }

        $this->update($id, $update);
    }
}
