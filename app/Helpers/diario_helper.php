<?php

/**
 * Definizione delle misurazioni della scheda "Stick a giorni alterni x 4 volte al giorno"
 * (U.O.S. Diabetologia e Malattie Metaboliche - Ospedale di Viareggio).
 *
 * Ogni slot ha due attributi che ne governano l'uso:
 *
 *  - `primary`: la colonna è bianca sulla scheda cartacea (le altre sono barrate);
 *    serve solo a impaginare il PDF come il modulo originale.
 *  - `livello`: quanto è in evidenza nell'applicazione.
 *      'base'      glicemie della scheda: a digiuno e 1 ora dopo i tre pasti;
 *      'extra'     controlli a 2 ore dopo il pasto, da compilare quando servono;
 *      'avanzato'  unità di insulina e glicemie prima dei pasti, visibili su richiesta.
 *
 * La chetonuria resta stampata sulla scheda in PDF ma non viene raccolta dall'app,
 * quindi non compare fra gli slot.
 */

if (! function_exists('slot_definitions')) {
    /**
     * @return array<string, array{
     *     group: string, type: string, label: string, sheet: list<string>,
     *     threshold: int|null, primary: bool, livello: string, unit: string, time_hint: string
     * }>
     */
    function slot_definitions(): array
    {
        static $slots = null;

        if ($slots !== null) {
            return $slots;
        }

        $slots = [
            'digiuno' => [
                'group' => 'colazione', 'type' => 'glicemia',
                'label' => 'A digiuno', 'sheet' => ['A digiuno', '(< 90 mg/dl)'],
                'threshold' => 90, 'primary' => true, 'livello' => 'base',
                'unit' => 'mg/dl', 'time_hint' => '07:30',
            ],
            'insulina_colazione' => [
                'group' => 'colazione', 'type' => 'insulina',
                'label' => 'Insulina colazione', 'sheet' => ['Unità di', 'insulina'],
                'threshold' => null, 'primary' => false, 'livello' => 'avanzato',
                'unit' => 'U', 'time_hint' => '07:45',
            ],
            'colazione_1h' => [
                'group' => 'colazione', 'type' => 'glicemia',
                'label' => '1 ora dopo colazione', 'sheet' => ['1 ora dopo la', 'colazione', '(< 130 mg/dl)'],
                'threshold' => 130, 'primary' => true, 'livello' => 'base',
                'unit' => 'mg/dl', 'time_hint' => '09:00',
            ],
            'colazione_2h' => [
                'group' => 'colazione', 'type' => 'glicemia',
                'label' => '2 ore dopo colazione', 'sheet' => ['2 ore dopo', 'il pasto', '(< 120 mg/dl)'],
                'threshold' => 120, 'primary' => false, 'livello' => 'extra',
                'unit' => 'mg/dl', 'time_hint' => '10:00',
            ],
            'pre_pranzo' => [
                'group' => 'pranzo', 'type' => 'glicemia',
                'label' => 'Prima di pranzo', 'sheet' => ['Prima di', 'pranzo'],
                'threshold' => null, 'primary' => false, 'livello' => 'avanzato',
                'unit' => 'mg/dl', 'time_hint' => '12:30',
            ],
            'insulina_pranzo' => [
                'group' => 'pranzo', 'type' => 'insulina',
                'label' => 'Insulina pranzo', 'sheet' => ['Unità di', 'insulina'],
                'threshold' => null, 'primary' => false, 'livello' => 'avanzato',
                'unit' => 'U', 'time_hint' => '12:45',
            ],
            'pranzo_1h' => [
                'group' => 'pranzo', 'type' => 'glicemia',
                'label' => '1 ora dopo pranzo', 'sheet' => ['1 ora dopo', 'il pasto', '(< 130 mg/dl)'],
                'threshold' => 130, 'primary' => true, 'livello' => 'base',
                'unit' => 'mg/dl', 'time_hint' => '14:00',
            ],
            'pranzo_2h' => [
                'group' => 'pranzo', 'type' => 'glicemia',
                'label' => '2 ore dopo pranzo', 'sheet' => ['2 ore dopo', 'il pasto', '(< 120 mg/dl)'],
                'threshold' => 120, 'primary' => false, 'livello' => 'extra',
                'unit' => 'mg/dl', 'time_hint' => '15:00',
            ],
            'pre_cena' => [
                'group' => 'cena', 'type' => 'glicemia',
                'label' => 'Prima di cena', 'sheet' => ['Prima di', 'cena'],
                'threshold' => null, 'primary' => false, 'livello' => 'avanzato',
                'unit' => 'mg/dl', 'time_hint' => '19:30',
            ],
            'insulina_cena' => [
                'group' => 'cena', 'type' => 'insulina',
                'label' => 'Insulina cena', 'sheet' => ['Unità di', 'insulina'],
                'threshold' => null, 'primary' => false, 'livello' => 'avanzato',
                'unit' => 'U', 'time_hint' => '19:45',
            ],
            'cena_1h' => [
                'group' => 'cena', 'type' => 'glicemia',
                'label' => '1 ora dopo cena', 'sheet' => ['1 ora dopo', 'cena', '(< 130 mg/dl)'],
                'threshold' => 130, 'primary' => true, 'livello' => 'base',
                'unit' => 'mg/dl', 'time_hint' => '21:00',
            ],
            'cena_2h' => [
                'group' => 'cena', 'type' => 'glicemia',
                'label' => '2 ore dopo cena', 'sheet' => ['2 ore dopo', 'il pasto', '(< 120 mg/dl)'],
                'threshold' => 120, 'primary' => false, 'livello' => 'extra',
                'unit' => 'mg/dl', 'time_hint' => '22:00',
            ],
            'insulina_notte' => [
                'group' => 'notte', 'type' => 'insulina',
                'label' => 'Insulina prima di coricarsi', 'sheet' => ['Unità di', 'insulina'],
                'threshold' => null, 'primary' => false, 'livello' => 'avanzato',
                'unit' => 'U', 'time_hint' => '23:00',
            ],
        ];

        return $slots;
    }
}

if (! function_exists('slot_keys')) {
    /**
     * Slot dell'applicazione, eventualmente filtrati per livello.
     *
     * @param list<string>|null $livelli es. ['base', 'extra']; null = tutti
     *
     * @return list<string>
     */
    function slot_keys(?array $livelli = null): array
    {
        $keys = array_keys(slot_definitions());

        if ($livelli === null) {
            return $keys;
        }

        return array_values(array_filter(
            $keys,
            static fn ($k) => in_array(slot_definitions()[$k]['livello'], $livelli, true)
        ));
    }
}

if (! function_exists('livelli_visibili')) {
    /**
     * Livelli da mostrare nei moduli: le glicemie della scheda e i controlli a 2 ore
     * sono sempre disponibili, insulina e pre-pasto solo su richiesta.
     *
     * @return list<string>
     */
    function livelli_visibili(bool $mostraTutte): array
    {
        return $mostraTutte ? ['base', 'extra', 'avanzato'] : ['base', 'extra'];
    }
}

if (! function_exists('slot_meta')) {
    /** @return array<string, mixed>|null */
    function slot_meta(string $slot): ?array
    {
        return slot_definitions()[$slot] ?? null;
    }
}

if (! function_exists('slot_label')) {
    function slot_label(string $slot): string
    {
        return slot_definitions()[$slot]['label'] ?? $slot;
    }
}

if (! function_exists('group_labels')) {
    /** @return array<string, string> */
    function group_labels(): array
    {
        return [
            'colazione' => 'Colazione',
            'pranzo'    => 'Pranzo',
            'cena'      => 'Cena',
            'notte'     => 'Prima di coricarsi',
        ];
    }
}

if (! function_exists('slot_threshold')) {
    /**
     * Soglia effettiva per lo slot, tenendo conto delle soglie personalizzate dell'utente.
     *
     * @param array<string, mixed>|null $user
     */
    function slot_threshold(string $slot, ?array $user = null): ?int
    {
        $meta = slot_meta($slot);

        if ($meta === null || $meta['threshold'] === null) {
            return null;
        }

        if ($user === null) {
            return $meta['threshold'];
        }

        return match ($meta['threshold']) {
            90      => (int) ($user['soglia_digiuno'] ?? 90),
            130     => (int) ($user['soglia_post_1h'] ?? 130),
            120     => (int) ($user['soglia_post_2h'] ?? 120),
            default => $meta['threshold'],
        };
    }
}

if (! function_exists('value_status')) {
    /**
     * 'ok' | 'alto' | 'basso' | 'neutro'
     *
     * @param array<string, mixed>|null $user
     */
    function value_status(string $slot, int|float|string|null $value, ?array $user = null): string
    {
        $meta = slot_meta($slot);

        if ($meta === null || $value === null || $value === '' || $meta['type'] !== 'glicemia') {
            return 'neutro';
        }

        $value = (float) $value;

        if ($value > 0 && $value < 60) {
            return 'basso';
        }

        $threshold = slot_threshold($slot, $user);

        if ($threshold === null) {
            return 'neutro';
        }

        return $value < $threshold ? 'ok' : 'alto';
    }
}

if (! function_exists('format_value')) {
    function format_value(int|float|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return rtrim(rtrim(number_format((float) $value, 1, ',', ''), '0'), ',');
    }
}

if (! function_exists('mesi_italiani')) {
    /** @return array<int, string> */
    function mesi_italiani(): array
    {
        return [
            1 => 'gennaio', 2 => 'febbraio', 3 => 'marzo', 4 => 'aprile',
            5 => 'maggio', 6 => 'giugno', 7 => 'luglio', 8 => 'agosto',
            9 => 'settembre', 10 => 'ottobre', 11 => 'novembre', 12 => 'dicembre',
        ];
    }
}

if (! function_exists('nome_mese')) {
    function nome_mese(int $mese, int $anno): string
    {
        return ucfirst(mesi_italiani()[$mese] ?? '') . ' ' . $anno;
    }
}

if (! function_exists('giorni_settimana')) {
    /** @return array<int, string> */
    function giorni_settimana(): array
    {
        return [1 => 'lun', 2 => 'mar', 3 => 'mer', 4 => 'gio', 5 => 'ven', 6 => 'sab', 7 => 'dom'];
    }
}

if (! function_exists('data_estesa')) {
    function data_estesa(string $date): string
    {
        $ts = strtotime($date);
        $g  = giorni_settimana()[(int) date('N', $ts)];

        return sprintf(
            '%s %d %s %d',
            ucfirst((string) $g),
            (int) date('j', $ts),
            mesi_italiani()[(int) date('n', $ts)],
            (int) date('Y', $ts)
        );
    }
}

if (! function_exists('e_amministratore')) {
    /**
     * Vero se l'utente può gestire gli account degli altri.
     *
     * @param array<string, mixed>|null $utente
     */
    function e_amministratore(?array $utente): bool
    {
        return App\Models\UserModel::eAmministratore($utente);
    }
}
