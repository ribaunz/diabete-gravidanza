<?= $this->extend('layouts/main') ?>
<?= $this->section('contenuto') ?>
<?php
/**
 * @var list<string>          $mesi
 * @var string                $corrente
 * @var array<string, mixed>  $utente
 */
$elenco = $mesi;

if (! in_array($corrente, $elenco, true)) {
    array_unshift($elenco, $corrente);
}
?>

<h1 class="mb-1 text-lg font-bold text-slate-900">Esporta la scheda</h1>
<p class="mb-4 text-sm text-slate-600">
    Il PDF riproduce la scheda dell'ambulatorio: una pagina per i giorni 1-15 e una per i giorni 16-31.
    La versione con note aggiunge un elenco delle annotazioni, giorno per giorno.
</p>

<div class="space-y-3">
    <?php foreach ($elenco as $mese): ?>
        <?php [$anno, $numeroMese] = array_map('intval', explode('-', $mese)); ?>
        <div class="scheda-bianca flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-semibold text-slate-800"><?= esc(nome_mese($numeroMese, $anno)) ?></p>
                <p class="text-xs text-slate-500"><?= $mese === $corrente ? 'mese in corso' : 'archivio' ?></p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a class="bottone-chiaro text-sm" target="_blank" rel="noopener" href="<?= site_url('esporta/pdf/' . $mese) ?>">
                    PDF scheda
                </a>
                <a class="bottone text-sm" target="_blank" rel="noopener" href="<?= site_url('esporta/pdf/' . $mese . '?note=1') ?>">
                    PDF con note
                </a>
                <a class="bottone-chiaro text-sm" href="<?= site_url('esporta/csv/' . $mese) ?>">CSV</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<p class="mt-4 text-xs text-slate-500">
    Suggerimento: dal telefono il PDF si apre nel browser e da lì puoi stamparlo o inviarlo per email al diabetologo.
</p>

<?= $this->endSection() ?>
