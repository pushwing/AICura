<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * 후기 첨부 이미지 모델 (board_files) — 외부(소비자) 앱 (이슈 #102)
 */
class BoardFileModel extends Model
{
    protected $table         = 'board_files';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $returnType    = 'array';
    protected $allowedFields = [
        'board_id',
        'type',
        'original_name',
        'file_name',
        'file_type',
        'order_by',
    ];

    /**
     * 후기에 이미지 파일들을 연결한다.
     *
     * @param array<int, string> $fileNames 업로드로 저장된 파일명 목록
     */
    public function attach(int $boardId, array $fileNames): void
    {
        $order = 1;

        foreach ($fileNames as $name) {
            $this->insert([
                'board_id'  => $boardId,
                'type'      => 'image',
                'file_name' => $name,
                'order_by'  => $order++,
            ]);
        }
    }

    /**
     * 후기의 이미지 파일명 목록 (순서대로).
     *
     * @return array<int, string>
     */
    public function fileNames(int $boardId): array
    {
        $rows = $this->select('file_name')
            ->where('board_id', $boardId)
            ->orderBy('order_by', 'ASC')
            ->findAll();

        return array_map(static fn (array $r): string => (string) $r['file_name'], $rows);
    }

    /**
     * 후기의 기존 이미지 전체 삭제 (수정 시 교체용).
     */
    public function deleteByBoard(int $boardId): void
    {
        $this->where('board_id', $boardId)->delete();
    }
}
