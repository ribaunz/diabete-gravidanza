<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTrustedDevices extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'token_hash'  => ['type' => 'CHAR', 'constraint' => 64],
            'etichetta'   => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'ip'          => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'ultimo_uso'  => ['type' => 'DATETIME', 'null' => true],
            'scade_il'    => ['type' => 'DATETIME'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token_hash');
        $this->forge->addKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('trusted_devices', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('trusted_devices', true);
    }
}
