<?php

use App\Models\CampaignModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CampaignModelTest extends CIUnitTestCase
{
    public function testStatusTransitionsCoversAllStatuses(): void
    {
        $statuses = array_keys(CampaignModel::STATUS_TRANSITIONS);
        sort($statuses);

        $this->assertSame(['active', 'ended', 'pending', 'rejected'], $statuses);
    }

    public function testPendingAllowsApproveAndReject(): void
    {
        $allowed = CampaignModel::STATUS_TRANSITIONS['pending'];

        $this->assertContains('active', $allowed);
        $this->assertContains('rejected', $allowed);
    }

    public function testActiveAllowsOnlyEnd(): void
    {
        $this->assertSame(['ended'], CampaignModel::STATUS_TRANSITIONS['active']);
    }

    public function testRejectedAllowsOnlyReopen(): void
    {
        $this->assertSame(['pending'], CampaignModel::STATUS_TRANSITIONS['rejected']);
    }

    public function testEndedHasNoTransitions(): void
    {
        $this->assertEmpty(CampaignModel::STATUS_TRANSITIONS['ended']);
    }

    public function testAdTypesHaveFiveEntries(): void
    {
        $this->assertCount(5, CampaignModel::AD_TYPES);
    }

    public function testAdTypesIncludeCpaAndCpm(): void
    {
        $this->assertArrayHasKey(1, CampaignModel::AD_TYPES);
        $this->assertSame('CPA', CampaignModel::AD_TYPES[1]);
        $this->assertArrayHasKey(2, CampaignModel::AD_TYPES);
        $this->assertSame('CPM', CampaignModel::AD_TYPES[2]);
    }

    public function testChannelsHaveTwoEntries(): void
    {
        $this->assertCount(2, CampaignModel::CHANNELS);
    }
}
