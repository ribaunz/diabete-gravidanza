<?php

namespace App\Controllers;

use App\Models\DayModel;
use App\Models\MeasurementModel;
use CodeIgniter\HTTP\ResponseInterface;

/** Esportazione della scheda mensile in PDF e dei dati grezzi in CSV. */
class Esporta extends BaseController
{
    public function index()
    {
        return view('diario/esporta', [
            'mesi'   => model(MeasurementModel::class)->mesiDisponibili((int) $this->utente['id']),
            'utente' => $this->utente,
            'corrente' => date('Y-m'),
        ]);
    }

    public function pdf(string $mese): ResponseInterface
    {
        $mese    = $this->normalizzaMese($mese);
        $conNote = $this->request->getGet('note') !== null && $this->request->getGet('note') !== '0';

        $pdf = service('pdfExporter')->schedaMensile($this->utente, $mese, $conNote);

        $nome = 'glicemie-' . $mese . ($conNote ? '-con-note' : '') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $nome . '"')
            ->setBody($pdf);
    }

    public function csv(string $mese): ResponseInterface
    {
        $mese = $this->normalizzaMese($mese);
        [$anno, $numeroMese] = array_map('intval', explode('-', $mese));

        $userId      = (int) $this->utente['id'];
        $giorniTotal = (int) date('t', mktime(0, 0, 0, $numeroMese, 1, $anno));
        $dal         = sprintf('%04d-%02d-01', $anno, $numeroMese);
        $al          = sprintf('%04d-%02d-%02d', $anno, $numeroMese, $giorniTotal);

        $misurazioni = model(MeasurementModel::class)->forRange($userId, $dal, $al);
        $giornate    = model(DayModel::class)->forRange($userId, $dal, $al);

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF"); // BOM: Excel apre il file con gli accenti corretti
        fputcsv($handle, ['Giorno', 'Misurazione', 'Ora', 'Valore', 'Unità', 'Stato', 'Nota', 'Nota della giornata'], ';');

        for ($g = 1; $g <= $giorniTotal; $g++) {
            $data = sprintf('%04d-%02d-%02d', $anno, $numeroMese, $g);

            foreach (slot_keys() as $slot) {
                $riga = $misurazioni[$data][$slot] ?? null;

                if ($riga === null) {
                    continue;
                }

                $meta = slot_meta($slot);

                fputcsv($handle, [
                    $data,
                    slot_label($slot),
                    $riga['ora'] !== null ? substr((string) $riga['ora'], 0, 5) : '',
                    format_value($riga['valore']),
                    $meta['unit'],
                    value_status($slot, $riga['valore'], $this->utente),
                    (string) ($riga['nota'] ?? ''),
                    (string) ($giornate[$data]['nota'] ?? ''),
                ], ';');
            }
        }

        rewind($handle);
        $csv = (string) stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="glicemie-' . $mese . '.csv"')
            ->setBody($csv);
    }

    private function normalizzaMese(string $mese): string
    {
        if (preg_match('/^\d{4}-\d{2}$/', $mese) !== 1) {
            return date('Y-m');
        }

        [$anno, $numero] = array_map('intval', explode('-', $mese));

        return $numero >= 1 && $numero <= 12 && $anno >= 2000 && $anno <= 2100 ? $mese : date('Y-m');
    }
}
