<?php

namespace App\Controllers\Admin;

use App\Models\SettingModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Override;
use Psr\Log\LoggerInterface;

/**
 * 어드민 설정 (이슈 #63)
 *
 *   - 내 계정: 본인 정보(이름·전화번호) 수정 / 비밀번호 변경
 *   - 시스템 설정: 사이트 전역 환경설정 (key-value)
 *
 * 비밀번호 변경 흐름은 Portal\ProfileController(이슈 #49)와 동일한 패턴을 따른다.
 */
class SettingController extends BaseAdminController
{
    private UserModel $userModel;
    private SettingModel $settingModel;

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger,
    ): void {
        parent::initController($request, $response, $logger);
        $this->userModel    = model(UserModel::class);
        $this->settingModel = model(SettingModel::class);
    }

    public function index(): RedirectResponse|string
    {
        $user = $this->userModel->findWithPasswordById($this->adminUserId());
        if ($user === null) {
            return redirect()->to('/admin/dashboard')->with('error', '사용자 정보를 찾을 수 없습니다.');
        }
        unset($user['password']);

        return $this->render('admin/settings/index', [
            'user'        => $user,
            'settings'    => $this->settingModel->getMap(),
            'settingKeys' => SettingModel::KEYS,
        ]);
    }

    public function updateProfile(): RedirectResponse
    {
        $rules = [
            'username' => 'required|max_length[100]',
            'phone'    => 'permit_empty|max_length[30]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->to('/admin/settings')->with('error', $this->firstError());
        }

        $username = (string) $this->request->getPost('username');
        $phone    = (string) $this->request->getPost('phone');

        $this->userModel->update($this->adminUserId(), [
            'username' => $username,
            'phone'    => $phone !== '' ? $phone : null,
        ]);

        // 세션의 표시 이름 동기화 (상단바·사이드바 반영)
        $adminUser             = (array) session()->get('admin_user');
        $adminUser['username'] = $username;
        session()->set('admin_user', $adminUser);

        return redirect()->to('/admin/settings')->with('success', '내 정보가 변경되었습니다.');
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
        if (! $this->validate($rules, $messages)) {
            return redirect()->to('/admin/settings')->with('error', $this->firstError());
        }

        $user = $this->userModel->findWithPasswordById($this->adminUserId());
        if ($user === null) {
            return redirect()->to('/admin/dashboard')->with('error', '사용자 정보를 찾을 수 없습니다.');
        }

        $current = (string) $this->request->getPost('current_password');
        if (! password_verify($current, (string) $user['password'])) {
            return redirect()->to('/admin/settings')->with('error', '현재 비밀번호가 올바르지 않습니다.');
        }

        $this->userModel->update($this->adminUserId(), [
            'password' => password_hash((string) $this->request->getPost('new_password'), PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/admin/settings')->with('success', '비밀번호가 변경되었습니다.');
    }

    public function updateSystem(): RedirectResponse
    {
        $rules = [
            'site_name'   => 'permit_empty|max_length[255]',
            'admin_email' => 'permit_empty|valid_email|max_length[255]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->to('/admin/settings')->with('error', $this->firstError());
        }

        $values = [];

        foreach (array_keys(SettingModel::KEYS) as $key) {
            $values[$key] = (string) ($this->request->getPost($key) ?? '');
        }
        $this->settingModel->saveMany($values);

        return redirect()->to('/admin/settings')->with('success', '시스템 설정이 저장되었습니다.');
    }

    /**
     * 로그인한 어드민의 user id
     */
    private function adminUserId(): int
    {
        $adminUser = (array) session()->get('admin_user');

        return (int) ($adminUser['id'] ?? 0);
    }

    /**
     * 검증 실패 시 첫 번째 에러 메시지 (레이아웃의 error 플래시에 노출)
     */
    private function firstError(): string
    {
        $errors = $this->validator->getErrors();

        return $errors === [] ? '입력값을 확인해주세요.' : reset($errors);
    }
}
