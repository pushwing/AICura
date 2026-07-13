<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * App 설정의 개발용 Host 허용 판별 단위 테스트 (이슈 #130)
 *
 * 이미지 URL 등 base_url() 결과가 요청 Host 에 묶여, 앱(에뮬레이터·실기기)이
 * 접근 불가한 localhost 로 내려오던 문제를 해결하기 위해, 개발 환경에서
 * 사설/로컬 호스트 + 개발 포트 조합만 baseURL 로 채택하도록 허용 판별을 도입한다.
 * Host header injection 방지를 위해 패턴 밖의 Host 는 반드시 거부해야 한다.
 *
 * @internal
 */
final class AppConfigDevHostTest extends CIUnitTestCase
{
    #[DataProvider('provideAllowsLocalAndDeviceHosts')]
    public function testAllowsLocalAndDeviceHosts(string $host): void
    {
        $this->assertTrue(App::isAllowedDevHost($host), "허용되어야 함: {$host}");
    }

    /**
     * 허용되어야 하는 Host — 로컬·에뮬레이터·실기기(LAN) + 개발 포트(8080·8300).
     *
     * @return list<array{string}>
     */
    public static function provideAllowsLocalAndDeviceHosts(): iterable
    {
        return [
            ['localhost:8300'],
            ['localhost:8080'],
            ['127.0.0.1:8300'],
            ['127.0.0.1:8080'],
            ['10.0.2.2:8300'],   // Android 에뮬레이터 → 호스트 PC (이슈 #130 핵심 케이스)
            ['10.0.2.2:8080'],
            ['192.168.0.10:8300'], // 실기기 LAN
            ['192.168.1.255:8080'],
        ];
    }

    #[DataProvider('provideRejectsUnsafeHosts')]
    public function testRejectsUnsafeHosts(string $host): void
    {
        $this->assertFalse(App::isAllowedDevHost($host), "거부되어야 함: {$host}");
    }

    /**
     * 거부되어야 하는 Host — 외부 도메인·비허용 포트·형식 오류(injection 방어).
     *
     * @return list<array{string}>
     */
    public static function provideRejectsUnsafeHosts(): iterable
    {
        return [
            [''],
            ['evil.com:8300'],
            ['localhost:9999'],
            ['10.0.2.2:80'],
            ['192.168.0.10:3000'],
            ['localhost'],                // 포트 없음
            ['localhost:8300.evil.com'],  // 접미 injection
            ['evil.localhost:8300'],      // 접두 injection
            ['192.168.0:8300'],           // 불완전한 IP
            ['10.0.2.2:8300 evil'],       // 공백 뒤 페이로드
        ];
    }
}
