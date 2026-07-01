<?php

namespace App\Controllers\Admin;

use Override;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use DateTime;
use DateTimeZone;
use App\Controllers\BaseController;

abstract class BaseAdminController extends BaseController
{
    /** @var array<string, mixed> */
    protected array $viewData = [];

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->viewData['authUser'] = session()->get('admin_user');
    }

    protected function toKst(string $datetime): string
    {
        $dt = new DateTime($datetime, new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone('Asia/Seoul'));
        return $dt->format('Y-m-d H:i');
    }

    /** @param array<string, mixed> $data */
    protected function render(string $view, array $data = []): string
    {
        $pageData = array_merge($this->viewData, $data);
        // 'content' 키는 레이아웃 슬롯 예약어 — $data 충돌을 방지하기 위해 마지막에 덮어씀
        return view('admin/layout/main', array_merge($pageData, [
            'content' => view($view, $pageData),
        ]));
    }
}
