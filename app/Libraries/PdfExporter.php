<?php

namespace App\Libraries;

use App\Models\DayModel;
use App\Models\MeasurementModel;
use Dompdf\Dompdf;
use Dompdf\Options;

/** Genera la scheda mensile in PDF, nella stessa forma del modulo cartaceo. */
class PdfExporter
{
    /**
     * @param array<string, mixed> $utente
     *
     * @return string il contenuto binario del PDF
     */
    public function schedaMensile(array $utente, string $mese, bool $conNote = false): string
    {
        $html = $this->html($utente, $mese, $conNote);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', ROOTPATH);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return (string) $dompdf->output();
    }

    /** @param array<string, mixed> $utente */
    public function html(array $utente, string $mese, bool $conNote = false): string
    {
        helper('diario');

        [$anno, $numeroMese] = array_map('intval', explode('-', $mese));

        $userId      = (int) $utente['id'];
        $giorniTotal = (int) date('t', mktime(0, 0, 0, $numeroMese, 1, $anno));
        $dal         = sprintf('%04d-%02d-01', $anno, $numeroMese);
        $al          = sprintf('%04d-%02d-%02d', $anno, $numeroMese, $giorniTotal);

        $misurazioni = model(MeasurementModel::class)->forRange($userId, $dal, $al);
        $giornate    = model(DayModel::class)->forRange($userId, $dal, $al);

        return view('pdf/scheda', [
            'utente'      => $utente,
            'mese'        => $mese,
            'anno'        => $anno,
            'numeroMese'  => $numeroMese,
            'giorniTotal' => $giorniTotal,
            'misurazioni' => $misurazioni,
            'giornate'    => $giornate,
            'conNote'     => $conNote,
            'note'        => $conNote ? $this->raccogliNote($misurazioni, $giornate) : [],
        ]);
    }

    /**
     * Note ordinate per giorno, pronte per l'appendice del PDF.
     *
     * @param array<string, array<string, array<string, mixed>>> $misurazioni
     * @param array<string, array<string, mixed>>                $giornate
     *
     * @return array<string, array{giornata: string|null, voci: list<array{slot: string, ora: string|null, valore: string, nota: string}>}>
     */
    private function raccogliNote(array $misurazioni, array $giornate): array
    {
        $note = [];

        foreach ($misurazioni as $giorno => $slots) {
            foreach (slot_keys() as $slot) {
                $riga = $slots[$slot] ?? null;

                if ($riga === null || ($riga['nota'] ?? '') === '' || $riga['nota'] === null) {
                    continue;
                }

                $note[$giorno]['giornata'] ??= null;
                $note[$giorno]['voci'][] = [
                    'slot'   => $slot,
                    'ora'    => $riga['ora'] !== null ? substr((string) $riga['ora'], 0, 5) : null,
                    'valore' => format_value($slot, $riga['valore'], $riga['valore_testo']),
                    'nota'   => (string) $riga['nota'],
                ];
            }
        }

        foreach ($giornate as $giorno => $riga) {
            if (($riga['nota'] ?? null) === null || trim((string) $riga['nota']) === '') {
                continue;
            }

            $note[$giorno]['giornata'] = (string) $riga['nota'];
            $note[$giorno]['voci'] ??= [];
        }

        ksort($note);

        foreach ($note as $giorno => $blocco) {
            $note[$giorno]['giornata'] ??= null;
            $note[$giorno]['voci'] ??= [];
        }

        return $note;
    }
}
