<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\BoardCommentModel;
use App\Models\BoardEstimationModel;
use App\Models\BoardFileModel;
use App\Models\BoardModel;
use App\Models\BoardSummaryModel;
use App\Models\CampaignModel;
use App\Models\HospitalModel;
use App\Models\UserModel;

/**
 * 외부(소비자) 앱 후기 커뮤니티 서비스 (이슈 #102)
 *
 * 후기 목록·상세·작성·수정·삭제·좋아요·신고·댓글을 담당한다.
 * - 대상(type 1 이벤트·2 병원)이 노출 가능한 경우에만 작성 허용.
 * - 작성/수정/삭제 시 board_summaries(별점 요약) 재집계. 본문은 태그 제거 후 저장.
 * - 좋아요/신고는 board_estimations + boards 집계 컬럼을 함께 갱신.
 */
class BoardService
{
    /** 상세에 함께 싣는 댓글 기본 개수 */
    private const DETAIL_COMMENT_LIMIT = 20;

    private BoardModel $boards;
    private BoardCommentModel $comments;
    private BoardEstimationModel $estimations;
    private BoardFileModel $files;
    private BoardSummaryModel $summaries;
    private CampaignModel $campaigns;
    private HospitalModel $hospitals;
    private UserModel $users;
    private UploadService $uploads;

    public function __construct(
        ?BoardModel $boards = null,
        ?BoardCommentModel $comments = null,
        ?BoardEstimationModel $estimations = null,
        ?BoardFileModel $files = null,
        ?BoardSummaryModel $summaries = null,
        ?CampaignModel $campaigns = null,
        ?HospitalModel $hospitals = null,
        ?UserModel $users = null,
        ?UploadService $uploads = null,
    ) {
        $this->boards      = $boards      ?? model(BoardModel::class);
        $this->comments    = $comments    ?? model(BoardCommentModel::class);
        $this->estimations = $estimations ?? model(BoardEstimationModel::class);
        $this->files       = $files       ?? model(BoardFileModel::class);
        $this->summaries   = $summaries   ?? model(BoardSummaryModel::class);
        $this->campaigns   = $campaigns   ?? model(CampaignModel::class);
        $this->hospitals   = $hospitals   ?? model(HospitalModel::class);
        $this->users       = $users       ?? model(UserModel::class);
        $this->uploads     = $uploads     ?? service('uploadService');
    }

    /**
     * 후기 목록
     *
     * @param array<string, mixed> $params
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function list(int $userId, array $params): array
    {
        $base  = $this->boards->getConsumerList($params);
        $items = array_map([$this, 'transformListItem'], $base['list']);
        $items = $this->overlayLikes($userId, $items);

        return ['items' => $items, 'total' => $base['total']];
    }

    /**
     * 후기 상세 (이미지·댓글·평점·is_liked)
     *
     * @return array<string, mixed>
     * @throws NotFoundException
     */
    public function detail(int $userId, int $id): array
    {
        $row = $this->boards->getConsumerDetail($id);
        if ($row === null) {
            throw NotFoundException::of('후기를 찾을 수 없습니다.');
        }

        $item = $this->transformDetail($row);
        $item['images']   = array_map(fn (string $n): string => $this->uploads->urlFor($n), $this->files->fileNames($id));
        $item['is_liked'] = $this->estimations->likedBoardIds($userId, [$id]) !== [];

        $comments         = $this->comments->getByBoard($id, 1, self::DETAIL_COMMENT_LIMIT);
        $item['comments'] = array_map([$this, 'transformComment'], $comments['list']);

        return $item;
    }

    /**
     * 후기 작성
     *
     * @param array<string, mixed> $input type·target_id·subject·contents·rating·images[]
     * @return array<string, mixed>
     * @throws NotFoundException 노출 불가 대상
     */
    public function create(int $userId, array $input): array
    {
        $type     = (int) $input['type'];
        $targetId = (int) $input['target_id'];
        $this->assertTargetVisible($type, $targetId);

        $images = $this->normalizeImages($input['images'] ?? []);

        $db = db_connect();
        $db->transStart();

        $id = $this->boards->createReview([
            'user_id'     => $userId,
            'user_name'   => $this->userName($userId),
            'type'        => $type,
            'target_id'   => $targetId,
            'subject'     => $this->clean($input['subject'] ?? ''),
            'contents'    => $this->clean($input['contents'] ?? ''),
            'rate_sum'    => $this->clampRating($input['rating'] ?? 0),
            'files_count' => count($images),
        ]);

        if ($images !== []) {
            $this->files->attach($id, $images);
        }

        $this->summaries->recalculate($type, $targetId);

        $db->transComplete();

        return $this->detail($userId, $id);
    }

    /**
     * 후기 수정 (본인)
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     * @throws NotFoundException
     */
    public function update(int $userId, int $id, array $input): array
    {
        $review = $this->boards->findOwnedReview($id, $userId);
        if ($review === null) {
            throw NotFoundException::of('후기를 찾을 수 없습니다.');
        }

        $data = [];
        if (array_key_exists('subject', $input)) {
            $data['subject'] = $this->clean($input['subject']);
        }
        if (array_key_exists('contents', $input)) {
            $data['contents'] = $this->clean($input['contents']);
        }
        if (array_key_exists('rating', $input)) {
            $data['rate_sum'] = $this->clampRating($input['rating']);
        }

        $db = db_connect();
        $db->transStart();

        // 이미지가 전달되면 전체 교체
        if (array_key_exists('images', $input)) {
            $images = $this->normalizeImages($input['images']);
            $this->files->deleteByBoard($id);
            $this->files->attach($id, $images);
            $data['files_count'] = count($images);
        }

        if ($data !== []) {
            $this->boards->updateReview($id, $data);
        }

        $this->summaries->recalculate((int) $review['type'], (int) $review['target_id']);

        $db->transComplete();

        return $this->detail($userId, $id);
    }

    /**
     * 후기 삭제 (본인)
     *
     * @throws NotFoundException
     */
    public function delete(int $userId, int $id): void
    {
        $review = $this->boards->findOwnedReview($id, $userId);
        if ($review === null) {
            throw NotFoundException::of('후기를 찾을 수 없습니다.');
        }

        $db = db_connect();
        $db->transStart();

        $this->boards->softDeleteReview($id);
        $this->summaries->recalculate((int) $review['type'], (int) $review['target_id']);

        $db->transComplete();
    }

    /**
     * 좋아요 토글
     *
     * @return array{liked: bool}
     * @throws NotFoundException
     */
    public function toggleLike(int $userId, int $id): array
    {
        $this->assertReviewVisible($id);

        $liked = $this->estimations->toggleLike($id, $userId);
        $this->boards->adjustCounter($id, 'like_count', $liked ? 1 : -1);

        return ['liked' => $liked];
    }

    /**
     * 신고 (1인 1회)
     *
     * @return array{reported: bool}
     * @throws NotFoundException
     */
    public function report(int $userId, int $id): array
    {
        $this->assertReviewVisible($id);

        $isNew = $this->estimations->report($id, $userId);
        if ($isNew) {
            $this->boards->adjustCounter($id, 'complain_count', 1);
        }

        return ['reported' => $isNew];
    }

    /**
     * 댓글 목록
     *
     * @return array{items: array<int, array<string, mixed>>, total: int}
     * @throws NotFoundException
     */
    public function comments(int $id, int $page, int $limit): array
    {
        $this->assertReviewVisible($id);

        $base = $this->comments->getByBoard($id, $page, $limit);

        return ['items' => array_map([$this, 'transformComment'], $base['list']), 'total' => $base['total']];
    }

    /**
     * 댓글 작성
     *
     * @return array<string, mixed>
     * @throws NotFoundException
     */
    public function addComment(int $userId, int $id, string $contents): array
    {
        $this->assertReviewVisible($id);

        $commentId = $this->comments->add($id, $userId, $this->userName($userId), $this->clean($contents));
        $this->boards->adjustCounter($id, 'comment_count', 1);

        $row = $this->comments->find($commentId);

        return $this->transformComment($row);
    }

    /**
     * 댓글 삭제 (본인)
     *
     * @throws NotFoundException
     */
    public function deleteComment(int $userId, int $boardId, int $commentId): void
    {
        $comment = $this->comments->findOwned($commentId, $boardId, $userId);
        if ($comment === null) {
            throw NotFoundException::of('댓글을 찾을 수 없습니다.');
        }

        $this->comments->softDelete($commentId);
        $this->boards->adjustCounter($boardId, 'comment_count', -1);
    }

    // ── 내부 헬퍼 ──────────────────────────────────────────

    /**
     * @throws NotFoundException
     */
    private function assertTargetVisible(int $type, int $targetId): void
    {
        $visible = match ($type) {
            BoardModel::TYPE_EVENT    => $this->campaigns->isVisibleEvent($targetId),
            BoardModel::TYPE_HOSPITAL => $this->hospitals->isVisible($targetId),
            default                   => false,
        };

        if (!$visible) {
            throw NotFoundException::of('후기를 작성할 수 있는 대상이 아닙니다.');
        }
    }

    /**
     * @throws NotFoundException
     */
    private function assertReviewVisible(int $id): void
    {
        if (!$this->boards->isVisibleReview($id)) {
            throw NotFoundException::of('후기를 찾을 수 없습니다.');
        }
    }

    /**
     * is_liked 일괄 오버레이 (N+1 방지)
     *
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function overlayLikes(int $userId, array $items): array
    {
        if ($items === []) {
            return $items;
        }

        $ids   = array_map(static fn (array $i): int => (int) $i['id'], $items);
        $liked = array_flip($this->estimations->likedBoardIds($userId, $ids));

        foreach ($items as &$item) {
            $item['is_liked'] = isset($liked[(int) $item['id']]);
        }
        unset($item);

        return $items;
    }

    /**
     * 목록·상세 공통 필드.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function baseFields(array $row): array
    {
        $type = (int) $row['type'];

        return [
            'id'            => (int) $row['id'],
            'type'          => $type,
            'type_label'    => BoardModel::TYPES[$type] ?? null,
            'target_id'     => (int) $row['target_id'],
            'user_name'     => $row['user_name'],
            'subject'       => $row['subject'],
            'rating'        => round((float) $row['rate_sum'], 2),
            'like_count'    => (int) $row['like_count'],
            'comment_count' => (int) $row['comment_count'],
            'files_count'   => (int) $row['files_count'],
            'created_at'    => $row['created_at'],
        ];
    }

    /**
     * 목록 아이템 — 본문은 발췌(excerpt)만 노출해 페이로드를 최소화한다.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function transformListItem(array $row): array
    {
        return $this->baseFields($row) + ['excerpt' => (string) ($row['excerpt'] ?? '')];
    }

    /**
     * 상세 아이템 — 본문 전체와 작성자·신고수를 포함한다.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function transformDetail(array $row): array
    {
        return $this->baseFields($row) + [
            'contents'       => $row['contents'],
            'user_id'        => (int) $row['user_id'],
            'complain_count' => (int) $row['complain_count'],
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function transformComment(array $row): array
    {
        return [
            'id'         => (int) $row['id'],
            'user_id'    => (int) $row['user_id'],
            'user_name'  => $row['user_name'],
            'contents'   => $row['contents'],
            'created_at' => $row['created_at'],
        ];
    }

    /**
     * 업로드 이미지 파일명 목록 정규화 (문자열·basename 만 허용).
     *
     * @param mixed $images
     * @return array<int, string>
     */
    private function normalizeImages(mixed $images): array
    {
        if (!is_array($images)) {
            return [];
        }

        $out = [];
        foreach ($images as $name) {
            if (is_string($name) && $name !== '' && $name === basename($name)) {
                $out[] = $name;
            }
        }

        return $out;
    }

    private function userName(int $userId): string
    {
        $row = $this->users->getProfile($userId);

        return (string) ($row['username'] ?? '');
    }

    /**
     * 본문·제목 정리 — 태그 제거 후 트림 (XSS 방지, 후기는 평문).
     */
    private function clean(mixed $value): string
    {
        return trim(strip_tags((string) $value));
    }

    /**
     * 별점 0~5 범위로 보정.
     */
    private function clampRating(mixed $value): float
    {
        $rating = (float) $value;

        return max(0.0, min(5.0, $rating));
    }
}
