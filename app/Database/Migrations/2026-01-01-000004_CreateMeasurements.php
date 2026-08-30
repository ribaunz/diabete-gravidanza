<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMeasurements extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'giorno'      => ['type' => 'DATE'],
            'slot'        => ['type' => 'VARCHAR', 'constraint' => 32],
            'valore'      => ['type' => 'DECIMAL', 'constraint' => '6,1', 'null' => true],
            'valore_testo'=> ['type' => 'VARCHAR', 'constraint' => 24, 'null' => true],
            'ora'         => ['type' => 'TIME', 'null' => true],
            'nota'        => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'giorno', 'slot'], false, 'uniq_misurazione');
        $this->forge->addKey(['user_id', 'giorno']);
        $this->forge->addForeignKey('user_id', 'users', 'id', '', 'CASCADE');
        $this->forge->createTable('misurazioni', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('misurazioni', true);
    }
}
