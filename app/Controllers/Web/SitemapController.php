<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * sitemap.xml 서빙 (이슈 #143)
 *
 * 정적 파일이 아닌 동적 생성 — 노출 이벤트 변동을 1시간 캐시로 반영한다.
 */
class SitemapController extends BaseController
{
    public function index(): ResponseInterface
    {
        $xml = Services::sitemapService()->xml();

        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->setBody($xml);
    }
}
