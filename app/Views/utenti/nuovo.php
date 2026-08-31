<?= $this->extend('layouts/main') ?>
<?= $this->section('contenuto') ?>

<h1 class="mb-4 text-lg font-bold text-slate-900">Nuovo utente</h1>

<form method="post" action="<?= site_url('utenti/nuovo') ?>" class="scheda-bianca max-w-lg space-y-4">
    <?= csrf_field() ?>

    <div>
        <label class="etichetta" for="nome">Nome</label>
        <input class="campo" type="text" id="nome" name="nome" value="<?= esc(old('nome'), 'attr') ?>" required>
    </div>

    <div>
        <label class="etichetta" for="email">Email</label>
        <input class="campo" type="email" id="email" name="email" value="<?= esc(old('email'), 'attr') ?>" required>
        <p class="mt-1 text-xs text-slate-500">
            A questo indirizzo arriva il link per scegliere la password. Serve anche
            come secondo fattore a ogni accesso.
        </p>
    </div>

    <div>
        <label class="etichetta" for="data_presunta_parto">Data presunta del parto (facoltativa)</label>
        <input class="campo" type="date" id="data_presunta_parto" name="data_presunta_parto"
               value="<?= esc(old('data_presunta_parto'), 'attr') ?>">
    </div>

    <div>
        <label class="etichetta" for="ruolo">Ruolo</label>
        <select class="campo" id="ruolo" name="ruolo">
            <option value="utente" <?= old('ruolo') === 'amministratore' ? '' : 'selected' ?>>
                Utente — vede solo il proprio diario
            </option>
            <option value="amministratore" <?= old('ruolo') === 'amministratore' ? 'selected' : '' ?>>
                Amministratore — può creare e gestire gli account
            </option>
        </select>
    </div>

    <div class="flex items-center gap-2">
        <button class="bottone" type="submit">Crea e invia l'invito</button>
        <a class="bottone-chiaro" href="<?= site_url('utenti') ?>">Annulla</a>
    </div>
</form>

<?= $this->endSection() ?>
