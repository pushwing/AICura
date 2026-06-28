<?php

namespace App\Controllers\Admin;

use App\Models\AdvertiserModel;
use App\Models\BoardModel;
use App\Models\CallRequestModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class UserController extends BaseAdminController
{
    private UserModel $userModel;

    // 목록 페이지당 행 수
    private const PER_PAGE = 20;

    // 상세 하위 목록(신청 이벤트·작성 후기)의 페이지당 행 수
    private const SUB_PER_PAGE = 5;

    // 대행사 탭 — user_type이 아닌 is_agency_account 플래그로 구분
    private const TAB_AGENCY = 4;

    // 사용자 탭 — 일반 사용자(user_type=1)
    private const TAB_USER = 1;

    // 기본 탭
    private const TAB_DEFAULT = 2;

    // user_type 그룹 → 실제 user_type 값 매핑
    private const TYPE_GROUPS = [
        self::TAB_USER => [UserModel::TYPE_USER],
        2 => [UserModel::TYPE_OPERATOR, UserModel::TYPE_ADMIN, UserModel::TYPE_STATS, UserModel::TYPE_GENERAL, UserModel::TYPE_INSTALL, UserModel::TYPE_EXTERNAL],
        3 => [UserModel::TYPE_HOSPITAL_AD, UserModel::TYPE_HOSPITAL_GENE, UserModel::TYPE_HOSPITAL_RECV],
    ];

    private const TAB_LABELS = [
        self::TAB_USER => '사용자',
        2 => '운영자',
        3 => '광고주/병원',
        self::TAB_AGENCY => '대행사',
    ];

    /** @var array<int, string> */
    private const USER_TYPE_LABELS = [
        UserModel::TYPE_USER          => '일반 사용자',
        UserModel::TYPE_OPERATOR      => '운영자',
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
        $typeGroup = (int) ($this->request->getGet('type') ?? self::TAB_DEFAULT);
        // 유효 탭(1·2·3·4)이 아니면 기본 탭으로
        if (!isset(self::TAB_LABELS[$typeGroup])) {
            $typeGroup = self::TAB_DEFAULT;
        }
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

        // 신청 이벤트·작성 후기 — 첫 페이지(5건)만 SSR, 이후는 더보기 AJAX로 로드
        $events  = model(CallRequestModel::class)->getByUser($id, self::SUB_PER_PAGE, 0);
        $reviews = model(BoardModel::class)->getByUser($id, self::SUB_PER_PAGE, 0);

        return $this->render('admin/users/show', [
            'user'              => $user,
            'userTypeLabels'    => self::USER_TYPE_LABELS,
            'whereFromLabels'   => UserModel::WHERE_FROM_LABELS,
            'providerLabels'    => UserModel::PROVIDER_LABELS,
            'agencyAdvertisers' => $agencyAdvertisers,
            'adType2Labels'     => \App\Models\ContractOrderModel::AD_TYPE2_LABELS,
            'events'            => array_map([$this, 'formatEvent'], $events['list']),
            'eventsTotal'       => $events['total'],
            'reviews'           => array_map([$this, 'formatReview'], $reviews['list']),
            'reviewsTotal'      => $reviews['total'],
            'subPerPage'        => self::SUB_PER_PAGE,
        ]);
    }

    // ──────────────────────────────────────────────
    // 상태 변경 (휴면·계정 활성, JSON)
    // ──────────────────────────────────────────────

    public function updateStatus(int $id): ResponseInterface
    {
        $body      = $this->request->getJSON(true);
        $isDormant = isset($body['is_dormant']) ? (int) $body['is_dormant'] : null;
        $isActive  = isset($body['is_active']) ? (int) $body['is_active'] : null;

        try {
            $this->userModel->updateStatus($id, $isDormant, $isActive);
        } catch (\RuntimeException $e) {
            return $this->response->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => '상태가 변경되었습니다.',
        ]);
    }

    // ──────────────────────────────────────────────
    // 신청 이벤트·작성 후기 더보기 (페이징, JSON)
    // ──────────────────────────────────────────────

    public function events(int $id): ResponseInterface
    {
        $page   = max(1, (int) ($this->request->getGet('page') ?? 1));
        $result = model(CallRequestModel::class)->getByUser($id, self::SUB_PER_PAGE, ($page - 1) * self::SUB_PER_PAGE);

        return $this->response->setJSON([
            'items'    => array_map([$this, 'formatEvent'], $result['list']),
            'has_more' => $page * self::SUB_PER_PAGE < $result['total'],
        ]);
    }

    public function reviews(int $id): ResponseInterface
    {
        $page   = max(1, (int) ($this->request->getGet('page') ?? 1));
        $result = model(BoardModel::class)->getByUser($id, self::SUB_PER_PAGE, ($page - 1) * self::SUB_PER_PAGE);

        return $this->response->setJSON([
            'items'    => array_map([$this, 'formatReview'], $result['list']),
            'has_more' => $page * self::SUB_PER_PAGE < $result['total'],
        ]);
    }

    /**
     * 신청 이벤트 행을 뷰·AJAX 공용 표현형으로 변환.
     *
     * @param array<string, mixed> $row
     * @return array{id: int, campaign_title: string, status_label: string, created_at: string}
     */
    private function formatEvent(array $row): array
    {
        return [
            'id'             => (int) $row['id'],
            'campaign_title' => (string) ($row['campaign_title'] ?? '-'),
            'status_label'   => CallRequestModel::STATUSES[(int) $row['status']] ?? (string) $row['status'],
            'created_at'     => substr((string) ($row['created_at'] ?? ''), 0, 10),
        ];
    }

    /**
     * 작성 후기 행을 뷰·AJAX 공용 표현형으로 변환.
     *
     * @param array<string, mixed> $row
     * @return array{id: int, type_label: string, subject: string, rate: int, is_deleted: bool, created_at: string}
     */
    private function formatReview(array $row): array
    {
        return [
            'id'         => (int) $row['id'],
            'type_label' => BoardModel::TYPES[(int) $row['type']] ?? (string) $row['type'],
            'subject'    => (string) ($row['subject'] ?? '-'),
            'rate'       => (int) ($row['rate_sum'] ?? 0),
            'is_deleted' => (int) ($row['is_delete'] ?? 0) !== BoardModel::DELETE_NONE,
            'created_at' => substr((string) ($row['created_at'] ?? ''), 0, 10),
        ];
    }
}
