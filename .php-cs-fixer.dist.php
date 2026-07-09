<?php

declare(strict_types=1);

use CodeIgniter\CodingStandard\CodeIgniter4;
use Nexus\CsConfig\Factory;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

// 검사·정리 대상 경로 (Views 제외 — PHPStan 규칙과 동일 기준)
$finder = Finder::create()
    ->files()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/tests',
    ])
    ->exclude([
        'Views', // CI4 뷰는 대체 구문 사용으로 포매팅 대상에서 제외
    ])
    ->notName('#Foobar.php$#')
    ->append([
        __FILE__,
    ]);

$overrides = [
    // assertEquals → assertSame 자동 변환 비활성화.
    // JSON 직렬화는 5.0 을 5 로 만들고 json_decode 는 이를 int 로 되돌리므로,
    // 실수/정수 타입을 무시하는 느슨한 assertEquals 가 의도된 곳이 있다.
    // (예: WebJsonLdFeatureTest 의 ratingValue 단언) — 강제 변환 시 테스트가 깨진다.
    'php_unit_strict' => false,
];

$options = [
    'finder'     => $finder,
    'cacheFile'  => 'build/.php-cs-fixer.cache',
    'usingCache' => true,
];

return Factory::create(new CodeIgniter4(), $overrides, $options)
    ->forProjects()
    ->setParallelConfig(ParallelConfigFactory::detect())
    // 웹 요청은 PHP 8.5 로 처리하지만 composer.json 최소 요구가 8.4 라
    // "unsupported PHP version" 경고가 뜬다. CI4 룰셋은 8.5 전용 구문을
    // 생성하지 않으므로 명시적으로 허용한다.
    ->setUnsupportedPhpVersionAllowed(true);
