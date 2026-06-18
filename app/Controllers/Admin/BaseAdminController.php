<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

abstract class BaseAdminController extends BaseController
{
    /** @var array<string, mixed> */
    protected array $viewData = [];

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->viewData['authUser'] = session()->get('admin_user');
    }

    /** @param array<string, mixed> $data */
    protected function render(string $view, array $data = []): string
    {
        return view($view, array_merge($this->viewData, $data));
    }
}
