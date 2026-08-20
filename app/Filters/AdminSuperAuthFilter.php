<?php

declare(strict_types=1);

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class AdminSuperAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $adminUser = session()->get('admin_user');
        if (! is_array($adminUser) || (int) ($adminUser['user_type'] ?? 0) !== UserModel::TYPE_ADMIN) {
            return service('response')->setStatusCode(403)->setBody('권한이 없습니다.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
