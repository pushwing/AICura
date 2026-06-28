<?php

namespace App\Services;

use App\Exceptions\CallRequestException;
use App\Exceptions\NotFoundException;
use App\Models\CallRequestModel;
use App\Models\CampaignModel;

/**
 * 외부(소비자) 앱 상담 신청 서비스 (이슈 #100)
 *
 * 이벤트(캠페인)에 대한 상담/전화 신청 생성·조회·취소를 담당한다.
 * - 생성 시 캠페인의 CPA 단가(db_cost)를 event_cost로 기록하고, 신청 즉시 CPA 과금 + AI 분석 큐 적재.
 * - 조회·취소는 본인 소유 건으로 한정하며, AI 분석·과금 등 내부 필드는 응답에서 제외한다.
 */
class CallRequestService
{
    private CallRequestModel $callRequests;
    private CampaignModel $campaigns;

    public function __construct(
        ?CallRequestModel $callRequests = null,
        ?CampaignModel $campaigns = null,
    ) {
        $this->callRequests = $callRequests ?? model(CallRequestModel::class);
        $this->campaigns    = $campaigns    ?? model(CampaignModel::class);
    }

    /**
     * 상담 신청 생성 — 캠페인 검증 → INSERT → CPA 과금 → AI 큐 적재.
     *
     * @param array<string, mixed> $input campaign_id·name·phone·content·call_time·age·sex·funnel·region·device·supply_third_party_agree
     * @return array<string, mixed> 생성된 신청 상세 (소비자 노출 컬럼)
     * @throws NotFoundException 노출 조건 미충족·미존재 캠페인
     */
    public function apply(int $userId, array $input): array
    {
        $campaign = $this->campaigns->getApplyTarget((int) $input['campaign_id']);
        if ($campaign === null) {
            throw NotFoundException::of('신청할 수 있는 이벤트가 아닙니다.');
        }

        $id = $this->callRequests->createApplication([
            'campaign_id'              => $campaign['id'],
            'hospital_id'              => $campaign['hospital_id'],
            'user_id'                  => $userId,
            'event_cost'              => $campaign['db_cost'],
            'device'                   => $this->normalizeDevice($input['device'] ?? null),
            'name'                     => trim((string) $input['name']),
            'phone'                    => trim((string) $input['phone']),
            'content'                  => $this->nullableString($input['content'] ?? null),
            'call_time'                => (string) ($input['call_time'] ?? ''),
            'age'                      => isset($input['age']) ? (int) $input['age'] : null,
            'sex'                      => isset($input['sex']) ? (int) $input['sex'] : 0,
            'privacy_agree'            => 1,
            'supply_third_party_agree' => !empty($input['supply_third_party_agree']) ? 1 : 0,
            'funnel'                   => $this->nullableString($input['funnel'] ?? null),
            'region'                   => $this->nullableString($input['region'] ?? null),
        ]);

        // CPA 과금 — 계약 미연결·금액 없음 등은 내부적으로 false 반환(신청 자체는 성공 처리)
        $this->callRequests->chargeCpa($id, $userId);

        // AI 리드 분석 큐 적재 (비동기 — leads:analyze 커맨드가 소비)
        $this->callRequests->enqueueAnalysis($id);

        return $this->detail($userId, $id);
    }

    /**
     * 본인 신청 상세
     *
     * @return array<string, mixed>
     * @throws NotFoundException 미존재·타인 소유
     */
    public function detail(int $userId, int $id): array
    {
        $row = $this->callRequests->getConsumerDetail($id, $userId);
        if ($row === null) {
            throw NotFoundException::of('신청 내역을 찾을 수 없습니다.');
        }

        return $this->transform($row);
    }

    /**
     * 본인 신청 취소 — 미확인(status=1) 건만 soft delete.
     *
     * @throws NotFoundException 미존재·타인 소유
     * @throws CallRequestException 이미 처리 진행되어 취소 불가
     */
    public function cancel(int $userId, int $id): void
    {
        $row = $this->callRequests->findOwned($id, $userId);
        if ($row === null) {
            throw NotFoundException::of('신청 내역을 찾을 수 없습니다.');
        }

        if ((int) $row['status'] !== CallRequestModel::STATUS_UNCONFIRMED) {
            throw CallRequestException::cannotCancel();
        }

        $this->callRequests->softDelete($id);
    }

    /**
     * 신청 행 → 소비자 응답 (상태 라벨 부여)
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function transform(array $row): array
    {
        $status = (int) $row['status'];

        return [
            'id'             => (int) $row['id'],
            'campaign_id'    => (int) $row['campaign_id'],
            'campaign_title' => $row['campaign_title'],
            'hospital_id'    => (int) $row['hospital_id'],
            'hospital_name'  => $row['hospital_name'],
            'status'         => $status,
            'status_label'   => CallRequestModel::STATUSES[$status] ?? null,
            'name'           => $row['name'],
            'phone'          => $row['phone'],
            'content'        => $row['content'],
            'call_time'      => $row['call_time'],
            'reserved_at'    => $row['reserved_at'],
            'created_at'     => $row['created_at'],
        ];
    }

    /**
     * 디바이스 정규화 — 1 안드로이드 · 2 iOS (앱 전용). 그 외/누락은 1.
     */
    private function normalizeDevice(mixed $value): int
    {
        $int = (int) $value;

        return in_array($int, [1, 2], true) ? $int : 1;
    }

    private function nullableString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
