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
    public const TYPE_ADVERTISER     = 3;   // 광고주
    public const TYPE_HOSPITAL_AD    = 201; // 광고주병원
    public const TYPE_HOSPITAL_GENE  = 202; // 일반병원
    public const TYPE_HOSPITAL_RECV  = 203; // 접수병원
    public const TYPE_ADMIN          = 401; // 관리자
    public const TYPE_STATS          = 402; // 통계운영자
    public const TYPE_GENERAL        = 403; // 일반운영자
    public const TYPE_INSTALL        = 404; // 접수설치
    public const TYPE_EXTERNAL       = 405; // 외부운영자

    /**
     * 어드민 로그인용 — 이메일로 운영자 조회
     *
     * @return array<string, mixed>|null
     */
    public function findAdminByEmail(string $email): ?array
    {
        return $this->where('email', $email)
            ->whereIn('user_type', [
                self::TYPE_OPERATOR,
                self::TYPE_ADMIN,
                self::TYPE_STATS,
                self::TYPE_GENERAL,
                self::TYPE_INSTALL,
                self::TYPE_EXTERNAL,
            ])
            ->where('is_active', 1)
            ->first();
    }

    /**
     * 목록 조회 (user_type 필터, 검색, 페이징)
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function getList(array $params): array
    {
        $builder = $this->builder();

        if (!empty($params['user_type'])) {
            $builder->where('user_type', (int) $params['user_type']);
        }

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
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->select('id, email, username, user_type, where_from, provider, is_agency_account, picture, phone, age, sex, health_point, is_dormant, last_login_at, created_at')
            ->orderBy('id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }
}
