<?php
// C:\xampp\htdocs\ClickPharma\action_reserver_ajax.php
session_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $bdd = new PDO('mysql:host=localhost;dbname=pharmalife_db;charset=utf8', 'root', '');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion BDD : ' . $e->getMessage()]);
    exit();
}

if (isset($_POST['id_pharmacie'], $_POST['medicaments'], $_POST['id_ordonnance'])) {
    $id_pharmacie = intval($_POST['id_pharmacie']);
    $medicaments_string = trim($_POST['medicaments']);
    $id_ordonnance = intval($_POST['id_ordonnance']);
    
    try {
        $stmt_ord = $bdd->prepare("SELECT p.nom, p.prenom, p.nss, p.telephone 
                                   FROM ordonnances o 
                                   JOIN patients p ON o.id_patient = p.id_patient 
                                   WHERE o.id_ordonnance = ?");
        $stmt_ord->execute([$id_ordonnance]);
        $patient = $stmt_ord->fetch(PDO::FETCH_ASSOC);

        if (!$patient) {
            echo json_encode(['success' => false, 'message' => 'Impossible de lier le patient à cette ordonnance.']);
            exit();
        }

        $nom_complet_client = $patient['prenom'] . ' ' . $patient['nom'];

        // Insertion avec statut initial strict 'en_attente'
        $query_ins = "INSERT INTO reservations (id_pharmacie, nom_client, nss, id_ordonnance, telephone_client, medicament_demande, statut, date_commande, patient_a_vu) 
                      VALUES (?, ?, ?, ?, ?, ?, 'en_attente', NOW(), 0)";
        
        $stmt_ins = $bdd->prepare($query_ins);
        $stmt_ins->execute([
            $id_pharmacie,
            $nom_complet_client,
            $patient['nss'],
            $id_ordonnance,
            $patient['telephone'],
            $medicaments_string
        ]);

        echo json_encode(['success' => true, 'message' => 'Votre demande de réservation a été envoyée avec succès à la pharmacie !']);
        exit();

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur SQL lors de l\'enregistrement : ' . $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Données POST incomplètes.']);
    exit();
}