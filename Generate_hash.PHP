<?php
/**
 * Camrail — Utilitaire de génération de hash bcrypt
 *
 * USAGE EN LIGNE DE COMMANDE :
 *   php generate_hash.php monmotdepasse
 *
 * USAGE VIA NAVIGATEUR (à supprimer en production) :
      http://localhost/camrail_annuaire/generate_hash.php
 *
 * ⚠️  SUPPRIMER CE FICHIER EN PRODUCTION ⚠️
 */

// CLI
if (php_sapi_name() === 'cli') {
    $pwd = $argv[1] ?? '';
    if (!$pwd) {
        echo "Usage: php generate_hash.php <mot_de_passe>\n";
        exit(1);
    }
    echo password_hash($pwd, PASSWORD_DEFAULT) . "\n";
    exit(0);
}

// Navigateur (dev uniquement)
$hash   = '';
$error  = '';
$input  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST['password'] ?? '';
    if (strlen($input) < 6) {
        $error = 'Le mot de passe doit faire au moins 6 caractères.';
    } else {
        $hash = password_hash($input, PASSWORD_DEFAULT);
    }
}
?><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <title>Générateur de hash — Camrail</title>
  <style>
    body { font-family: monospace; max-width: 600px; margin: 60px auto; padding: 0 20px; background: #f9fafb; }
    h1 { font-size: 20px; color: #1f2937; }
    .warning { background: #fef3c7; border: 1px solid #f59e0b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; }
    label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #374151; }
    input[type=text], input[type=password] { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-family: monospace; margin-bottom: 12px; }
    button { background: #b91c1c; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; cursor: pointer; }
    .result { background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 16px; margin-top: 20px; word-break: break-all; font-size: 13px; }
    .result code { display: block; margin-top: 8px; color: #15803d; font-size: 12px; line-height: 1.6; }
    .error { background: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; padding: 12px 16px; color: #dc2626; margin-top: 12px; }
    .sql-snippet { background: #1e293b; color: #e2e8f0; padding: 12px 16px; border-radius: 8px; margin-top: 12px; font-size: 12px; overflow-x: auto; }
  </style>
</head>
<body>
  <h1>🔐 Générateur de hash bcrypt — Camrail</h1>
  <div class="warning">⚠️ <strong>Outil de développement uniquement.</strong> Supprimez ce fichier avant la mise en production.</div>

  <form method="POST">
    <label for="password">Mot de passe à hasher</label>
    <input type="text" id="password" name="password" value="<?= htmlspecialchars($input) ?>" placeholder="Entrez un mot de passe…" autocomplete="off"/>
    <button type="submit">Générer le hash</button>
  </form>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($hash): ?>
    <div class="result">
      <strong>✓ Hash généré pour « <?= htmlspecialchars($input) ?> » :</strong>
      <code><?= htmlspecialchars($hash) ?></code>
      <div class="sql-snippet">
        INSERT INTO Utilisateurs (nom, prenom, login, mot_de_passe, id_role)<br>
        VALUES ('NOM', 'Prenom', 'CR-XXXXXX', '<?= htmlspecialchars($hash) ?>', 2);
      </div>
      <p style="margin-top:10px;font-size:12px;color:#6b7280">
        Vérification : <code style="color:#374151"><?= password_verify($input, $hash) ? '✓ Hash valide' : '✗ Erreur' ?></code>
      </p>
    </div>
  <?php endif; ?>

  <hr style="margin:30px 0;border-color:#e5e7eb"/>
  <h2 style="font-size:16px">Comptes de démo</h2>
  <?php
  $demo = [
    ['CR-000001','admin123','admin'],
    ['CR-000002','user123','user'],
    ['CR-000003','sarah456','user'],
  ];
  foreach ($demo as [$login, $pwd, $role]) {
      $h = password_hash($pwd, PASSWORD_DEFAULT);
      echo "<p style='font-size:12px;margin-bottom:6px'><strong>$login</strong> / $pwd ($role)</p>";
      echo "<div class='sql-snippet' style='margin-bottom:12px'>UPDATE Utilisateurs SET mot_de_passe = '$h' WHERE login = '$login';</div>";
  }
  ?>
</body>
</html>