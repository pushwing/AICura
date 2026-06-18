<?php

namespace App\Controllers\Admin;

use App\Models\ContractModel;
use App\Models\ContractOrderModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 계약 관리 컨트롤러
 *
 * 구조:
 *   contracts          - 병원당 1개 메인 계약
 *   contract_orders    - 추가계약건 (100만원, 200만원 등 N개)
 *   contract_order_connects - 매핑 테이블
 *   deposits           - 원장 (충전/소진/이월 이력)
 */
class ContractController extends BaseAdminController
{
    private ContractModel $contractModel;
    private ContractOrderModel $orderModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->contractModel = model(ContractModel::class);
        $this->orderModel    = model(ContractOrderModel::class);
    }

    // ──────────────────────────────────────────────
    // 메인 계약 목록 (병원별 계약 현황)
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $params = [
            'hospital_name' => $this->request->getGet('hospital_name') ?? '',
            'page'          => (int) ($this->request->getGet('page') ?? 1),
            'limit'         => 20,
        ];

        $result = $this->contractModel->getListWithOrders($params);

        return $this->render('admin/contracts/index', [
            'contracts'    => $result['list'],
            'total'        => $result['total'],
            'params'       => $params,
        ]);
    }

    // ──────────────────────────────────────────────
    // 메인 계약 상세 (수주계약건 목록 포함)
    // ──────────────────────────────────────────────

    public function show(int $id): string
    {
        $contract = $this->contractModel->getDetail($id);
        if ($contract === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('admin/contracts/show', ['contract' => $contract]);
    }

    // ──────────────────────────────────────────────
    // 수주계약 목록
    // ──────────────────────────────────────────────

    public function orders(): string
    {
        $params = [
            'hospital_name'   => $this->request->getGet('hospital_name') ?? '',
            'contract_status' => $this->request->getGet('contract_status') ?? '',
            'ad_type2'        => $this->request->getGet('ad_type2') ?? '',
            'page'            => (int) ($this->request->getGet('page') ?? 1),
            'limit'           => 20,
        ];

        $result = $this->orderModel->getList($params);

        return $this->render('admin/contracts/orders', [
            'orders' => $result['list'],
            'total'  => $result['total'],
            'params' => $params,
        ]);
    }

    // ──────────────────────────────────────────────
    // 수주계약 상세
    // ──────────────────────────────────────────────

    public function orderShow(int $id): string
    {
        $order = $this->orderModel->getDetail($id);
        if ($order === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('admin/contracts/order_show', ['order' => $order]);
    }

    // ──────────────────────────────────────────────
    // 수주계약 등록 폼
    // ──────────────────────────────────────────────

    public function orderNew(): string
    {
        return $this->render('admin/contracts/order_form', [
            'order'      => null,
            'contractId' => $this->request->getGet('contract_id'),
        ]);
    }

    // ──────────────────────────────────────────────
    // 수주계약 등록 처리
    // ──────────────────────────────────────────────

    public function orderCreate(): ResponseInterface
    {
        $rules = [
            'hospital_id'   => 'required|integer',
            'hospital_name' => 'required|max_length[255]',
            'contract_type' => 'required|in_list[1,2]',
            'ad_type'       => 'required|in_list[1,2]',
            'ad_type2'      => 'required|in_list[1,2,3,4,5]',
            'ad_price'      => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'hospital_id'      => (int) $this->request->getPost('hospital_id'),
            'hospital_name'    => $this->request->getPost('hospital_name'),
            'contract_type'    => (int) $this->request->getPost('contract_type'),
            'ad_type'          => (int) $this->request->getPost('ad_type'),
            'ad_type2'         => (int) $this->request->getPost('ad_type2'),
            'ad_price'         => (int) $this->request->getPost('ad_price'),
            'contract_status'  => 1,
            'pay_type'         => (int) ($this->request->getPost('pay_type') ?? 1),
            'tax_charge_name'  => $this->request->getPost('tax_charge_name'),
            'tax_charge_email' => $this->request->getPost('tax_charge_email'),
            'tax_business_no'  => $this->request->getPost('tax_business_no'),
            'memo'             => $this->request->getPost('memo'),
            'agency_user_id'   => (int) ($this->request->getPost('agency_user_id') ?? 0) ?: null,
            'manage_user_id'   => (int) ($this->request->getPost('manage_user_id') ?? 0) ?: null,
            'title'            => $this->request->getPost('title') ?? $this->request->getPost('hospital_name'),
            'contract_id'      => (int) ($this->request->getPost('contract_id') ?? 0),
            'parent_order_id'  => (int) ($this->request->getPost('parent_order_id') ?? 0) ?: null,
        ];

        $orderId = $this->orderModel->registerWithContract($data);

        return redirect()->to('/admin/contracts/orders/' . $orderId)
            ->with('success', '수주계약이 등록되었습니다.');
    }

    // ──────────────────────────────────────────────
    // 수주계약 수정 폼
    // ──────────────────────────────────────────────

    public function orderEdit(int $id): string
    {
        $order = $this->orderModel->getDetail($id);
        if ($order === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('admin/contracts/order_form', ['order' => $order]);
    }

    // ──────────────────────────────────────────────
    // 수주계약 수정 처리
    // ──────────────────────────────────────────────

    public function orderUpdate(int $id): ResponseInterface
    {
        $order = $this->orderModel->find($id);
        if ($order === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $updateData = array_filter([
            'tax_charge_name'       => $this->request->getPost('tax_charge_name'),
            'tax_charge_email'      => $this->request->getPost('tax_charge_email'),
            'tax_business_no'       => $this->request->getPost('tax_business_no'),
            'agency_company_name'   => $this->request->getPost('agency_company_name'),
            'agency_company_fee_rate' => $this->request->getPost('agency_company_fee_rate'),
            'memo'                  => $this->request->getPost('memo'),
        ], fn($v) => $v !== null && $v !== '');

        // 입금 전, 세금계산서 미발행인 경우에만 금액 수정 허용
        if (empty($order['deposit_date']) && empty($order['tax_issue_date'])) {
            $adPrice = $this->request->getPost('ad_price');
            if ($adPrice !== null && (int) $adPrice > 0) {
                $updateData['ad_price'] = (int) $adPrice;
            }
        }

        $this->orderModel->update($id, $updateData);

        return redirect()->to('/admin/contracts/orders/' . $id)
            ->with('success', '수정되었습니다.');
    }

    // ──────────────────────────────────────────────
    // 입금 확인 처리
    // ──────────────────────────────────────────────

    public function depositConfirm(int $id): ResponseInterface
    {
        /** @var array<string, mixed> $authUser */
        $authUser = session()->get('admin_user');
        $userId   = (int) ($authUser['id'] ?? 0);

        $order = $this->orderModel->find($id);
        if ($order === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!empty($order['deposit_date'])) {
            return redirect()->back()->with('error', '이미 입금 확인된 계약입니다.');
        }

        $this->orderModel->confirmDeposit($id, $userId);

        return redirect()->to('/admin/contracts/orders/' . $id)
            ->with('success', '입금이 확인되었습니다.');
    }
}
