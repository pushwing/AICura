<?php

namespace App\Controllers\Admin;

use App\Models\AdvertiserModel;
use App\Models\UserModel;

class UserController extends BaseAdminController
{
    private UserModel $userModel;

    // 목록 페이지당 행 수
    private const PER_PAGE = 20;

    // 대행사 탭 — user_type이 아닌 is_agency_account 플래그로 구분
    private const TAB_AGENCY = 4;

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
        self::TAB_AGENCY => '대행사',
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
        $typeGroup  = max(1, min(self::TAB_AGENCY, (int) ($this->request->getGet('type') ?? 1)));
        $subType    = (int) ($this->request->getGet('sub_type') ?? 0);
        $isDormant  = $this->request->getGet('is_dormant') ?? '';
        $searchWord = $this->request->getGet('search_word') ?? '';
        $page       = max(1, (int) ($this->request->getGet('page') ?? 1));
        $isAgency   = $typeGroup === self::TAB_AGENCY;

        $params = [
            'is_dormant'  => $isDormant,
            'search_word' => $searchWord,
            'page'        => $page,
            'limit'       => self::PER_PAGE,
        ];

        if ($isAgency) {
            $params['is_agency'] = 1;
        } elseif ($subType !== 0 && in_array($subType, self::TYPE_GROUPS[$typeGroup], true)) {
            $params['user_type'] = $subType;
        } else {
            $params['user_types'] = self::TYPE_GROUPS[$typeGroup];
        }

        $result = $this->userModel->getList($params);
        $users  = $result['list'];

        // 대행사 탭 — 현재 페이지 행에 소유 광고주 수·계약 집계 병합 (N+1 방지)
        if ($isAgency && $users !== []) {
            /** @var list<int> $agencyIds */
            $agencyIds = array_map(static fn (array $u): int => (int) $u['id'], $users);
            $stats     = model(AdvertiserModel::class)->getAgencyStats($agencyIds);

            $users = array_map(static function (array $u) use ($stats): array {
                $s = $stats[(int) $u['id']] ?? ['advertiser_count' => 0, 'order_count' => 0, 'total_price' => 0];
                $u['advertiser_count'] = $s['advertiser_count'];
                $u['order_count']      = $s['order_count'];
                $u['total_price']      = $s['total_price'];
                return $u;
            }, $users);
        }

        $total    = (int) $result['total'];
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));

        return $this->render('admin/users/index', [
            'users'          => $users,
            'total'          => $total,
            'page'           => $page,
            'lastPage'       => $lastPage,
            'typeGroup'      => $typeGroup,
            'subType'        => $subType,
            'isDormant'      => $isDormant,
            'searchWord'     => $searchWord,
            'isAgency'       => $isAgency,
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

        // 대행사 계정이면 소유 광고주 목록 + 계약 요약을 함께 노출
        $agencyAdvertisers = [];
        if ((int) $user['is_agency_account'] === 1) {
            $agencyAdvertisers = model(AdvertiserModel::class)->getOwnedWithContractSummary($id);
        }

        return $this->render('admin/users/show', [
            'user'              => $user,
            'userTypeLabels'    => self::USER_TYPE_LABELS,
            'agencyAdvertisers' => $agencyAdvertisers,
        ]);
    }
}
