<?php
$errori = session()->getFlashdata('errori');
?>
<?php if (session()->getFlashdata('successo')): ?>
    <div class="mb-4 rounded-xl border border-verde-200 bg-verde-50 px-4 py-3 text-sm text-verde-800">
        <?= esc(session()->getFlashdata('successo')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('errore')): ?>
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <?= esc(session()->getFlashdata('errore')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('avviso')): ?>
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <?= esc(session()->getFlashdata('avviso')) ?>
    </div>
<?php endif; ?>

<?php if (is_array($errori) && $errori !== []): ?>
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <ul class="list-inside list-disc space-y-1">
            <?php foreach ($errori as $errore): ?>
                <li><?= esc($errore) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('debug_link')): ?>
    <div class="mb-4 break-all rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-xs text-sky-900">
        <strong>Ambiente di sviluppo</strong> — l'email non è stata inviata davvero. Link di accesso:<br>
        <a class="font-mono underline" href="<?= esc(session()->getFlashdata('debug_link'), 'attr') ?>">
            <?= esc(session()->getFlashdata('debug_link')) ?>
        </a>
    </div>
<?php endif; ?>
