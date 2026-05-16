<?php

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "camrail_annuaire"
);

if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Encodage UTF-8
$conn->set_charset("utf8mb4");

?>