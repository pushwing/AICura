<?php

namespace App\Controllers\Api\V1;

use App\Libraries\Auth;
use CodeIgniter\RESTful\ResourceController;

abstract class BaseApiController extends ResourceController
{
    protected $format = 'json';

    protected function authUserId(): int
    {
        return Auth::userId();
    }

    /** @param array<string, mixed> $meta */
    protected function success(mixed $data, array $meta = [], int $status = 200): \CodeIgniter\HTTP\ResponseInterface
    {
        $body = ['status' => 'success', 'data' => $data];

        if (!empty($meta)) {
            $body['meta'] = $meta;
        }

        return $this->respond($body, $status);
    }

    protected function error(string $code, string $message, int $status = 400): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->respond([
            'status'  => 'error',
            'code'    => $code,
            'message' => $message,
        ], $status);
    }
}
