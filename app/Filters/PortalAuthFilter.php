<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * 광고주·광고대행사 포털 세션 인증 (이슈 #32)
 *
 * 어드민(admin_user)과 별도 세션 키(portal_user)를 사용한다.
 */
class PortalAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        if (!session()->get('portal_user')) {
            return redirect()->to('/portal/login');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
