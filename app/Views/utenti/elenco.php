<?= $this->extend('layouts/main') ?>
<?= $this->section('contenuto') ?>
<?php
/**
 * @var list<array<string, mixed>> $utenti
 * @var array<string, mixed>       $utente
 */
$io = (int) $utente['id'];
?>

<div class="mb-4 flex items-center justify-between gap-3">
    <h1 class="text-lg font-bold text-slate-900">Utenti</h1>
    <a class="bottone" href="<?= site_url('utenti/nuovo') ?>">Nuovo utente</a>
</div>

<p class="mb-4 text-sm text-slate-600">
    Ogni utente vede soltanto il proprio diario. La password la sceglie lei dal link
    che riceve per email: nessun altro la conosce, nemmeno chi crea l'account.
</p>

<div class="scheda-bianca overflow-x-auto">
    <table class="w-full min-w-[40rem] text-sm">
        <thead>
            <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                <th class="py-2 pr-3 font-semibold">Nome</th>
                <th class="py-2 pr-3 font-semibold">Email</th>
                <th class="py-2 pr-3 font-semibold">Ruolo</th>
                <th class="py-2 pr-3 font-semibold">Ultimo accesso</th>
                <th class="py-2 pr-3 font-semibold">Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utenti as $riga): ?>
                <?php
                $id        = (int) $riga['id'];
                $attivo    = (int) $riga['attivo'] === 1;
                $eAdmin    = $riga['ruolo'] === 'amministratore';
                $sonoIo    = $id === $io;
                ?>
                <tr class="border-b border-slate-100 last:border-0 <?= $attivo ? '' : 'text-slate-400' ?>">
                    <td class="py-3 pr-3">
                        <span class="font-medium"><?= esc($riga['nome']) ?></span>
                        <?php if ($sonoIo): ?>
                            <span class="ml-1 text-xs text-slate-500">(tu)</span>
                        <?php endif; ?>
                        <?php if (! $attivo): ?>
                            <span class="pillola ml-1 bg-slate-100 text-slate-600">disattivato</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 pr-3 break-all"><?= esc($riga['email']) ?></td>
                    <td class="py-3 pr-3">
                        <?php if ($eAdmin): ?>
                            <span class="pillola bg-verde-50 text-verde-800">amministratore</span>
                        <?php else: ?>
                            <span class="text-slate-500">utente</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 pr-3 text-slate-500">
                        <?= $riga['ultimo_accesso'] ? esc(date('d/m/Y H:i', strtotime((string) $riga['ultimo_accesso']))) : 'mai' ?>
                    </td>
                    <td class="py-3 pr-3">
                        <div class="flex flex-wrap items-center gap-1">
                            <form method="post" action="<?= site_url('utenti/' . $id . '/invito') ?>">
                                <?= csrf_field() ?>
                                <button class="bottone-chiaro text-xs" type="submit">Invia link password</button>
                            </form>

                            <?php if (! $sonoIo): ?>
                                <form method="post" action="<?= site_url('utenti/' . $id . '/ruolo') ?>">
                                    <?= csrf_field() ?>
                                    <button class="bottone-chiaro text-xs" type="submit">
                                        <?= $eAdmin ? 'Togli amministratore' : 'Rendi amministratore' ?>
                                    </button>
                                </form>

                                <form method="post" action="<?= site_url('utenti/' . $id . '/stato') ?>">
                                    <?= csrf_field() ?>
                                    <button class="bottone-chiaro text-xs <?= $attivo ? 'text-rose-700' : '' ?>" type="submit">
                                        <?= $attivo ? 'Disattiva' : 'Riattiva' ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p class="mt-4 text-xs text-slate-500">
    Gli account non si cancellano: disattivarli impedisce l'accesso senza perdere
    le misurazioni già registrate.
</p>

<?= $this->endSection() ?>
