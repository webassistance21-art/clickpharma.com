<?php
session_start();
require_once 'config/db.php';

// Sécurité : Vérifier l'accès
if (!isset($_SESSION['id_pharmacie']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login_pharmacie.php');
    exit();
}

// On récupère le bon ID de pharmacie (comme fait sur le dashboard pour la cohérence)
$id_session = $_SESSION['id_pharmacie'];
$stmt = $pdo->prepare("SELECT id FROM pharmacies WHERE id = ? OR id_utilisateur = ?");
$stmt->execute([$id_session, $id_session]);
$pharma = $stmt->fetch();
$id_pharma_reel = $pharma ? $pharma['id'] : $id_session;

// Récupération et nettoyage des données soumises
$id_medicament = intval($_POST['id_medicament'] ?? 0);
$quantite = intval($_POST['quantite'] ?? 0);
$prix = floatval($_POST['prix'] ?? 0);

if ($id_medicament > 0 && $quantite >= 0 && $prix >= 0) {

    // ÉTAPE FACULTATIVE MAIS RECOMMANDÉE : Vérifier si ce médicament existe déjà dans le stock de cette pharmacie
    $check = $pdo->prepare("SELECT id_stock, quantite FROM stocks WHERE id_pharmacie = ? AND id_medicament = ?");
    $check->execute([$id_pharma_reel, $id_medicament]);
    $existing_stock = $check->fetch();

    if ($existing_stock) {
        // Si le médicament est déjà dans le stock, on additionne la nouvelle quantité et on met à jour le prix
        $nouvelle_qte = $existing_stock['quantite'] + $quantite;
        $update = $pdo->prepare("UPDATE stocks SET quantite = ?, prix = ? WHERE id_stock = ?");
        $update->execute([$nouvelle_qte, $prix, $existing_stock['id_stock']]);
    } else {
        // Sinon, on crée une toute nouvelle ligne dans la table stocks
        $insert = $pdo->prepare("INSERT INTO stocks (id_pharmacie, id_medicament, quantite, prix) VALUES (?, ?, ?, ?)");
        $insert->execute([$id_pharma_reel, $id_medicament, $quantite, $prix]);
    }

    // Redirection avec un message de succès
    header('Location: ajouter_medicament.php?success=1');
    exit();

} else {
    // Redirection avec un message d'erreur si les champs sont incorrects
    header('Location: ajouter_medicament.php?error=Veuillez remplir correctement tous les champs.');
    exit();
}