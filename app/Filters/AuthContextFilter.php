<?php

declare(strict_types=1);

namespace App\Filters;

use App\Libraries\Auth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

final class AuthContextFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        Auth::clear();

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        Auth::clear();

        return null;
    }
}
