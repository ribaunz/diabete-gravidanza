<?php
/**
 * @var string $nome
 * @var string $link
 * @var string $codice
 * @var string $scopo
 * @var string $validita
 */
$invito = $scopo === 'invito';
$reset  = $scopo === 'reset' || $invito;
?>
<!DOCTYPE html>
<html lang="it">
<head><meta charset="utf-8"><title>Diario glicemie</title></head>
<body style="margin:0;padding:24px;background:#f4f1ec;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;margin:0 auto;background:#ffffff;border-radius:16px;padding:28px;">
        <tr><td>
            <p style="margin:0 0 4px;font-size:13px;color:#0d9488;font-weight:bold;">Diario glicemie</p>
            <h1 style="margin:0 0 16px;font-size:20px;">
                <?= $invito ? 'Benvenuta nel diario' : ($reset ? 'Reimposta la tua password' : 'Conferma il tuo accesso') ?>
            </h1>

            <p style="margin:0 0 16px;font-size:15px;line-height:1.5;">
                Ciao <?= esc($nome) ?>,
                <?= $reset
                    ? 'hai chiesto di reimpostare la password del diario. Apri il link qui sotto per sceglierne una nuova.'
                    : 'per completare l\'accesso apri il link qui sotto. È il secondo passaggio di verifica: senza di esso la password da sola non basta.' ?>
            </p>

            <p style="margin:0 0 20px;">
                <a href="<?= esc($link, 'attr') ?>"
                   style="display:inline-block;background:#0d9488;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:12px;font-weight:bold;font-size:15px;">
                    <?= $invito ? 'Scegli la tua password' : ($reset ? 'Scegli una nuova password' : 'Entra nel diario') ?>
                </a>
            </p>

            <?php if (! $reset): ?>
                <p style="margin:0 0 6px;font-size:14px;">Oppure inserisci questo codice nell'app:</p>
                <p style="margin:0 0 18px;font-size:30px;letter-spacing:8px;font-weight:bold;color:#0f766e;">
                    <?= esc($codice) ?>
                </p>
            <?php endif; ?>

            <p style="margin:0 0 8px;font-size:13px;color:#6b7280;">
                Il link vale <?= esc($validita) ?> e può essere usato una sola volta.
            </p>
            <p style="margin:0;font-size:13px;color:#6b7280;">
                Se non hai richiesto tu questo messaggio puoi ignorarlo: senza il link nessuno può entrare.
            </p>

            <p style="margin:20px 0 0;font-size:12px;color:#9ca3af;word-break:break-all;">
                Se il pulsante non funziona copia questo indirizzo nel browser:<br><?= esc($link) ?>
            </p>
        </td></tr>
    </table>
</body>
</html>
