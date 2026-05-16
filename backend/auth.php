<?php
/**
 * Camrail Directory — Authentification
 * POST /backend/auth.php
 *
 * Structure BDD attendue :
 *   Utilisateurs  : id_utilisateur, login, mot_de_passe (bcrypt), nom, prenom, id_role
 *   Roles         : id_role, nom_role  ('admin' | 'user')
 *   Employes      : id_employe, nom, prenom, poste, photo, email, id_service
 *   Services      : id_service, nom_service
 *   Historique    : id, action, table_cible, id_cible, id_utilisateur, created_at
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

// ── Lecture & nettoyage des inputs ────────────────────────────────────────────
$login    = strtoupper(trim($_POST['employee_id'] ?? ''));
$password = $_POST['password'] ?? '';
$role_req = in_array($_POST['role'] ?? '', ['admin', 'user'])
            ? $_POST['role']
            : 'user';
$remember = !empty($_POST['remember']);
$ip       = get_client_ip();

// Validation basique
if ($login === '' || $password === '') {
    header('Location: ../login.php?error=empty');
    exit;
}

// Validation format CR-XXXXXX
if (!preg_match('/^CR-\d{6}$/i', $login)) {
    header('Location: ../login.php?error=format');
    exit;
}

// ── Anti-brute-force ──────────────────────────────────────────────────────────
$lock_file = sys_get_temp_dir() . '/cr_lock_' . md5($ip) . '.json';

if (is_locked($lock_file)) {
    log_action('LOGIN_LOCKED', $login, $ip);
    header('Location: ../login.php?error=locked');
    exit;
}

// ── Requête BDD ───────────────────────────────────────────────────────────────
try {
    $stmt = db()->prepare("
        SELECT
            u.id_utilisateur,
            u.login,
            u.mot_de_passe,
            u.nom        AS u_nom,
            u.prenom     AS u_prenom,
            r.nom_role,
            e.id_employe,
            e.nom        AS e_nom,
            e.prenom     AS e_prenom,
            e.poste,
            e.photo,
            e.email,
            s.nom_service
        FROM Utilisateurs u
        INNER JOIN Roles r    ON r.id_role   = u.id_role
        LEFT  JOIN Employes e ON e.email     = u.login
                             OR e.email      = LOWER(u.login)
        LEFT  JOIN Services s ON s.id_service = e.id_service
        WHERE u.login = ?
        LIMIT 1
    ");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('[Camrail Auth] DB error: ' . $e->getMessage());
    header('Location: ../login.php?error=db');
    exit;
}

// ── Utilisateur introuvable ───────────────────────────────────────────────────
if (!$user) {
    fail_attempt($lock_file);
    log_action('LOGIN_FAIL_USER', $login, $ip);
    header('Location: ../login.php?error=user');
    exit;
}

// ── Vérification mot de passe ─────────────────────────────────────────────────
if (!password_verify($password, $user['mot_de_passe'])) {
    fail_attempt($lock_file);
    log_action('LOGIN_FAIL_PWD', $login, $ip);
    header('Location: ../login.php?error=password');
    exit;
}

// ── Vérification du rôle demandé ─────────────────────────────────────────────
if ($role_req === 'admin' && $user['nom_role'] !== 'admin') {
    log_action('LOGIN_FAIL_ROLE', $login, $ip);
    header('Location: ../login.php?error=role');
    exit;
}

// ── Succès — création de session ─────────────────────────────────────────────
clear_lock($lock_file);
session_regenerate_id(true);

// Nom affiché : priorité fiche Employé, fallback fiche Utilisateur
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

// Cookie "Se souvenir"
if ($remember) {
    $token = bin2hex(random_bytes(32));
    setcookie('camrail_remember', $token, [
        'expires'  => time() + REMEMBER_LIFETIME,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
}

log_action('LOGIN_SUCCESS', $login, $ip);
redirect_by_role($user['nom_role']);


// ════════════════════════════════════════════════════════
//  FONCTIONS UTILITAIRES
// ════════════════════════════════════════════════════════

/** Redirige selon le rôle */
function redirect_by_role(string $role): never {
    if ($role === 'admin') {
        header('Location: ../admin.PHP');
    } else {
        header('Location: ../dashboard.php');
    }
    exit;
}

/** Vérifie si l'IP est bloquée */
function is_locked(string $f): bool {
    if (!file_exists($f)) return false;
    $d = json_decode(file_get_contents($f), true);
    if ((time() - ($d['t'] ?? 0)) > LOCKOUT_TIME) {
        unlink($f);
        return false;
    }
    return ($d['n'] ?? 0) >= MAX_LOGIN_ATTEMPTS;
}

/** Enregistre une tentative échouée */
function fail_attempt(string $f): void {
    $d = file_exists($f) ? json_decode(file_get_contents($f), true) : ['n' => 0, 't' => time()];
    if ((time() - ($d['t'] ?? 0)) > LOCKOUT_TIME) {
        $d = ['n' => 0, 't' => time()];
    }
    $d['n']++;
    file_put_contents($f, json_encode($d), LOCK_EX);
}

/** Supprime le fichier de lock */
function clear_lock(string $f): void {
    if (file_exists($f)) unlink($f);
}

/** Journalise dans la table Historique (si elle existe) */
function log_action(string $action, string $login, string $ip): void {
    try {
        $stmt = db()->prepare("
            INSERT INTO Historique (action, table_cible, id_cible, id_utilisateur)
            SELECT ?, 'Utilisateurs', id_utilisateur, id_utilisateur
            FROM Utilisateurs WHERE login = ?
            UNION ALL
            SELECT ?, 'Utilisateurs', 0, 0
            LIMIT 1
        ");
        $stmt->execute([$action, $login, $action]);
    } catch (PDOException) {
        // Silencieux si la table n'existe pas encore
    }
}