<?php

namespace App\Controllers\Portal;

use App\Models\AdvertiserOwnerInviteModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Override;
use Psr\Log\LoggerInterface;

/**
 * 광고주 owner 연결 초대 응답 (이슈 #38)
 *
 * 광고주(병원유형) 계정이 대행사로부터 받은 연결 초대를 수락·거절한다.
 * 수락 시에만 advertisers.owner_user_id 가 확정되며, 로그인 세션도 즉시 갱신한다.
 */
class InviteController extends BasePortalController
{
    private AdvertiserOwnerInviteModel $inviteModel;

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->inviteModel = model(AdvertiserOwnerInviteModel::class);
    }

    public function accept(int $id): RedirectResponse
    {
        $this->requireAdvertiser();

        $result = $this->inviteModel->acceptInvite($id, $this->userId());

        if (! $result['ok']) {
            $message = $result['reason'] === 'already_linked'
                ? '이미 다른 계정이 해당 광고주에 연결되어 초대를 수락할 수 없습니다.'
                : '유효하지 않거나 만료된 초대입니다.';

            return redirect()->to('/portal/dashboard')->with('error', $message);
        }

        // 세션의 광고주 연결 정보 즉시 갱신 — 재로그인 없이 광고주 화면 이용 가능
        $portalUser                  = $this->portalUser();
        $portalUser['advertiser_id'] = $result['advertiser_id'];
        $portalUser['hospital_id']   = $result['hospital_id'];
        session()->set('portal_user', $portalUser);

        return redirect()->to('/portal/dashboard')
            ->with('success', '광고주 연결을 수락했습니다. 계약 동의 후 광고 서비스를 이용할 수 있습니다.');
    }

    public function reject(int $id): RedirectResponse
    {
        $this->requireAdvertiser();

        $ok = $this->inviteModel->rejectInvite($id, $this->userId());

        return redirect()->to('/portal/dashboard')
            ->with($ok ? 'success' : 'error', $ok ? '초대를 거절했습니다.' : '유효하지 않거나 이미 처리된 초대입니다.');
    }
}
