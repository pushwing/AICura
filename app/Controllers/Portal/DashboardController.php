<?php

namespace App\Controllers\Portal;

use App\Models\AdvertiserModel;
use App\Models\AdvertiserOwnerInviteModel;
use App\Models\CallRequestModel;
use App\Models\ContractModel;
use App\Models\ContractOrderModel;

/**
 * 포털 대시보드 — 역할(agency/advertiser)에 따라 다른 요약을 노출 (이슈 #32)
 */
class DashboardController extends BasePortalController
{
    public function index(): string
    {
        return $this->isAgency()
            ? $this->agencyDashboard()
            : $this->advertiserDashboard();
    }

    private function agencyDashboard(): string
    {
        $advertiserModel = model(AdvertiserModel::class);

        $all     = $advertiserModel->getListByAgency($this->userId(), ['limit' => 5]);
        $pending = $advertiserModel->where('agency_user_id', $this->userId())
            ->where('contract_agreed_at IS NULL', null, false)
            ->countAllResults();

        $stats = [
            'total'   => $all['total'],
            'pending' => $pending,
            'agreed'  => $all['total'] - $pending,
        ];

        $recent = array_map(function (array $row): array {
            $row['created_at_kst'] = !empty($row['created_at']) ? $this->toKst($row['created_at']) : '-';
            return $row;
        }, $all['list']);

        return $this->render('portal/dashboard/agency', [
            'pageTitle' => '대시보드',
            'stats'     => $stats,
            'recent'    => $recent,
        ]);
    }

    private function advertiserDashboard(): string
    {
        $hospitalId   = $this->hospitalId();
        $advertiserId = $this->advertiserId();

        $agreed       = false;
        $contractName = null;
        $newCount     = 0;
        $totalCount   = 0;
        $ledger       = ['charged' => 0, 'used' => 0, 'balance' => 0];

        if ($advertiserId !== null) {
            $advertiser = model(AdvertiserModel::class)->find($advertiserId);
            $agreed     = $advertiser !== null && !empty($advertiser['contract_agreed_at']);
        }

        if ($hospitalId !== null) {
            $contract     = model(ContractModel::class)->findByHospital($hospitalId);
            $contractName = $contract['title'] ?? null;

            $callModel  = model(CallRequestModel::class);
            $totalCount = $callModel->where('hospital_id', $hospitalId)->where('is_delete', 0)->countAllResults();
            $newCount   = $callModel->where('hospital_id', $hospitalId)->where('is_delete', 0)->where('status', 1)->countAllResults();

            // 병원 전체 충전금/소진/잔액 요약 (이슈 #49)
            $ledger = model(ContractOrderModel::class)->getHospitalLedgerSummary($hospitalId);
        }

        // 수신한 owner 연결 초대 (이슈 #38) — owner는 1:1이라 이미 연결된 광고주는 유효 초대가 없으므로 미연결 시에만 조회
        $invites = [];
        if ($advertiserId === null) {
            $invites = array_map(function (array $row): array {
                $row['expires_at_kst'] = !empty($row['expires_at']) ? $this->toKst($row['expires_at']) : '';
                return $row;
            }, model(AdvertiserOwnerInviteModel::class)->findPendingForInvitee($this->userId()));
        }

        return $this->render('portal/dashboard/advertiser', [
            'pageTitle'     => '대시보드',
            'hasAdvertiser' => $advertiserId !== null,
            'agreed'        => $agreed,
            'contractName'  => $contractName,
            'totalCount'    => $totalCount,
            'newCount'      => $newCount,
            'ledger'        => $ledger,
            'invites'       => $invites,
        ]);
    }
}
