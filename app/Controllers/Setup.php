<?php

namespace App\Controllers;

use App\Models\UserModel;

/** Creazione del primo account: disponibile solo finché non esiste alcun utente. */
class Setup extends BaseController
{
    public function index()
    {
        if ($this->utentiPresenti()) {
            return redirect()->to('/accedi');
        }

        return view('auth/setup');
    }

    public function create()
    {
        if ($this->utentiPresenti()) {
            return redirect()->to('/accedi');
        }

        $rules = [
            'nome'     => 'required|min_length[2]|max_length[120]',
            'email'    => 'required|valid_email|max_length[190]',
            'password' => 'required|min_length[10]|max_length[200]',
            'conferma' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errori', $this->validator->getErrors());
        }

        $users  = model(UserModel::class);
        $userId = $users->createUser([
            'nome'                => $this->request->getPost('nome'),
            'email'               => $this->request->getPost('email'),
            'data_presunta_parto' => $this->request->getPost('data_presunta_parto') ?: null,
        ], (string) $this->request->getPost('password'));

        service('auth')->login($userId);

        return redirect()->to('/giorno/' . date('Y-m-d'))
            ->with('successo', 'Account creato: puoi iniziare a registrare le misurazioni.');
    }

    private function utentiPresenti(): bool
    {
        return model(UserModel::class)->countAllResults() > 0;
    }
}
