<?php

namespace App\Controllers\Portal;

use App\Models\AdvertiserModel;
use App\Models\CallRequestModel;
use App\Models\ContractModel;

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
        }

        return $this->render('portal/dashboard/advertiser', [
            'pageTitle'     => '대시보드',
            'hasAdvertiser' => $advertiserId !== null,
            'agreed'        => $agreed,
            'contractName'  => $contractName,
            'totalCount'    => $totalCount,
            'newCount'      => $newCount,
        ]);
    }
}
