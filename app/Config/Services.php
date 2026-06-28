<?php

namespace Config;

use App\Services\AppAuthService;
use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * 외부(소비자) 앱 인증 서비스 (이슈 #96)
     *
     * 컨트롤러는 `service('appAuthService')` 또는 `Services::appAuthService()` 로 주입받아
     * 직접 `new` 없이 사용하며, 테스트에서는 `Services::injectMock()` 으로 대체할 수 있다.
     */
    public static function appAuthService(bool $getShared = true): AppAuthService
    {
        if ($getShared) {
            return static::getSharedInstance('appAuthService');
        }

        return new AppAuthService();
    }
}
