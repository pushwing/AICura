<?php

use App\Models\BoardModel;
use App\Models\BoardSummaryModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class BoardModelDatabaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $namespace;
    private int $boardId  = 0;
    private int $targetId = 7777;

    protected function setUp(): void
    {
        parent::setUp();

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('boards')->insert([
            'user_id'        => 1,
            'user_name'      => '__board_user__',
            'type'           => 1, // 이벤트
            'target_id'      => $this->targetId,
            'subject'        => '__board_subject__',
            'contents'       => '후기 내용',
            'rate_sum'       => 4.0,
            'rate1'          => 4.0,
            'rate2'          => 5.0,
            'rate3'          => 3.0,
            'complain_count' => 1,
            'is_delete'      => 0,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);
        $this->boardId = (int) $db->insertID();

        // 신고 1건 (board_estimations type=2)
        $db->table('board_estimations')->insert([
            'type'       => 2,
            'board_id'   => $this->boardId,
            'user_id'    => 99,
            'created_at' => $now,
        ]);
    }

    protected function tearDown(): void
    {
        $db = db_connect();
        $db->table('board_estimations')->where('board_id', $this->boardId)->delete();
        $db->table('board_summaries')->where('target_id', $this->targetId)->delete();
        $db->table('boards')->where('id', $this->boardId)->delete();

        parent::tearDown();
    }

    public function testGetDetailIncludesReports(): void
    {
        $board = model(BoardModel::class)->getDetail($this->boardId);

        $this->assertIsArray($board);
        $this->assertArrayHasKey('reports', $board);
        $this->assertCount(1, $board['reports']);
    }

    public function testGetListFiltersReportedOnly(): void
    {
        $result = model(BoardModel::class)->getList(['reported' => '1', 'page' => 1, 'limit' => 20]);

        $ids = array_map('intval', array_column($result['list'], 'id'));
        $this->assertContains($this->boardId, $ids);
    }

    public function testMarkDeletedSetsStateAndMemo(): void
    {
        model(BoardModel::class)->markDeleted($this->boardId, BoardModel::DELETE_TEMP, '부적절한 내용');

        $row = model(BoardModel::class)->find($this->boardId);
        $this->assertSame(1, (int) $row['is_delete']);
        $this->assertSame('부적절한 내용', $row['delete_memo']);
        $this->assertNotEmpty($row['delete_date']);
    }

    public function testMarkDeletedRejectsInvalidState(): void
    {
        $this->expectException(RuntimeException::class);
        model(BoardModel::class)->markDeleted($this->boardId, 0, '');
    }

    public function testRestoreClearsDeleteState(): void
    {
        $model = model(BoardModel::class);
        $model->markDeleted($this->boardId, BoardModel::DELETE_FULL, '사유');
        $model->restore($this->boardId);

        $row = $model->find($this->boardId);
        $this->assertSame(0, (int) $row['is_delete']);
        $this->assertNull($row['delete_memo']);
    }

    public function testRecalculateUpsertsSummaryFromBoards(): void
    {
        $result = model(BoardSummaryModel::class)->recalculate(1, $this->targetId);

        $this->assertSame(1, $result['count']);
        $this->assertSame(4.0, $result['rate_sum']); // 단일 후기 평균

        $this->seeInDatabase('board_summaries', [
            'type'      => 1,
            'target_id' => $this->targetId,
        ]);

        // 삭제 후 재집계 → 대상 0건
        model(BoardModel::class)->markDeleted($this->boardId, BoardModel::DELETE_TEMP, '');
        $after = model(BoardSummaryModel::class)->recalculate(1, $this->targetId);
        $this->assertSame(0, $after['count']);
    }
}
