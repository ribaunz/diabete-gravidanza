<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Ruolo dell'utente. Gli account oltre il primo li crea un amministratore:
 * la registrazione libera non esiste.
 */
class AddRoleToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'ruolo' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'utente',
            ],
        ]);

        // Un'installazione già avviata resterebbe altrimenti senza nessuno
        // in grado di creare altri utenti: il primo account diventa amministratore.
        $primo = $this->db->table('users')
            ->select('id')
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($primo !== null) {
            $this->db->table('users')
                ->where('id', $primo['id'])
                ->update(['ruolo' => 'amministratore']);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'ruolo');
    }
}
