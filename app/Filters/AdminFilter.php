<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Consente l'accesso alla gestione degli utenti ai soli amministratori. */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $utente = service('auth')->user();

        if ($utente === null) {
            session()->set('redirect_dopo_login', current_url());

            return redirect()->to('/accedi')->with('avviso', 'Accedi per continuare.');
        }

        if (UserModel::eAmministratore($utente)) {
            return null;
        }

        return redirect()->to('/oggi')->with('errore', 'Sezione riservata agli amministratori.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
