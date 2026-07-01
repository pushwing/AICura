<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContractOrderConnectsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'contract_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'contract_order_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('contract_id');
        $this->forge->addUniqueKey('contract_order_id');

        $this->forge->createTable('contract_order_connects');
    }

    public function down(): void
    {
        $this->forge->dropTable('contract_order_connects');
    }
}
