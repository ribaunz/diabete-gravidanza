<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthTokenModel extends Model
{
    public const SCOPO_LOGIN = 'login';
    public const SCOPO_RESET = 'reset';

    protected $table         = 'auth_tokens';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'user_id', 'scopo', 'token_hash', 'codice_hash', 'tentativi',
        'ip', 'user_agent', 'scade_il', 'usato_il',
    ];

    /** @return array<string, mixed>|null */
    public function findValidByToken(string $token, string $scopo): ?array
    {
        $row = $this->where('token_hash', hash('sha256', $token))
            ->where('scopo', $scopo)
            ->first();

        return $this->isUsable($row) ? $row : null;
    }

    /** @return array<string, mixed>|null */
    public function findValidById(int $id, string $scopo): ?array
    {
        $row = $this->where('id', $id)->where('scopo', $scopo)->first();

        return $this->isUsable($row) ? $row : null;
    }

    /** @param array<string, mixed>|null $row */
    private function isUsable(?array $row): bool
    {
        return $row !== null
            && $row['usato_il'] === null
            && strtotime((string) $row['scade_il']) >= time()
            && (int) $row['tentativi'] < 5;
    }

    public function consume(int $id): void
    {
        $this->update($id, ['usato_il' => date('Y-m-d H:i:s')]);
    }

    public function registerAttempt(int $id): void
    {
        $this->set('tentativi', 'tentativi + 1', false)->where('id', $id)->update();
    }

    public function invalidateFor(int $userId, string $scopo): void
    {
        $this->where('user_id', $userId)
            ->where('scopo', $scopo)
            ->where('usato_il', null)
            ->set(['usato_il' => date('Y-m-d H:i:s')])
            ->update();
    }

    public function purgeExpired(): void
    {
        $this->where('scade_il <', date('Y-m-d H:i:s', strtotime('-7 days')))->delete();
    }
}
