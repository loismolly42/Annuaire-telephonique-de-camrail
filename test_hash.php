<?php
require_once 'backend/config.php';
$user = db()->query("SELECT login, mot_de_passe FROM Utilisateurs LIMIT 3")->fetchAll();
foreach($user as $u) {
    echo $u['login'] . ' → ' . password_verify('password', $u['mot_de_passe']) ? '✓ OK' : '✗ FAIL';
    echo '<br>';
}