<?php

namespace App\Models;

use RuntimeException;
use InvalidArgumentException;
use CodeIgniter\Model;

/**
 * 후기/게시판 (boards)
 *
 * type:      1 이벤트, 2 병원, 3 접수
 * is_delete: 0 미삭제, 1 임시삭제, 2 완전삭제 (모두 논리 상태)
 *
 * 신고는 board_estimations.type = 2 로 적재되며 complain_count에 집계된다.
 */
class BoardModel extends Model
{
    protected $table      = 'boards';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'subject',
        'contents',
        'is_notice',
        'is_list',
        'is_delete',
        'delete_memo',
        'delete_date',
        'ai_sentiment',
        'ai_trust_score',
        'ai_flags',
        'ai_reason',
        'ai_status',
        // 외부 앱 후기 작성 필드 (이슈 #102)
        'user_id',
        'user_name',
        'type',
        'target_id',
        'rate_sum',
        'files_count',
        'is_secret',
    ];

    /** @var array<int, string> 후기 유형 */
    public const TYPES = [
        1 => '이벤트',
        2 => '병원',
        3 => '접수',
    ];

    /** @var array<int, string> 삭제 상태 */
    public const DELETE_STATES = [
        0 => '정상',
        1 => '임시삭제',
        2 => '완전삭제',
    ];

    public const DELETE_NONE = 0;
    public const DELETE_TEMP = 1;
    public const DELETE_FULL = 2;

    // board_estimations.type
    public const ESTIMATION_REPORT = 2; // 신고

    // ──────────────────────────────────────────────
    // AI 후기 신뢰성 분석 (이슈 #74)
    //
    // Redis 등 별도 큐 인프라 없이 ai_status 컬럼을 큐로 사용한다.
    // boards는 외부 시스템이 직접 INSERT하므로 신규 행은 DEFAULT 로 PENDING이 되고,
    // reviews:analyze 커맨드가 비동기로 소비한다.
    // ──────────────────────────────────────────────
    public const AI_STATUS_IDLE    = 0; // 미분석
    public const AI_STATUS_PENDING = 1; // 대기 (큐 적재됨)
    public const AI_STATUS_DONE    = 2; // 완료
    public const AI_STATUS_FAILED  = 3; // 실패

    /** 신뢰점수가 이 값 미만이면 '의심 후기'로 본다 */
    public const SUSPICIOUS_SCORE = 40;

    /** @var array<int, string> 감성 허용 값 */
    public const SENTIMENTS = ['positive', 'neutral', 'negative'];

    /** @var array<int, string> 플래그 허용 값 — AI가 임의 라벨을 만들지 못하도록 화이트리스트로 제한 */
    public const FLAGS = ['spam', 'fake', 'exaggeration', 'medical_overclaim', 'advertisement', 'duplicate'];

    /**
     * 후기 목록 (유형·삭제상태·신고 필터, 페이징)
     *
     * @param array<string, mixed> $params
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getList(array $params): array
    {
        $builder = $this->db->table('boards')
            ->select('id, type, target_id, subject, user_name, rate_sum, like_count, complain_count, is_delete, created_at')
            ->select('ai_sentiment, ai_trust_score, ai_flags, ai_status');

        if (($params['type'] ?? '') !== '') {
            $builder->where('type', (int) $params['type']);
        }
        // 삭제 상태: 빈 값이면 전체, 값이 있으면 해당 상태
        if (($params['is_delete'] ?? '') !== '') {
            $builder->where('is_delete', (int) $params['is_delete']);
        }
        // 신고만 보기
        if (!empty($params['reported'])) {
            $builder->where('complain_count >', 0);
        }
        // 의심 후기만 보기 — 분석 완료(DONE) 중 신뢰점수 낮거나 플래그가 있는 건
        if (!empty($params['suspicious'])) {
            $builder->where('ai_status', self::AI_STATUS_DONE)
                ->groupStart()
                    ->where('ai_trust_score <', self::SUSPICIOUS_SCORE)
                    ->orWhere('JSON_LENGTH(ai_flags) >', 0)
                ->groupEnd();
        }
        if (($params['keyword'] ?? '') !== '') {
            $builder->groupStart()
                ->like('subject', $params['keyword'])
                ->orLike('user_name', $params['keyword'])
                ->groupEnd();
        }

        $total = (clone $builder)->countAllResults(false);

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = (int) ($params['limit'] ?? 20);

        $list = $builder
            ->orderBy('id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        // ai_flags 는 JSON 문자열로 저장되므로 뷰에서 바로 쓰도록 list<string> 로 디코드
        foreach ($list as &$row) {
            $row['ai_flags'] = $this->decodeFlags($row['ai_flags'] ?? null);
        }
        unset($row);

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 특정 사용자가 작성한 후기 목록 (페이징) — 사용자 상세용. (이슈 #90)
     *
     * @param bool $onlyActive 삭제(임시·완전) 후기를 제외할지 여부. 소비자 노출(#97)은 true,
     *                         어드민 상세는 삭제 상태까지 보여주므로 false(기본).
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getByUser(int $userId, int $limit, int $offset, bool $onlyActive = false): array
    {
        $builder = $this->db->table('boards')
            ->select('id, type, target_id, subject, rate_sum, like_count, is_delete, created_at')
            ->where('user_id', $userId);

        if ($onlyActive) {
            $builder->where('is_delete', self::DELETE_NONE);
        }

        $total = (clone $builder)->countAllResults(false);

        $list = $builder
            ->orderBy('id', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 후기 상세 (신고 내역 포함)
     *
     * @return array<string, mixed>|null
     */
    public function getDetail(int $id): ?array
    {
        $board = $this->find($id);
        if ($board === null) {
            return null;
        }

        $board['ai_flags'] = $this->decodeFlags($board['ai_flags'] ?? null);

        $board['reports'] = $this->db->table('board_estimations be')
            ->select('be.id, be.user_id, be.created_at', false)
            ->select('u.username AS reporter_name', false)
            ->join('users u', 'u.id = be.user_id', 'left')
            ->where('be.board_id', $id)
            ->where('be.type', self::ESTIMATION_REPORT)
            ->orderBy('be.id', 'DESC')
            ->get()
            ->getResultArray();

        return $board;
    }

    /**
     * 삭제 처리 (임시 1 / 완전 2)
     *
     * @throws RuntimeException 유효하지 않은 삭제 유형 또는 후기 없음
     */
    public function markDeleted(int $id, int $state, string $memo): void
    {
        if (!in_array($state, [self::DELETE_TEMP, self::DELETE_FULL], true)) {
            throw new RuntimeException('유효하지 않은 삭제 유형입니다.');
        }
        if ($this->find($id) === null) {
            throw new RuntimeException('후기를 찾을 수 없습니다.');
        }

        $this->update($id, [
            'is_delete'   => $state,
            'delete_memo' => $memo,
            'delete_date' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 삭제 복구 (is_delete → 0)
     *
     * @throws RuntimeException 후기 없음
     */
    public function restore(int $id): void
    {
        if ($this->find($id) === null) {
            throw new RuntimeException('후기를 찾을 수 없습니다.');
        }

        $this->update($id, [
            'is_delete'   => self::DELETE_NONE,
            'delete_memo' => null,
            'delete_date' => null,
        ]);
    }

    // ──────────────────────────────────────────────
    // AI 후기 신뢰성 분석 큐 (이슈 #74)
    // ──────────────────────────────────────────────
    /**
     * 분석 큐에 적재 — ai_status를 PENDING으로 표시 (동기, AI 호출 없음).
     *
     * 운영자의 '재분석' 액션에서 호출한다. 실제 분석은 reviews:analyze 커맨드가
     * 비동기로 수행하므로 요청 응답을 막지 않는다. 삭제된 후기는 getPendingAnalysis가
     * 소비하지 않아 PENDING으로 방치되므로 적재 자체를 거부한다.
     *
     * @throws RuntimeException 후기 없음·삭제된 후기
     */
    public function enqueueAnalysis(int $id): void
    {
        $board = $this->find($id);
        if ($board === null) {
            throw new RuntimeException('후기를 찾을 수 없습니다.');
        }
        if ((int) $board['is_delete'] !== self::DELETE_NONE) {
            throw new RuntimeException('삭제된 후기는 분석할 수 없습니다.');
        }

        $this->update($id, ['ai_status' => self::AI_STATUS_PENDING]);
    }

    /**
     * 분석 대기(PENDING) 건을 분석 입력 필드만 추려서 반환 (오래된 순).
     *
     * @return array<int, array{id: int, subject: string|null, contents: string|null}>
     */
    public function getPendingAnalysis(int $limit = 50): array
    {
        /** @var array<int, array{id: int, subject: string|null, contents: string|null}> $rows */
        $rows = $this->select('id, subject, contents')
            ->where('ai_status', self::AI_STATUS_PENDING)
            ->where('is_delete', self::DELETE_NONE)
            ->orderBy('id', 'ASC')
            ->findAll(max(1, $limit));

        return $rows;
    }

    /**
     * 분석 결과 저장 — 감성·신뢰점수·플래그·근거 + 상태를 DONE으로.
     *
     * @param array{sentiment: string, trust_score: int, flags: list<string>, reason: string} $result
     */
    public function saveAnalysis(int $id, array $result): void
    {
        $this->update($id, [
            'ai_sentiment'   => $result['sentiment'],
            'ai_trust_score' => $result['trust_score'],
            'ai_flags'       => json_encode($result['flags'], JSON_UNESCAPED_UNICODE),
            'ai_reason'      => $result['reason'],
            'ai_status'      => self::AI_STATUS_DONE,
        ]);
    }

    /**
     * 분석 실패 표시 — 재처리 대상에서 빠지도록 FAILED로.
     */
    public function markAnalysisFailed(int $id): void
    {
        $this->update($id, ['ai_status' => self::AI_STATUS_FAILED]);
    }

    // ──────────────────────────────────────────────
    // 외부(소비자) 앱 — 후기 조회 (이슈 #99)
    // ──────────────────────────────────────────────

    /** boards.type — 1 이벤트 · 2 병원 · 3 접수 */
    public const TYPE_EVENT    = 1;
    public const TYPE_HOSPITAL = 2;

    /**
     * 대상(type+target_id)의 공개 후기 목록 — 비밀글·삭제글 제외, 최신순, 페이징.
     *
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getReviewsByTarget(int $type, int $targetId, int $page, int $limit): array
    {
        $builder = $this->db->table('boards')
            ->select('id, user_name, subject, contents, rate_sum, like_count, comment_count, files_count, created_at')
            ->where('type', $type)
            ->where('target_id', $targetId)
            ->where('is_delete', self::DELETE_NONE)
            ->where('is_secret', 0)
            ->where('is_list', 1);

        $total = (clone $builder)->countAllResults(false);

        $list = $builder
            ->orderBy('id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => (int) $total];
    }

    /**
     * 대상(type+target_id)의 공개 후기 수 — 상세 요약용.
     */
    public function countReviewsByTarget(int $type, int $targetId): int
    {
        return $this->where('type', $type)
            ->where('target_id', $targetId)
            ->where('is_delete', self::DELETE_NONE)
            ->where('is_secret', 0)
            ->where('is_list', 1)
            ->countAllResults();
    }

    // ──────────────────────────────────────────────
    // 외부(소비자) 앱 — 후기 작성/관리 (이슈 #102)
    // ──────────────────────────────────────────────

    /** 후기 정렬 컬럼 화이트리스트 (sort → ORDER BY) */
    private const array SORT_COLUMNS = [
        'latest' => 'id',
        'rating' => 'rate_sum',
        'likes'  => 'like_count',
    ];

    /**
     * 후기 목록 — type·target 필터, 정렬(latest/rating/likes), 페이징. 공개글만.
     *
     * @param array<string, mixed> $params type·target_id·sort·page·limit
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getConsumerList(array $params): array
    {
        // 목록은 본문 전체 대신 발췌만 조회해 전송량·페이로드를 최소화한다 (SUBSTR: MySQL·SQLite 공통)
        $builder = $this->db->table('boards')
            ->select('id, type, target_id, user_name, subject, SUBSTR(contents, 1, 150) AS excerpt, rate_sum, like_count, comment_count, files_count, created_at', false)
            ->where('is_delete', self::DELETE_NONE)
            ->where('is_secret', 0)
            ->where('is_list', 1);

        if (!empty($params['type'])) {
            $builder->where('type', (int) $params['type']);
        }
        if (!empty($params['target_id'])) {
            $builder->where('target_id', (int) $params['target_id']);
        }

        $total = (clone $builder)->countAllResults(false);

        $sortCol = self::SORT_COLUMNS[$params['sort'] ?? 'latest'] ?? 'id';
        $builder->orderBy($sortCol, 'DESC');
        if ($sortCol !== 'id') {
            $builder->orderBy('id', 'DESC');
        }

        $page  = max(1, (int) ($params['page'] ?? 1));
        $limit = max(1, (int) ($params['limit'] ?? 20));

        $list = $builder
            ->limit($limit, ($page - 1) * $limit)
            ->get()
            ->getResultArray();

        return ['list' => $list, 'total' => (int) $total];
    }

    /**
     * 후기 상세 (공개글만) — 댓글·이미지는 Service에서 합성.
     *
     * @return array<string, mixed>|null
     */
    public function getConsumerDetail(int $id): ?array
    {
        return $this->db->table('boards')
            ->select('id, type, target_id, user_id, user_name, subject, contents, rate_sum, like_count, comment_count, complain_count, files_count, created_at')
            ->where('id', $id)
            ->where('is_delete', self::DELETE_NONE)
            ->where('is_secret', 0)
            ->get()
            ->getRowArray();
    }

    /**
     * SEO 색인 적합 여부 (이슈 #144, §4.3)
     *
     * 신고(complain_count>0)되었거나 AI 분석상 의심(분석완료 && 저신뢰 또는 플래그)인 후기는
     * 검색·AI 인용 대상에서 제외하기 위해 false 를 반환한다(상세 페이지 noindex 판단용).
     */
    public function isReviewIndexable(int $id): bool
    {
        /** @var array{complain_count: int|string, ai_status: int|string, ai_trust_score: int|string|null, ai_flags: string|null}|null $row */
        $row = $this->db->table('boards')
            ->select('complain_count, ai_status, ai_trust_score, ai_flags')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if ($row === null) {
            return false;
        }
        if ((int) $row['complain_count'] > 0) {
            return false;
        }
        if ((int) $row['ai_status'] === self::AI_STATUS_DONE) {
            $lowTrust = (int) $row['ai_trust_score'] < self::SUSPICIOUS_SCORE;
            $hasFlags = $this->decodeFlags($row['ai_flags'] ?? null) !== [];
            if ($lowTrust || $hasFlags) {
                return false;
            }
        }

        return true;
    }

    /**
     * sitemap.xml 용 색인 가능 후기 목록 — 노출·미신고·비의심 건의 id·작성시각. (이슈 #144)
     *
     * 이식성을 위해 플래그(JSON) 판정은 제외하고 신고·저신뢰만 SQL 로 거른다(플래그 단독 의심은
     * 상세 noindex 에서 최종 차단). sitemaps.org 단일 파일 상한(50,000)을 기본 한도로 둔다.
     *
     * @return array<int, array{id: int, created_at: string|null}>
     */
    public function getSitemapReviews(int $limit = 50000): array
    {
        /** @var array<int, array{id: int, created_at: string|null}> $rows */
        $rows = $this->db->table('boards')
            ->select('id, created_at')
            ->where('is_delete', self::DELETE_NONE)
            ->where('is_secret', 0)
            ->where('is_list', 1)
            ->where('complain_count', 0)
            ->groupStart()
                ->where('ai_status !=', self::AI_STATUS_DONE)
                ->orWhere('ai_trust_score >=', self::SUSPICIOUS_SCORE)
            ->groupEnd()
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        return $rows;
    }

    /**
     * 후기 작성 — 생성된 id 반환. ai_status 는 DEFAULT(대기)로 자동 큐 적재.
     *
     * @param array<string, mixed> $data
     */
    public function createReview(array $data): int
    {
        return (int) $this->insert($data, true);
    }

    /**
     * 본인 소유·미삭제 후기 조회 (수정·삭제 권한 확인용).
     *
     * @return array<string, mixed>|null
     */
    public function findOwnedReview(int $id, int $userId): ?array
    {
        return $this->select('id, user_id, type, target_id')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->where('is_delete', self::DELETE_NONE)
            ->first();
    }

    /**
     * 후기 수정 — 재분석을 위해 ai_status 를 다시 대기로 돌린다.
     *
     * @param array<string, mixed> $data
     */
    public function updateReview(int $id, array $data): void
    {
        $this->update($id, $data + ['ai_status' => self::AI_STATUS_PENDING]);
    }

    /**
     * 후기 soft delete.
     */
    public function softDeleteReview(int $id): void
    {
        $this->update($id, ['is_delete' => self::DELETE_FULL]);
    }

    /**
     * 공개 후기 존재 여부 — 좋아요·신고·댓글 전 대상 검증용.
     */
    public function isVisibleReview(int $id): bool
    {
        return $this->where('id', $id)
            ->where('is_delete', self::DELETE_NONE)
            ->where('is_secret', 0)
            ->countAllResults() > 0;
    }

    /**
     * 집계 컬럼 증감 (like_count·comment_count·complain_count). 음수 방지.
     */
    public function adjustCounter(int $id, string $column, int $delta): void
    {
        if (!in_array($column, ['like_count', 'comment_count', 'complain_count'], true)) {
            throw new InvalidArgumentException('허용되지 않은 카운터 컬럼: ' . $column);
        }

        $this->db->table('boards')
            ->where('id', $id)
            ->set($column, "CASE WHEN {$column} + {$delta} < 0 THEN 0 ELSE {$column} + {$delta} END", false)
            ->update();
    }

    /**
     * ai_flags JSON 문자열을 안전하게 list<string> 로 디코드.
     *
     * @return list<string>
     */
    private function decodeFlags(mixed $raw): array
    {
        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, is_string(...)));
    }
}
