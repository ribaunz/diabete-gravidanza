<?php

namespace App\Controllers;

use App\Models\AuthTokenModel;
use App\Models\UserModel;
use RuntimeException;

/**
 * Accesso in due passaggi:
 *  1. email + password;
 *  2. link magico (o codice a 6 cifre) inviato per email.
 */
class Auth extends BaseController
{
    public function login()
    {
        if (model(UserModel::class)->countAllResults() === 0) {
            return redirect()->to('/installazione');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $throttler = service('throttler');
        $chiave    = 'accesso-' . $this->request->getIPAddress();

        if ($throttler->check($chiave, 10, MINUTE) === false) {
            return redirect()->back()->withInput()->with('errore', 'Troppi tentativi di accesso. Riprova tra un minuto.');
        }

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errori', $this->validator->getErrors());
        }

        $auth = service('auth');
        $user = $auth->verifyPassword(
            (string) $this->request->getPost('email'),
            (string) $this->request->getPost('password')
        );

        if ($user === null) {
            return redirect()->back()->withInput()->with('errore', 'Email o password non corrette.');
        }

        $ricorda = (bool) $this->request->getPost('ricorda');

        // Secondo fattore già superato su questo dispositivo: si entra direttamente.
        if ($auth->deviceIsTrusted((int) $user['id'])) {
            $auth->login((int) $user['id']);

            return $this->dopoLogin();
        }

        try {
            $esito = service('magicLink')->send($user);
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('errore', $e->getMessage());
        }

        session()->set([
            '2fa_user_id'  => (int) $user['id'],
            '2fa_token_id' => $esito['token_id'],
            '2fa_ricorda'  => $ricorda,
            '2fa_email'    => $user['email'],
        ]);

        return redirect()->to('/accedi/verifica')
            ->with('successo', 'Ti abbiamo inviato un link di accesso a ' . $this->mascheraEmail($user['email']) . '.')
            ->with('debug_link', ENVIRONMENT !== 'production' ? $esito['link'] : null);
    }

    public function verify()
    {
        if (session('2fa_token_id') === null) {
            return redirect()->to('/accedi');
        }

        return view('auth/verifica', [
            'email'  => $this->mascheraEmail((string) session('2fa_email')),
            'durata' => \App\Libraries\MagicLink::DURATA_MINUTI,
        ]);
    }

    public function attemptVerify()
    {
        $tokenId = session('2fa_token_id');
        $userId  = session('2fa_user_id');

        if ($tokenId === null || $userId === null) {
            return redirect()->to('/accedi')->with('errore', 'Sessione scaduta, ripeti l\'accesso.');
        }

        $throttler = service('throttler');

        if ($throttler->check('verifica-' . $this->request->getIPAddress(), 15, MINUTE) === false) {
            return redirect()->back()->with('errore', 'Troppi tentativi. Attendi un minuto.');
        }

        $codice = (string) $this->request->getPost('codice');
        $token  = service('magicLink')->verifyCode((int) $tokenId, $codice);

        if ($token === null || (int) $token['user_id'] !== (int) $userId) {
            return redirect()->back()->with('errore', 'Codice non valido o scaduto. Puoi richiederne uno nuovo.');
        }

        return $this->completa((int) $userId, (int) $tokenId, (bool) session('2fa_ricorda'));
    }

    public function loginByLink(string $token)
    {
        $riga = service('magicLink')->verifyToken($token);

        if ($riga === null) {
            return redirect()->to('/accedi')->with('errore', 'Link non valido o scaduto. Richiedine uno nuovo.');
        }

        $ricorda = (bool) session('2fa_ricorda');

        return $this->completa((int) $riga['user_id'], (int) $riga['id'], $ricorda);
    }

    public function resend()
    {
        $userId = session('2fa_user_id');

        if ($userId === null) {
            return redirect()->to('/accedi');
        }

        if (service('throttler')->check('rinvio-' . $this->request->getIPAddress(), 3, MINUTE) === false) {
            return redirect()->back()->with('errore', 'Hai già richiesto un nuovo link da poco. Attendi un minuto.');
        }

        $user = model(UserModel::class)->find((int) $userId);

        if ($user === null) {
            return redirect()->to('/accedi');
        }

        try {
            $esito = service('magicLink')->send($user);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('errore', $e->getMessage());
        }

        session()->set('2fa_token_id', $esito['token_id']);

        return redirect()->back()
            ->with('successo', 'Nuovo link inviato.')
            ->with('debug_link', ENVIRONMENT !== 'production' ? $esito['link'] : null);
    }

    public function forgot()
    {
        return view('auth/recupera');
    }

    public function sendReset()
    {
        if (! $this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput()->with('errori', $this->validator->getErrors());
        }

        if (service('throttler')->check('recupero-' . $this->request->getIPAddress(), 5, 10 * MINUTE) === false) {
            return redirect()->back()->with('errore', 'Troppe richieste. Riprova più tardi.');
        }

        $user = model(UserModel::class)->findByEmail((string) $this->request->getPost('email'));
        $link = null;

        if ($user !== null) {
            try {
                $esito = service('magicLink')->send($user, AuthTokenModel::SCOPO_RESET);
                $link  = ENVIRONMENT !== 'production' ? $esito['link'] : null;
            } catch (RuntimeException $e) {
                return redirect()->back()->with('errore', $e->getMessage());
            }
        }

        // Risposta identica anche se l'email non esiste: non si rivela chi è registrato.
        return redirect()->to('/accedi')
            ->with('successo', 'Se l\'indirizzo è registrato, riceverai un link per reimpostare la password.')
            ->with('debug_link', $link);
    }

    public function resetForm(string $token)
    {
        if (service('magicLink')->verifyToken($token, AuthTokenModel::SCOPO_RESET) === null) {
            return redirect()->to('/accedi')->with('errore', 'Link non valido o scaduto.');
        }

        return view('auth/reimposta', ['token' => $token]);
    }

    public function resetPassword(string $token)
    {
        $riga = service('magicLink')->verifyToken($token, AuthTokenModel::SCOPO_RESET);

        if ($riga === null) {
            return redirect()->to('/accedi')->with('errore', 'Link non valido o scaduto.');
        }

        $rules = [
            'password' => 'required|min_length[10]|max_length[200]',
            'conferma' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errori', $this->validator->getErrors());
        }

        model(UserModel::class)->updatePassword((int) $riga['user_id'], (string) $this->request->getPost('password'));
        service('magicLink')->consume((int) $riga['id']);

        return redirect()->to('/accedi')->with('successo', 'Password aggiornata: ora puoi accedere.');
    }

    public function logout()
    {
        service('auth')->logout();

        return redirect()->to('/accedi')->with('successo', 'Sei uscita dal diario.');
    }

    private function completa(int $userId, int $tokenId, bool $ricorda)
    {
        $auth = service('auth');
        service('magicLink')->consume($tokenId);
        $auth->login($userId);

        if ($ricorda) {
            $auth->rememberDevice($userId);
        }

        return $this->dopoLogin();
    }

    private function dopoLogin()
    {
        // Si punta direttamente alla giornata: passando da /oggi il messaggio andrebbe perso nel secondo redirect.
        $destinazione = session('redirect_dopo_login') ?? '/giorno/' . date('Y-m-d');
        session()->remove('redirect_dopo_login');

        // withCookies(): porta sulla risposta di redirect il cookie del dispositivo ricordato.
        return redirect()->to($destinazione)->withCookies()->with('successo', 'Accesso effettuato.');
    }

    private function mascheraEmail(string $email): string
    {
        [$locale, $dominio] = array_pad(explode('@', $email, 2), 2, '');

        $visibile = mb_substr($locale, 0, min(2, mb_strlen($locale)));

        return $visibile . str_repeat('•', max(1, mb_strlen($locale) - 2)) . '@' . $dominio;
    }
}
