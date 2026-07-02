<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageColumnsToCampaigns extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('campaigns', [
            't1_image_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'after'      => 'deliberation_code',
            ],
            't2_image_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'after'      => 't1_image_name',
            ],
            'd_image_json' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 't2_image_name',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('campaigns', ['t1_image_name', 't2_image_name', 'd_image_json']);
    }
}
