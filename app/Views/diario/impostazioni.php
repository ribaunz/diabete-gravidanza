<?= $this->extend('layouts/main') ?>
<?= $this->section('contenuto') ?>
<?php
/**
 * @var array<string, mixed>       $utente
 * @var list<array<string, mixed>> $dispositivi
 */
?>

<h1 class="mb-4 text-lg font-bold text-slate-900">Impostazioni</h1>

<div class="grid gap-4 lg:grid-cols-2">
    <form method="post" action="<?= site_url('impostazioni') ?>" class="scheda-bianca space-y-4">
        <?= csrf_field() ?>
        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Profilo e soglie</h2>

        <div>
            <label class="etichetta" for="nome">Nome (stampato sulla scheda)</label>
            <input class="campo" type="text" id="nome" name="nome" value="<?= esc($utente['nome'], 'attr') ?>">
        </div>

        <div>
            <label class="etichetta" for="data_presunta_parto">Data presunta del parto</label>
            <input class="campo" type="date" id="data_presunta_parto" name="data_presunta_parto"
                   value="<?= esc($utente['data_presunta_parto'], 'attr') ?>">
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div>
                <label class="etichetta" for="soglia_digiuno">A digiuno</label>
                <input class="campo" type="number" min="60" max="300" id="soglia_digiuno" name="soglia_digiuno"
                       value="<?= (int) $utente['soglia_digiuno'] ?>">
            </div>
            <div>
                <label class="etichetta" for="soglia_post_1h">1 ora dopo</label>
                <input class="campo" type="number" min="60" max="300" id="soglia_post_1h" name="soglia_post_1h"
                       value="<?= (int) $utente['soglia_post_1h'] ?>">
            </div>
            <div>
                <label class="etichetta" for="soglia_post_2h">2 ore dopo</label>
                <input class="campo" type="number" min="60" max="300" id="soglia_post_2h" name="soglia_post_2h"
                       value="<?= (int) $utente['soglia_post_2h'] ?>">
            </div>
        </div>
        <p class="-mt-2 text-xs text-slate-500">
            Valori della scheda: 90 a digiuno, 130 a un'ora, 120 a due ore. Cambiali solo se te lo indica il diabetologo.
        </p>

        <div>
            <label class="etichetta" for="intestazione">Intestazione della scheda</label>
            <input class="campo" type="text" id="intestazione" name="intestazione"
                   value="<?= esc($utente['intestazione'] ?? '', 'attr') ?>" placeholder="Azienda USL Toscana Nord Ovest">
        </div>

        <label class="flex items-start gap-2 text-sm text-slate-600">
            <input type="checkbox" name="mostra_tutte_colonne" value="1" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-verde-600"
                   <?= (int) $utente['mostra_tutte_colonne'] === 1 ? 'checked' : '' ?>>
            <span>Mostra sempre anche le colonne barrate (insulina, controlli a 2 ore, prima dei pasti)</span>
        </label>

        <button class="bottone" type="submit">Salva</button>
    </form>

    <div class="space-y-4">
        <form method="post" action="<?= site_url('impostazioni/password') ?>" class="scheda-bianca space-y-4">
            <?= csrf_field() ?>
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Password</h2>

            <div>
                <label class="etichetta" for="attuale">Password attuale</label>
                <input class="campo" type="password" id="attuale" name="attuale" autocomplete="current-password" required>
            </div>
            <div>
                <label class="etichetta" for="password">Nuova password</label>
                <input class="campo" type="password" id="password" name="password" autocomplete="new-password" required>
            </div>
            <div>
                <label class="etichetta" for="conferma">Ripeti la nuova password</label>
                <input class="campo" type="password" id="conferma" name="conferma" autocomplete="new-password" required>
            </div>

            <button class="bottone" type="submit">Aggiorna la password</button>
        </form>

        <div class="scheda-bianca">
            <h2 class="mb-1 text-sm font-bold uppercase tracking-wide text-slate-500">Dispositivi ricordati</h2>
            <p class="mb-3 text-xs text-slate-500">
                Su questi dispositivi il secondo fattore è già stato verificato: l'accesso richiede solo la password
                fino alla scadenza.
            </p>

            <?php if ($dispositivi === []): ?>
                <p class="text-sm text-slate-500">Nessun dispositivo ricordato: ogni accesso richiede il link via email.</p>
            <?php else: ?>
                <ul class="divide-y divide-slate-100">
                    <?php foreach ($dispositivi as $dispositivo): ?>
                        <li class="flex items-center justify-between gap-3 py-2">
                            <div class="text-sm">
                                <p class="font-medium text-slate-700"><?= esc($dispositivo['etichetta'] ?? 'Dispositivo') ?></p>
                                <p class="text-xs text-slate-500">
                                    scade il <?= esc(date('d/m/Y', strtotime((string) $dispositivo['scade_il']))) ?>
                                    <?php if ($dispositivo['ultimo_uso'] !== null): ?>
                                        · ultimo accesso <?= esc(date('d/m/Y H:i', strtotime((string) $dispositivo['ultimo_uso']))) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <form method="post" action="<?= site_url('impostazioni/dispositivi/' . $dispositivo['id'] . '/revoca') ?>">
                                <?= csrf_field() ?>
                                <button class="text-sm font-medium text-rose-600 hover:underline" type="submit">Revoca</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="scheda-bianca text-sm text-slate-600">
            <h2 class="mb-1 text-sm font-bold uppercase tracking-wide text-slate-500">Account</h2>
            <p>Email: <strong><?= esc($utente['email']) ?></strong></p>
            <p class="mt-1 text-xs text-slate-500">
                I link di accesso arrivano a questo indirizzo. Per cambiarlo modifica il record nel database.
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
