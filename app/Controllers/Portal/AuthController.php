<?php

namespace App\Controllers\Portal;

use App\Controllers\BaseController;
use App\Models\AdvertiserModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * 광고주·광고대행사 포털 로그인 (이슈 #32)
 *
 * 어드민과 별도 세션(portal_user)을 사용하며, 로그인 시 역할을 판별한다.
 *   - is_agency_account = 1 → 광고대행사(agency)
 *   - 그 외(병원 유형)        → 광고주(advertiser)
 */
class AuthController extends BaseController
{
    public function login(): string|RedirectResponse
    {
        if (session()->get('portal_user')) {
            return redirect()->to('/portal/dashboard');
        }

        return view('portal/auth/login', [
            'title'     => 'AICura 포털 로그인',
            'old_email' => old('email', ''),
            'error'     => session()->getFlashdata('login_error'),
        ]);
    }

    public function loginProcess(): RedirectResponse
    {
        $rules = [
            'email'    => 'required|valid_email|max_length[255]',
            'password' => 'required|min_length[1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('login_error', '이메일과 비밀번호를 입력해주세요.');
        }

        $email    = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        /** @var array<string, mixed>|null $user */
        $user = model(UserModel::class)->findPortalForAuth($email);

        // 사용자가 없을 때도 password_verify를 실행해 응답 시간을 일정하게 유지 (이메일 열거 방지)
        $hash  = ($user !== null) ? (string) $user['password'] : '$2y$12$invaliddummyhashfortimingXXXXXXXXXXXXXXXXXXXXXXXXXXX';
        $valid = password_verify($password, $hash);

        if ($user === null || !$valid) {
            return redirect()->back()->withInput()->with('login_error', '이메일 또는 비밀번호가 올바르지 않습니다.');
        }

        $role = ((int) ($user['is_agency_account'] ?? 0) === 1)
            ? BasePortalController::ROLE_AGENCY
            : BasePortalController::ROLE_ADVERTISER;

        $portalUser = [
            'id'                => (int) $user['id'],
            'email'             => $user['email'],
            'username'          => $user['username'],
            'user_type'         => (int) $user['user_type'],
            'is_agency_account' => (int) ($user['is_agency_account'] ?? 0),
            'role'              => $role,
            'advertiser_id'     => null,
            'hospital_id'       => null,
        ];

        // 광고주: 본인 로그인 계정에 연결된 광고주 레코드 해석
        if ($role === BasePortalController::ROLE_ADVERTISER) {
            $advertiser = model(AdvertiserModel::class)->findByOwner((int) $user['id']);
            if ($advertiser !== null) {
                $portalUser['advertiser_id'] = (int) $advertiser['id'];
                $portalUser['hospital_id']   = (int) $advertiser['hospital_id'];
            }
        }

        session()->set('portal_user', $portalUser);
        session()->regenerate();

        return redirect()->to('/portal/dashboard');
    }

    public function logout(): RedirectResponse
    {
        session()->remove('portal_user');

        return redirect()->to('/portal/login');
    }
}
