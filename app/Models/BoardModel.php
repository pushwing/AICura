<?php

namespace App\Models;

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
     * @return array{list: array<int, array<string, mixed>>, total: int}
     */
    public function getByUser(int $userId, int $limit, int $offset): array
    {
        $builder = $this->db->table('boards')
            ->select('id, type, target_id, subject, rate_sum, like_count, is_delete, created_at')
            ->where('user_id', $userId);

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
     * @throws \RuntimeException 유효하지 않은 삭제 유형 또는 후기 없음
     */
    public function markDeleted(int $id, int $state, string $memo): void
    {
        if (!in_array($state, [self::DELETE_TEMP, self::DELETE_FULL], true)) {
            throw new \RuntimeException('유효하지 않은 삭제 유형입니다.');
        }
        if ($this->find($id) === null) {
            throw new \RuntimeException('후기를 찾을 수 없습니다.');
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
     * @throws \RuntimeException 후기 없음
     */
    public function restore(int $id): void
    {
        if ($this->find($id) === null) {
            throw new \RuntimeException('후기를 찾을 수 없습니다.');
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
     * @throws \RuntimeException 후기 없음·삭제된 후기
     */
    public function enqueueAnalysis(int $id): void
    {
        $board = $this->find($id);
        if ($board === null) {
            throw new \RuntimeException('후기를 찾을 수 없습니다.');
        }
        if ((int) $board['is_delete'] !== self::DELETE_NONE) {
            throw new \RuntimeException('삭제된 후기는 분석할 수 없습니다.');
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

        return array_values(array_filter($decoded, 'is_string'));
    }
}
