<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['id_pharmacie']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login_pharmacie.php');
    exit();
}

$id_stock = intval($_POST['id_stock'] ?? 0);
$action = $_POST['action'] ?? '';
$quantite = intval($_POST['quantite'] ?? 0);
$prix = floatval($_POST['prix'] ?? 0);

if ($id_stock > 0) {
    if ($action === 'delete') {
        // Suppression du produit du stock
        $stmt = $pdo->prepare("DELETE FROM stocks WHERE id_stock = ?");
        $stmt->execute([$id_stock]);
        header('Location: space_pharmacien.php?msg=deleted');
        exit();
    } elseif ($action === 'update' && $quantite >= 0 && $prix >= 0) {
        // Mise à jour de la quantité et du prix
        $stmt = $pdo->prepare("UPDATE stocks SET quantite = ?, prix = ? WHERE id_stock = ?");
        $stmt->execute([$quantite, $prix, $id_stock]);
        header('Location: space_pharmacien.php?msg=updated');
        exit();
    }
}

header('Location: space_pharmacien.php');
exit();