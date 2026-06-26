<?php

namespace App\Controllers\Portal;

use App\Models\ContractOrderModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * 포털 내 정보 관리 (이슈 #49)
 *
 *   - 본인 계정 정보(이름·전화번호) 수정 / 비밀번호 변경
 *   - 광고주: 연결된 대행사 정보 표시 (수주계약에 기록된 대행사 정보)
 */
class ProfileController extends BasePortalController
{
    private UserModel $userModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->userModel = model(UserModel::class);
    }

    public function index(): string|RedirectResponse
    {
        $user = $this->userModel->findWithPasswordById($this->userId());
        if ($user === null) {
            return redirect()->to('/portal/dashboard')->with('error', '사용자 정보를 찾을 수 없습니다.');
        }

        // 광고주만 — 연결된 대행사 정보 (수주계약 기준)
        $agency     = null;
        $hospitalId = $this->hospitalId();
        if (!$this->isAgency() && $hospitalId !== null) {
            $agency = model(ContractOrderModel::class)->findAgencyInfoByHospital($hospitalId);
        }

        return $this->render('portal/profile/edit', [
            'pageTitle' => '내 정보',
            'user'      => $user,
            'agency'    => $agency,
        ]);
    }

    public function update(): RedirectResponse
    {
        $rules = [
            'username' => 'required|max_length[100]',
            'phone'    => 'permit_empty|max_length[30]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = (string) $this->request->getPost('username');
        $phone    = (string) $this->request->getPost('phone');

        $this->userModel->update($this->userId(), [
            'username' => $username,
            'phone'    => $phone !== '' ? $phone : null,
        ]);

        // 세션의 표시 이름 동기화 (상단바·사이드바 반영)
        $portalUser             = $this->portalUser();
        $portalUser['username'] = $username;
        session()->set('portal_user', $portalUser);

        return redirect()->to('/portal/profile')->with('success', '내 정보가 변경되었습니다.');
    }

    public function updatePassword(): RedirectResponse
    {
        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]|max_length[255]',
            'confirm_password' => 'required|matches[new_password]',
        ];
        $messages = [
            'confirm_password' => ['matches' => '새 비밀번호 확인이 일치하지 않습니다.'],
            'new_password'     => ['min_length' => '비밀번호는 8자 이상이어야 합니다.'],
        ];
        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $user = $this->userModel->findWithPasswordById($this->userId());
        if ($user === null) {
            return redirect()->to('/portal/dashboard')->with('error', '사용자 정보를 찾을 수 없습니다.');
        }

        $current = (string) $this->request->getPost('current_password');
        if (!password_verify($current, (string) $user['password'])) {
            return redirect()->to('/portal/profile')->with('error', '현재 비밀번호가 올바르지 않습니다.');
        }

        $this->userModel->update($this->userId(), [
            'password' => password_hash((string) $this->request->getPost('new_password'), PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/portal/profile')->with('success', '비밀번호가 변경되었습니다.');
    }
}
