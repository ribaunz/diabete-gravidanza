<?php

namespace App\Models;

use CodeIgniter\Model;

class TrustedDeviceModel extends Model
{
    protected $table         = 'trusted_devices';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'token_hash', 'etichetta', 'ip', 'ultimo_uso', 'scade_il'];

    /** @return array<string, mixed>|null */
    public function findValid(int $userId, string $token): ?array
    {
        $row = $this->where('user_id', $userId)
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if ($row === null || strtotime((string) $row['scade_il']) < time()) {
            return null;
        }

        return $row;
    }

    /** @return list<array<string, mixed>> */
    public function forUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->where('scade_il >=', date('Y-m-d H:i:s'))
            ->orderBy('ultimo_uso', 'DESC')
            ->findAll();
    }
}
