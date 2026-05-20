<?php
/**
 * Camrail — Déconnexion
 */
if (session_status() === PHP_SESSION_NONE) session_start();

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $p['path'],
        'domain'   => $p['domain'],
        'secure'   => $p['secure'],
        'httponly' => $p['httponly'],
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