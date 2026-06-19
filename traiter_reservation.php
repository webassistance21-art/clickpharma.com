<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['id_pharmacie'])) {
    header('Location: login_pharmacie.php');
    exit();
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id_reservation = intval($_GET['id']);
    $action = $_GET['action'];
    $id_pharmacie = $_SESSION['id_pharmacie'];

    // Mappage de l'action vers le statut cible
    $nouveau_statut = 'en_attente';
    if ($action === 'valider') {
        $nouveau_statut = 'Validée';
    } elseif ($action === 'annuler') {
        $nouveau_statut = 'Annulée';
    }

    try {
        // Sécurité stricte : WHERE id_pharmacie = ? empêche le contournement d'ID
        $stmt = $pdo->prepare("UPDATE reservations SET statut = ?, patient_a_vu = 0 WHERE id = ? AND id_pharmacie = ?");
        $stmt->execute([$nouveau_statut, $id_reservation, $id_pharmacie]);

        header('Location: dashboard_pharmacie.php?success=1');
        exit();
    } catch (PDOException $e) {
        die("Erreur de traitement SQL : " . $e->getMessage());
    }
} else {
    header('Location: dashboard_pharmacie.php');
    exit();
}