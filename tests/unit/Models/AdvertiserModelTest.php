<?php

use App\Models\AdvertiserModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AdvertiserModelTest extends CIUnitTestCase
{
    public function testAllowedFieldsContainsExpectedCount(): void
    {
        $model = new AdvertiserModel();

        // 기본 9개 + 포털 연동 3개(agency_user_id, owner_user_id, contract_agreed_at)
        $this->assertCount(12, $this->getPrivateProperty($model, 'allowedFields'));
    }

    public function testAllowedFieldsContainsPortalFields(): void
    {
        $model   = new AdvertiserModel();
        $allowed = $this->getPrivateProperty($model, 'allowedFields');

        foreach (['agency_user_id', 'owner_user_id', 'contract_agreed_at'] as $field) {
            $this->assertContains($field, $allowed);
        }
    }

    public function testAllowedFieldsContainsExpectedFields(): void
    {
        $model   = new AdvertiserModel();
        $allowed = $this->getPrivateProperty($model, 'allowedFields');

        $expected = [
            'hospital_id', 'hospital_name', 'contact_name',
            'contact_email', 'contact_phone', 'business_no',
            'is_network', 'network_parent_id', 'status',
        ];

        foreach ($expected as $field) {
            $this->assertContains($field, $allowed);
        }
    }

    public function testValidationRulesRequireHospitalIdAndName(): void
    {
        $model = new AdvertiserModel();
        $rules = $this->getPrivateProperty($model, 'validationRules');

        $this->assertStringContainsString('required', $rules['hospital_id']);
        $this->assertStringContainsString('required', $rules['hospital_name']);
    }

    public function testValidationRulesAllowEmptyContactFields(): void
    {
        $model = new AdvertiserModel();
        $rules = $this->getPrivateProperty($model, 'validationRules');

        $this->assertStringContainsString('permit_empty', $rules['contact_email']);
        $this->assertStringContainsString('permit_empty', $rules['contact_phone']);
        $this->assertStringContainsString('permit_empty', $rules['business_no']);
    }

    public function testValidationRulesEnforceValidEmailFormat(): void
    {
        $model = new AdvertiserModel();
        $rules = $this->getPrivateProperty($model, 'validationRules');

        $this->assertStringContainsString('valid_email', $rules['contact_email']);
    }

    public function testValidationRulesRestrictIsNetworkToValidValues(): void
    {
        $model = new AdvertiserModel();
        $rules = $this->getPrivateProperty($model, 'validationRules');

        $this->assertStringContainsString('in_list[0,1,2]', $rules['is_network']);
    }

    public function testValidationRulesRestrictStatusToValidValues(): void
    {
        $model = new AdvertiserModel();
        $rules = $this->getPrivateProperty($model, 'validationRules');

        $this->assertStringContainsString('in_list[1,2,3]', $rules['status']);
    }

    public function testReturnTypeIsArray(): void
    {
        $model = new AdvertiserModel();

        $this->assertSame('array', $this->getPrivateProperty($model, 'returnType'));
    }
}
