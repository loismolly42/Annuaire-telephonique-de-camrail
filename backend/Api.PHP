<?php
/**
 * Camrail Directory — API JSON
 * GET|POST /backend/api.php?action=xxx
 *
 * Toutes les routes nécessitent une session valide.
 * Les routes marquées [ADMIN] nécessitent nom_role = 'admin'.
 *
 * ┌─────────────────────────────────┬────────┬───────┐
 * │ Action                          │ Méthode│ Admin │
 * ├─────────────────────────────────┼────────┼───────┤
 * │ employees                       │ GET    │       │
 * │ employee                        │ GET    │       │
 * │ departments                     │ GET    │       │
 * │ stats                           │ GET    │       │
 * │ search                          │ GET    │       │
 * │ favorites                       │ GET    │       │
 * │ add_favorite                    │ POST   │       │
 * │ remove_favorite                 │ POST   │       │
 * │ activity_log                    │ GET    │  ✓    │
 * │ validation_queue                │ GET    │  ✓    │
 * │ approve_request                 │ POST   │  ✓    │
 * │ reject_request                  │ POST   │  ✓    │
 * │ add_employee                    │ POST   │  ✓    │
 * │ delete_employee                 │ POST   │  ✓    │
 * │ update_profile                  │ POST   │       │
 * │ request_modification            │ POST   │       │
 * │ change_password                 │ POST   │       │
 * └─────────────────────────────────┴────────┴───────┘
 */

$api_mode = true;
require_once __DIR__ . '/session_guard.php';

$action = trim($_GET['action'] ?? $_POST['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

// ── Routes ────────────────────────────────────────────────────────────────────
match (true) {

    // ── Employés ─────────────────────────────────────────────────────────────
    $action === 'employees' && $method === 'GET'
        => api_employees(),

    $action === 'employee' && $method === 'GET'
        => api_employee((int)($_GET['id'] ?? 0)),

    // ── Départements ─────────────────────────────────────────────────────────
    $action === 'departments' && $method === 'GET'
        => api_departments(),

    // ── Stats dashboard ───────────────────────────────────────────────────────
    $action === 'stats' && $method === 'GET'
        => api_stats(),

    // ── Recherche globale ─────────────────────────────────────────────────────
    $action === 'search' && $method === 'GET'
        => api_search(trim($_GET['q'] ?? '')),

    // ── Favoris ───────────────────────────────────────────────────────────────
    $action === 'favorites' && $method === 'GET'
        => api_favorites(),

    $action === 'add_favorite' && $method === 'POST'
        => api_add_favorite((int)($_POST['id_employe'] ?? 0)),

    $action === 'remove_favorite' && $method === 'POST'
        => api_remove_favorite((int)($_POST['id_employe'] ?? 0)),

    // ── Journal d'activité [ADMIN] ────────────────────────────────────────────
    $action === 'activity_log' && $method === 'GET'
        => api_activity_log(),

    // ── File de validation [ADMIN] ────────────────────────────────────────────
    $action === 'validation_queue' && $method === 'GET'
        => api_validation_queue(),

    $action === 'approve_request' && $method === 'POST'
        => api_handle_request((int)($_POST['id_demande'] ?? 0), 'valide'),

    $action === 'reject_request' && $method === 'POST'
        => api_handle_request((int)($_POST['id_demande'] ?? 0), 'refuse'),

    // ── CRUD employé [ADMIN] ──────────────────────────────────────────────────
    $action === 'add_employee' && $method === 'POST'
        => api_add_employee(),

    $action === 'delete_employee' && $method === 'POST'
        => api_delete_employee((int)($_POST['id_employe'] ?? 0)),

    // ── Profil utilisateur ────────────────────────────────────────────────────
    $action === 'update_profile' && $method === 'POST'
        => api_update_profile(),

    $action === 'request_modification' && $method === 'POST'
        => api_request_modification(),

    $action === 'change_password' && $method === 'POST'
        => api_change_password(),

    // ── Route inconnue ────────────────────────────────────────────────────────
    default => json_err('unknown_action', 404),
};

// =============================================================================
// IMPLÉMENTATION DES ROUTES
// =============================================================================

// ── Liste des employés (paginée, filtrable) ───────────────────────────────────
function api_employees(): never {
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $limit   = min(48, max(1, (int)($_GET['limit'] ?? 24)));
    $offset  = ($page - 1) * $limit;
    $dept    = (int)($_GET['dept'] ?? 0);
    $status  = $_GET['status'] ?? '';   // non utilisé dans le schéma actuel
    $search  = trim($_GET['q'] ?? '');

    $where   = [];
    $params  = [];

    if ($dept > 0) {
        $where[] = 'e.id_service = ?';
        $params[] = $dept;
    }
    if ($search !== '') {
        $where[] = '(e.nom LIKE ? OR e.prenom LIKE ? OR e.poste LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like);
    }

    $sql_where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Total
    $count = db()->prepare("
        SELECT COUNT(*) FROM Employes e $sql_where
    ");
    $count->execute($params);
    $total = (int) $count->fetchColumn();

    // Données
    $stmt = db()->prepare("
        SELECT
            e.id_employe,
            e.nom,
            e.prenom,
            e.poste,
            e.email,
            e.photo,
            s.id_service,
            s.nom_service,
            n.numero AS phone_principal
        FROM Employes e
        LEFT JOIN Services s ON s.id_service = e.id_service
        LEFT JOIN Numeros n  ON n.id_employe  = e.id_employe AND n.principal = 1
        $sql_where
        ORDER BY e.nom ASC, e.prenom ASC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $employees = $stmt->fetchAll();

    json_ok([
        'employees'   => $employees,
        'total'       => $total,
        'page'        => $page,
        'pages'       => (int) ceil($total / $limit),
        'limit'       => $limit,
    ]);
}

// ── Fiche détaillée d'un employé ─────────────────────────────────────────────
function api_employee(int $id): never {
    if ($id <= 0) json_err('invalid_id');

    // Infos de base
    $stmt = db()->prepare("
        SELECT
            e.id_employe, e.nom, e.prenom, e.poste, e.email, e.photo,
            e.date_creation,
            s.id_service, s.nom_service
        FROM Employes e
        LEFT JOIN Services s ON s.id_service = e.id_service
        WHERE e.id_employe = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $emp = $stmt->fetch();
    if (!$emp) json_err('not_found', 404);

    // Numéros
    $nstmt = db()->prepare("
        SELECT id_numero, type_numero, numero, principal
        FROM Numeros
        WHERE id_employe = ?
        ORDER BY principal DESC
    ");
    $nstmt->execute([$id]);
    $emp['numeros'] = $nstmt->fetchAll();

    json_ok(['employee' => $emp]);
}

// ── Départements avec comptage ────────────────────────────────────────────────
function api_departments(): never {
    $stmt = db()->query("
        SELECT
            s.id_service,
            s.nom_service,
            s.description,
            COUNT(e.id_employe) AS nb_employes
        FROM Services s
        LEFT JOIN Employes e ON e.id_service = s.id_service
        GROUP BY s.id_service, s.nom_service, s.description
        ORDER BY nb_employes DESC
    ");
    json_ok(['departments' => $stmt->fetchAll()]);
}

// ── Stats globales (dashboard) ────────────────────────────────────────────────
function api_stats(): never {
    $db = db();

    $total_emp  = (int) $db->query("SELECT COUNT(*) FROM Employes")->fetchColumn();
    $total_dept = (int) $db->query("SELECT COUNT(*) FROM Services")->fetchColumn();
    $total_user = (int) $db->query("SELECT COUNT(*) FROM Utilisateurs")->fetchColumn();
    $en_attente = (int) $db->query("SELECT COUNT(*) FROM Demandes_Modification WHERE statut = 'en_attente'")->fetchColumn();

    // Répartition par service (top 5)
    $repartition = $db->query("
        SELECT s.nom_service, COUNT(e.id_employe) AS total
        FROM Services s
        LEFT JOIN Employes e ON e.id_service = s.id_service
        GROUP BY s.id_service, s.nom_service
        ORDER BY total DESC
        LIMIT 5
    ")->fetchAll();

    // Dernières embauches
    $recent = $db->query("
        SELECT e.nom, e.prenom, e.poste, e.photo, e.date_creation, s.nom_service
        FROM Employes e
        LEFT JOIN Services s ON s.id_service = e.id_service
        ORDER BY e.date_creation DESC
        LIMIT 5
    ")->fetchAll();

    json_ok([
        'total_employes'  => $total_emp,
        'total_services'  => $total_dept,
        'total_users'     => $total_user,
        'en_attente'      => $en_attente,
        'repartition'     => $repartition,
        'recents'         => $recent,
    ]);
}

// ── Recherche globale ─────────────────────────────────────────────────────────
function api_search(string $q): never {
    if (strlen($q) < 2) json_ok(['results' => []]);

    $like = '%' . $q . '%';
    $stmt = db()->prepare("
        SELECT
            e.id_employe,
            e.nom,
            e.prenom,
            e.poste,
            e.photo,
            e.email,
            s.nom_service
        FROM Employes e
        LEFT JOIN Services s ON s.id_service = e.id_service
        WHERE e.nom LIKE ? OR e.prenom LIKE ? OR e.poste LIKE ? OR s.nom_service LIKE ?
        ORDER BY e.nom ASC
        LIMIT 20
    ");
    $stmt->execute([$like, $like, $like, $like]);
    json_ok(['results' => $stmt->fetchAll(), 'query' => $q]);
}

// ── Favoris ───────────────────────────────────────────────────────────────────
function api_favorites(): never {
    global $current_user;
    $stmt = db()->prepare("
        SELECT e.id_employe, e.nom, e.prenom, e.poste, e.photo, e.email, s.nom_service
        FROM Favoris f
        INNER JOIN Employes e ON e.id_employe  = f.id_employe
        LEFT  JOIN Services s ON s.id_service  = e.id_service
        WHERE f.id_utilisateur = ?
        ORDER BY f.date_ajout DESC
    ");
    $stmt->execute([$current_user['id_db']]);
    json_ok(['favorites' => $stmt->fetchAll()]);
}

function api_add_favorite(int $id_emp): never {
    global $current_user;
    if ($id_emp <= 0) json_err('invalid_id');
    try {
        db()->prepare("
            INSERT IGNORE INTO Favoris (id_utilisateur, id_employe) VALUES (?, ?)
        ")->execute([$current_user['id_db'], $id_emp]);
    } catch (PDOException) { json_err('db_error', 500); }
    json_ok(['message' => 'Ajouté aux favoris']);
}

function api_remove_favorite(int $id_emp): never {
    global $current_user;
    if ($id_emp <= 0) json_err('invalid_id');
    db()->prepare("
        DELETE FROM Favoris WHERE id_utilisateur = ? AND id_employe = ?
    ")->execute([$current_user['id_db'], $id_emp]);
    json_ok(['message' => 'Retiré des favoris']);
}

// ── Journal d'activité [ADMIN] ────────────────────────────────────────────────
function api_activity_log(): never {
    global $current_user;
    if (!$current_user['is_admin']) json_err('forbidden', 403);

    $limit = min(100, (int)($_GET['limit'] ?? 50));
    $stmt  = db()->prepare("
        SELECT
            h.id_historique,
            h.action,
            h.table_cible,
            h.id_cible,
            h.date_heure,
            CONCAT(u.prenom, ' ', u.nom) AS admin_name,
            u.login
        FROM Historique h
        LEFT JOIN Utilisateurs u ON u.id_utilisateur = h.id_utilisateur
        ORDER BY h.date_heure DESC
        LIMIT $limit
    ");
    $stmt->execute();
    json_ok(['logs' => $stmt->fetchAll()]);
}

// ── File de validation [ADMIN] ────────────────────────────────────────────────
function api_validation_queue(): never {
    global $current_user;
    if (!$current_user['is_admin']) json_err('forbidden', 403);

    $stmt = db()->query("
        SELECT
            d.id_demande,
            d.table_cible,
            d.id_cible,
            d.champ_modifie,
            d.ancienne_valeur,
            d.nouvelle_valeur,
            d.statut,
            d.type_validation,
            d.date_demande,
            CONCAT(u.prenom, ' ', u.nom) AS demandeur,
            e.nom AS emp_nom, e.prenom AS emp_prenom, e.photo AS emp_photo
        FROM Demandes_Modification d
        LEFT JOIN Utilisateurs u ON u.id_utilisateur = d.id_utilisateur
        LEFT JOIN Employes e     ON e.id_employe     = d.id_cible AND d.table_cible = 'Employes'
        WHERE d.statut = 'en_attente'
        ORDER BY d.date_demande ASC
    ");
    json_ok(['queue' => $stmt->fetchAll()]);
}

// ── Approuver / Refuser une demande [ADMIN] ───────────────────────────────────
function api_handle_request(int $id_demande, string $statut): never {
    global $current_user;
    if (!$current_user['is_admin']) json_err('forbidden', 403);
    if ($id_demande <= 0) json_err('invalid_id');

    $db = db();

    // Récupérer la demande
    $stmt = $db->prepare("SELECT * FROM Demandes_Modification WHERE id_demande = ? AND statut = 'en_attente' LIMIT 1");
    $stmt->execute([$id_demande]);
    $demande = $stmt->fetch();
    if (!$demande) json_err('not_found', 404);

    $db->beginTransaction();
    try {
        // Mettre à jour le statut
        $db->prepare("
            UPDATE Demandes_Modification
            SET statut = ?, id_admin = ?, date_validation = NOW()
            WHERE id_demande = ?
        ")->execute([$statut, $current_user['id_db'], $id_demande]);

        // Si approuvé : appliquer la modification
        if ($statut === 'valide' && $demande['table_cible'] === 'Employes') {
            $champ = $demande['champ_modifie'];
            // Whitelist des champs modifiables
            $allowed_fields = ['nom', 'prenom', 'poste', 'email', 'photo', 'id_service'];
            if (in_array($champ, $allowed_fields, true)) {
                $db->prepare("UPDATE Employes SET `$champ` = ? WHERE id_employe = ?")
                   ->execute([$demande['nouvelle_valeur'], $demande['id_cible']]);
            }
        }

        log_action('DEMANDE_' . strtoupper($statut), $demande['table_cible'], $id_demande, $current_user['id_db']);
        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        json_err('db_error', 500);
    }

    json_ok(['message' => $statut === 'valide' ? 'Demande approuvée' : 'Demande refusée']);
}

// ── Ajouter un employé [ADMIN] ────────────────────────────────────────────────
function api_add_employee(): never {
    global $current_user;
    if (!$current_user['is_admin']) json_err('forbidden', 403);

    $nom       = trim($_POST['nom']        ?? '');
    $prenom    = trim($_POST['prenom']     ?? '');
    $poste     = trim($_POST['poste']      ?? '');
    $email     = trim($_POST['email']      ?? '');
    $id_service= (int)($_POST['id_service']?? 0);
    $photo     = trim($_POST['photo']      ?? '');
    $numeros   = $_POST['numeros']         ?? [];   // tableau [{type, numero, principal}]

    if (!$nom || !$prenom || !$email) json_err('missing_fields');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_err('invalid_email');

    $db = db();
    $db->beginTransaction();
    try {
        $db->prepare("
            INSERT INTO Employes (nom, prenom, poste, email, photo, id_service)
            VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([$nom, $prenom, $poste, $email, $photo, $id_service ?: null]);

        $id_emp = (int) $db->lastInsertId();

        // Numéros
        foreach ((array)$numeros as $n) {
            $type  = $n['type']      ?? 'mobile';
            $num   = trim($n['numero'] ?? '');
            $princ = !empty($n['principal']) ? 1 : 0;
            if ($num) {
                $db->prepare("
                    INSERT INTO Numeros (id_employe, type_numero, numero, principal)
                    VALUES (?, ?, ?, ?)
                ")->execute([$id_emp, $type, $num, $princ]);
            }
        }

        log_action('ADD_EMPLOYE', 'Employes', $id_emp, $current_user['id_db']);
        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        json_err('db_error', 500);
    }

    json_ok(['message' => 'Employé ajouté', 'id_employe' => $id_emp]);
}

// ── Supprimer un employé [ADMIN] ──────────────────────────────────────────────
function api_delete_employee(int $id): never {
    global $current_user;
    if (!$current_user['is_admin']) json_err('forbidden', 403);
    if ($id <= 0) json_err('invalid_id');

    $db = db();
    $db->beginTransaction();
    try {
        $db->prepare("DELETE FROM Numeros  WHERE id_employe = ?")->execute([$id]);
        $db->prepare("DELETE FROM Favoris  WHERE id_employe = ?")->execute([$id]);
        $db->prepare("DELETE FROM Employes WHERE id_employe = ?")->execute([$id]);
        log_action('DELETE_EMPLOYE', 'Employes', $id, $current_user['id_db']);
        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        json_err('db_error', 500);
    }
    json_ok(['message' => 'Employé supprimé']);
}

// ── Mettre à jour son propre profil ──────────────────────────────────────────
function api_update_profile(): never {
    global $current_user;

    $id_emp = $current_user['id_employe'] ?? 0;
    if (!$id_emp) json_err('no_employee_linked');

    $allowed = ['poste', 'email', 'photo'];
    $sets = []; $params = [];

    foreach ($allowed as $field) {
        if (isset($_POST[$field])) {
            $val = trim($_POST[$field]);
            if ($field === 'email' && !filter_var($val, FILTER_VALIDATE_EMAIL)) json_err('invalid_email');
            $sets[]   = "`$field` = ?";
            $params[] = $val;
        }
    }
    if (!$sets) json_err('nothing_to_update');

    $params[] = $id_emp;
    db()->prepare("UPDATE Employes SET " . implode(', ', $sets) . " WHERE id_employe = ?")
        ->execute($params);

    log_action('UPDATE_PROFILE', 'Employes', $id_emp, $current_user['id_db']);
    json_ok(['message' => 'Profil mis à jour']);
}

// ── Soumettre une demande de modification ─────────────────────────────────────
function api_request_modification(): never {
    global $current_user;

    $table   = $_POST['table_cible']    ?? 'Employes';
    $id_cib  = (int)($_POST['id_cible'] ?? 0);
    $champ   = trim($_POST['champ']     ?? '');
    $valeur  = trim($_POST['valeur']    ?? '');
    $type    = $_POST['type_validation']?? 'ADMIN';

    $valid_tables = ['Employes', 'Numeros', 'Services'];
    if (!in_array($table, $valid_tables, true)) json_err('invalid_table');
    if (!$id_cib || !$champ || $valeur === '') json_err('missing_fields');

    // Récupérer l'ancienne valeur
    $old = '';
    if ($table === 'Employes') {
        $allowed_fields = ['nom','prenom','poste','email','photo','id_service'];
        if (!in_array($champ, $allowed_fields, true)) json_err('invalid_field');
        $row = db()->prepare("SELECT `$champ` FROM Employes WHERE id_employe = ?")->execute([$id_cib]);
        $old = db()->query("SELECT `$champ` FROM Employes WHERE id_employe = $id_cib")->fetchColumn() ?? '';
    }

    db()->prepare("
        INSERT INTO Demandes_Modification
            (table_cible, id_cible, champ_modifie, ancienne_valeur, nouvelle_valeur, type_validation, id_utilisateur)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ")->execute([$table, $id_cib, $champ, $old, $valeur, $type, $current_user['id_db']]);

    json_ok(['message' => 'Demande soumise, en attente de validation']);
}

// ── Changer son mot de passe ──────────────────────────────────────────────────
function api_change_password(): never {
    global $current_user;

    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';

    if (strlen($new) < 8) json_err('password_too_short');

    $stmt = db()->prepare("SELECT mot_de_passe FROM Utilisateurs WHERE id_utilisateur = ?");
    $stmt->execute([$current_user['id_db']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($old, $row['mot_de_passe'])) json_err('wrong_password');

    $hash = password_hash($new, PASSWORD_DEFAULT);
    db()->prepare("UPDATE Utilisateurs SET mot_de_passe = ? WHERE id_utilisateur = ?")
        ->execute([$hash, $current_user['id_db']]);

    log_action('CHANGE_PASSWORD', 'Utilisateurs', $current_user['id_db'], $current_user['id_db']);
    json_ok(['message' => 'Mot de passe modifié avec succès']);
}