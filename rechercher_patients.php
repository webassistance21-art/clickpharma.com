<?php
require_once 'config/setup.php';
require_once 'config/db.php';

// Blocage de sécurité
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'medecin') {
    echo json_encode([]);
    exit();
}

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT id_patient, nom, prenom, nss, poids, date_naissance FROM patients WHERE nom LIKE ? OR prenom LIKE ? OR nss LIKE ? LIMIT 8");
    $term = "%$search%";
    $stmt->execute([$term, $term, $term]);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("SELECT id_patient, nom, prenom, nss, poids, date_naissance FROM patients ORDER BY id_patient DESC LIMIT 5");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

foreach ($patients as &$p) {
    $p['age'] = !empty($p['date_naissance']) ? (new DateTime())->diff(new DateTime($p['date_naissance']))->y . " ans" : "N/A";
}

header('Content-Type: application/json');
echo json_encode($patients);