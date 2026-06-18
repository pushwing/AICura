<?php

namespace App\Controllers\Admin;

use App\Models\CampaignModel;
use App\Models\CampaignHistoryModel;
use App\Models\CampaignTempModel;
use App\Models\HospitalModel;
use App\Models\ContractModel;
use CodeIgniter\HTTP\ResponseInterface;

class CampaignController extends BaseAdminController
{
    private CampaignModel $campaignModel;
    private CampaignHistoryModel $historyModel;
    private CampaignTempModel $tempModel;
    private HospitalModel $hospitalModel;
    private ContractModel $contractModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->campaignModel = model(CampaignModel::class);
        $this->historyModel  = model(CampaignHistoryModel::class);
        $this->tempModel     = model(CampaignTempModel::class);
        $this->hospitalModel = model(HospitalModel::class);
        $this->contractModel = model(ContractModel::class);
    }

    // ──────────────────────────────────────────────
    // 목록 (AG Grid)
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $params = [
            'status'   => $this->request->getGet('status') ?? '',
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
            'campaign'   => null,
            'hospitals'  => $this->hospitalModel->getActiveList(),
            'contracts'  => $this->contractModel->findAll(),
            'adTypes'    => CampaignModel::AD_TYPES,
            'channels'   => CampaignModel::CHANNELS,
        ]);
    }

    // ──────────────────────────────────────────────
    // 등록 처리
    // ──────────────────────────────────────────────

    public function create(): ResponseInterface
    {
        $rules = [
            'ad_title'    => 'required|max_length[255]',
            'hospital_id' => 'required|integer',
            'ad_type'     => 'required|in_list[1,2,3,4,5]',
            'ad_start_date' => 'required|valid_date[Y-m-d]',
            'ad_end_date'   => 'required|valid_date[Y-m-d]',
            'cost_type'     => 'required|in_list[1,2]',
            'channel'       => 'required|in_list[1,2]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->buildCampaignData();
        $data['status'] = 'pending';

        $imageData = $this->handleImageUploads();
        $data = array_merge($data, $imageData);

        $id = $this->campaignModel->insert($data, true);

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');
        $this->historyModel->record(
            (int) $id,
            'create',
            '',
            'pending',
            (int) ($authUser['id'] ?? 0)
        );

        return redirect()->to('/admin/campaigns/' . $id)
            ->with('success', '캠페인이 등록되었습니다.');
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

        return $this->render('admin/campaigns/form', [
            'campaign'  => $campaign,
            'hospitals' => $this->hospitalModel->getActiveList(),
            'contracts' => $this->contractModel->findAll(),
            'adTypes'   => CampaignModel::AD_TYPES,
            'channels'  => CampaignModel::CHANNELS,
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

        $rules = [
            'ad_title'      => 'required|max_length[255]',
            'hospital_id'   => 'required|integer',
            'ad_type'       => 'required|in_list[1,2,3,4,5]',
            'ad_start_date' => 'required|valid_date[Y-m-d]',
            'ad_end_date'   => 'required|valid_date[Y-m-d]',
            'cost_type'     => 'required|in_list[1,2]',
            'channel'       => 'required|in_list[1,2]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->buildCampaignData();

        $imageData = $this->handleImageUploads($campaign);
        $data = array_merge($data, $imageData);

        $this->campaignModel->update($id, $data);

        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');
        $this->historyModel->record(
            $id,
            'update',
            $campaign['status'],
            $campaign['status'],
            (int) ($authUser['id'] ?? 0)
        );

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
        $memo   = isset($body['memo']) ? (string) $body['memo'] : null;

        if (!in_array($action, ['approve', 'reject', 'end', 'reopen'], true)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => '유효하지 않은 액션입니다.']);
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
    // 내부 헬퍼
    // ──────────────────────────────────────────────

    /** @return array<string, mixed> */
    private function buildCampaignData(): array
    {
        return [
            'ad_title'          => $this->request->getPost('ad_title'),
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
        $uploadPath = WRITEPATH . 'uploads/campaigns/';
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
