<?= $this->extend('layouts/auth') ?>
<?= $this->section('contenuto') ?>

<h2 class="mb-1 text-lg font-semibold text-slate-900">Conferma l'accesso</h2>
<p class="mb-4 text-sm text-slate-600">
    Abbiamo inviato un link a <strong><?= esc($email) ?></strong>.
    Aprilo dal telefono o dal computer, oppure inserisci qui il codice a 6 cifre che trovi nella stessa email.
    Vale <?= (int) $durata ?> minuti.
</p>

<form method="post" action="<?= site_url('accedi/verifica') ?>" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label class="etichetta" for="codice">Codice di verifica</label>
        <input class="campo text-center text-2xl font-semibold tracking-[0.4em]" type="text" id="codice" name="codice"
               inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" required autofocus>
    </div>

    <button class="bottone w-full" type="submit">Entra</button>
</form>

<form method="post" action="<?= site_url('accedi/rinvia') ?>" class="mt-3">
    <?= csrf_field() ?>
    <button class="bottone-chiaro w-full" type="submit">Invia un nuovo link</button>
</form>

<p class="mt-4 text-center text-sm">
    <a class="text-slate-500 underline" href="<?= site_url('esci') ?>">Usa un altro account</a>
</p>

<?= $this->endSection() ?>
