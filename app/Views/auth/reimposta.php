<?= $this->extend('layouts/auth') ?>
<?= $this->section('contenuto') ?>

<?php /** @var bool $invito */ $invito ??= false; ?>

<h2 class="mb-1 text-lg font-semibold text-slate-900">
    <?= $invito ? 'Scegli la tua password' : 'Nuova password' ?>
</h2>
<p class="mb-4 text-sm text-slate-600">
    <?= $invito
        ? 'Il tuo diario è pronto: scegli una password di almeno 10 caratteri e potrai accedere.'
        : 'Scegli una password di almeno 10 caratteri.' ?>
</p>

<form method="post" action="<?= site_url('password/reimposta/' . $token) ?>" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label class="etichetta" for="password">Nuova password</label>
        <input class="campo" type="password" id="password" name="password" required autocomplete="new-password"
               placeholder="Almeno 10 caratteri">
    </div>

    <div>
        <label class="etichetta" for="conferma">Ripeti la password</label>
        <input class="campo" type="password" id="conferma" name="conferma" required autocomplete="new-password"
               placeholder="Ripeti la password">
    </div>

    <button class="bottone w-full" type="submit">Salva la password</button>
</form>

<?= $this->endSection() ?>
