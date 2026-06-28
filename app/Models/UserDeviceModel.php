<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 사용자 기기·푸시 토큰 모델 — 외부(소비자) 앱 (이슈 #97)
 */
class UserDeviceModel extends Model
{
    protected $table      = 'user_devices';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    protected $allowedFields = [
        'user_id',
        'platform',
        'push_token',
    ];

    /**
     * 푸시 토큰 등록 — 토큰 기준 upsert.
     *
     * 같은 토큰이 다른 사용자에 묶여 있던 경우(기기 양도·재로그인) user_id 를 갱신한다.
     */
    public function register(int $userId, string $pushToken, int $platform): void
    {
        $existing = $this->where('push_token', $pushToken)->first();

        if ($existing === null) {
            $this->insert([
                'user_id'    => $userId,
                'platform'   => $platform,
                'push_token' => $pushToken,
            ]);

            return;
        }

        $this->update((int) $existing['id'], [
            'user_id'  => $userId,
            'platform' => $platform,
        ]);
    }
}
