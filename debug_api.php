<?php
/**
 * debug_api.php — Camrail Annuaire
 * Déposez ce fichier à la RACINE de votre projet (même niveau que admin.php)
 * Ouvrez : localhost/camrail_annuaire/debug_api.php
 * SUPPRIMEZ CE FICHIER après le diagnostic !
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<style>
body{font-family:monospace;padding:20px;background:#f9fafb}
h2{color:#C0392B;margin-top:30px}
.ok{color:#16a34a;font-weight:bold}
.err{color:#dc2626;font-weight:bold}
.warn{color:#d97706;font-weight:bold}
table{border-collapse:collapse;width:100%;margin-top:8px}
td,th{border:1px solid #e5e7eb;padding:6px 10px;font-size:13px;text-align:left}
th{background:#f3f4f6}
pre{background:#1e293b;color:#e2e8f0;padding:14px;border-radius:8px;overflow-x:auto;font-size:12px}
</style>";

echo "<h1>🔧 Diagnostic Camrail API</h1>";
echo "<p style='color:#6b7280'>Supprimez ce fichier après utilisation.</p>";

// ── 1. Connexion DB ──────────────────────────────────────────────────────────
echo "<h2>1. Connexion base de données</h2>";
try {
    require_once __DIR__ . '/backend/db.php';
    echo "<span class='ok'>✓ Connexion PDO OK</span><br>";

    // Version MySQL
    $ver = $pdo->query("SELECT VERSION()")->fetchColumn();
    echo "Version MySQL : <strong>$ver</strong><br>";

    // Base active
    $db = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "Base active : <strong>$db</strong><br>";
} catch (Throwable $e) {
    echo "<span class='err'>✗ Erreur connexion : " . htmlspecialchars($e->getMessage()) . "</span>";
    die();
}

// ── 2. Tables présentes ──────────────────────────────────────────────────────
echo "<h2>2. Tables présentes</h2>";
$tables_attendues = ['Employes','Services','Numeros','Utilisateurs','Roles',
                     'Localisations','Historique','Demandes_Modification',
                     'Favoris','Permissions','Role_Permission'];
$tables_existantes = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "<table><tr><th>Table</th><th>Statut</th><th>Nb lignes</th></tr>";
foreach ($tables_attendues as $t) {
    if (in_array($t, $tables_existantes)) {
        try {
            $nb = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            echo "<tr><td>$t</td><td class='ok'>✓ existe</td><td>$nb</td></tr>";
        } catch(Throwable $e) {
            echo "<tr><td>$t</td><td class='err'>✗ erreur lecture : ".htmlspecialchars($e->getMessage())."</td><td>—</td></tr>";
        }
    } else {
        echo "<tr><td>$t</td><td class='err'>✗ MANQUANTE</td><td>—</td></tr>";
    }
}
echo "</table>";

// ── 3. Colonnes critiques ────────────────────────────────────────────────────
echo "<h2>3. Colonnes critiques</h2>";
$cols_check = [
    'Employes'     => ['id_employe','nom','prenom','email','poste','photo','id_service','id_localisation'],
    'Utilisateurs' => ['id_utilisateur','nom','prenom','login','mot_de_passe','id_role','id_employe_lie'],
    'Localisations'=> ['id_localisation','nom_localisation','description','ville','site','batiment','etage','bureau'],
];
foreach ($cols_check as $table => $cols) {
    echo "<strong>$table</strong><br>";
    if (!in_array($table, $tables_existantes)) { echo "<span class='err'>Table absente</span><br>"; continue; }
    try {
        $existing = $pdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($cols as $col) {
            if (in_array($col, $existing))
                echo "&nbsp;&nbsp;<span class='ok'>✓ $col</span><br>";
            else
                echo "&nbsp;&nbsp;<span class='err'>✗ $col MANQUANT</span> &larr; exécutez migration.sql<br>";
        }
    } catch(Throwable $e) {
        echo "<span class='err'>".htmlspecialchars($e->getMessage())."</span><br>";
    }
}

// ── 4. Test requête stats ────────────────────────────────────────────────────
echo "<h2>4. Requête stats</h2>";
try {
    $total_employes = (int)$pdo->query("SELECT COUNT(*) FROM Employes")->fetchColumn();
    $total_services = (int)$pdo->query("SELECT COUNT(*) FROM Services")->fetchColumn();
    $total_users    = (int)$pdo->query("SELECT COUNT(*) FROM Utilisateurs")->fetchColumn();
    $en_attente     = (int)$pdo->query("SELECT COUNT(*) FROM Demandes_Modification WHERE statut='en_attente'")->fetchColumn();
    echo "<span class='ok'>✓ OK</span> — Employés: <strong>$total_employes</strong> | Services: <strong>$total_services</strong> | Utilisateurs: <strong>$total_users</strong> | En attente: <strong>$en_attente</strong><br>";
} catch(Throwable $e) {
    echo "<span class='err'>✗ ".htmlspecialchars($e->getMessage())."</span><br>";
}

// ── 5. Test requête employees_without_account ────────────────────────────────
echo "<h2>5. Requête employees_without_account</h2>";
try {
    $rows = $pdo->query(
        "SELECT e.id_employe, e.nom, e.prenom, e.poste, s.nom_service
         FROM Employes e
         LEFT JOIN Services s ON s.id_service = e.id_service
         WHERE e.id_employe NOT IN (
             SELECT id_employe_lie FROM Utilisateurs
             WHERE id_employe_lie IS NOT NULL
         )
         ORDER BY e.nom, e.prenom"
    )->fetchAll(PDO::FETCH_ASSOC);
    echo "<span class='ok'>✓ OK</span> — ".count($rows)." employé(s) sans compte<br>";
} catch(Throwable $e) {
    echo "<span class='err'>✗ ".htmlspecialchars($e->getMessage())."</span><br>";
    echo "<div class='warn'>→ Probablement la colonne <code>id_employe_lie</code> manquante dans Utilisateurs. Exécutez migration.sql</div>";
}

// ── 6. Test requête localisations ────────────────────────────────────────────
echo "<h2>6. Requête localisations</h2>";
try {
    $rows = $pdo->query(
        "SELECT id_localisation, nom_localisation, ville FROM Localisations ORDER BY nom_localisation"
    )->fetchAll(PDO::FETCH_ASSOC);
    echo "<span class='ok'>✓ OK</span> — ".count($rows)." localisation(s)<br>";
} catch(Throwable $e) {
    echo "<span class='err'>✗ ".htmlspecialchars($e->getMessage())."</span><br>";
}

// ── 7. Test requête validation_queue ────────────────────────────────────────
echo "<h2>7. Requête validation_queue</h2>";
try {
    $rows = $pdo->query(
        "SELECT dm.id_demande, dm.statut, u.login
         FROM Demandes_Modification dm
         JOIN Utilisateurs u ON u.id_utilisateur = dm.id_utilisateur
         WHERE dm.statut = 'en_attente' LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);
    echo "<span class='ok'>✓ OK</span> — ".count($rows)." demande(s) en attente<br>";
} catch(Throwable $e) {
    echo "<span class='err'>✗ ".htmlspecialchars($e->getMessage())."</span><br>";
}

// ── 8. Test API directement ──────────────────────────────────────────────────
echo "<h2>8. Appel direct api.php?action=stats</h2>";
echo "<p style='color:#6b7280;font-size:13px'>Cliquez pour tester depuis le navigateur :</p>";
$base = rtrim(dirname($_SERVER['PHP_SELF']), '/');
echo "<a href='{$base}/backend/api.php?action=stats' target='_blank' 
        style='display:inline-block;background:#C0392B;color:#fff;padding:8px 16px;border-radius:6px;text-decoration:none;font-size:13px'>
    Ouvrir api.php?action=stats dans un nouvel onglet →
</a><br><br>";

// ── 9. Session ───────────────────────────────────────────────────────────────
echo "<h2>9. Session</h2>";
session_start();
if (empty($_SESSION)) {
    echo "<span class='warn'>⚠ Session vide — vous n'êtes pas connecté ou la session a expiré.</span><br>";
    echo "<div class='warn'>→ Les actions admin nécessitent une session active (require_admin()).<br>
    Si vous n'êtes pas connecté, toutes les requêtes retournent <code>{success:false, error:'forbidden'}</code> → les données n'apparaissent pas.</div>";
} else {
    echo "<span class='ok'>✓ Session active</span><br>";
    echo "<table><tr><th>Clé</th><th>Valeur</th></tr>";
    foreach ($_SESSION as $k => $v) {
        $display = is_array($v) ? json_encode($v) : htmlspecialchars((string)$v);
        echo "<tr><td>$k</td><td>$display</td></tr>";
    }
    echo "</table>";
    // Vérifier rôle admin
    $role = strtolower($_SESSION['role'] ?? '');
    if ($role === 'administrateur') {
        echo "<br><span class='ok'>✓ Rôle administrateur détecté</span><br>";
    } else {
        echo "<br><span class='err'>✗ Rôle actuel : '<strong>" . htmlspecialchars($role ?: '(vide)') . "</strong>'</span><br>";
        echo "<div class='warn'>→ require_admin() va rejeter toutes les requêtes. 
        Vérifiez que <code>\$_SESSION['role']</code> vaut exactement <code>'administrateur'</code> (minuscules).</div>";
    }
}

echo "<hr style='margin-top:40px'><p style='color:#9ca3af;font-size:12px'>⚠ Supprimez debug_api.php après utilisation.</p>";