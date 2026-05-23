<?php
/**
 * test_simple.php — Camrail
 * Déposez à la racine, ouvrez dans le navigateur
 * SUPPRIMEZ après diagnostic
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Test Simple</title>
<style>
  body{font-family:monospace;padding:20px;background:#f9fafb}
  .ok{color:green;font-weight:bold} .err{color:red;font-weight:bold} .warn{color:orange;font-weight:bold}
  pre{background:#1e293b;color:#e2e8f0;padding:12px;border-radius:6px;overflow-x:auto;font-size:12px}
  h2{margin-top:24px;border-bottom:1px solid #e5e7eb;padding-bottom:4px}
  .box{background:#fff;border:1px solid #e5e7eb;padding:12px 16px;border-radius:6px;margin-top:8px}
  a.btn{display:inline-block;background:#C0392B;color:#fff;padding:6px 14px;border-radius:6px;text-decoration:none;margin:4px 4px 4px 0;font-size:13px}
</style>
</head>
<body>
<h1>🔧 Test Simple — Camrail</h1>

<?php

// ── 1. db.php ────────────────────────────────────────────────────────────────
echo "<h2>1. Chargement db.php</h2><div class='box'>";
$db_path = __DIR__ . '/backend/db.php';
if (!file_exists($db_path)) {
    die("<span class='err'>✗ backend/db.php introuvable !</span></div>");
}
try {
    require_once $db_path;
    $pdo = db();
    echo "<span class='ok'>✓ db() fonctionne</span><br>";
    echo "MySQL : <b>" . $pdo->query("SELECT VERSION()")->fetchColumn() . "</b><br>";
    echo "Base  : <b>" . $pdo->query("SELECT DATABASE()")->fetchColumn() . "</b>";
} catch(Throwable $e) {
    die("<span class='err'>✗ Erreur db() : " . htmlspecialchars($e->getMessage()) . "</span></div>");
}
echo "</div>";

// ── 2. Requêtes une par une ───────────────────────────────────────────────────
echo "<h2>2. Requêtes</h2><div class='box'>";

$tests = [
    'COUNT Employes'     => "SELECT COUNT(*) FROM Employes",
    'COUNT Services'     => "SELECT COUNT(*) FROM Services",
    'COUNT Utilisateurs' => "SELECT COUNT(*) FROM Utilisateurs",
    'COUNT Historique'   => "SELECT COUNT(*) FROM Historique",
    'COUNT Demandes_Modification' => "SELECT COUNT(*) FROM Demandes_Modification WHERE statut='en_attente'",
    'Repartition'        => "SELECT s.nom_service, COUNT(e.id_employe) as total FROM Services s LEFT JOIN Employes e ON e.id_service=s.id_service GROUP BY s.id_service",
    'Localisations'      => "SELECT COUNT(*) FROM Localisations",
    'Roles'              => "SELECT id_role, nom_role FROM Roles",
    'id_localisation in Employes'  => "SELECT id_localisation FROM Employes LIMIT 1",
    'id_employe_lie in Utilisateurs' => "SELECT id_employe_lie FROM Utilisateurs LIMIT 1",
    'employees_without_account'    => "SELECT e.id_employe FROM Employes e WHERE e.id_employe NOT IN (SELECT id_employe_lie FROM Utilisateurs WHERE id_employe_lie IS NOT NULL)",
    'validation_queue join'        => "SELECT dm.id_demande FROM Demandes_Modification dm JOIN Utilisateurs u ON u.id_utilisateur=dm.id_utilisateur WHERE dm.statut='en_attente' LIMIT 1",
    'activity_log join'            => "SELECT h.id_historique, CONCAT(u.prenom,' ',u.nom) AS admin_name FROM Historique h LEFT JOIN Utilisateurs u ON u.id_utilisateur=h.id_utilisateur ORDER BY h.date_heure DESC LIMIT 1",
];

foreach ($tests as $label => $sql) {
    try {
        $res = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $count = count($res);
        // Pour les COUNT(*) afficher la valeur
        if ($count === 1 && isset($res[0]['COUNT(*)'])) {
            echo "<span class='ok'>✓</span> <b>$label</b> → <b>" . $res[0]['COUNT(*)'] . "</b><br>";
        } else {
            echo "<span class='ok'>✓</span> <b>$label</b> → $count ligne(s)<br>";
        }
    } catch(Throwable $e) {
        echo "<span class='err'>✗ $label</span> → " . htmlspecialchars($e->getMessage()) . "<br>";
    }
}
echo "</div>";

// ── 3. Session ────────────────────────────────────────────────────────────────
echo "<h2>3. Session</h2><div class='box'>";
session_start();
if (empty($_SESSION)) {
    echo "<span class='err'>✗ Session vide — non connecté</span><br>";
    echo "→ <a href='login.php' class='btn'>Aller sur login.php</a>";
} else {
    echo "<span class='ok'>✓ Session active</span><br>";
    foreach ($_SESSION as $k => $v) {
        echo "<b>$k</b> = " . htmlspecialchars(is_array($v) ? json_encode($v) : (string)$v) . "<br>";
    }
    $role = strtolower($_SESSION['role'] ?? '');
    echo "<br>";
    if ($role === 'administrateur') {
        echo "<span class='ok'>✓ Rôle administrateur OK</span>";
    } else {
        echo "<span class='err'>✗ Rôle = '$role' (attendu : 'administrateur')</span><br>";
        echo "<span class='warn'>→ require_admin() va bloquer toutes les requêtes AJAX</span>";
    }
}
echo "</div>";

// ── 4. Simuler action=stats SANS session_guard ───────────────────────────────
echo "<h2>4. Simulation action=stats (sans vérification session)</h2><div class='box'>";
try {
    $total_employes = (int)$pdo->query("SELECT COUNT(*) FROM Employes")->fetchColumn();
    $total_services = (int)$pdo->query("SELECT COUNT(*) FROM Services")->fetchColumn();
    $total_users    = (int)$pdo->query("SELECT COUNT(*) FROM Utilisateurs")->fetchColumn();
    $en_attente     = (int)$pdo->query("SELECT COUNT(*) FROM Demandes_Modification WHERE statut='en_attente'")->fetchColumn();
    $result = [
        'success'        => true,
        'total_employes' => $total_employes,
        'total_services' => $total_services,
        'total_users'    => $total_users,
        'en_attente'     => $en_attente,
    ];
    echo "<span class='ok'>✓ Données récupérées :</span><br>";
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
} catch(Throwable $e) {
    echo "<span class='err'>✗ " . htmlspecialchars($e->getMessage()) . "</span>";
}
echo "</div>";

// ── 5. Lire la réponse réelle de api.php ─────────────────────────────────────
echo "<h2>5. Réponse réelle de api.php</h2><div class='box'>";
echo "<p style='color:#6b7280;font-size:13px'>Ouvrez ces liens dans un onglet — si vous voyez <code>{success:false,error:\"forbidden\"}</code> c'est la session.</p>";
$base = rtrim(str_replace('\\','/',dirname($_SERVER['PHP_SELF'])),'/');
foreach (['stats','departments','roles','localisations'] as $act) {
    echo "<a class='btn' href='{$base}/backend/api.php?action=$act' target='_blank'>api.php?action=$act</a>";
}
echo "</div>";

// ── 6. session_guard.php ─────────────────────────────────────────────────────
echo "<h2>6. Contenu de session_guard.php</h2><div class='box'>";
$sg = __DIR__ . '/backend/session_guard.php';
if (file_exists($sg)) {
    $content = file_get_contents($sg);
    // masquer données sensibles
    $display = preg_replace("/'[^']{6,}'/", "'***'", $content);
    echo "<pre>" . htmlspecialchars($display) . "</pre>";
} else {
    echo "<span class='err'>✗ session_guard.php introuvable</span>";
}
echo "</div>";

echo "<hr style='margin-top:32px'><p style='color:#9ca3af;font-size:12px'>⚠ Supprimez test_simple.php après utilisation.</p>";
?>
</body>
</html>