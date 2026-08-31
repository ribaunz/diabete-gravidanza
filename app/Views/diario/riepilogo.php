<?= $this->extend('layouts/main') ?>
<?= $this->section('contenuto') ?>
<?php
/**
 * @var string                                              $mese
 * @var array<string, array{n:int, media:float|null, in_target:int, fuori:int}> $statistiche
 * @var array<string, array<string, array<string, mixed>>>  $misurazioni
 * @var array<string, array<string, mixed>>                 $giornate
 * @var array<string, mixed>                                $utente
 */
[$anno, $numeroMese] = array_map('intval', explode('-', $mese));

$totali = ['n' => 0, 'in_target' => 0, 'fuori' => 0];

foreach ($statistiche as $s) {
    $totali['n'] += $s['n'];
    $totali['in_target'] += $s['in_target'];
    $totali['fuori'] += $s['fuori'];
}

$percentuale = $totali['n'] > 0 ? round($totali['in_target'] / $totali['n'] * 100) : null;

// Giorni con almeno una misurazione
$giorniCompilati = 0;
$fuoriSoglia     = [];

foreach ($misurazioni as $giorno => $slots) {
    $conValore = false;

    foreach ($slots as $slot => $riga) {
        if ($riga['valore'] !== null) {
            $conValore = true;
        }

        $stato = value_status($slot, $riga['valore'], $utente);

        if ($stato === 'alto' || $stato === 'basso') {
            $fuoriSoglia[] = ['giorno' => $giorno, 'slot' => $slot, 'riga' => $riga, 'stato' => $stato];
        }
    }

    if ($conValore) {
        $giorniCompilati++;
    }
}

$noteTotali = 0;

foreach ($misurazioni as $slots) {
    foreach ($slots as $riga) {
        if (($riga['nota'] ?? '') !== '') {
            $noteTotali++;
        }
    }
}

foreach ($giornate as $riga) {
    if (trim((string) ($riga['nota'] ?? '')) !== '') {
        $noteTotali++;
    }
}
?>

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
        <a href="<?= site_url('riepilogo/' . $precedente) ?>" class="bottone-chiaro px-3 py-2">←</a>
        <h1 class="text-lg font-bold text-slate-900">Riepilogo · <?= esc(nome_mese($numeroMese, $anno)) ?></h1>
        <a href="<?= site_url('riepilogo/' . $successivo) ?>" class="bottone-chiaro px-3 py-2">→</a>
    </div>
    <a href="<?= site_url('esporta/pdf/' . $mese . '?note=1') ?>" class="bottone text-sm" target="_blank" rel="noopener">PDF con note</a>
</div>

<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div class="scheda-bianca">
        <p class="text-xs uppercase tracking-wide text-slate-500">Misurazioni</p>
        <p class="text-2xl font-bold text-slate-900"><?= $totali['n'] ?></p>
        <p class="text-xs text-slate-500"><?= $giorniCompilati ?> giorni compilati</p>
    </div>
    <div class="scheda-bianca">
        <p class="text-xs uppercase tracking-wide text-slate-500">Nel target</p>
        <p class="text-2xl font-bold text-verde-700"><?= $percentuale !== null ? $percentuale . '%' : '—' ?></p>
        <p class="text-xs text-slate-500"><?= $totali['in_target'] ?> su <?= $totali['n'] ?></p>
    </div>
    <div class="scheda-bianca">
        <p class="text-xs uppercase tracking-wide text-slate-500">Fuori soglia</p>
        <p class="text-2xl font-bold text-rose-600"><?= $totali['fuori'] ?></p>
        <p class="text-xs text-slate-500">da segnalare alla visita</p>
    </div>
    <div class="scheda-bianca">
        <p class="text-xs uppercase tracking-wide text-slate-500">Note scritte</p>
        <p class="text-2xl font-bold text-slate-900"><?= $noteTotali ?></p>
        <p class="text-xs text-slate-500">incluse nel PDF con note</p>
    </div>
</div>

<div class="scheda-bianca mb-4">
    <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Medie per misurazione</h2>

    <?php if ($statistiche === []): ?>
        <p class="text-sm text-slate-500">Nessuna misurazione registrata in questo mese.</p>
    <?php else: ?>
        <div class="space-y-3">
            <?php foreach (slot_keys() as $slot): ?>
                <?php
                $s = $statistiche[$slot] ?? null;

                if ($s === null || $s['n'] === 0) {
                    continue;
                }

                $soglia = slot_threshold($slot, $utente);
                $quota  = $s['n'] > 0 ? round($s['in_target'] / $s['n'] * 100) : 0;
                ?>
                <div>
                    <div class="mb-1 flex items-baseline justify-between gap-2 text-sm">
                        <span class="font-medium text-slate-700"><?= esc(slot_label($slot)) ?></span>
                        <span class="text-slate-500">
                            media <strong class="<?= $soglia !== null && $s['media'] >= $soglia ? 'text-rose-600' : 'text-verde-700' ?>"><?= esc((string) $s['media']) ?></strong>
                            mg/dl · <?= $s['n'] ?> valori
                        </span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-verde-500" style="width: <?= $quota ?>%"></div>
                    </div>
                    <p class="mt-0.5 text-xs text-slate-400"><?= $quota ?>% nel target<?= $soglia !== null ? ' (< ' . $soglia . ')' : '' ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="scheda-bianca">
    <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Valori fuori soglia</h2>

    <?php if ($fuoriSoglia === []): ?>
        <p class="text-sm text-slate-500">Nessun valore fuori soglia: ottimo lavoro.</p>
    <?php else: ?>
        <ul class="divide-y divide-slate-100">
            <?php foreach ($fuoriSoglia as $voce): ?>
                <li class="flex flex-wrap items-baseline justify-between gap-2 py-2 text-sm">
                    <a class="font-medium text-slate-700 hover:underline" href="<?= site_url('giorno/' . $voce['giorno']) ?>">
                        <?= esc(data_estesa($voce['giorno'])) ?>
                    </a>
                    <span class="text-slate-500"><?= esc(slot_label($voce['slot'])) ?></span>
                    <span class="pillola <?= $voce['stato'] === 'alto' ? 'bg-rose-100 text-rose-800' : 'bg-sky-100 text-sky-800' ?>">
                        <?= esc(format_value($voce['riga']['valore'])) ?> mg/dl
                    </span>
                    <?php if (($voce['riga']['nota'] ?? '') !== ''): ?>
                        <span class="w-full text-xs text-slate-500">📝 <?= esc($voce['riga']['nota']) ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
