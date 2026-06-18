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

        return $this->render('admin/advertisers/index', [
            'advertisers' => $result['list'],
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

        return $this->render('admin/advertisers/show', ['advertiser' => $advertiser]);
    }

    // ──────────────────────────────────────────────
    // 광고주 등록 폼
    // ──────────────────────────────────────────────

    public function new(): string
    {
        return $this->render('admin/advertisers/form', [
            'advertiser' => null,
            'hospitals'  => $this->hospitalModel->getActiveList(),
        ]);
    }

    // ──────────────────────────────────────────────
    // 광고주 등록 처리
    // ──────────────────────────────────────────────

    public function create(): ResponseInterface
    {
        $rules = [
            'hospital_id'   => 'required|integer|greater_than[0]',
            'hospital_name' => 'required|max_length[255]',
            'contact_email' => 'permit_empty|valid_email',
            'is_network'    => 'required|in_list[0,1,2]',
            'status'        => 'required|in_list[1,2,3]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $isNetwork       = (int) $this->request->getPost('is_network');
        $networkParentId = (int) ($this->request->getPost('network_parent_id') ?? 0) ?: null;

        if ($isNetwork !== 2) {
            $networkParentId = null;
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
            'advertiser' => $advertiser,
            'hospitals'  => $this->hospitalModel->getActiveList(),
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

        $isNetwork       = (int) $this->request->getPost('is_network');
        $networkParentId = (int) ($this->request->getPost('network_parent_id') ?? 0) ?: null;

        if ($isNetwork !== 2) {
            $networkParentId = null;
        }

        $updateData = array_filter([
            'hospital_name'     => $this->request->getPost('hospital_name'),
            'contact_name'      => $this->request->getPost('contact_name') ?: null,
            'contact_email'     => $this->request->getPost('contact_email') ?: null,
            'contact_phone'     => $this->request->getPost('contact_phone') ?: null,
            'business_no'       => $this->request->getPost('business_no') ?: null,
            'is_network'        => $isNetwork,
            'network_parent_id' => $networkParentId,
            'status'            => (int) $this->request->getPost('status'),
        ], fn($v) => $v !== null && $v !== '');

        $this->advertiserModel->update($id, $updateData);

        return redirect()->to('/admin/advertisers/' . $id)
            ->with('success', '수정되었습니다.');
    }
}
