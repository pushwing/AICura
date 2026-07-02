<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHospitalsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            // 1 일반, 2 네트워크(모), 3 네트워크(자)
            'type' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            // active / inactive
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
            ],
            'is_deleted' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('name', false, false, 'idx_hospitals_name');
        $this->forge->addKey(['is_deleted', 'status'], false, false, 'idx_hospitals_is_deleted_status');

        $this->forge->createTable('hospitals');
    }

    public function down(): void
    {
        $this->forge->dropTable('hospitals');
    }
}
