<?= $this->extend('layouts/auth') ?>
<?= $this->section('contenuto') ?>

<h2 class="mb-1 text-lg font-semibold text-slate-900">Accedi</h2>
<p class="mb-4 text-sm text-slate-600">Dopo la password ti invieremo un link di conferma via email.</p>

<form method="post" action="<?= site_url('accedi') ?>" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label class="etichetta" for="email">Email</label>
        <input class="campo" type="email" id="email" name="email" required autocomplete="username"
               inputmode="email" placeholder="nome@esempio.it" value="<?= esc(old('email'), 'attr') ?>">
    </div>

    <div>
        <label class="etichetta" for="password">Password</label>
        <input class="campo" type="password" id="password" name="password" required autocomplete="current-password"
               placeholder="La tua password">
    </div>

    <label class="flex items-start gap-2 text-sm text-slate-600">
        <input type="checkbox" name="ricorda" value="1" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-verde-600 focus:ring-verde-400">
        <span>Ricorda questo dispositivo per 30 giorni (niente email a ogni accesso)</span>
    </label>

    <button class="bottone w-full" type="submit">Continua</button>
</form>

<p class="mt-4 text-center text-sm">
    <a class="text-verde-700 underline" href="<?= site_url('password/recupera') ?>">Password dimenticata?</a>
</p>

<?= $this->endSection() ?>
