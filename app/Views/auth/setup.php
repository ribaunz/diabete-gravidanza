<?= $this->extend('layouts/auth') ?>
<?= $this->section('contenuto') ?>

<h2 class="mb-1 text-lg font-semibold text-slate-900">Primo avvio</h2>
<p class="mb-4 text-sm text-slate-600">Crea l'account che userai per registrare le misurazioni.</p>

<form method="post" action="<?= site_url('installazione') ?>" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label class="etichetta" for="nome">Nome</label>
        <input class="campo" type="text" id="nome" name="nome" required value="<?= esc(old('nome'), 'attr') ?>">
    </div>

    <div>
        <label class="etichetta" for="email">Email</label>
        <input class="campo" type="email" id="email" name="email" required autocomplete="username"
               value="<?= esc(old('email'), 'attr') ?>">
        <p class="mt-1 text-xs text-slate-500">Qui arriveranno i link di accesso: usa un indirizzo che leggi dal telefono.</p>
    </div>

    <div>
        <label class="etichetta" for="data_presunta_parto">Data presunta del parto <span class="font-normal text-slate-400">(facoltativa)</span></label>
        <input class="campo" type="date" id="data_presunta_parto" name="data_presunta_parto"
               value="<?= esc(old('data_presunta_parto'), 'attr') ?>">
    </div>

    <div>
        <label class="etichetta" for="password">Password <span class="font-normal text-slate-400">(almeno 10 caratteri)</span></label>
        <input class="campo" type="password" id="password" name="password" required autocomplete="new-password">
    </div>

    <div>
        <label class="etichetta" for="conferma">Ripeti la password</label>
        <input class="campo" type="password" id="conferma" name="conferma" required autocomplete="new-password">
    </div>

    <button class="bottone w-full" type="submit">Crea l'account</button>
</form>

<?= $this->endSection() ?>
