<?php
// C:\xampp\htdocs\ClickPharma\action_reservation.php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_pharmacie'])) {
    echo json_encode(['success' => false, 'message' => 'Session expirée.']);
    exit();
}

$id_session = $_SESSION['id_pharmacie'];

// Récupération des identifiants de la pharmacie
$stmt = $pdo->prepare("SELECT id, id_utilisateur FROM pharmacies WHERE id = ? OR id_utilisateur = ?");
$stmt->execute([$id_session, $id_session]);
$pharma = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pharma) {
    echo json_encode(['success' => false, 'message' => 'Pharmacie introuvable.']);
    exit();
}

$id_reel = $pharma['id'];
$id_user = $pharma['id_utilisateur'];

if (!isset($_POST['id_reservation'], $_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
    exit();
}

$id_reservation = intval($_POST['id_reservation']);
$action = $_POST['action'];

// ALIGNEMENT DES VALEURS EN BASE : 'valide' ou 'annule'
$nouveau_statut = ($action === 'accepter') ? 'valide' : 'annule';
$label_affichage = ($action === 'accepter') ? 'Prêt / Validé' : 'Refusé / Annulé';

try {
    // Mise à jour du statut et réinitialisation de patient_a_vu à 0
    $query = "UPDATE reservations 
              SET statut = ?, patient_a_vu = 0 
              WHERE id = ? AND (id_pharmacie = ? OR id_pharmacie = ?)";
    $stmt_update = $pdo->prepare($query);
    $stmt_update->execute([$nouveau_statut, $id_reservation, $id_reel, $id_user]);

    echo json_encode([
        'success' => true,
        'nouveau_statut' => $label_affichage,
        'message' => 'Statut mis à jour avec succès.'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur BDD : ' . $e->getMessage()]);
}
exit();