<?php
/**
 * Camrail Directory — Déconnexion
 * GET /backend/logout.php
 */

if (session_status() === PHP_SESSION_NONE) session_start();

// Vider la session
$_SESSION = [];

// Supprimer le cookie de session
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

// Supprimer le cookie "remember"
if (isset($_COOKIE['camrail_remember'])) {
    setcookie('camrail_remember', '', [
        'expires'  => time() - 42000,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

session_destroy();

header('Location: ../login.php?msg=logged_out');
exit;