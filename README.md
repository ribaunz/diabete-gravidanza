# Diario glicemie — diabete gestazionale

Applicazione web (telefono e computer) per registrare le misurazioni della glicemia in gravidanza
seguendo la scheda dell'ambulatorio di diabetologia, aggiungere una nota a ogni misurazione ed
esportare il tutto in PDF — identico al modulo cartaceo, oppure nella versione con le note.

**Stack**: CodeIgniter 4 · PHP 8.2+ · MySQL/MariaDB · Tailwind CSS · dompdf.
**Accesso**: password + link magico via email (autenticazione a due fattori).

---

## Che cosa fa

**Registrazione giornaliera** — una scheda per ogni giorno con le glicemie del modulo: a digiuno
(< 90), 1 ora dopo colazione, dopo pranzo e dopo cena (< 130), più il controllo facoltativo a 2 ore
dopo ogni pasto (< 120), sempre disponibile quando serve. Unità di insulina e glicemie prima dei
pasti compaiono con "Mostra anche insulina e glicemie prima dei pasti" o attivandole nelle
impostazioni. La chetonuria non viene raccolta: la sua colonna resta stampata sul PDF, vuota, da
compilare a mano.

**Una nota per ogni misurazione** — accanto a ogni valore si può annotare cosa si è mangiato, come
ci si sentiva, l'attività fisica. In più c'è una nota generale della giornata e il peso.

**Fuori soglia evidenziati** — ogni valore viene confrontato con la soglia della scheda (o con quella
personalizzata dal diabetologo) e colorato di conseguenza, sia nell'app sia nel PDF.

**Griglia mensile** — da tablet in su la tabella del mese è modificabile cella per cella con
salvataggio automatico; da telefono si vede l'elenco dei giorni e si apre la singola giornata.

**Riepilogo** — medie per misurazione, percentuale di valori nel target, elenco dei valori fuori soglia
con le relative note: quello che serve alla visita di controllo.

**Esportazioni** — PDF della scheda mensile (giorni 1-15 e 16-31, come il modulo cartaceo),
PDF con l'appendice delle note, CSV per fogli di calcolo.

**Installabile** — il manifest PWA permette di aggiungerla alla schermata home del telefono.

## Accesso a due fattori

1. email e password;
2. link magico inviato per email, con in alternativa un codice a 6 cifre da digitare nell'app.

Il link e il codice valgono 15 minuti, sono monouso e vengono invalidati quando se ne richiede uno nuovo;
dopo 5 tentativi errati il token viene bruciato. I tentativi di accesso sono limitati per indirizzo IP.
Con "Ricorda questo dispositivo" il secondo fattore non viene richiesto per 30 giorni su quel
dispositivo; i dispositivi ricordati si revocano dalle impostazioni.

Password dimenticata: dalla pagina di accesso si riceve un link per impostarne una nuova (la risposta è
identica anche se l'indirizzo non è registrato, per non rivelare chi ha un account).

## Installazione

Servono PHP 8.2+ (estensioni `intl`, `mbstring`, `mysqlnd`, `gd`), MySQL 5.7+/MariaDB 10.3+,
Composer e — solo per rigenerare il CSS — Node.js.

```bash
composer install
cp .env.example .env          # poi compila database, SMTP e app.baseURL
php spark key:generate
php spark migrate
```

Il primo avvio dell'applicazione porta alla pagina `/installazione`, dove si crea l'unico account.
Da quel momento la pagina non è più raggiungibile.

**Server web** — la document root deve puntare a `public/`. Il file `public/.htaccess` fornito
gestisce le URL senza `index.php` su Apache. Su nginx:

```nginx
root /var/www/diario/public;
index index.php;
location / { try_files $uri $uri/ /index.php$is_args$args; }
location ~ \.php$ { fastcgi_pass unix:/run/php/php8.2-fpm.sock; include fastcgi.conf; }
```

**Prova in locale** senza MySQL né SMTP: `cp .env.sviluppo .env` (database SQLite, email salvate in
`writable/emails/` e link di accesso mostrato a video), poi `php spark migrate && php spark serve`.
Il percorso del database SQLite va indicato assoluto.

### Foglio di stile

Il CSS compilato è già in `public/assets/css/app.css`. Per rigenerarlo dopo aver modificato le viste:

```bash
npm install
npm run build:css      # oppure npm run watch:css durante lo sviluppo
```

### Prova end-to-end

Con il server avviato e il database vuoto:

```bash
php spark migrate && php spark serve &
tests/prova-completa.sh
```

Lo script crea un account, salva misurazioni e note, verifica il secondo fattore (codice errato, link
magico, link monouso, dispositivo ricordato), la validazione dei valori e le tre esportazioni.

## Struttura

```
app/
  Config/Routes.php           percorsi dell'applicazione
  Controllers/                Auth (2FA), Setup, Diario, Esporta, Impostazioni
  Database/Migrations/        users, auth_tokens, trusted_devices, misurazioni, giornate
  Filters/                    AuthFilter (area riservata), GuestFilter
  Helpers/diario_helper.php   definizione delle misurazioni, livelli, soglie, formattazione
  Libraries/Auth.php          sessione, password, dispositivi ricordati
  Libraries/MagicLink.php     generazione e invio dei link/codici monouso
  Libraries/PdfExporter.php   composizione del PDF mensile
  Models/                     UserModel, AuthTokenModel, TrustedDeviceModel,
                              MeasurementModel, DayModel
  Views/pdf/scheda.php        riproduzione della scheda cartacea
public/                       document root (assets compilati, manifest PWA)
resources/app.css             sorgente Tailwind
tests/prova-completa.sh       prova end-to-end
```

Le misurazioni sono una riga per (utente, giorno, slot): `valore` (glicemia o unità di insulina),
`ora` e `nota`. Svuotare valore e nota cancella la riga. Ogni slot ha un `livello` che ne governa la
visibilità nei moduli — `base` (glicemie della scheda), `extra` (controlli a 2 ore) e `avanzato`
(insulina, prima dei pasti) — e un flag `primary` che serve solo a impaginare il PDF come il modulo,
dove le colonne non bianche restano barrate.

## Dati e privacy

I dati sanitari restano sul server dove è installata l'applicazione: non c'è alcun servizio esterno,
a parte il server SMTP usato per i link di accesso. In produzione va usato HTTPS
(`app.forceGlobalSecureRequests = true` e `cookie.secure = true` nel `.env`) e va previsto un backup
del database. Le password sono salvate con `password_hash()`, i token di accesso e i cookie dei
dispositivi ricordati solo come hash SHA-256.

L'app è uno strumento di registrazione: non fornisce indicazioni terapeutiche e non sostituisce il
parere del diabetologo. Le soglie preimpostate (90 / 130 / 120 mg/dl) sono quelle stampate sulla
scheda e vanno cambiate solo se lo indica il medico.
