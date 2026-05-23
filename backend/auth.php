<?php
/**
 * Camrail Directory — Authentification
 */

// session_name AVANT tout session_start
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

if (!empty($_SESSION['user_id'])) {
    redirect_by_role($_SESSION['role']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php'); exit;
}

$email    = strtolower(trim($_POST['employee_email'] ?? ''));
$password = $_POST['password'] ?? '';
$role_req = in_array($_POST['role'] ?? '', ['admin', 'user']) ? $_POST['role'] : 'user';
$remember = !empty($_POST['remember']);
$ip       = get_client_ip();

if ($email === '' || $password === '') { header('Location: ../login.php?error=empty'); exit; }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { header('Location: ../login.php?error=format'); exit; }

$lock_file = sys_get_temp_dir() . '/cr_lock_' . md5($ip) . '.json';
if (is_ip_locked($lock_file)) {
    camrail_log('LOGIN_LOCKED', $email);
    header('Location: ../login.php?error=locked'); exit;
}

try {
    $stmt = db()->prepare("
        SELECT u.id_utilisateur, u.login, u.mot_de_passe,
               u.nom AS u_nom, u.prenom AS u_prenom, r.nom_role,
               e.id_employe, e.nom AS e_nom, e.prenom AS e_prenom,
               e.poste, e.photo, e.email, s.nom_service
        FROM Utilisateurs u
        INNER JOIN Roles r    ON r.id_role    = u.id_role
        LEFT  JOIN Employes e ON e.email      = u.login OR e.email = LOWER(u.login)
        LEFT  JOIN Services s ON s.id_service = e.id_service
        WHERE LOWER(u.login) = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('[Camrail] DB: ' . $e->getMessage());
    header('Location: ../login.php?error=db'); exit;
}

if (!$user) { record_fail($lock_file); camrail_log('LOGIN_FAIL_USER', $email); header('Location: ../login.php?error=user'); exit; }
if (!password_verify($password, $user['mot_de_passe'])) { record_fail($lock_file); camrail_log('LOGIN_FAIL_PWD', $email); header('Location: ../login.php?error=password'); exit; }

$role_db  = strtolower(trim($user['nom_role']));
$is_admin = in_array($role_db, ['admin','administrateur','administrator','superadmin','super_admin']);

if ($role_req === 'admin' && !$is_admin) { camrail_log('LOGIN_FAIL_ROLE', $email); header('Location: ../login.php?error=role'); exit; }

clear_ip_lock($lock_file);
session_regenerate_id(true);

$prenom       = $user['e_prenom'] ?? $user['u_prenom'] ?? '';
$nom          = $user['e_nom']    ?? $user['u_nom']    ?? '';
$role_session = $is_admin ? 'admin' : 'user';

$_SESSION = [];
$_SESSION['user_id']    = $user['login'];
$_SESSION['id_db']      = (int)$user['id_utilisateur'];
$_SESSION['id_employe'] = (int)($user['id_employe'] ?? 0);
$_SESSION['name']       = trim("$prenom $nom") ?: $email;
$_SESSION['role']       = $role_session;
$_SESSION['department'] = $user['nom_service'] ?? '';
$_SESSION['job_title']  = $user['poste']       ?? '';
$_SESSION['avatar']     = $user['photo']       ?? '';
$_SESSION['email']      = $user['email']       ?? $email;
$_SESSION['login_time'] = time();
$_SESSION['ip']         = $ip;

if ($remember) {
    setcookie('camrail_remember', bin2hex(random_bytes(32)), [
        'expires' => time() + REMEMBER_LIFETIME, 'path' => '/', 'httponly' => true, 'samesite' => 'Strict',
    ]);
}

camrail_log('LOGIN_SUCCESS', $email);
redirect_by_role($role_session);

// ── Fonctions ──────────────────────────────────────────────────────────────────
function redirect_by_role(string $role): never {
    header('Location: /camrail_annuaire/' . (strtolower($role) === 'admin' ? 'admin.php' : 'directory.php')); exit;
}
function is_ip_locked(string $f): bool {
    if (!file_exists($f)) return false;
    $d = json_decode(file_get_contents($f), true);
    if ((time() - ($d['t'] ?? 0)) > LOCKOUT_TIME) { unlink($f); return false; }
    return ($d['n'] ?? 0) >= MAX_LOGIN_ATTEMPTS;
}
function record_fail(string $f): void {
    $d = file_exists($f) ? json_decode(file_get_contents($f), true) : ['n'=>0,'t'=>time()];
    if ((time()-($d['t']??0)) > LOCKOUT_TIME) $d = ['n'=>0,'t'=>time()];
    $d['n']++; file_put_contents($f, json_encode($d), LOCK_EX);
}
function clear_ip_lock(string $f): void { if (file_exists($f)) unlink($f); }
function camrail_log(string $action, string $login): void {
    try {
        db()->prepare("
            INSERT INTO Historique (action, table_cible, id_cible, id_utilisateur)
            SELECT ?, 'Utilisateurs', id_utilisateur, id_utilisateur
            FROM Utilisateurs WHERE LOWER(login) = ?
            UNION ALL SELECT ?, 'Utilisateurs', 0, 0 LIMIT 1
        ")->execute([$action, $login, $action]);
    } catch (PDOException) {}
}