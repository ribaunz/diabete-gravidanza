<?php

namespace App\Models;

use CodeIgniter\Model;

class DayModel extends Model
{
    protected $table         = 'giornate';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'giorno', 'nota', 'peso'];

    /** @return array<string, mixed>|null */
    public function forDay(int $userId, string $giorno): ?array
    {
        return $this->where('user_id', $userId)->where('giorno', $giorno)->first();
    }

    /** @return array<string, array<string, mixed>> */
    public function forRange(int $userId, string $dal, string $al): array
    {
        $rows = $this->where('user_id', $userId)
            ->where('giorno >=', $dal)
            ->where('giorno <=', $al)
            ->findAll();

        return array_column($rows, null, 'giorno');
    }

    public function saveDay(int $userId, string $giorno, ?string $nota, ?string $peso = null): void
    {
        $nota = $nota !== null && trim($nota) !== '' ? trim($nota) : null;
        $peso = $peso !== null && trim($peso) !== '' ? (float) str_replace(',', '.', $peso) : null;

        $existing = $this->forDay($userId, $giorno);

        if ($nota === null && $peso === null) {
            if ($existing !== null) {
                $this->delete($existing['id']);
            }

            return;
        }

        if ($existing !== null) {
            $this->update($existing['id'], ['nota' => $nota, 'peso' => $peso]);

            return;
        }

        $this->insert(['user_id' => $userId, 'giorno' => $giorno, 'nota' => $nota, 'peso' => $peso]);
    }
}
