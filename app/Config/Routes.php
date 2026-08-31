<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');

// --- Primo avvio ------------------------------------------------------------
$routes->get('installazione', 'Setup::index');
$routes->post('installazione', 'Setup::create');

// --- Autenticazione ---------------------------------------------------------
$routes->group('', ['filter' => 'guest'], static function ($routes): void {
    $routes->get('accedi', 'Auth::login');
    $routes->post('accedi', 'Auth::attemptLogin');
    $routes->get('accedi/verifica', 'Auth::verify');
    $routes->post('accedi/verifica', 'Auth::attemptVerify');
    $routes->post('accedi/rinvia', 'Auth::resend');
    $routes->get('password/recupera', 'Auth::forgot');
    $routes->post('password/recupera', 'Auth::sendReset');
    $routes->get('password/reimposta/(:segment)', 'Auth::resetForm/$1');
    $routes->post('password/reimposta/(:segment)', 'Auth::resetPassword/$1');
});

// Il link magico deve funzionare anche in una sessione già aperta o su un altro dispositivo.
$routes->get('accedi/link/(:segment)', 'Auth::loginByLink/$1');
$routes->get('esci', 'Auth::logout');

// --- Area riservata ---------------------------------------------------------
$routes->group('', ['filter' => 'auth'], static function ($routes): void {
    $routes->get('oggi', 'Diario::oggi');
    $routes->get('giorno/(:segment)', 'Diario::giorno/$1');
    $routes->post('giorno/(:segment)', 'Diario::salvaGiorno/$1');
    $routes->post('giorno/(:segment)/slot/(:segment)', 'Diario::salvaSlot/$1/$2');

    $routes->get('mese', 'Diario::mese');
    $routes->get('mese/(:segment)', 'Diario::mese/$1');
    $routes->get('riepilogo', 'Diario::riepilogo');
    $routes->get('riepilogo/(:segment)', 'Diario::riepilogo/$1');

    $routes->get('esporta', 'Esporta::index');
    $routes->get('esporta/pdf/(:segment)', 'Esporta::pdf/$1');
    $routes->get('esporta/csv/(:segment)', 'Esporta::csv/$1');

    $routes->get('impostazioni', 'Impostazioni::index');
    $routes->post('impostazioni', 'Impostazioni::salva');
    $routes->post('impostazioni/password', 'Impostazioni::cambiaPassword');
    $routes->post('impostazioni/dispositivi/(:num)/revoca', 'Impostazioni::revocaDispositivo/$1');
});
