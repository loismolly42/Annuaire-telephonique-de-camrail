<?php
/**
 * Camrail — Déconnexion
 */

session_name('camrail_sess');
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    setcookie('camrail_sess', '', [
        'expires'  => time() - 42000,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

if (isset($_COOKIE['camrail_remember'])) {
    setcookie('camrail_remember', '', [
        'expires'  => time() - 42000,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

session_destroy();
header('Location: /camrail_annuaire/login.php?msg=logged_out');
exit;