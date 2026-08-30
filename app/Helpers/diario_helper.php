<?php

/**
 * Definizione delle misurazioni della scheda "Stick a giorni alterni x 4 volte al giorno"
 * (U.O.S. Diabetologia e Malattie Metaboliche - Ospedale di Viareggio).
 *
 * Le colonne bianche della scheda cartacea sono quelle da compilare: chetonuria, a digiuno,
 * 1 ora dopo colazione, 1 ora dopo pranzo, 1 ora dopo cena. Le altre colonne sono barrate
 * sulla scheda ma restano disponibili nell'app per chi ha una terapia insulinica.
 */

if (! function_exists('slot_definitions')) {
    /**
     * @return array<string, array{
     *     group: string, type: string, label: string, sheet: list<string>,
     *     threshold: int|null, primary: bool, unit: string, time_hint: string
     * }>
     */
    function slot_definitions(): array
    {
        static $slots = null;

        if ($slots !== null) {
            return $slots;
        }

        $slots = [
            'chetonuria' => [
                'group' => 'giorno', 'type' => 'chetonuria',
                'label' => 'Chetonuria', 'sheet' => ['chetonuria'],
                'threshold' => null, 'primary' => true, 'unit' => '', 'time_hint' => '07:00',
            ],
            'digiuno' => [
                'group' => 'colazione', 'type' => 'glicemia',
                'label' => 'A digiuno', 'sheet' => ['A digiuno', '(< 90 mg/dl)'],
                'threshold' => 90, 'primary' => true, 'unit' => 'mg/dl', 'time_hint' => '07:30',
            ],
            'insulina_colazione' => [
                'group' => 'colazione', 'type' => 'insulina',
                'label' => 'Insulina colazione', 'sheet' => ['Unità di', 'insulina'],
                'threshold' => null, 'primary' => false, 'unit' => 'U', 'time_hint' => '07:45',
            ],
            'colazione_1h' => [
                'group' => 'colazione', 'type' => 'glicemia',
                'label' => '1 ora dopo colazione', 'sheet' => ['1 ora dopo la', 'colazione', '(< 130 mg/dl)'],
                'threshold' => 130, 'primary' => true, 'unit' => 'mg/dl', 'time_hint' => '09:00',
            ],
            'colazione_2h' => [
                'group' => 'colazione', 'type' => 'glicemia',
                'label' => '2 ore dopo colazione', 'sheet' => ['2 ore dopo', 'il pasto', '(< 120 mg/dl)'],
                'threshold' => 120, 'primary' => false, 'unit' => 'mg/dl', 'time_hint' => '10:00',
            ],
            'pre_pranzo' => [
                'group' => 'pranzo', 'type' => 'glicemia',
                'label' => 'Prima di pranzo', 'sheet' => ['Prima di', 'pranzo'],
                'threshold' => null, 'primary' => false, 'unit' => 'mg/dl', 'time_hint' => '12:30',
            ],
            'insulina_pranzo' => [
                'group' => 'pranzo', 'type' => 'insulina',
                'label' => 'Insulina pranzo', 'sheet' => ['Unità di', 'insulina'],
                'threshold' => null, 'primary' => false, 'unit' => 'U', 'time_hint' => '12:45',
            ],
            'pranzo_1h' => [
                'group' => 'pranzo', 'type' => 'glicemia',
                'label' => '1 ora dopo pranzo', 'sheet' => ['1 ora dopo', 'il pasto', '(< 130 mg/dl)'],
                'threshold' => 130, 'primary' => true, 'unit' => 'mg/dl', 'time_hint' => '14:00',
            ],
            'pranzo_2h' => [
                'group' => 'pranzo', 'type' => 'glicemia',
                'label' => '2 ore dopo pranzo', 'sheet' => ['2 ore dopo', 'il pasto', '(< 120 mg/dl)'],
                'threshold' => 120, 'primary' => false, 'unit' => 'mg/dl', 'time_hint' => '15:00',
            ],
            'pre_cena' => [
                'group' => 'cena', 'type' => 'glicemia',
                'label' => 'Prima di cena', 'sheet' => ['Prima di', 'cena'],
                'threshold' => null, 'primary' => false, 'unit' => 'mg/dl', 'time_hint' => '19:30',
            ],
            'insulina_cena' => [
                'group' => 'cena', 'type' => 'insulina',
                'label' => 'Insulina cena', 'sheet' => ['Unità di', 'insulina'],
                'threshold' => null, 'primary' => false, 'unit' => 'U', 'time_hint' => '19:45',
            ],
            'cena_1h' => [
                'group' => 'cena', 'type' => 'glicemia',
                'label' => '1 ora dopo cena', 'sheet' => ['1 ora dopo', 'cena', '(< 130 mg/dl)'],
                'threshold' => 130, 'primary' => true, 'unit' => 'mg/dl', 'time_hint' => '21:00',
            ],
            'cena_2h' => [
                'group' => 'cena', 'type' => 'glicemia',
                'label' => '2 ore dopo cena', 'sheet' => ['2 ore dopo', 'il pasto', '(< 120 mg/dl)'],
                'threshold' => 120, 'primary' => false, 'unit' => 'mg/dl', 'time_hint' => '22:00',
            ],
            'insulina_notte' => [
                'group' => 'notte', 'type' => 'insulina',
                'label' => 'Insulina prima di coricarsi', 'sheet' => ['Unità di', 'insulina'],
                'threshold' => null, 'primary' => false, 'unit' => 'U', 'time_hint' => '23:00',
            ],
        ];

        return $slots;
    }
}

if (! function_exists('slot_keys')) {
    /** @return list<string> */
    function slot_keys(bool $onlyPrimary = false): array
    {
        $keys = array_keys(slot_definitions());

        if (! $onlyPrimary) {
            return $keys;
        }

        return array_values(array_filter($keys, static fn ($k) => slot_definitions()[$k]['primary']));
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
            'giorno'    => 'Giornata',
            'colazione' => 'Colazione',
            'pranzo'    => 'Pranzo',
            'cena'      => 'Cena',
            'notte'     => 'Prima di coricarsi',
        ];
    }
}

if (! function_exists('chetonuria_options')) {
    /** @return array<string, string> */
    function chetonuria_options(): array
    {
        return [
            'assente' => 'Assente',
            'tracce'  => 'Tracce',
            '+'       => '+',
            '++'      => '++',
            '+++'     => '+++',
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
    function format_value(string $slot, int|float|string|null $value, ?string $textValue = null): string
    {
        $meta = slot_meta($slot);

        if (($meta['type'] ?? '') === 'chetonuria') {
            if ($textValue === null || $textValue === '') {
                return '';
            }

            return chetonuria_options()[$textValue] ?? $textValue;
        }

        if ($value === null || $value === '') {
            return '';
        }

        $value = (float) $value;

        return rtrim(rtrim(number_format($value, 1, ',', ''), '0'), ',');
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
