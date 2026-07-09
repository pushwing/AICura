<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * 소비자 앱 텔레메트리 가공 로그 모델 (이슈 #115)
 *
 * Consumer(logs:consume)가 Redis 큐에서 꺼낸 원시 로그를 가공해 적재한다.
 * 조회보다 적재(INSERT) 위주이므로 created_at 만 사용한다.
 */
class AppLogModel extends Model
{
    protected $table         = 'app_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $updatedField  = ''; // updated_at 없음
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id',
        'level',
        'event',
        'message',
        'context',
        'client_received_at',
    ];
}
