<?php

namespace App\Libraries;

use App\Models\AuthTokenModel;
use RuntimeException;

/**
 * Genera e invia i link magici usati come secondo fattore di autenticazione
 * (e per il recupero della password).
 */
class MagicLink
{
    public const DURATA_MINUTI = 15;

    private AuthTokenModel $tokens;

    public function __construct()
    {
        $this->tokens = model(AuthTokenModel::class);
    }

    /**
     * Crea un token monouso e invia l'email con link e codice.
     *
     * @param array<string, mixed> $user
     *
     * @return array{token_id: int, link: string, codice: string}
     */
    public function send(array $user, string $scopo = AuthTokenModel::SCOPO_LOGIN): array
    {
        $userId = (int) $user['id'];
        $this->tokens->invalidateFor($userId, $scopo);

        $token  = bin2hex(random_bytes(32));
        $codice = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request = service('request');

        $tokenId = (int) $this->tokens->insert([
            'user_id'     => $userId,
            'scopo'       => $scopo,
            'token_hash'  => hash('sha256', $token),
            'codice_hash' => hash('sha256', $codice),
            'ip'          => $request->getIPAddress(),
            'user_agent'  => mb_substr((string) $request->getUserAgent(), 0, 255),
            'scade_il'    => date('Y-m-d H:i:s', strtotime('+' . self::DURATA_MINUTI . ' minutes')),
        ], true);

        $link = $scopo === AuthTokenModel::SCOPO_RESET
            ? site_url('password/reimposta/' . $token)
            : site_url('accedi/link/' . $token);

        $this->deliver($user, $link, $codice, $scopo);

        return ['token_id' => $tokenId, 'link' => $link, 'codice' => $codice];
    }

    /**
     * Verifica il codice a 6 cifre associato a un token.
     *
     * @return array<string, mixed>|null il token, se il codice è corretto
     */
    public function verifyCode(int $tokenId, string $codice, string $scopo = AuthTokenModel::SCOPO_LOGIN): ?array
    {
        $row = $this->tokens->findValidById($tokenId, $scopo);

        if ($row === null) {
            return null;
        }

        $codice = preg_replace('/\D/', '', $codice) ?? '';

        if (! hash_equals((string) $row['codice_hash'], hash('sha256', $codice))) {
            $this->tokens->registerAttempt($tokenId);

            return null;
        }

        return $row;
    }

    /** @return array<string, mixed>|null */
    public function verifyToken(string $token, string $scopo = AuthTokenModel::SCOPO_LOGIN): ?array
    {
        return $this->tokens->findValidByToken($token, $scopo);
    }

    public function consume(int $tokenId): void
    {
        $this->tokens->consume($tokenId);
    }

    /**
     * Invia l'email. In ambiente di sviluppo, se non è configurato l'SMTP,
     * il messaggio viene salvato in writable/emails così da poter provare il flusso.
     *
     * @param array<string, mixed> $user
     */
    private function deliver(array $user, string $link, string $codice, string $scopo): void
    {
        $html = view('emails/magic_link', [
            'nome'   => $user['nome'] ?: 'ciao',
            'link'   => $link,
            'codice' => $codice,
            'scopo'  => $scopo,
            'durata' => self::DURATA_MINUTI,
        ]);

        $oggetto = $scopo === AuthTokenModel::SCOPO_RESET
            ? 'Reimposta la password del diario glicemie'
            : 'Il tuo codice di accesso al diario glicemie';

        if (env('email.disabilitata') === true || env('email.disabilitata') === 'true') {
            $this->saveToDisk($user['email'], $oggetto, $html);

            return;
        }

        $email = service('email');
        $email->setTo($user['email']);
        $email->setSubject($oggetto);
        $email->setMessage($html);

        if (! $email->send(false)) {
            $this->saveToDisk($user['email'], $oggetto, $html);

            log_message('error', 'Invio email fallito: ' . $email->printDebugger(['headers']));

            if (ENVIRONMENT === 'production') {
                throw new RuntimeException("Non è stato possibile inviare l'email. Riprova più tardi.");
            }
        }
    }

    private function saveToDisk(string $destinatario, string $oggetto, string $html): void
    {
        $dir = WRITEPATH . 'emails';

        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = $dir . '/' . date('Ymd-His') . '-' . preg_replace('/[^a-z0-9]+/i', '_', $destinatario) . '.html';
        file_put_contents($file, "<!-- A: {$destinatario} · Oggetto: {$oggetto} -->\n" . $html);
        log_message('info', "Email salvata in {$file}");
    }
}
