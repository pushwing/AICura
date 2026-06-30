<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * 공개 SSR 웹 컨트롤러 베이스 (이슈 #137 — SEO/GEO)
 *
 * 인증 없이 HTML을 반환하는 공개 페이지의 공통 렌더링과 SEO 메타 기본값을 담당한다.
 * 비즈니스 로직은 Service 에 위임하고, 컨트롤러는 입력 정규화·뷰 렌더만 수행한다(얇은 컨트롤러).
 * 공개 페이지는 비로그인 컨텍스트이므로 Service 호출 시 userId 는 0 을 넘긴다.
 */
abstract class BaseWebController extends BaseController
{
    /** 공개 페이지 비로그인 사용자 ID (찜 여부 오버레이용 — 항상 비활성) */
    protected const GUEST_USER_ID = 0;

    /**
     * SEO 메타 기본값. 컨트롤러가 페이지별로 부분 덮어쓴다.
     *
     * @return array<string, mixed>
     */
    protected function defaultMeta(): array
    {
        return [
            'title'       => 'AICura — 성형·시술 이벤트 플랫폼',
            'description' => 'AICura에서 검증된 성형·시술 이벤트와 병원, 실사용 후기를 확인하세요.',
            'canonical'   => current_url(),
            'robots'      => 'index, follow', // 색인 허용 전제 (이슈 #137 결정사항)
            'og_type'     => 'website',
            'og_image'    => null,
        ];
    }

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
    }

    /**
     * 공개 레이아웃으로 뷰 렌더.
     *
     * @param array<string, mixed> $data 뷰 데이터
     * @param array<string, mixed> $meta SEO 메타 부분 덮어쓰기
     */
    protected function render(string $view, array $data = [], array $meta = []): string
    {
        $pageData = array_merge($data, ['meta' => array_merge($this->defaultMeta(), $meta)]);

        // 'content' 키는 레이아웃 슬롯 예약어 — 마지막에 덮어써 충돌 방지 (Admin 레이아웃 패턴 동일)
        return view('web/layout/main', array_merge($pageData, [
            'content' => view($view, $pageData),
        ]));
    }
}
