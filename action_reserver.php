<?php
session_start();
require_once 'config/db.php';

// Protection de la page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header('Location: login_patient.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_pharmacie'], $_POST['id_ordonnance'])) {
    $id_pharmacie = intval($_POST['id_pharmacie']);
    $id_ordonnance = intval($_POST['id_ordonnance']);
    $id_patient = $_SESSION['id_patient'];

    try {
        // 1. Récupérer les informations de l'ordonnance et du patient jointes
        $stmt_ord = $pdo->prepare("SELECT o.contenu_medocs, p.nom, p.prenom, p.nss, p.telephone 
                                   FROM ordonnances o 
                                   JOIN patients p ON o.id_patient = p.id_patient 
                                   WHERE o.id_ordonnance = ? AND o.id_patient = ?");
        $stmt_ord->execute([$id_ordonnance, $id_patient]);
        $ordonnance = $stmt_ord->fetch();

        if (!$ordonnance) {
            header("Location: space_patient.php?error=ordonnance_introuvable");
            exit();
        }

        // 2. Extraire et concaténer les noms des médicaments depuis le JSON
        $medocs_liste = json_decode($ordonnance['contenu_medocs'], true);
        $noms_medocs = [];
        if (!empty($medocs_liste)) {
            foreach ($medocs_liste as $m) {
                if (!empty($m['nom_medicament'])) {
                    $noms_medocs[] = $m['nom_medicament'];
                }
            }
        }
        // Concaténation textuelle demandée : "Biafine, Augmentin"
        $medicaments_string = implode(', ', $noms_medocs); 
        
        $nom_complet_client = $ordonnance['prenom'] . ' ' . $ordonnance['nom'];

        // 3. Insérer la demande de réservation en statut 'en_attente'
        $query_ins = "INSERT INTO reservations (id_pharmacie, nom_client, nss, telephone_client, medicament_demande, statut, date_commande, patient_a_vu) 
                      VALUES (?, ?, ?, ?, ?, 'en_attente', NOW(), 0)";
        
        $stmt_ins = $pdo->prepare($query_ins);
        $stmt_ins->execute([
            $id_pharmacie,
            $nom_complet_client,
            $ordonnance['nss'],
            $ordonnance['telephone'],
            $medicaments_string
        ]);

        // Redirection vers l'espace patient avec succès
        header("Location: space_patient.php?reservation_success=1");
        exit();

    } catch (PDOException $e) {
        die("Erreur lors de l'enregistrement de la réservation : " . $e->getMessage());
    }
} else {
    header('Location: space_patient.php');
    exit();
}