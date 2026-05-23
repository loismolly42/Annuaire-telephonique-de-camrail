<?php
/**
 * backend/api.php  — Camrail Annuaire
 */

$GLOBALS['api_mode'] = true;

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name('camrail_sess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

if (empty($_SESSION['user_id'])) {
    json_err('unauthenticated', 401);
}

header('Content-Type: application/json; charset=utf-8');

// ── Utilitaires locaux ────────────────────────────────────────────────────────

function json_out(array $data): void {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function is_admin(): bool {
    return ($_SESSION['role'] ?? '') === 'admin';
}

function require_admin(): void {
    if (!is_admin()) json_out(['success' => false, 'error' => 'forbidden']);
}

function clean(?string $v): string {
    return trim(htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'));
}

function current_user_id(): int {
    return (int)($_SESSION['id_utilisateur'] ?? 0);
}

function log_act(string $action, string $table, int $id_cible): void {
    $uid = current_user_id();
    if (!$uid) return;
    log_action($action, $table, $id_cible, $uid);
}

// ── Routeur ──────────────────────────────────────────────────────────────────

$method = $_SERVER['REQUEST_METHOD'];
$action = $method === 'GET'
    ? ($_GET['action']  ?? '')
    : ($_POST['action'] ?? '');

// ── Actions GET ──────────────────────────────────────────────────────────────

if ($method === 'GET') {

    // ── stats ─────────────────────────────────────────────────────────────────
    if ($action === 'stats') {
        require_admin();
        $total_employes = (int)db()->query("SELECT COUNT(*) FROM Employes")->fetchColumn();
        $total_services = (int)db()->query("SELECT COUNT(*) FROM Services")->fetchColumn();
        $total_users    = (int)db()->query("SELECT COUNT(*) FROM Utilisateurs")->fetchColumn();
        $en_attente     = (int)db()->query(
            "SELECT COUNT(*) FROM Demandes_Modification WHERE statut='en_attente'"
        )->fetchColumn();
        $repartition = db()->query(
            "SELECT s.nom_service, COUNT(e.id_employe) as total
             FROM Services s
             LEFT JOIN Employes e ON e.id_service = s.id_service
             GROUP BY s.id_service ORDER BY total DESC LIMIT 6"
        )->fetchAll(PDO::FETCH_ASSOC);
        json_out(['success' => true, 'total_employes' => $total_employes,
                  'total_services' => $total_services, 'total_users' => $total_users,
                  'en_attente' => $en_attente, 'repartition' => $repartition]);
    }

    // ── departments ───────────────────────────────────────────────────────────
    if ($action === 'departments') {
        $rows = db()->query(
            "SELECT s.id_service, s.nom_service, COUNT(e.id_employe) AS nb_employes
             FROM Services s
             LEFT JOIN Employes e ON e.id_service = s.id_service
             GROUP BY s.id_service ORDER BY s.nom_service"
        )->fetchAll(PDO::FETCH_ASSOC);
        json_out(['success' => true, 'departments' => $rows]);
    }

    // ── roles ─────────────────────────────────────────────────────────────────
    if ($action === 'roles') {
        require_admin();
        $rows = db()->query("SELECT id_role, nom_role FROM Roles ORDER BY nom_role")
                    ->fetchAll(PDO::FETCH_ASSOC);
        json_out(['success' => true, 'roles' => $rows]);
    }

    // ── localisations ─────────────────────────────────────────────────────────
    if ($action === 'localisations') {
        $rows = db()->query(
            "SELECT id_localisation, nom_localisation, ville, site, batiment, etage, bureau
             FROM Localisations ORDER BY nom_localisation"
        )->fetchAll(PDO::FETCH_ASSOC);
        json_out(['success' => true, 'localisations' => $rows]);
    }

    // ── employees (liste paginée) ─────────────────────────────────────────────
    if ($action === 'employees') {
        $page   = max(1, (int)($_GET['page']  ?? 1));
        $limit  = min(100, max(1, (int)($_GET['limit'] ?? 24)));
        $sort   = in_array($_GET['sort'] ?? '', ['nom','poste','date_creation']) ? $_GET['sort'] : 'nom';
        $q      = trim($_GET['q']    ?? '');
        $dept   = (int)($_GET['dept'] ?? 0);
        $offset = ($page - 1) * $limit;

        $where  = ['1=1'];
        $params = [];

        if ($q) {
            $where[]      = "(e.nom LIKE :q OR e.prenom LIKE :q OR e.poste LIKE :q OR s.nom_service LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }
        if ($dept) {
            $where[]         = "e.id_service = :dept";
            $params[':dept'] = $dept;
        }

        $whereStr = implode(' AND ', $where);
        $orderBy  = $sort === 'date_creation' ? 'e.date_creation DESC' : "e.$sort ASC";

        $stCount = db()->prepare(
            "SELECT COUNT(*) FROM Employes e
             LEFT JOIN Services s ON s.id_service = e.id_service
             WHERE $whereStr"
        );
        $stCount->execute($params);
        $total = (int)$stCount->fetchColumn();

        $stRows = db()->prepare(
            "SELECT e.id_employe, e.prenom, e.nom, e.poste, e.photo, e.email,
                    s.nom_service,
                    (SELECT numero FROM Numeros
                     WHERE id_employe = e.id_employe AND principal = 1 LIMIT 1) AS phone_principal
             FROM Employes e
             LEFT JOIN Services s ON s.id_service = e.id_service
             WHERE $whereStr
             ORDER BY $orderBy
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) $stRows->bindValue($k, $v);
        $stRows->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stRows->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stRows->execute();
        $employees = $stRows->fetchAll(PDO::FETCH_ASSOC);

        json_out([
            'success'   => true,
            'employees' => $employees,
            'total'     => $total,
            'page'      => $page,
            'pages'     => (int)ceil($total / $limit),
            'limit'     => $limit,
        ]);
    }

    // ── employee (détail) ─────────────────────────────────────────────────────
    if ($action === 'employee') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) json_out(['success' => false, 'error' => 'invalid_id']);

        $st = db()->prepare(
            "SELECT e.*, s.nom_service
             FROM Employes e
             LEFT JOIN Services s ON s.id_service = e.id_service
             WHERE e.id_employe = :id"
        );
        $st->execute([':id' => $id]);
        $emp = $st->fetch(PDO::FETCH_ASSOC);
        if (!$emp) json_out(['success' => false, 'error' => 'not_found']);

        $stN = db()->prepare(
            "SELECT type_numero, numero, principal FROM Numeros WHERE id_employe = :id"
        );
        $stN->execute([':id' => $id]);
        $emp['numeros'] = $stN->fetchAll(PDO::FETCH_ASSOC);

        json_out(['success' => true, 'employee' => $emp]);
    }

    // ── employees_without_account ─────────────────────────────────────────────
    if ($action === 'employees_without_account') {
        require_admin();
        $rows = db()->query(
            "SELECT e.id_employe, e.nom, e.prenom, e.poste, s.nom_service
             FROM Employes e
             LEFT JOIN Services s ON s.id_service = e.id_service
             WHERE e.id_employe NOT IN (
                 SELECT id_employe_lie FROM Utilisateurs
                 WHERE id_employe_lie IS NOT NULL
             )
             ORDER BY e.nom, e.prenom"
        )->fetchAll(PDO::FETCH_ASSOC);
        json_out(['success' => true, 'employees' => $rows]);
    }

    // ── get_employee_full ─────────────────────────────────────────────────────
    if ($action === 'get_employee_full') {
        require_admin();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) json_out(['success' => false, 'error' => 'invalid_id']);

        $st = db()->prepare(
            "SELECT e.*, l.nom_localisation, l.ville, l.site, l.batiment,
                    l.etage, l.bureau, l.description
             FROM Employes e
             LEFT JOIN Localisations l ON l.id_localisation = e.id_localisation
             WHERE e.id_employe = :id"
        );
        $st->execute([':id' => $id]);
        $emp = $st->fetch(PDO::FETCH_ASSOC);
        if (!$emp) json_out(['success' => false, 'error' => 'not_found']);

        $loc_fields = ['nom_localisation','ville','site','batiment','etage','bureau','description'];
        $localisation = null;
        foreach ($loc_fields as $f) {
            if (!empty($emp[$f])) { $localisation = []; break; }
        }
        if ($localisation !== null) {
            foreach ($loc_fields as $f) { $localisation[$f] = $emp[$f]; unset($emp[$f]); }
        } else {
            foreach ($loc_fields as $f) unset($emp[$f]);
        }
        $emp['localisation'] = $localisation;

        $st2 = db()->prepare(
            "SELECT type_numero AS type, numero, principal FROM Numeros WHERE id_employe = :id"
        );
        $st2->execute([':id' => $id]);
        $emp['numeros'] = $st2->fetchAll(PDO::FETCH_ASSOC);

        json_out(['success' => true, 'employee' => $emp]);
    }

    // ── validation_queue ──────────────────────────────────────────────────────
    if ($action === 'validation_queue') {
        require_admin();
        $rows = db()->query(
            "SELECT dm.*, u.nom AS dem_nom, u.prenom AS dem_prenom,
                    e.nom AS emp_nom, e.prenom AS emp_prenom, e.photo AS emp_photo
             FROM Demandes_Modification dm
             JOIN Utilisateurs u ON u.id_utilisateur = dm.id_utilisateur
             LEFT JOIN Employes e ON e.id_employe = dm.id_cible AND dm.table_cible = 'Employes'
             WHERE dm.statut = 'en_attente'
             ORDER BY dm.date_demande ASC LIMIT 20"
        )->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['demandeur'] = trim(($r['dem_prenom'] ?? '') . ' ' . ($r['dem_nom'] ?? ''));
        }
        json_out(['success' => true, 'queue' => $rows]);
    }

    // ── activity_log ──────────────────────────────────────────────────────────
    if ($action === 'activity_log') {
        require_admin();
        $limit = min((int)($_GET['limit'] ?? 20), 500);
        $rows = db()->query(
            "SELECT h.*, CONCAT(u.prenom,' ',u.nom) AS admin_name
             FROM Historique h
             LEFT JOIN Utilisateurs u ON u.id_utilisateur = h.id_utilisateur
             ORDER BY h.date_heure DESC LIMIT $limit"
        )->fetchAll(PDO::FETCH_ASSOC);
        json_out(['success' => true, 'logs' => $rows]);
    }

    json_out(['success' => false, 'error' => 'unknown_action']);
}

// ── Actions POST ─────────────────────────────────────────────────────────────

if ($method === 'POST') {

    // ── add_employee / edit_employee ──────────────────────────────────────────
    if (in_array($action, ['add_employee', 'edit_employee'])) {
        require_admin();

        $prenom = clean($_POST['prenom'] ?? '');
        $nom    = clean($_POST['nom']    ?? '');
        $email  = trim($_POST['email']   ?? '');
        $poste  = clean($_POST['poste']  ?? '');
        $photo  = clean($_POST['photo']  ?? '');
        $id_svc = (int)($_POST['id_service'] ?? 0) ?: null;

        if (!$prenom || !$nom || !$email) json_out(['success'=>false,'error'=>'missing_fields']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_out(['success'=>false,'error'=>'invalid_email']);

        $id_localisation = (int)($_POST['id_localisation'] ?? 0) ?: null;
        $loc_nom  = clean($_POST['loc_nom'] ?? '');

        if ($loc_nom) {
            $stLoc = db()->prepare(
                "INSERT INTO Localisations
                   (nom_localisation, description, ville, site, batiment, etage, bureau)
                 VALUES (:nom, :desc, :ville, :site, :bat, :etage, :bureau)"
            );
            $stLoc->execute([
                ':nom'   => $loc_nom,
                ':desc'  => clean($_POST['loc_description'] ?? ''),
                ':ville' => clean($_POST['loc_ville']       ?? ''),
                ':site'  => clean($_POST['loc_site']        ?? ''),
                ':bat'   => clean($_POST['loc_batiment']    ?? ''),
                ':etage' => clean($_POST['loc_etage']       ?? ''),
                ':bureau'=> clean($_POST['loc_bureau']      ?? ''),
            ]);
            $id_localisation = (int)db()->lastInsertId();
        }

        if ($action === 'add_employee') {
            $chk = db()->prepare("SELECT id_employe FROM Employes WHERE email = :e");
            $chk->execute([':e' => $email]);
            if ($chk->fetch()) json_out(['success'=>false,'error'=>'email_already_used']);

            $st = db()->prepare(
                "INSERT INTO Employes (prenom, nom, email, poste, photo, id_service, id_localisation)
                 VALUES (:prenom,:nom,:email,:poste,:photo,:svc,:loc)"
            );
            $st->execute([
                ':prenom' => $prenom, ':nom' => $nom, ':email' => $email,
                ':poste'  => $poste,  ':photo' => $photo ?: null,
                ':svc'    => $id_svc, ':loc'  => $id_localisation,
            ]);
            $id_emp = (int)db()->lastInsertId();

        } else {
            $id_emp = (int)($_POST['id_employe'] ?? 0);
            if (!$id_emp) json_out(['success'=>false,'error'=>'invalid_id']);

            $chk = db()->prepare("SELECT id_employe FROM Employes WHERE email=:e AND id_employe<>:id");
            $chk->execute([':e' => $email, ':id' => $id_emp]);
            if ($chk->fetch()) json_out(['success'=>false,'error'=>'email_already_used']);

            $st = db()->prepare(
                "UPDATE Employes
                 SET prenom=:prenom, nom=:nom, email=:email, poste=:poste,
                     photo=:photo, id_service=:svc, id_localisation=:loc
                 WHERE id_employe=:id"
            );
            $st->execute([
                ':prenom' => $prenom, ':nom' => $nom, ':email' => $email,
                ':poste'  => $poste,  ':photo' => $photo ?: null,
                ':svc'    => $id_svc, ':loc'  => $id_localisation, ':id' => $id_emp,
            ]);
        }

        $numeros = $_POST['numeros'] ?? [];
        db()->prepare("DELETE FROM Numeros WHERE id_employe=:id")->execute([':id'=>$id_emp]);

        foreach ($numeros as $n) {
            $type   = in_array($n['type']??'', ['fixe','mobile','whatsapp','poste']) ? $n['type'] : 'mobile';
            $numero = clean($n['numero'] ?? '');
            $princ  = ($n['principal'] ?? '0') === '1' ? 1 : 0;
            if (!$numero) continue;
            $stN = db()->prepare(
                "INSERT INTO Numeros (id_employe, type_numero, numero, principal)
                 VALUES (:id, :type, :num, :princ)"
            );
            $stN->execute([':id'=>$id_emp,':type'=>$type,':num'=>$numero,':princ'=>$princ]);
        }

        $logAction = $action === 'add_employee' ? 'ADD_EMPLOYE' : 'EDIT_EMPLOYE';
        log_act($logAction, 'Employes', $id_emp);

        json_out(['success' => true, 'id_employe' => $id_emp]);
    }

    // ── add_user ──────────────────────────────────────────────────────────────
    if ($action === 'add_user') {
        require_admin();

        $id_employe_lie = (int)($_POST['id_employe_lie'] ?? 0);
        $nom            = clean($_POST['nom']    ?? '');
        $prenom         = clean($_POST['prenom'] ?? '');
        $login          = strtolower(trim($_POST['login'] ?? ''));
        $pwd            = $_POST['mot_de_passe'] ?? '';
        $pwd_conf       = $_POST['mot_de_passe_conf'] ?? '';
        $id_role        = (int)($_POST['id_role'] ?? 0) ?: null;

        if (!$id_employe_lie || !$nom || !$prenom || !$login || !$id_role)
            json_out(['success'=>false,'error'=>'missing_fields']);
        if (!$pwd || strlen($pwd) < 8)
            json_out(['success'=>false,'error'=>'missing_fields']);
        if ($pwd !== $pwd_conf)
            json_out(['success'=>false,'error'=>'missing_fields']);

        $chkEmp = db()->prepare("SELECT id_employe FROM Employes WHERE id_employe=:id");
        $chkEmp->execute([':id'=>$id_employe_lie]);
        if (!$chkEmp->fetch()) json_out(['success'=>false,'error'=>'invalid_employee']);

        $chkDup = db()->prepare(
            "SELECT id_utilisateur FROM Utilisateurs WHERE id_employe_lie=:id"
        );
        $chkDup->execute([':id'=>$id_employe_lie]);
        if ($chkDup->fetch()) json_out(['success'=>false,'error'=>'employee_has_account']);

        $chkLogin = db()->prepare("SELECT id_utilisateur FROM Utilisateurs WHERE login=:l");
        $chkLogin->execute([':l'=>$login]);
        if ($chkLogin->fetch()) json_out(['success'=>false,'error'=>'login_already_used']);

        $chkRole = db()->prepare("SELECT id_role FROM Roles WHERE id_role=:id");
        $chkRole->execute([':id'=>$id_role]);
        if (!$chkRole->fetch()) json_out(['success'=>false,'error'=>'invalid_role']);

        $hash = password_hash($pwd, PASSWORD_BCRYPT);

        $st = db()->prepare(
            "INSERT INTO Utilisateurs (nom, prenom, login, mot_de_passe, id_role, id_employe_lie)
             VALUES (:nom, :prenom, :login, :pwd, :role, :emp)"
        );
        $st->execute([
            ':nom'    => $nom,
            ':prenom' => $prenom,
            ':login'  => $login,
            ':pwd'    => $hash,
            ':role'   => $id_role,
            ':emp'    => $id_employe_lie,
        ]);
        $id_user = (int)db()->lastInsertId();

        log_act('ADD_USER', 'Utilisateurs', $id_user);

        json_out(['success' => true, 'id_utilisateur' => $id_user]);
    }

    // ── edit_user ─────────────────────────────────────────────────────────────
    if ($action === 'edit_user') {
        require_admin();

        $id_user  = (int)($_POST['id_utilisateur'] ?? 0);
        $nom      = clean($_POST['nom']    ?? '');
        $prenom   = clean($_POST['prenom'] ?? '');
        $login    = strtolower(trim($_POST['login'] ?? ''));
        $pwd      = $_POST['mot_de_passe'] ?? '';
        $pwd_conf = $_POST['mot_de_passe_conf'] ?? '';
        $id_role  = (int)($_POST['id_role'] ?? 0) ?: null;

        if (!$id_user || !$nom || !$prenom || !$login || !$id_role)
            json_out(['success'=>false,'error'=>'missing_fields']);

        $chkLogin = db()->prepare(
            "SELECT id_utilisateur FROM Utilisateurs WHERE login=:l AND id_utilisateur<>:id"
        );
        $chkLogin->execute([':l'=>$login,':id'=>$id_user]);
        if ($chkLogin->fetch()) json_out(['success'=>false,'error'=>'login_already_used']);

        if ($pwd) {
            if (strlen($pwd) < 8) json_out(['success'=>false,'error'=>'missing_fields']);
            if ($pwd !== $pwd_conf) json_out(['success'=>false,'error'=>'missing_fields']);
            $hash = password_hash($pwd, PASSWORD_BCRYPT);
            $st = db()->prepare(
                "UPDATE Utilisateurs
                 SET nom=:nom,prenom=:prenom,login=:login,mot_de_passe=:pwd,id_role=:role
                 WHERE id_utilisateur=:id"
            );
            $st->execute([':nom'=>$nom,':prenom'=>$prenom,':login'=>$login,
                          ':pwd'=>$hash,':role'=>$id_role,':id'=>$id_user]);
        } else {
            $st = db()->prepare(
                "UPDATE Utilisateurs
                 SET nom=:nom,prenom=:prenom,login=:login,id_role=:role
                 WHERE id_utilisateur=:id"
            );
            $st->execute([':nom'=>$nom,':prenom'=>$prenom,':login'=>$login,
                          ':role'=>$id_role,':id'=>$id_user]);
        }

        log_act('EDIT_USER', 'Utilisateurs', $id_user);
        json_out(['success' => true, 'id_utilisateur' => $id_user]);
    }

    // ── approve_request / reject_request ──────────────────────────────────────
    if (in_array($action, ['approve_request','reject_request'])) {
        require_admin();
        $id_demande = (int)($_POST['id_demande'] ?? 0);
        $statut     = $action === 'approve_request' ? 'valide' : 'refuse';
        $st = db()->prepare(
            "UPDATE Demandes_Modification
             SET statut=:s, id_admin=:a, date_validation=NOW()
             WHERE id_demande=:id AND statut='en_attente'"
        );
        $st->execute([':s'=>$statut,':a'=>current_user_id(),':id'=>$id_demande]);
        if ($st->rowCount() === 0) json_out(['success'=>false,'error'=>'not_found']);
        $logAct = $action === 'approve_request' ? 'DEMANDE_VALIDE' : 'DEMANDE_REFUSE';
        log_act($logAct, 'Employes', $id_demande);
        json_out(['success' => true]);
    }

    // ── delete_employee ───────────────────────────────────────────────────────
    if ($action === 'delete_employee') {
        require_admin();
        $id = (int)($_POST['id_employe'] ?? 0);
        if (!$id) json_out(['success'=>false,'error'=>'invalid_id']);
        db()->prepare("DELETE FROM Numeros WHERE id_employe=:id")->execute([':id'=>$id]);
        $st = db()->prepare("DELETE FROM Employes WHERE id_employe=:id");
        $st->execute([':id'=>$id]);
        if ($st->rowCount() === 0) json_out(['success'=>false,'error'=>'not_found']);
        log_act('DELETE_EMPLOYE', 'Employes', $id);
        json_out(['success' => true]);
    }

    // ── add_favorite ──────────────────────────────────────────────────────────
    if ($action === 'add_favorite') {
        $id_employe = (int)($_POST['id_employe'] ?? 0);
        if (!$id_employe) json_out(['success'=>false,'error'=>'invalid_id']);
        $uid = current_user_id();
        // Vérifier si déjà en favori
        $chk = db()->prepare(
            "SELECT id FROM Favoris WHERE id_utilisateur=:u AND id_employe=:e"
        );
        $chk->execute([':u'=>$uid,':e'=>$id_employe]);
        if ($chk->fetch()) {
            db()->prepare("DELETE FROM Favoris WHERE id_utilisateur=:u AND id_employe=:e")
                ->execute([':u'=>$uid,':e'=>$id_employe]);
            json_out(['success'=>true,'action'=>'removed']);
        } else {
            db()->prepare("INSERT INTO Favoris (id_utilisateur, id_employe) VALUES (:u,:e)")
                ->execute([':u'=>$uid,':e'=>$id_employe]);
            json_out(['success'=>true,'action'=>'added']);
        }
    }

    json_out(['success' => false, 'error' => 'unknown_action']);
}

json_out(['success' => false, 'error' => 'method_not_allowed']);