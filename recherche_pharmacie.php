<?php
// C:\xampp\htdocs\ClickPharma\recherche_pharmacie.php

// 1. Connexion sécurisée à la base de données via PDO
try {
    $bdd = new PDO('mysql:host=localhost;dbname=pharmalife_db;charset=utf8', 'root', '');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// Coordonnées par défaut du patient (Ex: Centre-ville de Skikda)
$patient_lat = isset($_GET['lat']) ? floatval($_GET['lat']) : 36.879000;
$patient_lon = isset($_GET['lon']) ? floatval($_GET['lon']) : 6.904000;

// Fonction PHP pour calculer la distance entre le patient et la pharmacie
function calculerDistance($lat1, $lon1, $lat2, $lon2) {
    if (empty($lat2) || empty($lon2)) return null;
    $earth_radius = 6371; // Rayon de la Terre en km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return round($earth_radius * $c, 2); // Distance finale en km
}

// 2. Récupération et sécurisation de l'ID de l'ordonnance depuis l'URL
$ordonnance_id = isset($_GET['id_ordonnance']) ? intval($_GET['id_ordonnance']) : 0;
$pharmacies = [];
$liste_noms_meds = [];
$chaine_meds_reservation = "";
$nss_patient = ""; 

if ($ordonnance_id > 0) {
    try {
        // ÉTAPE 1 : Récupérer le contenu JSON et le NSS du patient
        $stmt_ord = $bdd->prepare("SELECT o.contenu_medocs, p.nss 
                                   FROM ordonnances o 
                                   LEFT JOIN patients p ON o.id_patient = p.id_patient 
                                   WHERE o.id_ordonnance = :id");
        $stmt_ord->execute(['id' => $ordonnance_id]);
        $ordonnance = $stmt_ord->fetch(PDO::FETCH_ASSOC);

        if ($ordonnance && !empty($ordonnance['contenu_medocs'])) {
            $nss_patient = $ordonnance['nss'];
            $meds_array = json_decode($ordonnance['contenu_medocs'], true);

            if (is_array($meds_array)) {
                foreach ($meds_array as $med) {
                    $nom_brut = isset($med['nom_medicament']) ? $med['nom_medicament'] : (isset($med['medicament']) ? $med['medicament'] : '');
                    if (!empty($nom_brut)) {
                        $nom_nettoye = trim(preg_replace('/\s*\(.*?\)\s*/', '', $nom_brut));
                        if (!empty($nom_nettoye)) {
                            $liste_noms_meds[] = $nom_nettoye;
                        }
                    }
                }
            }

            // Chaîne propre servant à la transmission JavaScript
            $chaine_meds_reservation = implode(', ', $liste_noms_meds);

            // ÉTAPE 2 : Recherche des pharmacies correspondantes
            if (!empty($liste_noms_meds)) {
                $clauses = [];
                $params = [];
                
                foreach ($liste_noms_meds as $index => $nom_med) {
                    $clauses[] = "m.nom_medicament LIKE :med" . $index;
                    $params['med' . $index] = '%' . $nom_med . '%';
                }
                
                $where_clause = implode(' OR ', $clauses);

                $query = "SELECT DISTINCT p.id, p.nom_pharmacie, p.adresse, p.telephone, p.ville, p.latitude, p.longitude 
                          FROM pharmacies p
                          INNER JOIN stocks s ON p.id = s.id_pharmacie
                          INNER JOIN medicaments m ON s.id_medicament = m.id_medoc
                          WHERE ($where_clause) AND s.quantite > 0";

                $stmt = $bdd->prepare($query);
                $stmt->execute($params);
                $brut_pharmacies = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Calculer les distances et vérifier l'existence d'une réservation active
                foreach ($brut_pharmacies as $pharma) {
                    $dist = calculerDistance($patient_lat, $patient_lon, $pharma['latitude'], $pharma['longitude']);
                    $pharma['distance'] = $dist;

                    // CORRECTION STATUT : Recherche uniquement 'en_attente' ou 'valide'
                    $stmt_verif = $bdd->prepare("SELECT statut FROM reservations 
                                                 WHERE id_pharmacie = ? 
                                                 AND id_ordonnance = ? 
                                                 AND statut IN ('en_attente', 'valide') 
                                                 ORDER BY id DESC LIMIT 1");
                    $stmt_verif->execute([$pharma['id'], $ordonnance_id]);
                    $res_verif = $stmt_verif->fetch(PDO::FETCH_ASSOC);
                    
                    $pharma['deja_reserve'] = $res_verif ? $res_verif['statut'] : false;
                    $pharmacies[] = $pharma;
                }

                // Trier du plus proche au plus lointain
                usort($pharmacies, function($a, $b) {
                    return ($a['distance'] <=> $b['distance']);
                });
            }
        }
    } catch (PDOException $e) {
        $erreur_sql = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClickPharma - Recherche de Pharmacie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #00a65a;
            --primary-hover: #008d4c;
            --bg-color: #f8f9fa;
            --text-color: #333;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        h2 {
            color: #1e293b;
            font-size: 24px;
            margin-bottom: 20px;
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 10px;
        }
        .meds-badge-container {
            margin-bottom: 20px;
        }
        .med-badge {
            display: inline-block;
            background: #e6fffa;
            color: #047481;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            margin-right: 8px;
            margin-bottom: 8px;
            font-weight: 600;
            border: 1px solid #b2f5ea;
        }
        .pharmacies-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 20px;
        }
        .pharmacie-card {
            border: 1px solid #e2e8f0;
            padding: 20px;
            border-radius: 10px;
            background: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .pharmacie-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .pharmacie-info strong {
            font-size: 18px;
            color: #2d3748;
        }
        .pharmacie-info p {
            margin: 6px 0;
            color: #718096;
            font-size: 14px;
        }
        .pharmacie-meta {
            font-size: 13px;
            color: #a0aec0;
            margin-top: 4px;
            display: flex;
            gap: 15px;
        }
        .distance-badge {
            color: #2b6cb0;
            font-weight: 600;
        }
        .right-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
        }
        .status-badge {
            background: #edfbd2;
            color: #4c7a1a;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: bold;
        }
        .btn-reserve {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-reserve:hover {
            background-color: var(--primary-hover);
        }
        .btn-disabled {
            background-color: #cbd5e0;
            color: #718096;
            cursor: not-allowed;
        }
        .btn-success-badge {
            background-color: #10b981;
            color: white;
            cursor: not-allowed;
        }
        .btn-back {
            display: inline-block;
            margin-top: 25px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        .btn-back:hover {
            text-decoration: underline;
        }
        .error-box {
            background-color: #fff5f5;
            color: #c53030;
            padding: 15px;
            border-left: 4px solid #e53e3e;
            border-radius: 4px;
            margin-top: 20px;
        }
        .no-result {
            background-color: #f7fafc;
            color: #4a5568;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            border: 1px dashed #cbd5e0;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>
        <i class="fa-solid fa-map-location-dot" style="color: var(--primary-color);"></i> 
        Pharmacies pour l'ordonnance #<?php echo $ordonnance_id; ?>
    </h2>

    <?php if (!empty($liste_noms_meds)): ?>
        <div class="meds-badge-container">
            <strong>Traitements recherchés :</strong> <br><br>
            <?php foreach ($liste_noms_meds as $nom): ?>
                <span class="med-badge"><i class="fa-solid fa-pill"></i> <?php echo htmlspecialchars($nom); ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($erreur_sql)): ?>
        <div class="error-box">
            <strong>Erreur de structure de base de données :</strong><br>
            <code><?php echo htmlspecialchars($erreur_sql); ?></code>
        </div>
    <?php elseif (!empty($pharmacies)): ?>
        <div class="pharmacies-list">
            <?php foreach ($pharmacies as $pharmacie): ?>
                <div class="pharmacie-card">
                    <div class="pharmacie-info">
                        <strong><?php echo htmlspecialchars($pharmacie['nom_pharmacie']); ?></strong>
                        <p><i class="fa-solid fa-location-dot" style="color: #e53e3e; margin-right: 5px;"></i> <?php echo htmlspecialchars($pharmacie['adresse']); ?>, <?php echo htmlspecialchars($pharmacie['ville']); ?></p>
                        
                        <div class="pharmacie-meta">
                            <?php if(!empty($pharmacie['telephone'])): ?>
                                <span><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($pharmacie['telephone']); ?></span>
                            <?php endif; ?>
                            
                            <?php if($pharmacie['distance'] !== null): ?>
                                <span class="distance-badge">
                                    <i class="fa-solid fa-route"></i> à <?php echo $pharmacie['distance']; ?> km
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="right-actions">
                        <span class="status-badge">● En Stock</span>
                        
                        <?php if ($pharmacie['deja_reserve']): ?>
                            <?php if (trim(strtolower($pharmacie['deja_reserve'])) === 'en_attente'): ?>
                                <button class="btn-reserve btn-disabled" disabled>
                                    <i class="fa-solid fa-lock"></i> Demande en attente
                                </button>
                            <?php else: ?>
                                <button class="btn-reserve btn-success-badge" disabled>
                                    <i class="fa-solid fa-circle-check"></i> Réservation validée
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="btn-reserve" onclick="reserverMedocs(<?php echo $pharmacie['id']; ?>, '<?php echo addslashes($chaine_meds_reservation); ?>', <?php echo $ordonnance_id; ?>)">
                                <i class="fa-solid fa-calendar-check"></i> Réserver
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-result">
            <i class="fa-solid fa-circle-info" style="font-size: 24px; color: #a0aec0; margin-bottom: 10px;"></i>
            <p>Aucune pharmacie trouvée possédant ces produits en stock.</p>
        </div>
    <?php endif; ?>

    <br>
    <a href="javascript:history.back()" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Retour à l'espace patient</a>
</div>

<script>
window.addEventListener('load', () => {
    if (navigator.geolocation && !window.location.search.includes('&lat=')) {
        navigator.geolocation.getCurrentPosition((position) => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            window.location.href = window.location.search + '&lat=' + lat + '&lon=' + lon;
        });
    }
});

function reserverMedocs(idPharmacie, medicamentsString, idOrdonnance) {
    if(confirm("Voulez-vous envoyer une demande de réservation pour ces médicaments à cette pharmacie ?")) {
        const formData = new FormData();
        formData.append('id_pharmacie', idPharmacie);
        formData.append('medicaments', medicamentsString);
        formData.append('id_ordonnance', idOrdonnance);

        fetch('action_reserver_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("Réponse réseau incorrecte");
            }
            return response.json();
        })
        .then(data => {
            alert(data.message);
            if(data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Erreur rencontrée:', error);
            alert("Une erreur est survenue lors de l'envoi de la réservation.");
        });
    }
}
</script>
</body>
</html>