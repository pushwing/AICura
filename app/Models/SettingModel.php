<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * settings — 사이트 전역 환경설정 (이슈 #63)
 *
 * key-value 저장소. setting_key → setting_value 맵으로 다루며,
 * 정의된 키 목록은 KEYS 상수로 관리한다.
 */
class SettingModel extends Model
{
    protected $table      = 'settings';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'setting_key',
        'setting_value',
    ];

    /**
     * 관리 대상 설정 키 → 라벨 (뷰 렌더링·검증 기준)
     *
     * @var array<string, string>
     */
    public const KEYS = [
        'site_name'   => '사이트명',
        'admin_email' => '대표 이메일',
        'compliance_check_enabled' => '의료광고 심의 사전검사 사용',
    ];

    /** 체크박스(불리언)로 다루는 설정 키 — 뷰 렌더링·검증 분기용 */
    public const BOOLEAN_KEYS = [
        'compliance_check_enabled',
    ];

    /**
     * 전체 설정을 key → value 맵으로 반환 (정의된 키는 항상 포함, 미설정 시 '')
     *
     * @return array<string, string>
     */
    public function getMap(): array
    {
        $rows = $this->select('setting_key, setting_value')->findAll();

        $stored = [];
        foreach ($rows as $row) {
            $stored[(string) $row['setting_key']] = (string) ($row['setting_value'] ?? '');
        }

        $map = [];
        foreach (array_keys(self::KEYS) as $key) {
            $map[$key] = $stored[$key] ?? '';
        }

        return $map;
    }

    /**
     * 불리언 설정이 켜져 있는지 여부 (값이 '1' 이면 true)
     */
    public function enabled(string $key): bool
    {
        $row = $this->select('setting_value')->where('setting_key', $key)->first();

        return $row !== null && (string) ($row['setting_value'] ?? '') === '1';
    }

    /**
     * 다수 설정을 upsert — 정의된 키(KEYS)만 저장한다.
     *
     * @param array<string, string> $values setting_key → setting_value
     */
    public function saveMany(array $values): void
    {
        foreach ($values as $key => $value) {
            if (!isset(self::KEYS[$key])) {
                continue;
            }

            $existing = $this->where('setting_key', $key)->first();
            if ($existing === null) {
                $this->insert(['setting_key' => $key, 'setting_value' => $value]);
            } else {
                $this->update($existing['id'], ['setting_value' => $value]);
            }
        }
    }
}
