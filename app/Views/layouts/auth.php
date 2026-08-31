<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0d9488">
    <title><?= esc($titolo ?? 'Diario glicemie') ?></title>
    <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>">
    <link rel="icon" href="<?= base_url('assets/img/icona.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset_versionato('assets/css/app.css') ?>">
</head>
<body class="min-h-screen bg-gradient-to-b from-verde-50 via-sabbia-50 to-sabbia-100">
    <main class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-10">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-verde-600 text-2xl text-white shadow-lg shadow-verde-200">
                ♥
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Diario glicemie</h1>
            <p class="text-sm text-slate-600">Monitoraggio del diabete gestazionale</p>
        </div>

        <div class="scheda-bianca">
            <?= $this->include('partials/flash') ?>
            <?= $this->renderSection('contenuto') ?>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            I dati restano sul tuo server. Accesso protetto da password e verifica via email.
        </p>
    </main>
</body>
</html>
