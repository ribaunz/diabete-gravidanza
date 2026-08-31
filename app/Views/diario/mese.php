<?= $this->extend('layouts/main') ?>
<?= $this->section('contenuto') ?>
<?php
/**
 * @var string                                              $mese
 * @var int                                                 $anno
 * @var int                                                 $numeroMese
 * @var int                                                 $giorniTotal
 * @var array<string, array<string, array<string, mixed>>>  $misurazioni
 * @var array<string, array<string, mixed>>                 $giornate
 * @var array<string, mixed>                                $utente
 * @var bool                                                $mostraTutte
 */
$colonne = slot_keys(livelli_visibili($mostraTutte));
$oggi    = date('Y-m-d');

$classiCella = [
    'ok'     => 'bg-verde-50 text-verde-900',
    'alto'   => 'bg-rose-50 text-rose-900 font-semibold',
    'basso'  => 'bg-sky-50 text-sky-900 font-semibold',
    'neutro' => '',
];
?>

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
        <a href="<?= site_url('mese/' . $precedente . ($mostraTutte ? '?tutte=1' : '')) ?>" class="bottone-chiaro px-3 py-2">←</a>
        <h1 class="text-lg font-bold text-slate-900"><?= esc(nome_mese($numeroMese, $anno)) ?></h1>
        <a href="<?= site_url('mese/' . $successivo . ($mostraTutte ? '?tutte=1' : '')) ?>" class="bottone-chiaro px-3 py-2">→</a>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="<?= site_url('mese/' . $mese . ($mostraTutte ? '' : '?tutte=1')) ?>" class="bottone-chiaro text-sm">
            <?= $mostraTutte ? 'Solo glicemie' : 'Tutte le colonne' ?>
        </a>
        <a href="<?= site_url('esporta/pdf/' . $mese) ?>" class="bottone text-sm" target="_blank" rel="noopener">Scarica PDF</a>
    </div>
</div>

<!-- Griglia completa: da tablet in su, con salvataggio automatico cella per cella -->
<div class="hidden overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
    <table class="w-full min-w-[46rem] table-fixed border-collapse text-center text-sm" id="griglia-mese"
           data-url-slot="<?= site_url('giorno') ?>">
        <colgroup>
            <col style="width: 5.5rem">
            <?php foreach ($colonne as $slot): ?>
                <col>
            <?php endforeach; ?>
            <col style="width: 3.5rem">
        </colgroup>
        <thead>
            <tr class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <th class="sticky left-0 z-10 bg-slate-50 px-2 py-2 text-left">Giorno</th>
                <?php foreach ($colonne as $slot): ?>
                    <?php $meta = slot_meta($slot); ?>
                    <th class="px-1 py-2 font-medium leading-tight <?= $meta['livello'] === 'base' ? '' : 'text-slate-400' ?>">
                        <?= esc($meta['label']) ?>
                        <?php if ($meta['threshold'] !== null): ?>
                            <span class="block text-[10px] font-normal normal-case text-slate-400">
                                &lt; <?= slot_threshold($slot, $utente) ?>
                            </span>
                        <?php endif; ?>
                    </th>
                <?php endforeach; ?>
                <th class="px-2 py-2 font-medium">Note</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($g = 1; $g <= $giorniTotal; $g++): ?>
                <?php
                $data     = sprintf('%04d-%02d-%02d', $anno, $numeroMese, $g);
                $futuro   = $data > $oggi;
                $noteDelDi = 0;

                foreach (slot_keys() as $s) {
                    if (($misurazioni[$data][$s]['nota'] ?? null) !== null && $misurazioni[$data][$s]['nota'] !== '') {
                        $noteDelDi++;
                    }
                }

                $notaGiornata = trim((string) ($giornate[$data]['nota'] ?? ''));
                ?>
                <tr class="border-t border-slate-100 <?= $data === $oggi ? 'bg-verde-50/40' : '' ?>">
                    <th class="sticky left-0 z-10 whitespace-nowrap bg-white px-2 py-1 text-left text-xs font-semibold text-slate-600">
                        <a class="hover:underline" href="<?= site_url('giorno/' . $data) ?>">
                            <?= $g ?> <span class="font-normal text-slate-400"><?= esc(giorni_settimana()[(int) date('N', strtotime($data))]) ?></span>
                        </a>
                    </th>

                    <?php foreach ($colonne as $slot): ?>
                        <?php
                        $meta   = slot_meta($slot);
                        $riga   = $misurazioni[$data][$slot] ?? null;
                        $stato  = value_status($slot, $riga['valore'] ?? null, $utente);
                        $valore = $riga !== null ? format_value($riga['valore']) : '';
                        ?>
                        <td class="p-0.5">
                            <input class="cella w-full rounded-lg border border-slate-200 bg-white px-1 py-1.5 text-center hover:border-slate-300 focus:border-verde-500 focus:outline-none focus:ring-1 focus:ring-verde-200 disabled:border-transparent disabled:bg-transparent <?= $classiCella[$stato] ?>"
                                   type="text" inputmode="decimal" value="<?= esc($valore, 'attr') ?>"
                                   data-giorno="<?= esc($data, 'attr') ?>" data-slot="<?= esc($slot, 'attr') ?>" data-campo="valore"
                                   aria-label="<?= esc($meta['label'] . ' del ' . $g, 'attr') ?>" <?= $futuro ? 'disabled' : '' ?>>
                        </td>
                    <?php endforeach; ?>

                    <td class="px-2 py-1 text-xs">
                        <a href="<?= site_url('giorno/' . $data) ?>" class="text-slate-500 hover:text-verde-700 hover:underline">
                            <?php if ($noteDelDi > 0 || $notaGiornata !== ''): ?>
                                📝 <?= $noteDelDi + ($notaGiornata !== '' ? 1 : 0) ?>
                            <?php else: ?>
                                <span class="text-slate-300">+</span>
                            <?php endif; ?>
                        </a>
                    </td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
    <p class="border-t border-slate-100 px-3 py-2 text-xs text-slate-500">
        I valori si salvano da soli appena esci dalla casella. Per le note apri la singola giornata.
    </p>
</div>

<!-- Elenco compatto per telefono -->
<div class="space-y-2 md:hidden">
    <?php for ($g = $giorniTotal; $g >= 1; $g--): ?>
        <?php
        $data   = sprintf('%04d-%02d-%02d', $anno, $numeroMese, $g);
        $slots  = $misurazioni[$data] ?? [];
        $futuro = $data > $oggi;

        if ($futuro && $slots === []) {
            continue;
        }
        ?>
        <a href="<?= site_url('giorno/' . $data) ?>" class="block rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
            <div class="mb-1 flex items-center justify-between">
                <span class="font-semibold text-slate-800">
                    <?= $g ?> <?= esc(mesi_italiani()[$numeroMese]) ?>
                    <span class="text-xs font-normal text-slate-400"><?= esc(giorni_settimana()[(int) date('N', strtotime($data))]) ?></span>
                </span>
                <?php if ($data === $oggi): ?>
                    <span class="pillola bg-verde-100 text-verde-800">oggi</span>
                <?php endif; ?>
            </div>

            <?php if ($slots === []): ?>
                <p class="text-sm text-slate-400">Nessuna misurazione registrata</p>
            <?php else: ?>
                <div class="flex flex-wrap gap-1.5">
                    <?php foreach ($colonne as $slot): ?>
                        <?php
                        $riga = $slots[$slot] ?? null;

                        if ($riga === null) {
                            continue;
                        }

                        $stato = value_status($slot, $riga['valore'], $utente);
                        $badge = ['ok' => 'bg-verde-100 text-verde-800', 'alto' => 'bg-rose-100 text-rose-800', 'basso' => 'bg-sky-100 text-sky-800', 'neutro' => 'bg-slate-100 text-slate-600'][$stato];
                        ?>
                        <span class="pillola <?= $badge ?>">
                            <?= esc(str_replace(['1 ora dopo ', '2 ore dopo ', 'A digiuno'], ['1h ', '2h ', 'digiuno'], slot_label($slot))) ?>:
                            <?= esc(format_value($riga['valore'])) ?>
                            <?= ($riga['nota'] ?? '') !== '' ? ' 📝' : '' ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </a>
    <?php endfor; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
    // Salvataggio automatico delle celle della griglia mensile.
    (function () {
        const griglia = document.getElementById('griglia-mese');
        if (!griglia) return;

        const base = griglia.dataset.urlSlot;
        const csrfNome = '<?= csrf_token() ?>';
        let csrfValore = '<?= csrf_hash() ?>';

        const salva = async (campo) => {
            const corpo = new FormData();
            corpo.append(csrfNome, csrfValore);
            corpo.append(campo.dataset.campo, campo.value.trim());

            campo.classList.add('opacity-50');

            try {
                const risposta = await fetch(`${base}/${campo.dataset.giorno}/slot/${campo.dataset.slot}`, {
                    method: 'POST',
                    body: corpo,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });

                const dati = await risposta.json().catch(() => ({}));
                if (dati.csrf) csrfValore = dati.csrf;

                if (!risposta.ok) {
                    campo.classList.add('ring-2', 'ring-rose-400');
                    if (dati.errore) alert(dati.errore);
                    return;
                }

                campo.classList.remove('ring-2', 'ring-rose-400');
                campo.classList.remove('bg-verde-50', 'bg-rose-50', 'bg-sky-50', 'text-verde-900', 'text-rose-900', 'text-sky-900', 'font-semibold');

                const stile = {
                    ok: ['bg-verde-50', 'text-verde-900'],
                    alto: ['bg-rose-50', 'text-rose-900', 'font-semibold'],
                    basso: ['bg-sky-50', 'text-sky-900', 'font-semibold'],
                }[dati.stato];

                if (stile) campo.classList.add(...stile);
            } catch (errore) {
                campo.classList.add('ring-2', 'ring-rose-400');
            } finally {
                campo.classList.remove('opacity-50');
            }
        };

        griglia.addEventListener('change', (evento) => {
            const campo = evento.target.closest('.cella');
            if (campo) salva(campo);
        });

        // Invio: si passa alla casella sotto, come in un foglio di calcolo.
        griglia.addEventListener('keydown', (evento) => {
            if (evento.key !== 'Enter') return;
            const campo = evento.target.closest('input.cella');
            if (!campo) return;

            evento.preventDefault();
            campo.blur();

            const celle = [...griglia.querySelectorAll('input.cella:not([disabled])')];
            const successiva = celle[celle.indexOf(campo) + <?= count($colonne) ?>];
            if (successiva) successiva.focus();
        });
    })();
</script>
<?= $this->endSection() ?>
