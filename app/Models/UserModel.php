<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    public const RUOLO_AMMINISTRATORE = 'amministratore';
    public const RUOLO_UTENTE         = 'utente';

    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'email', 'password_hash', 'nome', 'data_presunta_parto',
        'soglia_digiuno', 'soglia_post_1h', 'soglia_post_2h',
        'intestazione', 'mostra_tutte_colonne', 'attivo', 'ruolo', 'ultimo_accesso',
    ];

    /** @param array<string, mixed>|null $user */
    public static function eAmministratore(?array $user): bool
    {
        return ($user['ruolo'] ?? null) === self::RUOLO_AMMINISTRATORE;
    }

    /** @return list<array<string, mixed>> */
    public function elenco(): array
    {
        return $this->orderBy('attivo', 'DESC')->orderBy('nome', 'ASC')->findAll();
    }

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
