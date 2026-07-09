<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 의료광고 심의 사전검사 결과 모델 (이슈 #71)
 *
 * risk_level: safe(안전) / warning(주의) / violation(위반)
 * flags: 위반 플래그 JSON 문자열 [{rule, severity, quote, reason, suggestion}]
 */
class ComplianceCheckModel extends Model
{
    public const RISK_SAFE      = 'safe';
    public const RISK_WARNING   = 'warning';
    public const RISK_VIOLATION = 'violation';

    /**
     * 허용 위험 등급 (정규화 검증용)
     */
    public const RISK_LEVELS = [
        self::RISK_SAFE,
        self::RISK_WARNING,
        self::RISK_VIOLATION,
    ];

    protected $table         = 'creative_compliance_checks';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    /**
     * @var list<string>
     */
    protected $allowedFields = [
        'campaign_id',
        'review_request_id',
        'risk_level',
        'flags',
        'checked_text',
        'model',
    ];

    /**
     * 캠페인의 최신 검사 결과 1건 (없으면 null)
     *
     * @return array<string, mixed>|null
     */
    public function latestByCampaign(int $campaignId): ?array
    {
        return $this->where('campaign_id', $campaignId)
            ->orderBy('id', 'DESC')
            ->first();
    }
}
