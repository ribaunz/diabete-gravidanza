<?php

namespace App\Libraries;

use App\Models\AuthTokenModel;
use App\Models\TrustedDeviceModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Autenticazione a due fattori: password (primo fattore) + magic link o codice
 * inviato via email (secondo fattore). Il dispositivo può essere ricordato per 30 giorni.
 */
class Auth
{
    public const DEVICE_COOKIE = 'dispositivo_fidato';

    private UserModel $users;
    private AuthTokenModel $tokens;
    private TrustedDeviceModel $devices;

    public function __construct()
    {
        $this->users   = model(UserModel::class);
        $this->tokens  = model(AuthTokenModel::class);
        $this->devices = model(TrustedDeviceModel::class);
    }

    /** @return array<string, mixed>|null */
    public function user(): ?array
    {
        $id = session('user_id');

        if ($id === null) {
            return null;
        }

        $user = $this->users->find((int) $id);

        if ($user === null || (int) $user['attivo'] !== 1) {
            $this->logout();

            return null;
        }

        return $user;
    }

    public function id(): ?int
    {
        $user = $this->user();

        return $user === null ? null : (int) $user['id'];
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Verifica il primo fattore.
     *
     * @return array<string, mixed>|null
     */
    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->users->findByEmail($email);

        if ($user === null || (int) $user['attivo'] !== 1) {
            // Confronto fittizio: il tempo di risposta non deve rivelare se l'email esiste.
            password_verify($password, '$2y$10$usedasadummyhashforprotectionxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

            return null;
        }

        if (! password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }

    /** Il dispositivo corrente è già stato verificato con il secondo fattore? */
    public function deviceIsTrusted(int $userId): bool
    {
        $cookie = service('request')->getCookie(self::DEVICE_COOKIE);

        if (! is_string($cookie) || $cookie === '') {
            return false;
        }

        $device = $this->devices->findValid($userId, $cookie);

        if ($device === null) {
            return false;
        }

        $this->devices->update($device['id'], ['ultimo_uso' => date('Y-m-d H:i:s')]);

        return true;
    }

    public function rememberDevice(int $userId): void
    {
        $token   = bin2hex(random_bytes(32));
        $request = service('request');

        $this->devices->insert([
            'user_id'    => $userId,
            'token_hash' => hash('sha256', $token),
            'etichetta'  => $this->deviceLabel($request),
            'ip'         => $request->getIPAddress(),
            'ultimo_uso' => date('Y-m-d H:i:s'),
            'scade_il'   => date('Y-m-d H:i:s', strtotime('+30 days')),
        ]);

        service('response')->setCookie([
            'name'     => self::DEVICE_COOKIE,
            'value'    => $token,
            'expire'   => 30 * DAY,
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => ! is_cli() && (service('request')->isSecure()),
        ]);
    }

    /** Completa l'accesso: sessione rigenerata per evitare il session fixation. */
    public function login(int $userId): void
    {
        $session = session();
        $session->regenerate(true);
        $session->set('user_id', $userId);
        $session->remove(['2fa_user_id', '2fa_token_id', '2fa_ricorda']);

        $this->users->update($userId, ['ultimo_accesso' => date('Y-m-d H:i:s')]);
    }

    public function logout(): void
    {
        $session = session();
        $session->remove(['user_id', '2fa_user_id', '2fa_token_id', '2fa_ricorda']);
        $session->destroy();
    }

    private function deviceLabel(IncomingRequest $request): string
    {
        $agent = $request->getUserAgent();
        $label = trim(($agent->getPlatform() ?: 'Dispositivo') . ' · ' . ($agent->getBrowser() ?: 'browser'));

        return mb_substr($label, 0, 120);
    }
}
