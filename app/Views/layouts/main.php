<?php
/** @var array<string, mixed>|null $utente */
$utente ??= service('auth')->user();

$voci = [
    ['url' => '/oggi', 'etichetta' => 'Oggi', 'icona' => '📋', 'attivo' => ['giorno', 'oggi']],
    ['url' => '/mese', 'etichetta' => 'Mese', 'icona' => '🗓️', 'attivo' => ['mese']],
    ['url' => '/riepilogo', 'etichetta' => 'Riepilogo', 'icona' => '📈', 'attivo' => ['riepilogo']],
    ['url' => '/esporta', 'etichetta' => 'Esporta', 'icona' => '📄', 'attivo' => ['esporta']],
    ['url' => '/impostazioni', 'etichetta' => 'Impostazioni', 'icona' => '⚙️', 'attivo' => ['impostazioni']],
];

// La gestione utenti compare solo a chi puo usarla.
if (e_amministratore($utente)) {
    $voci[] = ['url' => '/utenti', 'etichetta' => 'Utenti', 'icona' => '👥', 'attivo' => ['utenti']];
}

$primoSegmento = service('uri')->getSegment(1);
$eAttivo       = static fn (array $voce): bool => in_array($primoSegmento, $voce['attivo'], true);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0d9488">
    <title><?= esc($titolo ?? 'Diario glicemie') ?></title>
    <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
    <link rel="icon" href="<?= base_url('assets/img/icona.svg') ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/icona.svg') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="min-h-screen pb-20 md:pb-0">
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
            <a href="<?= site_url('oggi') ?>" class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-verde-600 text-white">♥</span>
                <span class="font-bold text-slate-900">Diario glicemie</span>
            </a>

            <nav class="hidden items-center gap-1 md:flex">
                <?php foreach ($voci as $voce): ?>
                    <a href="<?= site_url(ltrim($voce['url'], '/')) ?>"
                       class="rounded-lg px-3 py-2 text-sm font-medium transition <?= $eAttivo($voce) ? 'bg-verde-50 text-verde-800' : 'text-slate-600 hover:bg-slate-100' ?>">
                        <?= esc($voce['etichetta']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="flex items-center gap-2">
                <span class="hidden text-sm text-slate-500 sm:inline"><?= esc($utente['nome'] ?? '') ?></span>
                <a href="<?= site_url('esci') ?>" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50">Esci</a>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-5">
        <?= $this->include('partials/flash') ?>
        <?= $this->renderSection('contenuto') ?>
    </main>

    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur md:hidden">
        <div class="grid" style="grid-template-columns: repeat(<?= count($voci) ?>, minmax(0, 1fr));">
            <?php foreach ($voci as $voce): ?>
                <a href="<?= site_url(ltrim($voce['url'], '/')) ?>"
                   class="flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium <?= $eAttivo($voce) ? 'text-verde-700' : 'text-slate-500' ?>">
                    <span class="text-lg leading-none"><?= $voce['icona'] ?></span>
                    <?= esc($voce['etichetta']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <?= $this->renderSection('script') ?>
</body>
</html>
