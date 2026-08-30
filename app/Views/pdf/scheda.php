<?php
/**
 * Riproduzione della scheda cartacea "Stick a giorni alterni x 4 volte al giorno".
 *
 * @var array<string, mixed>                                    $utente
 * @var int                                                     $anno
 * @var int                                                     $numeroMese
 * @var int                                                     $giorniTotal
 * @var array<string, array<string, array<string, mixed>>>      $misurazioni
 * @var array<string, array<string, mixed>>                     $giornate
 * @var bool                                                    $conNote
 * @var array<string, array{giornata: string|null, voci: list<array<string, mixed>>}> $note
 */
helper('diario');

$colonne = [
    ['slot' => 'chetonuria', 'gruppo' => null],
    ['slot' => 'digiuno', 'gruppo' => 'colazione'],
    ['slot' => 'insulina_colazione', 'gruppo' => 'colazione'],
    ['slot' => 'colazione_1h', 'gruppo' => 'colazione'],
    ['slot' => 'colazione_2h', 'gruppo' => 'colazione'],
    ['slot' => 'pre_pranzo', 'gruppo' => 'pranzo'],
    ['slot' => 'insulina_pranzo', 'gruppo' => 'pranzo'],
    ['slot' => 'pranzo_1h', 'gruppo' => 'pranzo'],
    ['slot' => 'pranzo_2h', 'gruppo' => 'pranzo'],
    ['slot' => 'pre_cena', 'gruppo' => 'cena'],
    ['slot' => 'insulina_cena', 'gruppo' => 'cena'],
    ['slot' => 'cena_1h', 'gruppo' => 'cena'],
    ['slot' => 'cena_2h', 'gruppo' => 'cena'],
    ['slot' => 'insulina_notte', 'gruppo' => 'notte'],
];

$blocchi = [[1, min(15, $giorniTotal)]];

if ($giorniTotal > 15) {
    $blocchi[] = [16, $giorniTotal];
}

$intestazione = $utente['intestazione'] ?: 'Azienda USL Toscana Nord Ovest';
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="utf-8">
<title>Scheda glicemie <?= esc(nome_mese($numeroMese, $anno)) ?></title>
<style>
    @page { margin: 10mm 8mm; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 7pt; color: #000; }
    .intestazione { font-size: 8pt; }
    .struttura { font-size: 10pt; font-weight: bold; }
    .reparto { font-size: 9pt; font-weight: bold; }
    .stick { font-size: 9pt; font-weight: bold; font-style: italic; text-align: right; }
    .riga-mese { margin-top: 4px; font-size: 9pt; }
    .riga-mese .mese { float: right; }
    .barrato { text-decoration: line-through; }
    .avvertenza { font-size: 6.5pt; font-style: italic; margin: 3px 0 4px; }
    table.scheda { width: 100%; border-collapse: collapse; table-layout: fixed; }
    table.scheda th, table.scheda td { border: 0.6pt solid #000; text-align: center; vertical-align: middle; }
    table.scheda th { font-size: 6.5pt; font-weight: normal; padding: 3px 1px; }
    table.scheda th.gruppo { font-size: 10pt; font-weight: bold; background: #d4d4d4; padding: 5px 0; }
    table.scheda th.giorno, table.scheda th.cheto { font-weight: bold; font-size: 6.5pt; }
    table.scheda td { height: 21pt; font-size: 9pt; }
    td.giorno { font-size: 7pt; }
    .spenta { background: #cccccc; color: #555; }
    .spenta span { text-decoration: line-through; }
    td.alto { font-weight: bold; }
    td.alto:after { content: " ▲"; font-size: 5pt; }
    .marcatore { font-size: 5.5pt; vertical-align: super; }
    .pieno { page-break-after: always; }
    .note h2 { font-size: 10pt; margin: 0 0 6px; }
    .note h3 { font-size: 8pt; margin: 8px 0 2px; border-bottom: 0.6pt solid #999; padding-bottom: 2px; }
    table.note { width: 100%; border-collapse: collapse; font-size: 7.5pt; }
    table.note td { border-bottom: 0.4pt solid #ddd; padding: 2px 4px; vertical-align: top; }
    table.note td.quando { width: 26%; }
    table.note td.valore { width: 12%; }
    .pie { font-size: 6pt; color: #444; margin-top: 6px; }
</style>
</head>
<body>
<?php foreach ($blocchi as $indice => [$da, $a]): ?>
    <div class="<?= $indice < count($blocchi) - 1 || $conNote ? 'pieno' : '' ?>">
        <?php if ($indice === 0): ?>
            <div class="intestazione"><?= esc($intestazione) ?></div>
            <div>
                <span class="struttura">Ospedale Viareggio</span>
                &nbsp;&nbsp;&nbsp;
                <span class="reparto">U.O.S. DIABETOLOGIA E MALATTIE METABOLICHE</span>
            </div>
            <div class="stick">STICK A GIORNI ALTERNI x 4 VOLTE AL GIORNO</div>
        <?php else: ?>
            <div>
                <span class="struttura">Ospedale Viareggio</span>
                &nbsp;&nbsp;&nbsp;
                <span class="reparto">U.O.S. DIABETOLOGIA E MALATTIE METABOLICHE</span>
            </div>
        <?php endif; ?>

        <div class="riga-mese">
            <span class="mese">Mese <u>&nbsp;<?= esc(nome_mese($numeroMese, $anno)) ?>&nbsp;</u></span>
            <?php if ($indice === 0): ?>
                Albis <span class="barrato">Gravidanza</span>
                <?php if (($utente['nome'] ?? '') !== ''): ?>
                    &nbsp;&nbsp;·&nbsp;&nbsp;<?= esc($utente['nome']) ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($indice === 0): ?>
            <div class="avvertenza">
                Da compilare solo le colonne bianche: a digiuno, 1 ora dopo colazione, 1 ora dopo pranzo,
                1 ora dopo cena (+ chetonuria). Le colonne barrate non vanno compilate.
            </div>
        <?php else: ?>
            <div class="avvertenza">&nbsp;</div>
        <?php endif; ?>

        <table class="scheda">
            <colgroup>
                <col style="width: 4%">
                <col style="width: 6%">
                <?php foreach (array_slice($colonne, 1) as $c): ?>
                    <col style="width: <?= $c['slot'] === 'insulina_notte' ? '7' : '6.9' ?>%">
                <?php endforeach; ?>
            </colgroup>
            <tr>
                <th rowspan="2" class="giorno">Giorno</th>
                <th rowspan="2" class="cheto">chetonuria</th>
                <th colspan="4" class="gruppo">COLAZIONE</th>
                <th colspan="4" class="gruppo">PRANZO</th>
                <th colspan="4" class="gruppo">CENA</th>
                <th class="gruppo spenta"><span>Prima di coricarsi</span></th>
            </tr>
            <tr>
                <?php foreach (array_slice($colonne, 1) as $c): ?>
                    <?php $meta = slot_meta($c['slot']); ?>
                    <th class="<?= $meta['primary'] ? '' : 'spenta' ?>">
                        <span><?= implode('<br>', array_map('esc', $meta['sheet'])) ?></span>
                    </th>
                <?php endforeach; ?>
            </tr>

            <?php for ($g = $da; $g <= $a; $g++): ?>
                <?php $data = sprintf('%04d-%02d-%02d', $anno, $numeroMese, $g); ?>
                <tr>
                    <td class="giorno"><?= $g ?></td>
                    <?php foreach ($colonne as $c): ?>
                        <?php
                        $slot   = $c['slot'];
                        $meta   = slot_meta($slot);
                        $riga   = $misurazioni[$data][$slot] ?? null;
                        $testo  = $riga !== null ? format_value($slot, $riga['valore'], $riga['valore_testo']) : '';
                        $stato  = $riga !== null ? value_status($slot, $riga['valore'], $utente) : 'neutro';
                        $classi = $meta['primary'] ? [] : ['spenta'];

                        if ($stato === 'alto' || $stato === 'basso') {
                            $classi[] = 'alto';
                        }
                        ?>
                        <td class="<?= implode(' ', $classi) ?>">
                            <?= esc($testo) ?><?php if ($conNote && $riga !== null && ($riga['nota'] ?? null) !== null && $riga['nota'] !== ''): ?><span class="marcatore">*</span><?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endfor; ?>
        </table>

        <?php if ($conNote): ?>
            <div class="pie">* la misurazione ha una nota: vedi l'elenco nelle pagine seguenti.</div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if ($conNote): ?>
    <div class="note">
        <h2>Note del mese di <?= esc(nome_mese($numeroMese, $anno)) ?><?= ($utente['nome'] ?? '') !== '' ? ' · ' . esc($utente['nome']) : '' ?></h2>

        <?php if ($note === []): ?>
            <p>Nessuna nota registrata in questo mese.</p>
        <?php else: ?>
            <?php foreach ($note as $giorno => $blocco): ?>
                <h3><?= esc(data_estesa($giorno)) ?></h3>
                <?php if ($blocco['giornata'] !== null): ?>
                    <table class="note">
                        <tr>
                            <td class="quando"><em>Nota della giornata</em></td>
                            <td class="valore"></td>
                            <td><?= nl2br(esc($blocco['giornata'])) ?></td>
                        </tr>
                    </table>
                <?php endif; ?>
                <?php if ($blocco['voci'] !== []): ?>
                    <table class="note">
                        <?php foreach ($blocco['voci'] as $voce): ?>
                            <tr>
                                <td class="quando">
                                    <?= esc(slot_label($voce['slot'])) ?><?= $voce['ora'] !== null ? ' · ' . esc($voce['ora']) : '' ?>
                                </td>
                                <td class="valore">
                                    <?= esc($voce['valore']) ?>
                                    <?= $voce['valore'] !== '' ? esc(slot_meta($voce['slot'])['unit']) : '' ?>
                                </td>
                                <td><?= nl2br(esc($voce['nota'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
</body>
</html>
