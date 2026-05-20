<?php
/**
 * Camrail Directory — Fonctions utilitaires backend
 * À inclure dans session_guard.php ou api.php
 */

// ── Connexion PDO (singleton) ─────────────────────────────────────────────────
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    // ⚠️ Modifiez ces valeurs selon votre configuration locale
    $host   = 'localhost';
    $dbname = 'camrail_annuaire';   // nom de votre base de données
    $user   = 'root';               // votre utilisateur MySQL
    $pass   = '';                   // votre mot de passe MySQL (souvent vide en local)
    $charset= 'utf8mb4';

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=$charset",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        // En mode API on retourne du JSON, sinon on affiche l'erreur
        if (!empty($GLOBALS['api_mode'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'db_connection_failed']);
            exit;
        }
        die('Erreur de connexion à la base de données : ' . $e->getMessage());
    }

    return $pdo;
}

// ── Réponse JSON succès ───────────────────────────────────────────────────────
function json_ok(array $data = []): never {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Réponse JSON erreur ───────────────────────────────────────────────────────
function json_err(string $error, int $http_code = 400): never {
    http_response_code($http_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Enregistrer une action dans l'historique ──────────────────────────────────
function log_action(string $action, string $table_cible, int $id_cible, int $id_utilisateur): void {
    try {
        db()->prepare("
            INSERT INTO Historique (action, table_cible, id_cible, id_utilisateur, date_heure)
            VALUES (?, ?, ?, ?, NOW())
        ")->execute([$action, $table_cible, $id_cible, $id_utilisateur]);
    } catch (PDOException) {
        // On ne bloque pas l'exécution si le log échoue
    }
}