#!/bin/bash
#
# Prova end-to-end dell'applicazione: crea un account, registra misurazioni,
# verifica l'accesso a due fattori e le esportazioni.
#
# Uso (con il server di sviluppo già avviato e il database vuoto):
#   php spark migrate && php spark serve &
#   tests/prova-completa.sh [http://127.0.0.1:8080]
#
set -u
B=${1:-http://127.0.0.1:8080}
S=$(mktemp -d)
J=$S/jar.txt; P=$S/page.html
rm -f "$J"
ok=0; ko=0
tok() { grep -o 'name="diario_csrf" value="[^"]*"' "$P" | head -1 | sed 's/.*value="//;s/"//'; }
get() { curl -s -c "$J" -b "$J" -L "$B$1" -o "$P" -w "%{http_code}"; }
post() { curl -s -c "$J" -b "$J" -L "$B$1" --data "$2" -o "$P" -w "%{http_code}"; }
verifica() { # descrizione, atteso-in-pagina
  if grep -qF "$2" "$P"; then echo "  OK   $1"; ok=$((ok+1)); else echo "  FAIL $1 (manca: $2)"; ko=$((ko+1)); fi
}
codice() { [ "$1" = "$2" ] && { echo "  OK   $3 ($1)"; ok=$((ok+1)); } || { echo "  FAIL $3 (atteso $2, ricevuto $1)"; ko=$((ko+1)); }; }

echo "1) Installazione"
codice "$(get /installazione)" 200 "pagina di primo avvio"
codice "$(post /installazione "diario_csrf=$(tok)&nome=Daniela&email=test@esempio.it&password=passwordlunga1&conferma=passwordlunga1")" 200 "creazione account"
verifica "account creato ed entrata nel diario" "Account creato"

echo "2) Inserimento misurazioni"
OGGI=$(date +%F)
codice "$(get /giorno/$OGGI)" 200 "vista giornata"
codice "$(post /giorno/$OGGI "diario_csrf=$(tok)&valore[digiuno]=82&ora[digiuno]=07:30&nota[digiuno]=notte tranquilla&valore[colazione_1h]=145&nota[colazione_1h]=fette biscottate e marmellata&valore[pranzo_1h]=118&valore[pranzo_2h]=112&nota[pranzo_2h]=pasta integrale&valore[cena_1h]=126&nota_giornata=camminata di 30 minuti&peso=68,4")" 200 "salvataggio giornata"
verifica "conferma di salvataggio" "Misurazioni salvate"
verifica "valore rileggibile nel modulo" 'value="82"'
verifica "nota rileggibile" "notte tranquilla"
verifica "valore fuori soglia segnalato" "sopra soglia"
verifica "controllo a 2 ore registrato" 'value="112"'

echo "3) Validazione"
codice "$(post /giorno/$OGGI "diario_csrf=$(tok)&valore[digiuno]=900")" 200 "valore assurdo rifiutato"
verifica "messaggio di errore" "compreso tra 20 e 600"

echo "4) Uscita e accesso con 2FA"
get /esci > /dev/null
codice "$(get /mese)" 200 "area riservata protetta"
verifica "reindirizzata all'accesso" "Accedi per continuare"
codice "$(post /accedi "diario_csrf=$(tok)&email=test@esempio.it&password=sbagliata")" 200 "password errata"
verifica "credenziali rifiutate" "Email o password non corrette"
codice "$(post /accedi "diario_csrf=$(tok)&email=test@esempio.it&password=passwordlunga1&ricorda=1")" 200 "primo fattore superato"
verifica "richiesta del secondo fattore" "Conferma l'accesso"
LINK=$(grep -o "$B/accedi/link/[a-f0-9]*" "$P" | head -1)
[ -n "$LINK" ] && { echo "  OK   link magico generato"; ok=$((ok+1)); } || { echo "  FAIL link magico assente"; ko=$((ko+1)); }
codice "$(post /accedi/verifica "diario_csrf=$(tok)&codice=000000")" 200 "codice errato"
verifica "codice errato respinto" "Codice non valido"
codice "$(get "${LINK#$B}")" 200 "accesso con link magico"
verifica "sessione aperta" "Accesso effettuato"
get /esci > /dev/null
codice "$(get "${LINK#$B}")" 200 "link riutilizzato"
verifica "link monouso" "Link non valido o scaduto"

echo "5) Dispositivo ricordato"
get /esci > /dev/null
get /accedi > /dev/null
codice "$(post /accedi "diario_csrf=$(tok)&email=test@esempio.it&password=passwordlunga1")" 200 "accesso su dispositivo fidato"
verifica "secondo fattore saltato" "Accesso effettuato"

echo "6) Viste"
for u in /mese /riepilogo /esporta /impostazioni; do codice "$(get $u)" 200 "vista $u"; done
get /mese > /dev/null
verifica "griglia con il valore inserito" 'value="145"'
get /riepilogo > /dev/null
verifica "riepilogo con la media" "Medie per misurazione"

echo "7) Salvataggio AJAX"
get /mese > /dev/null
TA=$(grep -o "let csrfValore = '[^']*'" "$P" | head -1 | sed "s/.*'\(.*\)'/\1/")
R=$(curl -s -b "$J" -c "$J" "$B/giorno/$OGGI/slot/pranzo_1h" --data "diario_csrf=$TA&valore=155" -H "X-Requested-With: XMLHttpRequest")
echo "$R" | grep -q '"stato": *"alto"' && { echo "  OK   cella salvata e classificata alta"; ok=$((ok+1)); } || { echo "  FAIL risposta AJAX: $R"; ko=$((ko+1)); }

echo "8) Esportazioni"
MESE=$(date +%Y-%m)
C=$(curl -s -b "$J" -c "$J" "$B/esporta/pdf/$MESE" -o $S/scheda.pdf -w "%{http_code}")
codice "$C" 200 "PDF scheda"
C=$(curl -s -b "$J" -c "$J" "$B/esporta/pdf/$MESE?note=1" -o $S/scheda-note.pdf -w "%{http_code}")
codice "$C" 200 "PDF con note"
C=$(curl -s -b "$J" -c "$J" "$B/esporta/csv/$MESE" -o $S/scheda.csv -w "%{http_code}")
codice "$C" 200 "CSV"
head -c 4 $S/scheda.pdf | grep -q "%PDF" && { echo "  OK   file PDF valido ($(stat -c%s $S/scheda.pdf) byte)"; ok=$((ok+1)); } || { echo "  FAIL PDF non valido"; ko=$((ko+1)); }
grep -q "fette biscottate" $S/scheda.csv && { echo "  OK   note presenti nel CSV"; ok=$((ok+1)); } || { echo "  FAIL note assenti nel CSV"; ko=$((ko+1)); }

rm -rf "$S"

echo
echo "Risultato: $ok superati, $ko falliti"
[ "$ko" -eq 0 ]
