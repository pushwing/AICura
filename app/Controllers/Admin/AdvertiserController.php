<?php

namespace App\Controllers\Admin;

use App\Models\AdvertiserModel;
use App\Models\HospitalModel;
use CodeIgniter\HTTP\ResponseInterface;

class AdvertiserController extends BaseAdminController
{
    private AdvertiserModel $advertiserModel;
    private HospitalModel $hospitalModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->advertiserModel = model(AdvertiserModel::class);
        $this->hospitalModel   = model(HospitalModel::class);
    }

    // ──────────────────────────────────────────────
    // 광고주 목록
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $params = [
            'hospital_name' => $this->request->getGet('hospital_name') ?? '',
            'status'        => $this->request->getGet('status') ?? '',
            'is_network'    => $this->request->getGet('is_network') ?? '',
            'page'          => (int) ($this->request->getGet('page') ?? 1),
            'limit'         => 20,
        ];

        $result = $this->advertiserModel->getList($params);

        // Fix #6: toKst() 헬퍼로 KST 변환 통합
        $advertisers = array_map(function (array $row): array {
            $row['created_at_kst'] = !empty($row['created_at']) ? $this->toKst($row['created_at']) : '-';
            return $row;
        }, $result['list']);

        return $this->render('admin/advertisers/index', [
            'advertisers' => $advertisers,
            'total'       => $result['total'],
            'params'      => $params,
        ]);
    }

    // ──────────────────────────────────────────────
    // 광고주 상세 (계약 목록 + KPI 포함)
    // ──────────────────────────────────────────────

    public function show(int $id): string
    {
        $advertiser = $this->advertiserModel->getDetail($id);
        if ($advertiser === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Fix #6: toKst() 헬퍼로 KST 변환 통합
        $advertiser['created_at_kst'] = !empty($advertiser['created_at']) ? $this->toKst($advertiser['created_at']) : '-';

        /** @var list<array<string, mixed>> $contracts */
        $contracts = is_array($advertiser['contracts'] ?? null) ? $advertiser['contracts'] : [];
        $advertiser['contracts'] = array_map(function (array $c): array {
            $c['created_at_kst'] = !empty($c['created_at']) ? $this->toKst($c['created_at']) : '-';
            return $c;
        }, $contracts);

        return $this->render('admin/advertisers/show', ['advertiser' => $advertiser]);
    }

    // ──────────────────────────────────────────────
    // 광고주 등록 폼
    // ──────────────────────────────────────────────

    // Fix #4: new() → newForm() (new는 PHP 예약어)
    public function newForm(): string
    {
        return $this->render('admin/advertisers/form', [
            'advertiser'        => null,
            'hospitals'         => $this->hospitalModel->getActiveList(),
            'parentAdvertisers' => $this->advertiserModel->select('id, hospital_name')
                ->where('is_network', 1)
                ->where('status', 1)
                ->orderBy('hospital_name', 'ASC')
                ->findAll(),
        ]);
    }

    // ──────────────────────────────────────────────
    // 광고주 등록 처리
    // ──────────────────────────────────────────────

    public function create(): ResponseInterface
    {
        // Fix #3: hospital_id 존재 여부 DB 검증 추가
        $rules = [
            'hospital_id'   => 'required|integer|greater_than[0]|is_not_unique[hospitals.id]',
            'hospital_name' => 'required|max_length[255]',
            'contact_email' => 'permit_empty|valid_email',
            'is_network'    => 'required|in_list[0,1,2]',
            'status'        => 'required|in_list[1,2,3]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $isNetwork = (int) $this->request->getPost('is_network');

        // Fix #2: is_network === 2 일 때만 parent 설정, 미선택(0·빈값)은 null
        $networkParentId = null;
        if ($isNetwork === 2) {
            $raw             = (int) $this->request->getPost('network_parent_id');
            $networkParentId = $raw > 0 ? $raw : null;
        }

        $data = [
            'hospital_id'       => (int) $this->request->getPost('hospital_id'),
            'hospital_name'     => $this->request->getPost('hospital_name'),
            'contact_name'      => $this->request->getPost('contact_name') ?: null,
            'contact_email'     => $this->request->getPost('contact_email') ?: null,
            'contact_phone'     => $this->request->getPost('contact_phone') ?: null,
            'business_no'       => $this->request->getPost('business_no') ?: null,
            'is_network'        => $isNetwork,
            'network_parent_id' => $networkParentId,
            'status'            => (int) $this->request->getPost('status'),
        ];

        $id = $this->advertiserModel->insert($data);
        if ($id === false) {
            return redirect()->back()->withInput()->with('errors', $this->advertiserModel->errors());
        }

        return redirect()->to('/admin/advertisers/' . $id)
            ->with('success', '광고주가 등록되었습니다.');
    }

    // ──────────────────────────────────────────────
    // 광고주 수정 폼
    // ──────────────────────────────────────────────

    public function edit(int $id): string
    {
        $advertiser = $this->advertiserModel->find($id);
        if ($advertiser === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('admin/advertisers/form', [
            'advertiser'        => $advertiser,
            'hospitals'         => $this->hospitalModel->getActiveList(),
            'parentAdvertisers' => $this->advertiserModel->select('id, hospital_name')
                ->where('is_network', 1)
                ->where('status', 1)
                ->orderBy('hospital_name', 'ASC')
                ->findAll(),
        ]);
    }

    // ──────────────────────────────────────────────
    // 광고주 수정 처리
    // ──────────────────────────────────────────────

    public function update(int $id): ResponseInterface
    {
        $advertiser = $this->advertiserModel->find($id);
        if ($advertiser === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'hospital_name' => 'required|max_length[255]',
            'contact_email' => 'permit_empty|valid_email',
            'is_network'    => 'required|in_list[0,1,2]',
            'status'        => 'required|in_list[1,2,3]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $isNetwork = (int) $this->request->getPost('is_network');

        // Fix #2: is_network === 2 일 때만 parent 설정, 미선택(0·빈값)은 null
        $networkParentId = null;
        if ($isNetwork === 2) {
            $raw             = (int) $this->request->getPost('network_parent_id');
            $networkParentId = $raw > 0 ? $raw : null;
        }

        $updateData = [
            'hospital_name'     => $this->request->getPost('hospital_name'),
            'contact_name'      => $this->request->getPost('contact_name') ?: null,
            'contact_email'     => $this->request->getPost('contact_email') ?: null,
            'contact_phone'     => $this->request->getPost('contact_phone') ?: null,
            'business_no'       => $this->request->getPost('business_no') ?: null,
            'is_network'        => $isNetwork,
            'network_parent_id' => $networkParentId,
            'status'            => (int) $this->request->getPost('status'),
        ];

        if ($this->advertiserModel->update($id, $updateData) === false) {
            return redirect()->back()->withInput()->with('errors', $this->advertiserModel->errors());
        }

        return redirect()->to('/admin/advertisers/' . $id)
            ->with('success', '수정되었습니다.');
    }
}
