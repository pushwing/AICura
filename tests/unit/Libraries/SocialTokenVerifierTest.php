<?php

use App\Exceptions\AuthException;
use App\Libraries\Social\SocialTokenVerifier;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCURLRequest;
use Config\App;
use Config\Services;

/**
 * 소셜 로그인 토큰 검증기 단위 테스트 (이슈 #187)
 *
 * 실제 네트워크 호출 없이 MockCURLRequest 로 제공자 응답을 흉내내어
 * 응답 파싱·검증 실패 처리를 확인한다. 보안상 핵심은 "제공자가 보증한 uid 만
 * 신뢰"하는 것이므로, 정상 응답의 uid 추출과 비정상 응답의 거부를 검증한다.
 *
 * @internal
 */
final class SocialTokenVerifierTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Services::reset(true);
    }

    /**
     * curlrequest 서비스를 지정한 상태코드·본문을 반환하는 목으로 교체
     */
    private function mockCurl(int $status, string $body): void
    {
        $config = new App();

        $response = new Response($config);
        $response->setStatusCode($status);

        $mock = new MockCURLRequest($config, new URI(), $response, []);
        $mock->setOutput($body);

        Services::injectMock('curlrequest', $mock);
    }

    /**
     * Kakao 정상 응답 → id 를 uid(문자열)로, 프로필 필드를 매핑
     */
    public function testKakaoSuccessParsesUid(): void
    {
        $this->mockCurl(200, json_encode([
            'id'            => 1234567890,
            'kakao_account' => ['profile' => ['nickname' => '카카오', 'profile_image_url' => 'https://img/k.png']],
        ]));

        $profile = (new SocialTokenVerifier())->verify('kakao', 'valid-token');

        $this->assertSame('1234567890', $profile->uid);
        $this->assertSame('카카오', $profile->username);
        $this->assertSame('https://img/k.png', $profile->picture);
    }

    /**
     * Naver 정상 응답 → response.id 를 uid 로
     */
    public function testNaverSuccessParsesUid(): void
    {
        $this->mockCurl(200, json_encode([
            'resultcode' => '00',
            'message'    => 'success',
            'response'   => ['id' => 'naver-abc-1', 'nickname' => '네이버', 'profile_image' => 'https://img/n.png'],
        ]));

        $profile = (new SocialTokenVerifier())->verify('naver', 'valid-token');

        $this->assertSame('naver-abc-1', $profile->uid);
        $this->assertSame('네이버', $profile->username);
    }

    /**
     * 제공자가 401(만료·위조) 반환 → 검증 실패 예외
     */
    public function testRejectsNon2xx(): void
    {
        $this->mockCurl(401, json_encode(['msg' => 'invalid token', 'code' => -401]));

        try {
            (new SocialTokenVerifier())->verify('kakao', 'forged-token');
            $this->fail('예외가 발생해야 한다.');
        } catch (AuthException $e) {
            $this->assertSame('SOCIAL_AUTH_FAILED', $e->errorCode());
            $this->assertSame(401, $e->httpStatusCode());
        }
    }

    /**
     * 2xx 이지만 id 가 없는 응답 → 검증 실패 (uid 없이는 신뢰 불가)
     */
    public function testRejectsResponseWithoutId(): void
    {
        $this->mockCurl(200, json_encode(['kakao_account' => ['profile' => ['nickname' => 'noid']]]));

        $this->expectException(AuthException::class);
        (new SocialTokenVerifier())->verify('kakao', 'valid-token');
    }

    /**
     * JSON 이 아닌 본문 → 검증 실패
     */
    public function testRejectsNonJsonBody(): void
    {
        $this->mockCurl(200, '<html>error</html>');

        $this->expectException(AuthException::class);
        (new SocialTokenVerifier())->verify('naver', 'valid-token');
    }

    /**
     * 지원하지 않는 제공자 → VALIDATION_ERROR
     */
    public function testRejectsUnsupportedProvider(): void
    {
        try {
            (new SocialTokenVerifier())->verify('google', 'token');
            $this->fail('예외가 발생해야 한다.');
        } catch (AuthException $e) {
            $this->assertSame('VALIDATION_ERROR', $e->errorCode());
        }
    }

    /**
     * 빈 access_token → 검증 실패 (HTTP 호출 없이 즉시 거부)
     */
    public function testRejectsEmptyToken(): void
    {
        try {
            (new SocialTokenVerifier())->verify('kakao', '   ');
            $this->fail('예외가 발생해야 한다.');
        } catch (AuthException $e) {
            $this->assertSame('SOCIAL_AUTH_FAILED', $e->errorCode());
        }
    }
}
