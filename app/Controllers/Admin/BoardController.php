<?php

namespace App\Controllers\Admin;

use App\Models\BoardModel;
use App\Models\BoardSummaryModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * 후기 운영 컨트롤러
 *
 *   boards            - 후기 원글 (임시/완전 삭제, 신고 관리)
 *   board_estimations - 신고 내역 (type=2)
 *   board_summaries   - 별점 집계
 */
class BoardController extends BaseAdminController
{
    private BoardModel $boardModel;
    private BoardSummaryModel $summaryModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->boardModel   = model(BoardModel::class);
        $this->summaryModel = model(BoardSummaryModel::class);
    }

    // ──────────────────────────────────────────────
    // 후기 목록 (유형·삭제·신고 필터)
    // ──────────────────────────────────────────────

    public function index(): string
    {
        $params = [
            'type'      => $this->request->getGet('type') ?? '',
            'is_delete' => $this->request->getGet('is_delete') ?? '',
            'reported'  => $this->request->getGet('reported') ?? '',
            'keyword'   => $this->request->getGet('keyword') ?? '',
            'page'      => (int) ($this->request->getGet('page') ?? 1),
            'limit'     => 20,
        ];

        $result = $this->boardModel->getList($params);

        return $this->render('admin/boards/index', [
            'boards'       => $result['list'],
            'total'        => $result['total'],
            'params'       => $params,
            'types'        => BoardModel::TYPES,
            'deleteStates' => BoardModel::DELETE_STATES,
        ]);
    }

    // ──────────────────────────────────────────────
    // 후기 상세 (신고 내역 포함)
    // ──────────────────────────────────────────────

    public function show(int $id): string
    {
        $board = $this->boardModel->getDetail($id);
        if ($board === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->render('admin/boards/show', [
            'board'        => $board,
            'types'        => BoardModel::TYPES,
            'deleteStates' => BoardModel::DELETE_STATES,
        ]);
    }

    // ──────────────────────────────────────────────
    // 삭제 처리 (임시 1 / 완전 2)
    // ──────────────────────────────────────────────

    public function delete(int $id): ResponseInterface
    {
        $state = (int) $this->request->getPost('state');
        $memo  = (string) ($this->request->getPost('delete_memo') ?? '');

        try {
            $this->boardModel->markDeleted($id, $state, $memo);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $label = BoardModel::DELETE_STATES[$state] ?? '삭제';

        return redirect()->to('/admin/boards/' . $id)
            ->with('success', $label . ' 처리되었습니다.');
    }

    // ──────────────────────────────────────────────
    // 삭제 복구
    // ──────────────────────────────────────────────

    public function restore(int $id): ResponseInterface
    {
        try {
            $this->boardModel->restore($id);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to('/admin/boards/' . $id)
            ->with('success', '복구되었습니다.');
    }

    // ──────────────────────────────────────────────
    // 별점 집계 갱신 (board_summaries)
    // ──────────────────────────────────────────────

    public function recalcSummary(int $id): ResponseInterface
    {
        $board = $this->boardModel->find($id);
        if ($board === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $result = $this->summaryModel->recalculate((int) $board['type'], (int) $board['target_id']);

        return redirect()->to('/admin/boards/' . $id)
            ->with('success', sprintf('별점 집계가 갱신되었습니다. (대상 후기 %d건, 평균 %.2f점)', $result['count'], $result['rate_sum']));
    }
}
