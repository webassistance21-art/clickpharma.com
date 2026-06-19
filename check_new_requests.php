<?php
// C:\xampp\htdocs\ClickPharma\check_new_requests.php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_pharmacie'])) {
    echo json_encode(['count' => 0, 'requests' => []]);
    exit();
}

$id_session = $_SESSION['id_pharmacie'];

try {
    // 1. Récupérer le vrai ID de la pharmacie (comme sur le dashboard)
    $stmt = $pdo->prepare("SELECT id FROM pharmacies WHERE id = ? OR id_utilisateur = ?");
    $stmt->execute([$id_session, $id_session]);
    $pharma = $stmt->fetch();
    
    $id_pharma_reel = $pharma ? $pharma['id'] : $id_session;

    // 2. On cherche les réservations en attente avec le bon ID réel
    // On accepte 'en_attente', vide '', ou NULL pour être plus souple au test
    $query = "SELECT id, nom_client, nss, medicament_demande, date_commande 
              FROM reservations 
              WHERE id_pharmacie = ? 
              AND (statut = 'en_attente' OR statut = '' OR statut IS NULL)
              ORDER BY date_commande DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_pharma_reel]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'count' => count($requests),
        'requests' => $requests
    ]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0, 'error' => $e->getMessage()]);
}