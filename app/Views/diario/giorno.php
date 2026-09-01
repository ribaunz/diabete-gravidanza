<?= $this->extend('layouts/main') ?>
<?= $this->section('contenuto') ?>
<?php
/**
 * @var string                                   $data
 * @var array<string, array<string, mixed>>      $misurazioni
 * @var array<string, mixed>|null                $giornata
 * @var bool                                     $mostraTutte
 * @var array<string, mixed>                     $utente
 */
$gruppi = group_labels();

$livelli       = livelli_visibili($mostraTutte);
$slotPerGruppo = [];

foreach (slot_keys($livelli) as $slot) {
    $slotPerGruppo[slot_meta($slot)['group']][] = $slot;
}

$classiStato = [
    'ok'     => 'border-verde-300 bg-verde-50',
    'alto'   => 'border-rose-300 bg-rose-50',
    'basso'  => 'border-sky-300 bg-sky-50',
    'neutro' => 'border-slate-200 bg-white',
];
$badgeStato = [
    'ok'    => ['classe' => 'bg-verde-100 text-verde-800', 'testo' => 'nel target'],
    'alto'  => ['classe' => 'bg-rose-100 text-rose-800', 'testo' => 'sopra soglia'],
    'basso' => ['classe' => 'bg-sky-100 text-sky-800', 'testo' => 'valore basso'],
];
?>

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
        <a href="<?= site_url('giorno/' . $precedente) ?>" class="bottone-chiaro px-3 py-2" aria-label="Giorno precedente">←</a>
        <div>
            <h1 class="text-lg font-bold leading-tight text-slate-900"><?= esc(data_estesa($data)) ?></h1>
            <p class="text-xs text-slate-500">
                <?= $data === $oggi ? 'Giornata di oggi' : 'Registrazione di una giornata passata' ?>
            </p>
        </div>
        <a href="<?= site_url('giorno/' . $successivo) ?>"
           class="bottone-chiaro px-3 py-2 <?= $data >= $oggi ? 'pointer-events-none opacity-40' : '' ?>"
           aria-label="Giorno successivo">→</a>
    </div>

    <div class="flex items-center gap-2">
        <form method="get" action="<?= site_url('giorno/' . $data) ?>" class="hidden sm:block"
              onsubmit="event.preventDefault(); window.location = '<?= site_url('giorno/') ?>' + this.giorno.value;">
            <input type="date" name="giorno" value="<?= esc($data, 'attr') ?>" max="<?= esc($oggi, 'attr') ?>"
                   class="campo py-2" onchange="window.location = '<?= site_url('giorno/') ?>' + this.value;">
        </form>
        <?php if ($data !== $oggi): ?>
            <a href="<?= site_url('oggi') ?>" class="bottone-chiaro">Oggi</a>
        <?php endif; ?>
    </div>
</div>

<form method="post" action="<?= site_url('giorno/' . $data) ?>" id="modulo-giorno" class="space-y-4">
    <?= csrf_field() ?>

    <?php foreach ($gruppi as $gruppo => $etichettaGruppo): ?>
        <?php if (empty($slotPerGruppo[$gruppo])) {
            continue;
        } ?>
        <section class="scheda-bianca">
            <h2 class="mb-3 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-slate-500">
                <?= esc($etichettaGruppo) ?>
            </h2>

            <div class="grid gap-3 sm:grid-cols-2">
                <?php foreach ($slotPerGruppo[$gruppo] as $slot): ?>
                    <?php
                    $meta      = slot_meta($slot);
                    $riga      = $misurazioni[$slot] ?? null;
                    $valore    = $riga['valore'] ?? null;
                    $nota      = (string) ($riga['nota'] ?? '');
                    $ora       = ($riga['ora'] ?? null) !== null ? substr((string) $riga['ora'], 0, 5) : '';
                    $stato     = value_status($slot, $valore, $utente);
                    $soglia    = slot_threshold($slot, $utente);
                    ?>
                    <div class="rounded-xl border p-3 transition <?= $classiStato[$stato] ?>">
                        <div class="mb-2 flex items-start justify-between gap-2">
                            <div>
                                <p class="font-semibold text-slate-800"><?= esc($meta['label']) ?></p>
                                <p class="text-xs text-slate-500">
                                    <?= $soglia !== null ? 'obiettivo &lt; ' . $soglia . ' mg/dl' : esc($meta['unit']) ?>
                                    <?php if ($meta['livello'] !== 'base'): ?>
                                        <span class="text-slate-400">· facoltativa</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <?php if (isset($badgeStato[$stato])): ?>
                                <span class="pillola <?= $badgeStato[$stato]['classe'] ?>"><?= $badgeStato[$stato]['testo'] ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="flex gap-2">
                            <input class="campo min-w-0 flex-1 text-lg font-semibold" type="text" inputmode="decimal"
                                   name="valore[<?= esc($slot, 'attr') ?>]"
                                   value="<?= esc(format_value($valore), 'attr') ?>"
                                   placeholder="<?= $meta['type'] === 'insulina' ? 'U' : 'mg/dl' ?>"
                                   aria-label="<?= esc($meta['label'], 'attr') ?>">

                            <label class="relative w-28 shrink-0">
                                <input class="campo campo-nativo w-full min-w-0" type="time" name="ora[<?= esc($slot, 'attr') ?>]"
                                       value="<?= esc($ora, 'attr') ?>" aria-label="Ora della misurazione"
                                       oninput="this.setAttribute('value', this.value)">
                                <span class="segnaposto-sovrapposto">Ora</span>
                            </label>
                        </div>

                        <details class="mt-2" <?= $nota !== '' ? 'open' : '' ?>>
                            <summary class="cursor-pointer text-xs font-medium text-slate-500 hover:text-slate-700">
                                Nota<?= $nota !== '' ? '' : ' (facoltativa)' ?>
                            </summary>
                            <textarea class="campo mt-2 text-sm" rows="2" maxlength="500"
                                      name="nota[<?= esc($slot, 'attr') ?>]"
                                      placeholder="Cosa hai mangiato, come ti sentivi, attività fisica…"><?= esc($nota) ?></textarea>
                        </details>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>

    <section class="scheda-bianca">
        <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Nota della giornata</h2>
        <textarea class="campo" rows="3" name="nota_giornata" maxlength="2000"
                  placeholder="Appunti generali da riportare alla visita…"><?= esc($giornata['nota'] ?? '') ?></textarea>

        <div class="mt-3 max-w-[12rem]">
            <label class="etichetta" for="peso">Peso (kg)</label>
            <input class="campo" type="text" inputmode="decimal" id="peso" name="peso" placeholder="kg"
                   value="<?= esc(($giornata['peso'] ?? null) !== null ? rtrim(rtrim(number_format((float) $giornata['peso'], 2, ',', ''), '0'), ',') : '', 'attr') ?>">
        </div>
    </section>

    <div class="sticky bottom-16 z-20 -mx-4 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur md:bottom-0">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-3">
            <a href="<?= site_url('giorno/' . $data . ($mostraTutte ? '' : '?tutte=1')) ?>" class="text-sm text-slate-500 underline">
                <?= $mostraTutte ? 'Mostra solo glicemie a digiuno, 1 e 2 ore' : 'Mostra anche insulina e glicemie prima dei pasti' ?>
            </a>
            <button class="bottone" type="submit">Salva la giornata</button>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
    // Avvisa se si lascia la pagina con modifiche non salvate.
    (function () {
        const modulo = document.getElementById('modulo-giorno');
        let sporco = false;

        modulo.addEventListener('input', () => { sporco = true; });
        modulo.addEventListener('submit', () => { sporco = false; });

        window.addEventListener('beforeunload', (evento) => {
            if (!sporco) return;
            evento.preventDefault();
            evento.returnValue = '';
        });
    })();
</script>
<?= $this->endSection() ?>
