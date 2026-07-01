<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * IndexNow 키 검증 파일 서빙 (이슈 #152)
 *
 * IndexNow 는 제출한 키의 소유를 증명하기 위해 keyLocation 에서 키 문자열을 확인한다.
 * `GET /indexnow-key.txt` 로 .env(INDEXNOW_KEY) 값을 평문으로 노출한다(키 미설정 시 404).
 */
class IndexNowController extends BaseController
{
    public function key(): ResponseInterface
    {
        $key = Services::indexNowService()->key();
        if ($key === '') {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setBody($key);
    }
}
