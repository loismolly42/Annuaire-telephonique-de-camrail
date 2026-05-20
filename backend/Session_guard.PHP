<?php
/**
 * Camrail Directory — Garde de session
 * À inclure en haut de chaque page protégée (dashboard, directory, departments, profile, settings)
 * 
 * Usage dans chaque page :
 *   $active_page = 'dashboard'; // adapter selon la page
 *   require_once __DIR__ . '/backend/session_guard.php';
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';

// Pas connecté → retour au login
if (empty($_SESSION['user_id'])) {
    header('Location: /camrail_annuaire/login.php?error=session');
    exit;
}

// ── Construire $current_user depuis la session ────────────────────────────────
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