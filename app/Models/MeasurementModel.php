<?php

namespace App\Models;

use CodeIgniter\Model;

class MeasurementModel extends Model
{
    protected $table         = 'misurazioni';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['user_id', 'giorno', 'slot', 'valore', 'ora', 'nota'];

    /**
     * Misurazioni di un giorno indicizzate per slot.
     *
     * @return array<string, array<string, mixed>>
     */
    public function forDay(int $userId, string $giorno): array
    {
        $rows = $this->where('user_id', $userId)->where('giorno', $giorno)->findAll();

        return array_column($rows, null, 'slot');
    }

    /**
     * Misurazioni di un intervallo, indicizzate come [giorno][slot].
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function forRange(int $userId, string $dal, string $al): array
    {
        $rows = $this->where('user_id', $userId)
            ->where('giorno >=', $dal)
            ->where('giorno <=', $al)
            ->orderBy('giorno', 'ASC')
            ->findAll();

        $out = [];

        foreach ($rows as $row) {
            $out[$row['giorno']][$row['slot']] = $row;
        }

        return $out;
    }

    /**
     * Inserisce, aggiorna o cancella la misurazione di uno slot.
     *
     * @param array{valore?: mixed, ora?: mixed, nota?: mixed} $data
     */
    public function saveSlot(int $userId, string $giorno, string $slot, array $data): void
    {
        $valore = ($data['valore'] ?? '') === '' ? null : (float) str_replace(',', '.', (string) $data['valore']);
        $ora    = trim((string) ($data['ora'] ?? '')) ?: null;
        $nota   = trim((string) ($data['nota'] ?? '')) ?: null;

        if ($ora !== null && preg_match('/^\d{2}:\d{2}$/', $ora) === 1) {
            $ora .= ':00';
        }

        $existing = $this->where('user_id', $userId)
            ->where('giorno', $giorno)
            ->where('slot', $slot)
            ->first();

        $isEmpty = $valore === null && $nota === null;

        if ($isEmpty) {
            if ($existing !== null) {
                $this->delete($existing['id']);
            }

            return;
        }

        $payload = [
            'valore' => $valore,
            'ora'    => $ora,
            'nota'   => $nota,
        ];

        if ($existing !== null) {
            $this->update($existing['id'], $payload);

            return;
        }

        $this->insert($payload + [
            'user_id' => $userId,
            'giorno'  => $giorno,
            'slot'    => $slot,
        ]);
    }

    /**
     * Statistiche per slot su un intervallo.
     *
     * @return array<string, array{n: int, media: float|null, in_target: int, fuori: int}>
     */
    public function statistiche(int $userId, string $dal, string $al): array
    {
        helper('diario');

        $rows = $this->where('user_id', $userId)
            ->where('giorno >=', $dal)
            ->where('giorno <=', $al)
            ->where('valore IS NOT NULL')
            ->findAll();

        $user  = model(UserModel::class)->find($userId);
        $stats = [];

        foreach ($rows as $row) {
            $meta = slot_meta($row['slot']);

            if ($meta === null || $meta['type'] !== 'glicemia') {
                continue;
            }

            $slot = $row['slot'];
            $stats[$slot] ??= ['n' => 0, 'somma' => 0.0, 'media' => null, 'in_target' => 0, 'fuori' => 0];

            $stats[$slot]['n']++;
            $stats[$slot]['somma'] += (float) $row['valore'];

            $status = value_status($slot, $row['valore'], $user);

            if ($status === 'alto' || $status === 'basso') {
                $stats[$slot]['fuori']++;
            } elseif ($status === 'ok') {
                $stats[$slot]['in_target']++;
            }
        }

        foreach ($stats as $slot => $s) {
            $stats[$slot]['media'] = $s['n'] > 0 ? round($s['somma'] / $s['n'], 1) : null;
            unset($stats[$slot]['somma']);
        }

        return $stats;
    }

    /** @return list<string> Mesi (YYYY-MM) con almeno una misurazione */
    public function mesiDisponibili(int $userId): array
    {
        $rows = $this->select('giorno')
            ->where('user_id', $userId)
            ->orderBy('giorno', 'DESC')
            ->findAll();

        $mesi = [];

        foreach ($rows as $row) {
            $mesi[substr((string) $row['giorno'], 0, 7)] = true;
        }

        return array_keys($mesi);
    }
}
