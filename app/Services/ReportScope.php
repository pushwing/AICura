<?php

namespace App\Services;

use App\Models\AiReportModel;

/**
 * AI 보고서 생성 범위 (이슈 #65 — 포털 확장)
 *
 * 같은 매출/소진 생성 로직을 전체·병원·대행사 단위로 재사용하기 위한 값 객체.
 *   - hospitalIds: 집계 대상 병원 id 집합. null이면 전체(global), 빈 배열이면 대상 없음.
 *   - label:       보고서 제목·프롬프트에 쓰일 주체 이름.
 */
final class ReportScope
{
    /**
     * @param 'global'|'hospital'|'agency' $type
     * @param list<int>|null               $hospitalIds
     */
    private function __construct(
        public readonly string $type,
        public readonly ?int $id,
        public readonly ?array $hospitalIds,
        public readonly string $label,
    ) {
    }

    public static function global(): self
    {
        return new self(AiReportModel::SCOPE_GLOBAL, null, null, '전체');
    }

    public static function hospital(int $hospitalId, string $hospitalName): self
    {
        return new self(AiReportModel::SCOPE_HOSPITAL, $hospitalId, [$hospitalId], $hospitalName);
    }

    /**
     * @param list<int> $hospitalIds 대행사 소속 광고주들의 병원 id 집합
     */
    public static function agency(int $agencyUserId, array $hospitalIds, string $agencyName): self
    {
        return new self(AiReportModel::SCOPE_AGENCY, $agencyUserId, $hospitalIds, $agencyName);
    }
}
