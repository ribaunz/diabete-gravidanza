<?php

namespace App\Controllers;

use App\Models\TrustedDeviceModel;
use App\Models\UserModel;

class Impostazioni extends BaseController
{
    public function index()
    {
        return view('diario/impostazioni', [
            'utente'      => $this->utente,
            'dispositivi' => model(TrustedDeviceModel::class)->forUser((int) $this->utente['id']),
        ]);
    }

    public function salva()
    {
        $rules = [
            'nome'                => 'permit_empty|max_length[120]',
            'data_presunta_parto' => 'permit_empty|valid_date[Y-m-d]',
            'soglia_digiuno'      => 'required|is_natural_no_zero|less_than_equal_to[300]',
            'soglia_post_1h'      => 'required|is_natural_no_zero|less_than_equal_to[300]',
            'soglia_post_2h'      => 'required|is_natural_no_zero|less_than_equal_to[300]',
            'intestazione'        => 'permit_empty|max_length[190]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errori', $this->validator->getErrors());
        }

        model(UserModel::class)->update((int) $this->utente['id'], [
            'nome'                 => $this->request->getPost('nome'),
            'data_presunta_parto'  => $this->request->getPost('data_presunta_parto') ?: null,
            'soglia_digiuno'       => (int) $this->request->getPost('soglia_digiuno'),
            'soglia_post_1h'       => (int) $this->request->getPost('soglia_post_1h'),
            'soglia_post_2h'       => (int) $this->request->getPost('soglia_post_2h'),
            'intestazione'         => $this->request->getPost('intestazione') ?: null,
            'mostra_tutte_colonne' => $this->request->getPost('mostra_tutte_colonne') ? 1 : 0,
        ]);

        return redirect()->to('/impostazioni')->with('successo', 'Impostazioni aggiornate.');
    }

    public function cambiaPassword()
    {
        $rules = [
            'attuale'  => 'required',
            'password' => 'required|min_length[10]|max_length[200]',
            'conferma' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('errori', $this->validator->getErrors());
        }

        $auth = service('auth');

        if ($auth->verifyPassword((string) $this->utente['email'], (string) $this->request->getPost('attuale')) === null) {
            return redirect()->back()->with('errore', 'La password attuale non è corretta.');
        }

        model(UserModel::class)->updatePassword((int) $this->utente['id'], (string) $this->request->getPost('password'));

        return redirect()->to('/impostazioni')->with('successo', 'Password aggiornata.');
    }

    public function revocaDispositivo(int $id)
    {
        model(TrustedDeviceModel::class)
            ->where('user_id', (int) $this->utente['id'])
            ->where('id', $id)
            ->delete();

        return redirect()->to('/impostazioni')->with('successo', 'Dispositivo revocato: al prossimo accesso servirà di nuovo il link via email.');
    }
}
