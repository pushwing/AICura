<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

final class AddAuthTokenVersionToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'auth_token_version' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 1,
                'after'    => 'last_logout_at',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'auth_token_version');
    }
}
