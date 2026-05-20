<?php
/**
 * Camrail Directory — Configuration & Connexion BDD
 * require_once __DIR__ . '/config.php';
 */

define('DB_HOST',    'localhost');
define('DB_NAME',    'camrail_annuaire');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

define('SESSION_LIFETIME',  3600);
define('REMEMBER_LIFETIME', 60 * 60 * 24 * 30);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900);

// ── PDO Singleton ─────────────────────────────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'database_unavailable']);
        exit;
    }
    return $pdo;
}

// ── Réponse JSON propre ───────────────────────────────────────────────────────
function json_ok(array $data = [], int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true] + $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_err(string $error, int $code = 400): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Log dans table Historique ─────────────────────────────────────────────────
function log_action(string $action, string $table, int $id_cible, int $id_user): void {
    static $valid = ['Employes', 'Numeros', 'Services', 'Utilisateurs'];
    if (!in_array($table, $valid, true)) return;
    try {
        db()->prepare("
            INSERT INTO Historique (action, table_cible, id_cible, id_utilisateur)
            VALUES (?, ?, ?, ?)
        ")->execute([substr($action, 0, 50), $table, $id_cible, $id_user]);
    } catch (PDOException) {}//
}

// ── IP client ─────────────────────────────────────────────────────────────────
function get_client_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
        if (!empty($_SERVER[$k])) return trim(explode(',', $_SERVER[$k])[0]);
    }
    return '0.0.0.0';
}