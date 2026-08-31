<?php

namespace App\Controllers;

use App\Models\AuthTokenModel;
use App\Models\UserModel;
use RuntimeException;

/**
 * Gestione degli account, riservata agli amministratori.
 *
 * Non esiste registrazione libera: gli account li apre un amministratore e la
 * password se la sceglie la destinataria aprendo il link ricevuto per email,
 * così nemmeno chi crea l'account la conosce.
 *
 * Il divieto di agire su se stessi basta a garantire che resti sempre almeno
 * un amministratore attivo: per disattivare o retrocedere un amministratore
 * bisogna esserlo a propria volta, quindi ne resta comunque un altro.
 */
class Utenti extends BaseController
{
    public function index()
    {
        return view('utenti/elenco', [
            'titolo'  => 'Utenti',
            'utenti'  => model(UserModel::class)->elenco(),
            'utente'  => $this->utente,
        ]);
    }

    public function nuovo()
    {
        return view('utenti/nuovo', ['titolo' => 'Nuovo utente', 'utente' => $this->utente]);
    }

    public function crea()
    {
        $rules = [
            'nome'  => 'required|min_length[2]|max_length[120]',
            'email' => 'required|valid_email|max_length[190]|is_unique[users.email]',
            'ruolo' => 'required|in_list[utente,amministratore]',
        ];

        $messaggi = [
            'email' => ['is_unique' => 'Esiste già un account con questo indirizzo.'],
        ];

        if (! $this->validate($rules, $messaggi)) {
            return redirect()->back()->withInput()->with('errori', $this->validator->getErrors());
        }

        $users = model(UserModel::class);

        // Password provvisoria irrecuperabile: l'account resta inaccessibile
        // finché la destinataria non ne sceglie una dal link di invito.
        $userId = $users->createUser([
            'nome'                => $this->request->getPost('nome'),
            'email'               => $this->request->getPost('email'),
            'data_presunta_parto' => $this->request->getPost('data_presunta_parto') ?: null,
            'ruolo'               => $this->request->getPost('ruolo'),
        ], bin2hex(random_bytes(32)));

        return $this->inviaInvito($users->find($userId), 'Account creato.');
    }

    public function rinviaInvito(int $id)
    {
        $utente = model(UserModel::class)->find($id);

        if ($utente === null) {
            return redirect()->to('/utenti')->with('errore', 'Utente inesistente.');
        }

        return $this->inviaInvito($utente, 'Nuovo link inviato.');
    }

    public function cambiaStato(int $id)
    {
        $users  = model(UserModel::class);
        $utente = $users->find($id);

        if ($utente === null) {
            return redirect()->to('/utenti')->with('errore', 'Utente inesistente.');
        }

        if ($id === (int) $this->utente['id']) {
            return redirect()->to('/utenti')->with('errore', 'Non puoi disattivare il tuo stesso account.');
        }

        $attivo = (int) $utente['attivo'] === 1;

        $users->update($id, ['attivo' => $attivo ? 0 : 1]);

        return redirect()->to('/utenti')->with(
            'successo',
            $attivo
                ? esc($utente['nome']) . ' non può più accedere.'
                : esc($utente['nome']) . ' può accedere di nuovo.'
        );
    }

    public function cambiaRuolo(int $id)
    {
        $users  = model(UserModel::class);
        $utente = $users->find($id);

        if ($utente === null) {
            return redirect()->to('/utenti')->with('errore', 'Utente inesistente.');
        }

        if ($id === (int) $this->utente['id']) {
            return redirect()->to('/utenti')->with('errore', 'Non puoi cambiare il tuo stesso ruolo.');
        }

        $amministratore = UserModel::eAmministratore($utente);

        $users->update($id, [
            'ruolo' => $amministratore ? UserModel::RUOLO_UTENTE : UserModel::RUOLO_AMMINISTRATORE,
        ]);

        return redirect()->to('/utenti')->with(
            'successo',
            esc($utente['nome']) . ($amministratore ? ' ora è un utente normale.' : ' ora è amministratore.')
        );
    }

    /** @param array<string, mixed> $utente */
    private function inviaInvito(array $utente, string $premessa)
    {
        try {
            $esito = service('magicLink')->send($utente, AuthTokenModel::SCOPO_INVITO);
        } catch (RuntimeException $e) {
            return redirect()->to('/utenti')
                ->with('errore', $premessa . ' Invio dell\'email fallito: ' . $e->getMessage());
        }

        return redirect()->to('/utenti')
            ->with('successo', $premessa . ' Link per scegliere la password inviato a ' . esc($utente['email']) . '.')
            ->with('debug_link', ENVIRONMENT !== 'production' ? $esito['link'] : null);
    }
}
