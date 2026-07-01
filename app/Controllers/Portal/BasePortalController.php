<?php

namespace App\Controllers\Portal;

use Override;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use DateTime;
use DateTimeZone;
use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * 광고주·광고대행사 포털 공통 컨트롤러 (이슈 #32)
 *
 * 세션 키 portal_user 구조:
 *   id, email, username, user_type, is_agency_account
 *   role          'agency' | 'advertiser'
 *   advertiser_id 광고주 레코드 id (advertiser 전용, 없으면 null)
 *   hospital_id   병원 id          (advertiser 전용, 없으면 null)
 */
abstract class BasePortalController extends BaseController
{
    public const ROLE_AGENCY     = 'agency';
    public const ROLE_ADVERTISER = 'advertiser';

    /** @var array<string, mixed> */
    protected array $viewData = [];

    #[Override]
    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->viewData['portalUser'] = $this->portalUser();
        $this->viewData['role']       = $this->role();
    }

    /** @return array<string, mixed> */
    protected function portalUser(): array
    {
        $user = session()->get('portal_user');

        return is_array($user) ? $user : [];
    }

    protected function role(): string
    {
        return (string) ($this->portalUser()['role'] ?? '');
    }

    protected function userId(): int
    {
        return (int) ($this->portalUser()['id'] ?? 0);
    }

    protected function isAgency(): bool
    {
        return $this->role() === self::ROLE_AGENCY;
    }

    /** 광고주 본인의 병원 id (대행사이거나 미연결이면 null) */
    protected function hospitalId(): ?int
    {
        $id = $this->portalUser()['hospital_id'] ?? null;

        return $id !== null ? (int) $id : null;
    }

    /** 광고주 레코드 id (대행사이거나 미연결이면 null) */
    protected function advertiserId(): ?int
    {
        $id = $this->portalUser()['advertiser_id'] ?? null;

        return $id !== null ? (int) $id : null;
    }

    /** 대행사 전용 화면 가드 */
    protected function requireAgency(): void
    {
        if (!$this->isAgency()) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    /** 광고주 전용 화면 가드 */
    protected function requireAdvertiser(): void
    {
        if ($this->isAgency()) {
            throw PageNotFoundException::forPageNotFound();
        }
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

        // 'content' 키는 레이아웃 슬롯 예약어 — 마지막에 덮어씀
        return view('portal/layout/main', array_merge($pageData, [
            'content' => view($view, $pageData),
        ]));
    }
}
