<?php

namespace App\Controllers;

use App\Models\DayModel;
use App\Models\MeasurementModel;
use CodeIgniter\HTTP\ResponseInterface;

/** Inserimento e consultazione delle misurazioni. */
class Diario extends BaseController
{
    public function oggi()
    {
        return redirect()->to('/giorno/' . date('Y-m-d'));
    }

    public function giorno(string $data)
    {
        $data = $this->normalizzaData($data);

        if ($data === null) {
            return redirect()->to('/oggi')->with('errore', 'Data non valida.');
        }

        $userId       = (int) $this->utente['id'];
        $misurazioni  = model(MeasurementModel::class)->forDay($userId, $data);
        $giornata     = model(DayModel::class)->forDay($userId, $data);
        $mostraTutte  = (bool) $this->request->getGet('tutte') || (int) $this->utente['mostra_tutte_colonne'] === 1;

        return view('diario/giorno', [
            'data'        => $data,
            'misurazioni' => $misurazioni,
            'giornata'    => $giornata,
            'mostraTutte' => $mostraTutte,
            'utente'      => $this->utente,
            'precedente'  => date('Y-m-d', strtotime($data . ' -1 day')),
            'successivo'  => date('Y-m-d', strtotime($data . ' +1 day')),
            'oggi'        => date('Y-m-d'),
        ]);
    }

    public function salvaGiorno(string $data)
    {
        $data = $this->normalizzaData($data);

        if ($data === null) {
            return redirect()->to('/oggi')->with('errore', 'Data non valida.');
        }

        $userId  = (int) $this->utente['id'];
        $valori  = (array) ($this->request->getPost('valore') ?? []);
        $ore     = (array) ($this->request->getPost('ora') ?? []);
        $note    = (array) ($this->request->getPost('nota') ?? []);
        $modello = model(MeasurementModel::class);

        foreach (slot_keys() as $slot) {
            if (! array_key_exists($slot, $valori) && ! array_key_exists($slot, $note)) {
                continue; // slot non presente nel modulo (colonne nascoste): non si tocca il dato salvato
            }

            $errore = $this->validaValore($slot, (string) ($valori[$slot] ?? ''));

            if ($errore !== null) {
                return redirect()->back()->withInput()->with('errore', $errore);
            }

            $modello->saveSlot($userId, $data, $slot, [
                'valore' => $valori[$slot] ?? '',
                'ora'    => $ore[$slot] ?? '',
                'nota'   => $note[$slot] ?? '',
            ]);
        }

        model(DayModel::class)->saveDay(
            $userId,
            $data,
            (string) $this->request->getPost('nota_giornata'),
            (string) $this->request->getPost('peso')
        );

        return redirect()->to('/giorno/' . $data)->with('successo', 'Misurazioni salvate.');
    }

    /** Salvataggio singolo via fetch(): usato dalla vista giorno per non perdere dati. */
    public function salvaSlot(string $data, string $slot): ResponseInterface
    {
        $data = $this->normalizzaData($data);

        if ($data === null || slot_meta($slot) === null) {
            return $this->response->setStatusCode(422)->setJSON(['errore' => 'Richiesta non valida.', 'csrf' => csrf_hash()]);
        }

        $valore = (string) $this->request->getPost('valore');
        $errore = $this->validaValore($slot, $valore);

        if ($errore !== null) {
            return $this->response->setStatusCode(422)->setJSON(['errore' => $errore, 'csrf' => csrf_hash()]);
        }

        $userId = (int) $this->utente['id'];
        model(MeasurementModel::class)->saveSlot($userId, $data, $slot, [
            'valore' => $valore,
            'ora'    => (string) $this->request->getPost('ora'),
            'nota'   => (string) $this->request->getPost('nota'),
        ]);

        return $this->response->setJSON([
            'ok'    => true,
            'stato' => value_status($slot, $valore === '' ? null : $valore, $this->utente),
            'csrf'  => csrf_hash(),
        ]);
    }

    public function mese(?string $mese = null)
    {
        $mese = $this->normalizzaMese($mese);
        [$anno, $numeroMese] = array_map('intval', explode('-', $mese));

        $userId      = (int) $this->utente['id'];
        $giorniTotal = (int) date('t', mktime(0, 0, 0, $numeroMese, 1, $anno));
        $dal         = sprintf('%04d-%02d-01', $anno, $numeroMese);
        $al          = sprintf('%04d-%02d-%02d', $anno, $numeroMese, $giorniTotal);

        return view('diario/mese', [
            'mese'         => $mese,
            'anno'         => $anno,
            'numeroMese'   => $numeroMese,
            'giorniTotal'  => $giorniTotal,
            'misurazioni'  => model(MeasurementModel::class)->forRange($userId, $dal, $al),
            'giornate'     => model(DayModel::class)->forRange($userId, $dal, $al),
            'utente'       => $this->utente,
            'precedente'   => date('Y-m', strtotime($dal . ' -1 month')),
            'successivo'   => date('Y-m', strtotime($dal . ' +1 month')),
            'mostraTutte'  => (bool) $this->request->getGet('tutte') || (int) $this->utente['mostra_tutte_colonne'] === 1,
        ]);
    }

    public function riepilogo(?string $mese = null)
    {
        $mese = $this->normalizzaMese($mese);
        [$anno, $numeroMese] = array_map('intval', explode('-', $mese));

        $userId = (int) $this->utente['id'];
        $dal    = sprintf('%04d-%02d-01', $anno, $numeroMese);
        $al     = sprintf('%04d-%02d-%02d', $anno, $numeroMese, (int) date('t', mktime(0, 0, 0, $numeroMese, 1, $anno)));

        $misurazioni = model(MeasurementModel::class)->forRange($userId, $dal, $al);

        return view('diario/riepilogo', [
            'mese'        => $mese,
            'statistiche' => model(MeasurementModel::class)->statistiche($userId, $dal, $al),
            'misurazioni' => $misurazioni,
            'giornate'    => model(DayModel::class)->forRange($userId, $dal, $al),
            'utente'      => $this->utente,
            'precedente'  => date('Y-m', strtotime($dal . ' -1 month')),
            'successivo'  => date('Y-m', strtotime($dal . ' +1 month')),
        ]);
    }

    private function validaValore(string $slot, string $valore): ?string
    {
        if ($valore === '') {
            return null;
        }

        $numero = str_replace(',', '.', $valore);

        if (! is_numeric($numero)) {
            return slot_label($slot) . ': inserisci solo numeri.';
        }

        $numero = (float) $numero;
        $meta   = slot_meta($slot);

        if ($meta['type'] === 'glicemia' && ($numero < 20 || $numero > 600)) {
            return slot_label($slot) . ': il valore deve essere compreso tra 20 e 600 mg/dl.';
        }

        if ($meta['type'] === 'insulina' && ($numero < 0 || $numero > 200)) {
            return slot_label($slot) . ': le unità di insulina devono essere tra 0 e 200.';
        }

        return null;
    }

    private function normalizzaData(string $data): ?string
    {
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $data);

        if ($d === false || $d->format('Y-m-d') !== $data) {
            return null;
        }

        // Niente misurazioni nel futuro oltre la giornata corrente.
        if ($d->format('Y-m-d') > date('Y-m-d')) {
            return date('Y-m-d');
        }

        return $data;
    }

    private function normalizzaMese(?string $mese): string
    {
        if ($mese === null || preg_match('/^\d{4}-\d{2}$/', $mese) !== 1) {
            return date('Y-m');
        }

        [$anno, $numero] = array_map('intval', explode('-', $mese));

        if ($numero < 1 || $numero > 12 || $anno < 2000 || $anno > 2100) {
            return date('Y-m');
        }

        return $mese;
    }
}
