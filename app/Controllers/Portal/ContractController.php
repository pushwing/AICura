<?php

namespace App\Controllers\Portal;

use App\Models\AdvertiserModel;
use App\Models\ContractModel;
use App\Models\ContractOrderModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * 포털 계약 관리 (이슈 #32)
 *
 *   광고주(advertiser): 본인 계약 조회 + 계약 동의 + 수주계약(충전) 신청
 *   대행사(agency)    : 소유 광고주들의 계약 요약 조회 (읽기 전용)
 */
class ContractController extends BasePortalController
{
    private ContractModel $contractModel;
    private ContractOrderModel $orderModel;
    private AdvertiserModel $advertiserModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->contractModel   = model(ContractModel::class);
        $this->orderModel      = model(ContractOrderModel::class);
        $this->advertiserModel = model(AdvertiserModel::class);
    }

    public function index(): string
    {
        return $this->isAgency()
            ? $this->agencyIndex()
            : $this->advertiserIndex();
    }

    // ──────────────────────────────────────────────
    // 대행사 — 소유 광고주 계약 요약
    // ──────────────────────────────────────────────

    private function agencyIndex(): string
    {
        $advertisers = $this->advertiserModel
            ->select('id, hospital_id, hospital_name, status, contract_agreed_at')
            ->where('agency_user_id', $this->userId())
            ->orderBy('id', 'DESC')
            ->findAll();

        /** @var list<int> $hospitalIds */
        $hospitalIds = array_values(array_unique(array_map(
            static fn (array $a): int => (int) $a['hospital_id'],
            $advertisers
        )));

        $summary = $this->contractModel->getSummaryByHospitalIds($hospitalIds);

        $rows = array_map(static function (array $a) use ($summary): array {
            $s = $summary[(int) $a['hospital_id']] ?? ['order_count' => 0, 'total_price' => 0];
            $a['order_count'] = $s['order_count'];
            $a['total_price'] = $s['total_price'];
            $a['agreed']      = !empty($a['contract_agreed_at']);
            return $a;
        }, $advertisers);

        return $this->render('portal/contracts/agency_index', [
            'pageTitle' => '계약 관리',
            'rows'      => $rows,
        ]);
    }

    // ──────────────────────────────────────────────
    // 광고주 — 본인 계약 + 수주계약 목록
    // ──────────────────────────────────────────────

    private function advertiserIndex(): string
    {
        $advertiserId = $this->advertiserId();
        $hospitalId   = $this->hospitalId();

        $advertiser = $advertiserId !== null ? $this->advertiserModel->find($advertiserId) : null;
        $agreed     = $advertiser !== null && !empty($advertiser['contract_agreed_at']);

        $contract = $hospitalId !== null ? $this->contractModel->findByHospital($hospitalId) : null;

        $orders = [];
        if ($contract !== null) {
            $detail = $this->contractModel->getDetail((int) $contract['id']);
            /** @var list<array<string, mixed>> $rawOrders */
            $rawOrders = is_array($detail['orders'] ?? null) ? $detail['orders'] : [];

            // 수주계약별 잔액을 단일 쿼리로 일괄 집계 (N+1 방지)
            $balances = $this->orderModel->getBalancesByContract((int) $contract['id']);

            $orders = array_map(function (array $o) use ($balances): array {
                $o['balance']        = $balances[(int) $o['id']] ?? 0;
                $o['created_at_kst'] = !empty($o['created_at']) ? $this->toKst($o['created_at']) : '-';
                return $o;
            }, $rawOrders);
        }

        return $this->render('portal/contracts/advertiser_index', [
            'pageTitle'     => '계약 관리',
            'hasAdvertiser' => $advertiser !== null,
            'advertiser'    => $advertiser,
            'agreed'        => $agreed,
            'contract'      => $contract,
            'orders'        => $orders,
            'adTypeLabels'  => ContractOrderModel::AD_TYPE2_LABELS,
            'statusLabels'  => ContractOrderModel::STATUS_LABELS,
        ]);
    }

    // ──────────────────────────────────────────────
    // 광고주 — 계약 동의
    // ──────────────────────────────────────────────

    public function agree(): RedirectResponse
    {
        $this->requireAdvertiser();

        $advertiserId = $this->advertiserId();
        $hospitalId   = $this->hospitalId();
        if ($advertiserId === null || $hospitalId === null) {
            return redirect()->to('/portal/contracts')->with('error', '연결된 광고주 정보가 없습니다.');
        }

        $advertiser = $this->advertiserModel->find($advertiserId);
        if ($advertiser === null) {
            return redirect()->to('/portal/contracts')->with('error', '광고주 정보를 찾을 수 없습니다.');
        }

        // 경쟁 조건 방어: 조건부 UPDATE로 동의를 선점하고, 선점한 호출만 메인 계약을 생성한다 (이슈 #33)
        $agreed = $this->advertiserModel->agreeContract(
            $advertiserId,
            $hospitalId,
            (string) $advertiser['hospital_name']
        );

        if (!$agreed) {
            return redirect()->to('/portal/contracts')->with('error', '이미 계약에 동의하셨습니다.');
        }

        return redirect()->to('/portal/contracts')->with('success', '계약에 동의했습니다. 이제 광고 충전(수주계약)을 신청할 수 있습니다.');
    }

    // ──────────────────────────────────────────────
    // 광고주 — 수주계약(충전) 신청 폼
    // ──────────────────────────────────────────────

    public function orderNew(): string|RedirectResponse
    {
        $this->requireAdvertiser();

        $advertiserId = $this->advertiserId();
        $advertiser   = $advertiserId !== null ? $this->advertiserModel->find($advertiserId) : null;
        if ($advertiser === null || empty($advertiser['contract_agreed_at'])) {
            return redirect()->to('/portal/contracts')->with('error', '계약 동의 후 충전을 신청할 수 있습니다.');
        }

        return $this->render('portal/contracts/order_form', [
            'pageTitle'    => '광고 충전 신청',
            'adTypeLabels' => ContractOrderModel::AD_TYPE2_LABELS,
        ]);
    }

    // ──────────────────────────────────────────────
    // 광고주 — 수주계약(충전) 신청 처리
    // ──────────────────────────────────────────────

    public function orderCreate(): RedirectResponse
    {
        $this->requireAdvertiser();

        $advertiserId = $this->advertiserId();
        $hospitalId   = $this->hospitalId();
        $advertiser   = $advertiserId !== null ? $this->advertiserModel->find($advertiserId) : null;

        if ($advertiser === null || $hospitalId === null || empty($advertiser['contract_agreed_at'])) {
            return redirect()->to('/portal/contracts')->with('error', '계약 동의 후 충전을 신청할 수 있습니다.');
        }

        $rules = [
            'ad_type2'  => 'required|in_list[1,2,3,4,5]',
            'ad_price'  => 'required|integer|greater_than[0]|less_than_equal_to[50000000]',
            'pay_type'  => 'required|in_list[1,2]',
        ];
        $messages = [
            'ad_price' => [
                'greater_than'        => '충전 금액은 0보다 커야 합니다.',
                'less_than_equal_to'  => '충전 금액은 1회 5천만 원을 초과할 수 없습니다.',
            ],
        ];
        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $contract = $this->contractModel->findByHospital($hospitalId);
        if ($contract === null) {
            return redirect()->to('/portal/contracts')->with('error', '계약 정보를 찾을 수 없습니다.');
        }

        $this->orderModel->registerWithContract([
            'contract_id'   => (int) $contract['id'],
            'contract_type' => 2, // 기존 계약에 수주계약 추가 (parent_order_id 없음 → 이월 미발생)
            'hospital_id'   => $hospitalId,
            'hospital_name' => $advertiser['hospital_name'],
            'ad_type'       => 1,
            'ad_type2'      => (int) $this->request->getPost('ad_type2'),
            'ad_price'      => (int) $this->request->getPost('ad_price'),
            'contract_status' => 1,
            'pay_type'      => (int) $this->request->getPost('pay_type'),
        ]);

        return redirect()->to('/portal/contracts')
            ->with('success', '충전 신청이 접수되었습니다. 입금 확인 후 광고 잔액에 반영됩니다.');
    }
}
