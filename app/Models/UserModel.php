<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'email', 'password_hash', 'nome', 'data_presunta_parto',
        'soglia_digiuno', 'soglia_post_1h', 'soglia_post_2h',
        'intestazione', 'mostra_tutte_colonne', 'attivo', 'ultimo_accesso',
    ];

    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }

    /** @param array<string, mixed> $data */
    public function createUser(array $data, string $password): int
    {
        $data['email']         = strtolower(trim((string) $data['email']));
        $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);

        return (int) $this->insert($data, true);
    }

    public function updatePassword(int $userId, string $password): bool
    {
        return $this->update($userId, ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    }
}
