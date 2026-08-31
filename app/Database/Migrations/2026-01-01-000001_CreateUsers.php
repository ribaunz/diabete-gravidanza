<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'email'                => ['type' => 'VARCHAR', 'constraint' => 190],
            'password_hash'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'nome'                 => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'data_presunta_parto'  => ['type' => 'DATE', 'null' => true],
            'soglia_digiuno'       => ['type' => 'SMALLINT', 'constraint' => 5, 'unsigned' => true, 'default' => 90],
            'soglia_post_1h'       => ['type' => 'SMALLINT', 'constraint' => 5, 'unsigned' => true, 'default' => 130],
            'soglia_post_2h'       => ['type' => 'SMALLINT', 'constraint' => 5, 'unsigned' => true, 'default' => 120],
            'intestazione'         => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'mostra_tutte_colonne' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'attivo'               => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'ultimo_accesso'       => ['type' => 'DATETIME', 'null' => true],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('users', true);
    }
}
