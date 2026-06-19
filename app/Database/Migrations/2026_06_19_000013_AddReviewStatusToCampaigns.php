<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReviewStatusToCampaigns extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('campaigns', [
            'review_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'status',
            ],
        ]);

        $this->db->query('CREATE INDEX idx_campaigns_review_status ON campaigns (review_status)');
    }

    public function down(): void
    {
        $this->forge->dropColumn('campaigns', 'review_status');
    }
}
