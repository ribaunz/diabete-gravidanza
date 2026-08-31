<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Consente l'accesso solo agli utenti autenticati (con secondo fattore superato). */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (service('auth')->check()) {
            return null;
        }

        if ($request->isAJAX()) {
            return service('response')->setStatusCode(401)->setJSON(['errore' => 'Sessione scaduta']);
        }

        session()->set('redirect_dopo_login', current_url());

        return redirect()->to('/accedi')->with('avviso', 'Accedi per continuare.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
