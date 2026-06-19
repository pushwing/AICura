<?php

namespace App\Controllers\Admin;

use App\Models\UserModel;

class UserController extends BaseAdminController
{
    private UserModel $userModel;

    // 목록 페이지당 행 수
    private const PER_PAGE = 20;

    // user_type 그룹 → 실제 user_type 값 매핑
    private const TYPE_GROUPS = [
        1 => [UserModel::TYPE_USER],
        2 => [UserModel::TYPE_ADMIN, UserModel::TYPE_STATS, UserModel::TYPE_GENERAL, UserModel::TYPE_INSTALL, UserModel::TYPE_EXTERNAL],
        3 => [UserModel::TYPE_HOSPITAL_AD, UserModel::TYPE_HOSPITAL_GENE, UserModel::TYPE_HOSPITAL_RECV],
    ];

    private const TAB_LABELS = [
        1 => '일반 사용자',
        2 => '운영자',
        3 => '광고주/병원',
    ];

    /** @var array<int, string> */
    private const USER_TYPE_LABELS = [
        UserModel::TYPE_USER          => '일반 사용자',
        UserModel::TYPE_HOSPITAL_AD   => '광고주병원',
        UserModel::TYPE_HOSPITAL_GENE => '일반병원',
        UserModel::TYPE_HOSPITAL_RECV => '접수병원',
        UserModel::TYPE_ADMIN         => '관리자',
        UserModel::TYPE_STATS         => '통계운영자',
        UserModel::TYPE_GENERAL       => '일반운영자',
        UserModel::TYPE_INSTALL       => '접수설치',
        UserModel::TYPE_EXTERNAL      => '외부운영자',
    ];

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->userModel = model(UserModel::class);
    }

    // ──────────────────────────────────────────────
    // 목록 (AG Grid, user_type 탭)
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $typeGroup  = max(1, min(3, (int) ($this->request->getGet('type') ?? 1)));
        $subType    = (int) ($this->request->getGet('sub_type') ?? 0);
        $isDormant  = $this->request->getGet('is_dormant') ?? '';
        $searchWord = $this->request->getGet('search_word') ?? '';
        $page       = max(1, (int) ($this->request->getGet('page') ?? 1));

        $params = [
            'is_dormant'  => $isDormant,
            'search_word' => $searchWord,
            'page'        => $page,
            'limit'       => self::PER_PAGE,
        ];

        if ($subType !== 0 && in_array($subType, self::TYPE_GROUPS[$typeGroup], true)) {
            $params['user_type'] = $subType;
        } else {
            $params['user_types'] = self::TYPE_GROUPS[$typeGroup];
        }

        $result = $this->userModel->getList($params);

        $total    = (int) $result['total'];
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));

        return $this->render('admin/users/index', [
            'users'          => $result['list'],
            'total'          => $total,
            'page'           => $page,
            'lastPage'       => $lastPage,
            'typeGroup'      => $typeGroup,
            'subType'        => $subType,
            'isDormant'      => $isDormant,
            'searchWord'     => $searchWord,
            'tabLabels'      => self::TAB_LABELS,
            'typeGroups'     => self::TYPE_GROUPS,
            'userTypeLabels' => self::USER_TYPE_LABELS,
        ]);
    }

    // ──────────────────────────────────────────────
    // 상세
    // ──────────────────────────────────────────────

    public function show(int $id): string
    {
        $user = $this->userModel->find($id);
        if ($user === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('admin/users/show', [
            'user'           => $user,
            'userTypeLabels' => self::USER_TYPE_LABELS,
        ]);
    }
}
