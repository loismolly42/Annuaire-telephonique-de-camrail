<?php
/**
 * Camrail Directory — Authentification
 * POST /backend/auth.php
 */

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// Déjà connecté → redirection directe
if (!empty($_SESSION['user_id'])) {
    redirect_by_role($_SESSION['role']);
}

// Uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

// ── Inputs ────────────────────────────────────────────────────────────────────
$login    = strtoupper(trim($_POST['employee_id'] ?? ''));
$password = $_POST['password'] ?? '';
$role_req = in_array($_POST['role'] ?? '', ['admin', 'user']) ? $_POST['role'] : 'user';
$remember = !empty($_POST['remember']);
$ip       = get_client_ip();

if ($login === '' || $password === '') {
    header('Location: ../login.php?error=empty'); exit;
}
if (!preg_match('/^CR-\d{6}$/i', $login)) {
    header('Location: ../login.php?error=format'); exit;
}

// ── Anti-brute-force ──────────────────────────────────────────────────────────
$lock_file = sys_get_temp_dir() . '/cr_lock_' . md5($ip) . '.json';

if (is_ip_locked($lock_file)) {
    camrail_log('LOGIN_LOCKED', $login);
    header('Location: ../login.php?error=locked'); exit;
}

// ── Requête BDD ───────────────────────────────────────────────────────────────
try {
    $stmt = db()->prepare("
        SELECT
            u.id_utilisateur, u.login, u.mot_de_passe,
            u.nom AS u_nom, u.prenom AS u_prenom,
            r.nom_role,
            e.id_employe, e.nom AS e_nom, e.prenom AS e_prenom,
            e.poste, e.photo, e.email,
            s.nom_service
        FROM Utilisateurs u
        INNER JOIN Roles r    ON r.id_role    = u.id_role
        LEFT  JOIN Employes e ON e.email      = u.login OR e.email = LOWER(u.login)
        LEFT  JOIN Services s ON s.id_service = e.id_service
        WHERE u.login = ?
        LIMIT 1
    ");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('[Camrail] DB: ' . $e->getMessage());
    header('Location: ../login.php?error=db'); exit;
}

if (!$user) {
    record_fail($lock_file);
    camrail_log('LOGIN_FAIL_USER', $login);
    header('Location: ../login.php?error=user'); exit;
}

if (!password_verify($password, $user['mot_de_passe'])) {
    record_fail($lock_file);
    camrail_log('LOGIN_FAIL_PWD', $login);
    header('Location: ../login.php?error=password'); exit;
}

if ($role_req === 'admin' && $user['nom_role'] !== 'admin') {
    camrail_log('LOGIN_FAIL_ROLE', $login);
    header('Location: ../login.php?error=role'); exit;
}

// ── Succès ────────────────────────────────────────────────────────────────────
clear_ip_lock($lock_file);
session_regenerate_id(true);

$prenom    = $user['e_prenom'] ?? $user['u_prenom'] ?? '';
$nom       = $user['e_nom']    ?? $user['u_nom']    ?? '';
$full_name = trim("$prenom $nom");

$_SESSION = [];
$_SESSION['user_id']    = $login;
$_SESSION['id_db']      = (int)$user['id_utilisateur'];
$_SESSION['id_employe'] = (int)($user['id_employe'] ?? 0);
$_SESSION['name']       = $full_name ?: $login;
$_SESSION['role']       = $user['nom_role'];
$_SESSION['department'] = $user['nom_service'] ?? '';
$_SESSION['job_title']  = $user['poste']       ?? '';
$_SESSION['avatar']     = $user['photo']       ?? '';
$_SESSION['email']      = $user['email']       ?? strtolower($login) . '@camrail.cm';
$_SESSION['login_time'] = time();
$_SESSION['ip']         = $ip;

if ($remember) {
    setcookie('camrail_remember', bin2hex(random_bytes(32)), [
        'expires'  => time() + REMEMBER_LIFETIME,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

camrail_log('LOGIN_SUCCESS', $login);
redirect_by_role($user['nom_role']);

// ════════════════════════════════════════════════════════
//  FONCTIONS
// ════════════════════════════════════════════════════════

function redirect_by_role(string $role): never {
    // admin → admin.php  |  user → dashboard.php
    $page = ($role === 'admin') ? 'admin.php' : 'dashboard.php';
    header('Location: /camrail_annuaire/' . $page);
    exit;
}

function is_ip_locked(string $f): bool {
    if (!file_exists($f)) return false;
    $d = json_decode(file_get_contents($f), true);
    if ((time() - ($d['t'] ?? 0)) > LOCKOUT_TIME) { unlink($f); return false; }
    return ($d['n'] ?? 0) >= MAX_LOGIN_ATTEMPTS;
}

function record_fail(string $f): void {
    $d = file_exists($f) ? json_decode(file_get_contents($f), true) : ['n' => 0, 't' => time()];
    if ((time() - ($d['t'] ?? 0)) > LOCKOUT_TIME) $d = ['n' => 0, 't' => time()];
    $d['n']++;
    file_put_contents($f, json_encode($d), LOCK_EX);
}

function clear_ip_lock(string $f): void {
    if (file_exists($f)) unlink($f);
}

function camrail_log(string $action, string $login): void {
    try {
        $stmt = db()->prepare("
            INSERT INTO Historique (action, table_cible, id_cible, id_utilisateur)
            SELECT ?, 'Utilisateurs', id_utilisateur, id_utilisateur
            FROM Utilisateurs WHERE login = ?
            UNION ALL SELECT ?, 'Utilisateurs', 0, 0
            LIMIT 1
        ");
        $stmt->execute([$action, $login, $action]);
    } catch (PDOException) {}
}