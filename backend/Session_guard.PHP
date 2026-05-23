<?php
/**
 * Camrail Directory — Garde de session
 */

// session_name AVANT session_start — identique à auth.php et login.php
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

require_once __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    if (!empty($GLOBALS['api_mode'])) json_err('unauthenticated', 401);
    header('Location: /camrail_annuaire/login.php?error=session'); exit;
}

if (!empty($GLOBALS['require_admin']) && ($_SESSION['role'] ?? '') !== 'admin') {
    if (!empty($GLOBALS['api_mode'])) json_err('forbidden', 403);
    header('Location: /camrail_annuaire/login.php?error=session'); exit;
}

$current_user = [
    'id'         => $_SESSION['user_id']    ?? '',
    'id_db'      => $_SESSION['id_db']      ?? 0,
    'id_employe' => $_SESSION['id_employe'] ?? 0,
    'name'       => $_SESSION['name']       ?? '',
    'role'       => $_SESSION['role']       ?? 'user',
    'department' => $_SESSION['department'] ?? '',
    'job_title'  => $_SESSION['job_title']  ?? '',
    'avatar'     => $_SESSION['avatar']     ?? '',
    'email'      => $_SESSION['email']      ?? '',
    'login_time' => $_SESSION['login_time'] ?? time(),
    'is_admin'   => ($_SESSION['role'] ?? '') === 'admin',
];