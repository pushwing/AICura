<?php
/**
 * 500 Internal Server Error — CodeIgniter4 가 $code, $message 를 넘겨준다.
 * Config/Exceptions.php 의 $views[500] 에 이 파일을 매핑해야 사용된다.
 * 운영 환경에서는 상세 예외 메시지를 출력하지 않는다.
 */
$aivCode    = $code ?? 500;
$aivHeading = '일시적인 오류가 발생했습니다';
$aivMessage = ENVIRONMENT !== 'production' && ! empty($message)
    ? $message
    : '서버에서 문제가 발생했습니다. 잠시 후 다시 시도해 주세요.';

include __DIR__ . '/_partials/page.php';
