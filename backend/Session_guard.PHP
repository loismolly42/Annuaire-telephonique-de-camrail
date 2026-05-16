<?php
/**
 * Camrail Directory — Garde de session
 * À inclure EN PREMIER sur chaque page protégée.
 *
 * Variables optionnelles à définir AVANT l'include :
 *   $require_admin = true;   // réservé aux admins
 *   $api_mode      = true;   // renvoie JSON au lieu de rediriger
 */

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$_require_admin = $require_admin ?? false;
$_api_mode      = $api_mode      ?? false;

// 1. Connecté ?
if (empty($_SESSION['user_id'])) {
    if ($_api_mode) json_err('unauthenticated', 401);
    header('Location: ../login.php?error=session'); exit;
}

// 2. Session expirée ?
if (time() - ($_SESSION['login_time'] ?? 0) > SESSION_LIFETIME) {
    session_destroy();
    if ($_api_mode) json_err('session_expired', 401);
    header('Location: ../login.php?error=expired'); exit;
}

// 3. Rôle admin requis ?
if ($_require_admin && $_SESSION['role'] !== 'admin') {
    if ($_api_mode) json_err('forbidden', 403);
    header('Location: ../dashboard.php?error=forbidden'); exit;
}

// 4. Sliding session (renouvelle le timer à chaque requête)
$_SESSION['login_time'] = time();

// 5. $current_user disponible dans toutes les pages
$current_user = [
    'id'         => $_SESSION['user_id'],
    'id_db'      => $_SESSION['id_db']      ?? 0,
    'name'       => $_SESSION['name']       ?? '',
    'short_name' => $_SESSION['short_name'] ?? '',
    'role'       => $_SESSION['role']       ?? 'user',
    'department' => $_SESSION['department'] ?? '',
    'job_title'  => $_SESSION['job_title']  ?? '',
    'avatar'     => $_SESSION['avatar']     ?? '',
    'email'      => $_SESSION['email']      ?? '',
    'is_admin'   => ($_SESSION['role'] ?? '') === 'admin',
];