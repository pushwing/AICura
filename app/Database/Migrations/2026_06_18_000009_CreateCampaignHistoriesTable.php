<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCampaignHistoriesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'campaign_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            // approve / reject / end / create / update
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'status_from' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'status_to' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'memo' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'admin_user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
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
        $this->forge->addKey('campaign_id', false, false, 'idx_campaign_histories_campaign_id');

        $this->forge->createTable('campaign_histories');
    }

    public function down(): void
    {
        $this->forge->dropTable('campaign_histories');
    }
}
