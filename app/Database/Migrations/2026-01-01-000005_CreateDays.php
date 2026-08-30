<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDays extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'giorno'     => ['type' => 'DATE'],
            'nota'       => ['type' => 'TEXT', 'null' => true],
            'peso'       => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'giorno'], false, 'uniq_giornata');
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('giornate', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('giornate', true);
    }
}
