<?php

use App\Services\IndexNowService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * IndexNow 키 엔드포인트 + 발행 트리거 피처 테스트 (이슈 #152)
 *
 * @internal
 */
final class IndexNowFeatureTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $refresh = true;
    protected $namespace;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();
    }

    /**
     * 키 미설정이면 /indexnow-key.txt 404
     */
    public function testKeyEndpoint404WhenUnset(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->get('indexnow-key.txt');
    }

    /**
     * 키 설정 시 /indexnow-key.txt 가 키를 평문 반환
     */
    public function testKeyEndpointServesKey(): void
    {
        $mock = $this->createMock(IndexNowService::class);
        $mock->method('key')->willReturn('testkey123');
        Services::injectMock('indexNowService', $mock);

        $result = $this->get('indexnow-key.txt');
        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertSame('testkey123', (string) $result->response()->getBody());
    }

    /**
     * 가이드 발행 시 IndexNow 제출 트리거
     */
    public function testPublishingGuideTriggersSubmit(): void
    {
        $mock = $this->createMock(IndexNowService::class);
        $mock->expects($this->once())
            ->method('submit')
            ->with($this->stringContains('/guides/'));
        Services::injectMock('indexNowService', $mock);

        service('guideService')->create([
            'title' => '가이드', 'slug' => 'g1', 'status' => 'published',
        ]);
    }

    /**
     * 임시저장(draft) 가이드는 제출하지 않음
     */
    public function testDraftGuideDoesNotTriggerSubmit(): void
    {
        $mock = $this->createMock(IndexNowService::class);
        $mock->expects($this->never())->method('submit');
        Services::injectMock('indexNowService', $mock);

        service('guideService')->create([
            'title' => '임시', 'slug' => 'g2', 'status' => 'draft',
        ]);
    }
}
