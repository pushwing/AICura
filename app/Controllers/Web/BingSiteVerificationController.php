<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Bing Webmaster Tools 사이트 인증 파일 서빙 (이슈 #160)
 *
 * ChatGPT Search 는 Bing 색인에 의존하므로 Bing Webmaster Tools 사이트 등록이 노출의 전제조건이다.
 * 등록 시 발급되는 인증 코드를 `GET /BingSiteAuth.xml` 로 서빙해 소유권을 증명한다.
 * 키는 .env(BING_SITE_VERIFICATION)에 두며, 미설정 시 404.
 */
class BingSiteVerificationController extends BaseController
{
    public function index(): ResponseInterface
    {
        $code = (string) env('BING_SITE_VERIFICATION', '');
        if ($code === '') {
            throw PageNotFoundException::forPageNotFound();
        }

        $xml = '<?xml version="1.0"?>' . "\n"
            . '<users>' . "\n"
            . '    <user>' . $code . '</user>' . "\n"
            . '</users>';

        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->setBody($xml);
    }
}
