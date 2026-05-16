<?php
/**
 * Camrail Directory — Export
 * GET /backend/export.php?format=csv|pdf
 */
$require_admin = true;
require_once __DIR__ . '/session_guard.php';

$format = $_GET['format'] ?? 'csv';

// Récupérer tous les employés
$stmt = db()->query("
    SELECT e.nom, e.prenom, e.poste, e.email,
           s.nom_service,
           n.numero AS telephone,
           e.date_creation
    FROM Employes e
    LEFT JOIN Services s ON s.id_service = e.id_service
    LEFT JOIN Numeros n  ON n.id_employe = e.id_employe AND n.principal = 1
    ORDER BY e.nom, e.prenom
");
$rows = $stmt->fetchAll();

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="camrail_employes_' . date('Ymd') . '.csv"');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 pour Excel

    fputcsv($out, ['Nom', 'Prénom', 'Poste', 'Email', 'Département', 'Téléphone', 'Date création'], ';');
    foreach ($rows as $row) {
        fputcsv($out, [
            $row['nom'],
            $row['prenom'],
            $row['poste'],
            $row['email'],
            $row['nom_service'],
            $row['telephone'],
            $row['date_creation'],
        ], ';');
    }
    fclose($out);
    exit;
}

// PDF basique (HTML imprimable) — en prod utiliser TCPDF/Dompdf
if ($format === 'pdf') {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"/>
    <style>body{font-family:sans-serif;font-size:12px}
    table{border-collapse:collapse;width:100%}
    th,td{border:1px solid #ccc;padding:6px 8px;text-align:left}
    th{background:#e0e0e0}h1{font-size:18px}
    @media print{button{display:none}}</style></head><body>
    <h1>Répertoire Camrail — Export ' . date('d/m/Y') . '</h1>
    <button onclick="window.print()">🖨 Imprimer / Sauvegarder en PDF</button>
    <br><br>
    <table><thead><tr>
    <th>Nom</th><th>Prénom</th><th>Poste</th><th>Département</th><th>Email</th><th>Téléphone</th>
    </tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr><td>' . htmlspecialchars($row['nom']) . '</td>'
           . '<td>' . htmlspecialchars($row['prenom']) . '</td>'
           . '<td>' . htmlspecialchars($row['poste']) . '</td>'
           . '<td>' . htmlspecialchars($row['nom_service']) . '</td>'
           . '<td>' . htmlspecialchars($row['email']) . '</td>'
           . '<td>' . htmlspecialchars($row['telephone']) . '</td></tr>';
    }
    echo '</tbody></table></body></html>';
    exit;
}

json_err('invalid_format');