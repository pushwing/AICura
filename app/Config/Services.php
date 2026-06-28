<?php

namespace Config;

use App\Services\AppAuthService;
use App\Services\CallRequestService;
use App\Services\EventService;
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

    /**
     * 외부(소비자) 앱 이벤트 서비스 (이슈 #98)
     *
     * 컨트롤러는 `service('eventService')` 또는 `Services::eventService()` 로 주입받아
     * 직접 `new` 없이 사용하며, 테스트에서는 `Services::injectMock()` 으로 대체할 수 있다.
     */
    public static function eventService(bool $getShared = true): EventService
    {
        if ($getShared) {
            return static::getSharedInstance('eventService');
        }

        return new EventService();
    }

    /**
     * 외부(소비자) 앱 상담 신청 서비스 (이슈 #100)
     *
     * 컨트롤러는 `service('callRequestService')` 또는 `Services::callRequestService()` 로 주입받아
     * 직접 `new` 없이 사용하며, 테스트에서는 `Services::injectMock()` 으로 대체할 수 있다.
     */
    public static function callRequestService(bool $getShared = true): CallRequestService
    {
        if ($getShared) {
            return static::getSharedInstance('callRequestService');
        }

        return new CallRequestService();
    }
}
