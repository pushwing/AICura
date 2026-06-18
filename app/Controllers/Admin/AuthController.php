<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    public function login(): string|RedirectResponse
    {
        if (session()->get('admin_user')) {
            return redirect()->to('/admin/dashboard');
        }

        return view('admin/auth/login', [
            'title'     => 'AICura Admin 로그인',
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
        $user = model(UserModel::class)->findAdminForAuth($email);

        if ($user === null || !password_verify($password, (string) $user['password'])) {
            return redirect()->back()->withInput()->with('login_error', '이메일 또는 비밀번호가 올바르지 않습니다.');
        }

        unset($user['password']);

        session()->set('admin_user', $user);
        session()->regenerate();

        return redirect()->to('/admin/dashboard');
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();

        return redirect()->to('/admin/login');
    }
}
