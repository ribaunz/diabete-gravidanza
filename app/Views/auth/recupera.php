<?= $this->extend('layouts/auth') ?>
<?= $this->section('contenuto') ?>

<h2 class="mb-1 text-lg font-semibold text-slate-900">Password dimenticata</h2>
<p class="mb-4 text-sm text-slate-600">Inserisci la tua email: ti invieremo un link per sceglierne una nuova.</p>

<form method="post" action="<?= site_url('password/recupera') ?>" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label class="etichetta" for="email">Email</label>
        <input class="campo" type="email" id="email" name="email" required placeholder="nome@esempio.it"
               value="<?= esc(old('email'), 'attr') ?>">
    </div>

    <button class="bottone w-full" type="submit">Invia il link</button>
</form>

<p class="mt-4 text-center text-sm">
    <a class="text-verde-700 underline" href="<?= site_url('accedi') ?>">Torna all'accesso</a>
</p>

<?= $this->endSection() ?>
