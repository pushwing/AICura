<?php

namespace App\Controllers\Admin;

use App\Models\CampaignModel;
use App\Models\CampaignHistoryModel;
use App\Models\CampaignReviewRequestModel;
use App\Models\CampaignTempModel;
use App\Models\HospitalModel;
use App\Models\ContractModel;
use App\Models\EventCategoryModel;
use App\Models\SettingModel;
use App\Services\AiComplianceService;
use App\Services\AiCopyService;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class CampaignController extends BaseAdminController
{
    private CampaignModel $campaignModel;
    private CampaignHistoryModel $historyModel;
    private CampaignReviewRequestModel $reviewRequestModel;
    private CampaignTempModel $tempModel;
    private HospitalModel $hospitalModel;
    private ContractModel $contractModel;
    private EventCategoryModel $eventCategoryModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->campaignModel      = model(CampaignModel::class);
        $this->historyModel       = model(CampaignHistoryModel::class);
        $this->reviewRequestModel = model(CampaignReviewRequestModel::class);
        $this->tempModel          = model(CampaignTempModel::class);
        $this->hospitalModel      = model(HospitalModel::class);
        $this->contractModel      = model(ContractModel::class);
        $this->eventCategoryModel = model(EventCategoryModel::class);
    }

    // ──────────────────────────────────────────────
    // 목록 (AG Grid)
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $params = [
            'status'        => $this->request->getGet('status') ?? '',
            'review_status' => $this->request->getGet('review_status') ?? '',
            'ad_type'  => $this->request->getGet('ad_type') ?? '',
            'channel'  => $this->request->getGet('channel') ?? '',
            'keyword'  => $this->request->getGet('keyword') ?? '',
            'page'     => (int) ($this->request->getGet('page') ?? 1),
            'limit'    => 20,
        ];

        $result = $this->campaignModel->getCampaignList($params);

        return $this->render('admin/campaigns/index', [
            'campaigns' => $result['list'],
            'total'     => $result['total'],
            'params'    => $params,
            'adTypes'   => CampaignModel::AD_TYPES,
            'channels'  => CampaignModel::CHANNELS,
        ]);
    }

    // ──────────────────────────────────────────────
    // 상세
    // ──────────────────────────────────────────────

    public function show(int $id): string
    {
        $campaign = $this->campaignModel->getCampaignDetail($id);
        if ($campaign === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $histories = $this->campaignModel->getHistoryList($id, ['limit' => 5]);

        return $this->render('admin/campaigns/show', [
            'campaign'   => $campaign,
            'histories'  => $histories['list'],
            'adTypes'    => CampaignModel::AD_TYPES,
            'channels'   => CampaignModel::CHANNELS,
            'transitions' => CampaignModel::STATUS_TRANSITIONS,
        ]);
    }

    // ──────────────────────────────────────────────
    // 등록 폼
    // ──────────────────────────────────────────────

    public function new(): string
    {
        return $this->render('admin/campaigns/form', [
            'campaign'        => null,
            'hospitals'       => $this->hospitalModel->getActiveList(),
            'contracts'       => $this->contractModel->findAll(),
            'adTypes'         => CampaignModel::AD_TYPES,
            'channels'        => CampaignModel::CHANNELS,
            'categories'      => $this->eventCategoryModel->getSelectOptions(),
            'adTypeCostField' => CampaignModel::AD_TYPE_COST_FIELD,
        ]);
    }

    // ──────────────────────────────────────────────
    // 등록 처리
    // ──────────────────────────────────────────────

    public function create(): ResponseInterface
    {
        $rules = $this->campaignValidationRules();

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data      = $this->buildCampaignData();
        $imageData = $this->handleImageUploads();
        $data      = array_merge($data, $imageData);

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');
        $adminId  = (int) ($authUser['id'] ?? 0);

        // campaigns 에는 메타데이터만 저장 — 콘텐츠는 검수 승인 후 복사됨
        $id = $this->campaignModel->insert([
            'hospital_id'   => $data['hospital_id'],
            'hospital_type' => $data['hospital_type'],
            'status'        => 'pending',
            'review_status' => 'pending',
        ], true);

        // 모든 콘텐츠 필드 → 검수 요청 테이블
        $reviewRequestId = $this->reviewRequestModel->record((int) $id, $data, $adminId, 'create');

        $this->historyModel->record((int) $id, 'create', '', 'pending', $adminId);

        // 의료광고 심의 사전검사 (토글 ON 시) — 실패해도 등록 흐름은 유지
        $this->runComplianceCheck((int) $id, $reviewRequestId);

        return redirect()->to('/admin/campaigns/' . $id)
            ->with('success', '캠페인이 등록되었습니다. 검수 대기 상태입니다.');
    }

    // ──────────────────────────────────────────────
    // 수정 폼
    // ──────────────────────────────────────────────

    public function edit(int $id): string
    {
        $campaign = $this->campaignModel->find($id);
        if ($campaign === null || $campaign['is_deleted']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 폼 pre-populate: 최신 검수 요청 데이터 우선, 없으면 캠페인 승인 데이터
        $latest = $this->reviewRequestModel->getLatest($id);
        if ($latest !== null) {
            foreach (CampaignReviewRequestModel::CONTENT_FIELDS as $field) {
                if (isset($latest[$field])) {
                    $campaign[$field] = $latest[$field];
                }
            }
        }

        return $this->render('admin/campaigns/form', [
            'campaign'        => $campaign,
            'hospitals'       => $this->hospitalModel->getActiveList(),
            'contracts'       => $this->contractModel->findAll(),
            'adTypes'         => CampaignModel::AD_TYPES,
            'channels'        => CampaignModel::CHANNELS,
            'categories'      => $this->eventCategoryModel->getSelectOptions(),
            'adTypeCostField' => CampaignModel::AD_TYPE_COST_FIELD,
        ]);
    }

    // ──────────────────────────────────────────────
    // 수정 처리
    // ──────────────────────────────────────────────

    public function update(int $id): ResponseInterface
    {
        $campaign = $this->campaignModel->find($id);
        if ($campaign === null || $campaign['is_deleted']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = $this->campaignValidationRules();

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data      = $this->buildCampaignData();
        $imageData = $this->handleImageUploads($campaign);
        $data      = array_merge($data, $imageData);

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');
        $adminId  = (int) ($authUser['id'] ?? 0);

        // campaigns 콘텐츠는 검수 승인 전까지 변경하지 않음
        // review_status 만 pending 으로 표시
        $this->campaignModel->update($id, ['review_status' => 'pending']);

        // 변경 요청 전체를 검수 테이블에 기록
        $reviewRequestId = $this->reviewRequestModel->record($id, $data, $adminId, 'update');

        $this->historyModel->record($id, 'update', $campaign['status'], $campaign['status'], $adminId);

        // 의료광고 심의 사전검사 (토글 ON 시) — 실패해도 수정 흐름은 유지
        $this->runComplianceCheck($id, $reviewRequestId);

        return redirect()->to('/admin/campaigns/' . $id)
            ->with('success', '수정되었습니다.');
    }

    // ──────────────────────────────────────────────
    // 상태 변경 (Ajax POST)
    // ──────────────────────────────────────────────

    public function action(int $id): ResponseInterface
    {
        $campaign = $this->campaignModel->find($id);
        if ($campaign === null || $campaign['is_deleted']) {
            return $this->response->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => '캠페인을 찾을 수 없습니다.']);
        }

        $body   = $this->request->getJSON(true);
        $action = (string) ($body['action'] ?? '');
        $memo   = isset($body['memo']) ? $this->sanitizeDetailHtml((string) $body['memo']) : null;

        if (!in_array($action, ['approve', 'reject', 'end', 'reopen'], true)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => '유효하지 않은 액션입니다.']);
        }

        // 종료 처리는 사유 기록이 필수다 (이슈 #157)
        if ($action === 'end' && trim(strip_tags($memo ?? '')) === '') {
            return $this->response->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => '종료 처리 시 메모(사유)를 반드시 입력해야 합니다.']);
        }

        try {
            $newStatus = $this->campaignModel->updateStatus($id, $action);
        } catch (\RuntimeException $e) {
            return $this->response->setStatusCode(422)
                ->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');
        $this->historyModel->record(
            $id,
            $action,
            $campaign['status'],
            $newStatus,
            (int) ($authUser['id'] ?? 0),
            $memo
        );

        return $this->response->setJSON([
            'success' => true,
            'status'  => $newStatus,
            'message' => '상태가 변경되었습니다.',
        ]);
    }

    // ──────────────────────────────────────────────
    // 히스토리 목록
    // ──────────────────────────────────────────────

    public function history(int $id): string
    {
        $campaign = $this->campaignModel->find($id);
        if ($campaign === null || $campaign['is_deleted']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $params = [
            'page'  => (int) ($this->request->getGet('page') ?? 1),
            'limit' => 20,
        ];

        $result = $this->campaignModel->getHistoryList($id, $params);

        return $this->render('admin/campaigns/history', [
            'campaign'  => $campaign,
            'histories' => $result['list'],
            'total'     => $result['total'],
            'params'    => $params,
        ]);
    }

    // ──────────────────────────────────────────────
    // 임시저장 목록
    // ──────────────────────────────────────────────

    public function tempList(): string
    {
        $params = [
            'keyword' => $this->request->getGet('keyword') ?? '',
            'page'    => (int) ($this->request->getGet('page') ?? 1),
            'limit'   => 20,
        ];

        $result = $this->tempModel->getList($params);

        return $this->render('admin/campaigns/temp_list', [
            'temps'  => $result['list'],
            'total'  => $result['total'],
            'params' => $params,
        ]);
    }

    // ──────────────────────────────────────────────
    // 삭제 처리 (is_deleted = 1)
    // ──────────────────────────────────────────────

    public function delete(int $id): ResponseInterface
    {
        $campaign = $this->campaignModel->find($id);
        if ($campaign === null || $campaign['is_deleted']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($campaign['status'] !== 'ended') {
            return redirect()->back()->with('error', '종료된 캠페인만 삭제할 수 있습니다.');
        }

        $this->campaignModel->update($id, ['is_deleted' => 1]);

        return redirect()->to('/admin/campaigns')
            ->with('success', '캠페인이 삭제되었습니다.');
    }

    // ──────────────────────────────────────────────
    // AI 광고 카피 추천 (이슈 #73) — POST, JSON 응답
    // ──────────────────────────────────────────────

    /**
     * 키워드·병원유형·카테고리로 제목 후보 3개 + 상세문구(HTML)를 생성한다.
     * 사용자가 버튼을 눌러 즉시 결과를 봐야 하므로 동기 호출(단발성)이며,
     * 서비스 레이어에서 동일 입력 캐시로 반복 호출을 방어한다.
     */
    public function suggestCopy(): ResponseInterface
    {
        $keyword         = (string) ($this->request->getPost('keyword') ?? '');
        $hospitalTypeInt = (int) ($this->request->getPost('hospital_type') ?? 1);
        $categoryId      = (int) ($this->request->getPost('category') ?? 0);

        $categoryTitle = $categoryId > 0 ? $this->eventCategoryModel->titleById($categoryId) : '';

        try {
            $result = (new AiCopyService())->suggest([
                'keyword'       => $keyword,
                'hospital_type' => CampaignModel::HOSPITAL_TYPES[$hospitalTypeInt] ?? '',
                'category'      => $categoryTitle,
            ]);
        } catch (Throwable $e) {
            log_message('error', 'AI 카피 생성 실패: {msg}', ['msg' => $e->getMessage()]);

            return $this->response->setStatusCode(500)
                ->setJSON(['success' => false, 'message' => 'AI 카피 생성에 실패했습니다.', 'csrf' => csrf_hash()]);
        }

        // CSRF 토큰이 매 요청마다 회전($regenerate=true)하므로 새 토큰을 함께 반환해
        // 클라이언트가 폼 hidden 필드·메타 태그를 갱신하도록 한다(이후 폼 제출 보호).
        return $this->response->setJSON([
            'success' => true,
            'titles'  => $result['titles'],
            'detail'  => $result['detail'],
            'csrf'    => csrf_hash(),
        ]);
    }

    // ──────────────────────────────────────────────
    // 내부 헬퍼
    // ──────────────────────────────────────────────

    /**
     * 리치 텍스트 HTML 정화 — 허용 태그만 남기고 모든 속성 제거 (XSS 방지).
     * Tiptap 에디터가 제출하는 상세문구·상태변경 메모를 저장 전에 화이트리스트 필터한다.
     */
    private function sanitizeDetailHtml(string $html): string
    {
        $stripped = strip_tags($html, '<p><br><strong><em><s><ul><ol><li><h3><h4><blockquote>');
        $clean    = preg_replace('/<(\w+)[^>]*>/', '<$1>', $stripped);

        return trim($clean ?? $stripped);
    }

    /**
     * 의료광고 심의 사전검사 실행 — 설정 토글이 켜져 있을 때만 동기로 1회 수행.
     *
     * 본 프로젝트는 별도 큐 인프라를 두지 않으므로 저장 시점에 직접 호출한다.
     * AI 호출 실패가 캠페인 등록/수정 자체를 막지 않도록 예외는 로깅만 한다.
     */
    private function runComplianceCheck(int $campaignId, int $reviewRequestId): void
    {
        if (! model(SettingModel::class)->enabled('compliance_check_enabled')) {
            return;
        }

        try {
            (new AiComplianceService())->check($campaignId, $reviewRequestId);
        } catch (Throwable $e) {
            log_message('error', '심의 사전검사 실패 [campaign {id}]: {msg}', [
                'id'  => $campaignId,
                'msg' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 캠페인 등록·수정 공통 검증 규칙 — 광고 타입별 필수 단가 필드를 동적으로 추가한다(이슈 #157).
     *
     * CPA=db_cost, CPM=cpm_price, CPC=cpc_price 만 필수이며, 프로모션·옵션은 별도 과금 단가가 없다.
     *
     * @return array<string, string>
     */
    private function campaignValidationRules(): array
    {
        $rules = [
            'ad_title'      => 'required|max_length[255]',
            'hospital_id'   => 'required|integer',
            'ad_type'       => 'required|in_list[1,2,3,4,5]',
            'ad_start_date' => 'required|valid_date[Y-m-d]',
            'ad_end_date'   => 'required|valid_date[Y-m-d]',
            'cost_type'     => 'required|in_list[1,2]',
            'channel'       => 'required|in_list[1,2]',
        ];

        $adType    = (int) $this->request->getPost('ad_type');
        $costField = CampaignModel::AD_TYPE_COST_FIELD[$adType] ?? null;
        if ($costField !== null) {
            $rules[$costField] = 'required|integer|greater_than[0]';
        }

        return $rules;
    }

    /** @return array<string, mixed> */
    private function buildCampaignData(): array
    {
        return [
            'ad_title'          => $this->request->getPost('ad_title'),
            'ad_detail_info'    => $this->sanitizeDetailHtml((string) ($this->request->getPost('ad_detail_info') ?? '')),
            'hospital_id'       => (int) $this->request->getPost('hospital_id'),
            'hospital_type'     => (int) ($this->request->getPost('hospital_type') ?? 1),
            'ad_type'           => (int) $this->request->getPost('ad_type'),
            'ad_start_date'     => $this->request->getPost('ad_start_date'),
            'ad_end_date'       => $this->request->getPost('ad_end_date'),
            'cost_type'         => (int) $this->request->getPost('cost_type'),
            'general_cost'      => (int) ($this->request->getPost('general_cost') ?? 0),
            'discount_cost'     => (int) ($this->request->getPost('discount_cost') ?? 0),
            'text_cost'         => $this->request->getPost('text_cost'),
            'db_cost'           => (int) ($this->request->getPost('db_cost') ?? 0),
            'cpm_price'         => (int) ($this->request->getPost('cpm_price') ?? 0),
            'cpc_price'         => (int) ($this->request->getPost('cpc_price') ?? 0),
            'category'          => (int) ($this->request->getPost('category') ?? 0),
            'exposure'          => (int) ($this->request->getPost('exposure') ?? 1),
            'contract_id'       => ((int) ($this->request->getPost('contract_id') ?? 0)) ?: null,
            'contract_order_id' => ((int) ($this->request->getPost('contract_order_id') ?? 0)) ?: null,
            'region'            => $this->request->getPost('region'),
            'keyword'           => $this->request->getPost('keyword'),
            'deliberation_code' => $this->request->getPost('deliberation_code'),
            'channel'           => (int) $this->request->getPost('channel'),
        ];
    }

    /**
     * 이미지 업로드 처리 — 업로드된 파일만 덮어씀
     *
     * @param array<string, mixed>|null $existing 기존 캠페인 데이터 (수정 시)
     * @return array<string, mixed>
     */
    private function handleImageUploads(?array $existing = null): array
    {
        $uploadPath = FCPATH . 'uploads/campaigns/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $result = [];

        foreach (['t1_image_name', 't2_image_name'] as $field) {
            $file = $this->request->getFile($field);
            if ($file !== null && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);
                $result[$field] = 'campaigns/' . $newName;
            } elseif ($existing !== null) {
                $result[$field] = $existing[$field] ?? null;
            }
        }

        // 상세 이미지 다중 업로드
        $dImages = $this->request->getFileMultiple('d_images');
        if (!empty($dImages)) {
            $paths = [];
            foreach ($dImages as $file) {
                if ($file !== null && $file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move($uploadPath, $newName);
                    $paths[] = 'campaigns/' . $newName;
                }
            }
            if (!empty($paths)) {
                $result['d_image_json'] = json_encode($paths);
            } elseif ($existing !== null) {
                $result['d_image_json'] = $existing['d_image_json'] ?? null;
            }
        } elseif ($existing !== null) {
            $result['d_image_json'] = $existing['d_image_json'] ?? null;
        }

        return $result;
    }
}
