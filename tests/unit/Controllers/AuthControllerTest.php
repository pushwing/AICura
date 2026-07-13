<?php

use CodeIgniter\Security\Security;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/**
 * @internal
 */
final class AuthControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace;

    protected function setUp(): void
    {
        parent::setUp();

        // FeatureTestTrait POST 요청의 CSRF 필터를 통과시키기 위해 verify() 만 목킹한다.
        // 실제 생성자를 실행해 $config 등 나머지 상태를 정상 초기화한 뒤 verify() 만 교체.
        $security = $this->getMockBuilder(Security::class)
            ->setConstructorArgs([config('Security')])
            ->onlyMethods(['verify'])
            ->getMock();
        $security->method('verify')->willReturn(true);

        Services::injectMock('security', $security);
    }

    public function testLoginPageReturns200(): void
    {
        $result = $this->get('admin/login');

        $result->assertStatus(200);
    }

    public function testLoginRedirectsToDashboardWhenAlreadyLoggedIn(): void
    {
        $result = $this->withSession(['admin_user' => ['id' => 1, 'email' => 'admin@example.com']])
            ->get('admin/login');

        $result->assertRedirectTo(site_url('admin/dashboard'));
    }

    public function testLoginProcessWithEmptyDataRedirects(): void
    {
        $result = $this->post('admin/login', []);

        $result->assertRedirect();
    }

    public function testLoginProcessWithInvalidEmailRedirects(): void
    {
        $result = $this->post('admin/login', [
            'email'    => 'not-a-valid-email',
            'password' => 'anypassword',
        ]);

        $result->assertRedirect();
    }

    public function testLoginProcessWithNonExistentUserRedirects(): void
    {
        $result = $this->post('admin/login', [
            'email'    => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $result->assertRedirect();
    }

    public function testLogoutRedirectsToLogin(): void
    {
        $result = $this->withSession(['admin_user' => ['id' => 1, 'email' => 'admin@example.com']])
            ->post('admin/logout', []);

        $result->assertRedirectTo(site_url('admin/login'));
    }
}
