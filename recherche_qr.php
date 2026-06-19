<?php
session_start();
require_once 'config/db.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Token invalide.");
}

try {
    // 1. Récupération de l'ordonnance et de son ID
    $stmt = $pdo->prepare("SELECT id_ordonnance, contenu_medocs FROM ordonnances WHERE qr_token = ?");
    $stmt->execute([$token]);
    $ordonnance = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ordonnance) {
        die("Ordonnance introuvable.");
    }

    $id_ordonnance = $ordonnance['id_ordonnance'];
    $ordonnance_complete = json_decode($ordonnance['contenu_medocs'], true);
    $pharmacies_scores = []; 
    $total_medocs_ordonnance = count($ordonnance_complete);
   
    if (is_array($ordonnance_complete) && $total_medocs_ordonnance > 0) {
        
        // Extraction et nettoyage de tous les noms de médicaments
        $noms_medocs = [];
        foreach ($ordonnance_complete as $ligne) {
            $nom = trim($ligne['MEDICAMENT'] ?? $ligne['medicament'] ?? '');
            if ($nom) {
                // Nettoyage des espaces multiples internes si présents
                $nom = preg_replace('/\s+/', ' ', $nom);
                $noms_medocs[] = $nom;
            }
        }

        if (!empty($noms_medocs)) {
            // 2. Construction dynamique de la clause WHERE avec LIKE pour tolérer les variations (ex: dosages)
            $conditions = [];
            $params = [];
            
            foreach ($noms_medocs as $medoc) {
                $conditions[] = "m.nom_medicament LIKE ?";
                $params[] = "%" . $medoc . "%";
            }
            
            $where_clause = implode(' OR ', $conditions);
            
            $query = "SELECT p.id, p.nom_pharmacie, p.adresse, p.latitude, p.longitude, m.nom_medicament
                      FROM stocks s
                      JOIN pharmacies p ON s.id_pharmacie = p.id
                      JOIN medicaments m ON s.id_medicament = m.id_medoc
                      WHERE ($where_clause) AND s.quantite > 0";
            
            $stmt_p = $pdo->prepare($query);
            $stmt_p->execute($params);
            $all_found = $stmt_p->fetchAll(PDO::FETCH_ASSOC);

            // 3. Regroupement par pharmacie en PHP
            foreach ($all_found as $row) {
                $id_p = $row['id'];
                if (!isset($pharmacies_scores[$id_p])) {
                    $pharmacies_scores[$id_p] = [
                        'infos' => [
                            'id' => $row['id'],
                            'nom_pharmacie' => $row['nom_pharmacie'],
                            'adresse' => $row['adresse'],
                            'latitude' => $row['latitude'],
                            'longitude' => $row['longitude']
                        ],
                        'medocs_trouves' => [],
                        'count' => 0
                    ];
                }
                
                // On associe le médicament trouvé à la liste s'il n'y est pas déjà
                foreach ($noms_medocs as $original_medoc) {
                    if (mb_stripos($row['nom_medicament'], $original_medoc) !== false) {
                        if (!in_array($original_medoc, $pharmacies_scores[$id_p]['medocs_trouves'])) {
                            $pharmacies_scores[$id_p]['medocs_trouves'][] = $original_medoc;
                            $pharmacies_scores[$id_p]['count']++;
                        }
                    }
                }
            }
        }
    }

    // Séparation des groupes (Totale vs Partielle)
    $dispo_totale = [];
    $dispo_partielle = [];

    foreach ($pharmacies_scores as $data) {
        if ($data['count'] == $total_medocs_ordonnance) {
            $dispo_totale[] = $data;
        } else {
            $dispo_partielle[] = $data;
        }
    }

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche | PharmaLife</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen p-4 md:p-8">

    <div class="max-w-4xl mx-auto">
        <header class="flex justify-between items-center mb-8 bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <div class="flex items-center gap-4">
                <img src="images/Pharma (1).png" alt="Logo PharmaLife" class="h-12 w-auto object-contain">
                <div>
                    <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tight">PharmaLife</h1>
                    <p class="text-xs text-slate-400 font-bold">Analyse & Disponibilité</p>
                </div>
            </div>
            <a href="space_patient.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-5 py-2.5 rounded-xl text-xs font-black transition tracking-wider">RETOUR</a>
        </header>

        <h2 class="text-emerald-600 font-black mb-4 flex items-center gap-2 tracking-wide uppercase text-sm">
            <i class="fa-solid fa-circle-check text-lg"></i> TOUT EN STOCK (<?= count($dispo_totale) ?>)
        </h2>
        
        <div id="container-totale" class="space-y-4 mb-10">
            <?php if (empty($dispo_totale)): ?>
                <p class="text-sm text-slate-400 italic bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">Aucune pharmacie n'a l'ordonnance complète.</p>
            <?php else: ?>
                <?php foreach ($dispo_totale as $item): ?>
                    <?php $liste_medocs_txt = implode(', ', $item['medocs_trouves']); ?>
                    <div class="pharma-item bg-white p-6 rounded-3xl border-2 border-emerald-500 shadow-sm transition-all duration-300 hover:shadow-md" 
                         data-lat="<?= htmlspecialchars($item['infos']['latitude']) ?>" 
                         data-lon="<?= htmlspecialchars($item['infos']['longitude']) ?>">
                        
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                            <div class="flex-1">
                                <h3 class="font-black text-lg text-slate-800 mb-1"><?= htmlspecialchars($item['infos']['nom_pharmacie']) ?></h3>
                                <p class="text-xs text-slate-500 mb-4 flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-slate-400 text-sm"></i><?= htmlspecialchars($item['infos']['adresse']) ?>
                                </p>
                                
                                <div class="flex flex-wrap gap-1.5">
                                    <?php foreach ($item['medocs_trouves'] as $m): ?>
                                        <span class="text-[10px] bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-xl font-bold border border-emerald-100">✓ <?= htmlspecialchars($m) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="text-right flex sm:flex-col items-end justify-between sm:justify-search h-full w-full sm:w-auto min-w-[140px] pt-4 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                <span class="distance-display inline-flex items-center gap-1 text-indigo-600 font-black text-sm mb-2 italic">
                                    <i class="fa-solid fa-spinner fa-spin text-xs"></i> Calcul...
                                </span>
                                
                                <button onclick="reserver(<?= (int)$item['infos']['id'] ?>, <?= htmlspecialchars(json_encode($liste_medocs_txt), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$id_ordonnance ?>, event)" 
                                        class="bg-emerald-600 text-white px-5 py-3 rounded-xl text-[10px] font-black uppercase tracking-wider transition-colors hover:bg-emerald-700 shadow-sm">
                                    Réserver tout
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <h2 class="text-orange-500 font-black mb-4 flex items-center gap-2 tracking-wide uppercase text-sm">
            <i class="fa-solid fa-circle-exclamation text-lg"></i> DISPONIBILITÉ PARTIELLE (<?= count($dispo_partielle) ?>)
        </h2>
        
        <div id="container-partielle" class="grid md:grid-cols-2 gap-4">
            <?php if (empty($dispo_partielle)): ?>
                <p class="text-sm text-slate-400 italic col-span-2 bg-white p-6 rounded-3xl border border-slate-100 shadow-sm">Aucune autre pharmacie trouvée.</p>
            <?php else: ?>
                <?php foreach ($dispo_partielle as $item_p): ?>
                    <?php $liste_medocs_txt_p = implode(', ', $item_p['medocs_trouves']); ?>
                    <div class="pharma-item bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between transition-all duration-300 hover:shadow-md"
                         data-lat="<?= htmlspecialchars($item_p['infos']['latitude']) ?>" 
                         data-lon="<?= htmlspecialchars($item_p['infos']['longitude']) ?>">
                        <div>
                            <h3 class="font-black text-slate-800 text-base mb-1"><?= htmlspecialchars($item_p['infos']['nom_pharmacie']) ?></h3>
                            <p class="text-[11px] text-slate-400 mb-3 line-clamp-1"><?= htmlspecialchars($item_p['infos']['adresse']) ?></p>
                            <p class="text-[11px] font-bold text-orange-600 bg-orange-50 inline-block px-2.5 py-1 rounded-lg mb-4 border border-orange-100">
                                <?= (int)$item_p['count'] ?> / <?= (int)$total_medocs_ordonnance ?> médicaments dispo
                            </p>
                        </div>
                        
                        <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                            <span class="distance-display text-xs font-black text-slate-500 italic">
                                <i class="fa-solid fa-spinner fa-spin text-[10px]"></i> Calcul...
                            </span>
                            <button onclick="reserver(<?= (int)$item_p['infos']['id'] ?>, <?= htmlspecialchars(json_encode($liste_medocs_txt_p), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$id_ordonnance ?>, event)" 
                                    class="text-indigo-600 font-black text-[10px] uppercase hover:underline tracking-wider">
                                Réserver
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function deg2rad(deg) { return deg * (Math.PI/180); }

    // Formule de Haversine
    function getDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; 
        const dLat = deg2rad(lat2-lat1);
        const dLon = deg2rad(lon2-lon1); 
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * Math.sin(dLon/2) * Math.sin(dLon/2); 
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
        return parseFloat((R * c).toFixed(1));
    }

    // Tri dynamique des éléments du DOM par distance calculée
    function trierPharmaciesParDistance(containerId, userLat, userLon) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const items = Array.from(container.querySelectorAll('.pharma-item'));
        if (items.length === 0) return;

        items.forEach(item => {
            const pLat = parseFloat(item.dataset.lat);
            const pLon = parseFloat(item.dataset.lon);
            if (!isNaN(pLat) && !isNaN(pLon)) {
                item.computedDistance = getDistance(userLat, userLon, pLat, pLon);
                item.querySelector('.distance-display').innerHTML = `<i class="fa-solid fa-route text-xs"></i> ${item.computedDistance} km`;
            } else {
                item.computedDistance = Infinity;
                item.querySelector('.distance-display').innerText = "N/A";
            }
        });

        // Tri ascendant
        items.sort((a, b) => a.computedDistance - b.computedDistance);

        // Réinjection ordonnée dans le DOM
        items.forEach(item => container.appendChild(item));
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const uLat = position.coords.latitude;
            const uLon = position.coords.longitude;

            trierPharmaciesParDistance('container-totale', uLat, uLon);
            trierPharmaciesParDistance('container-partielle', uLat, uLon);

        }, error => {
            document.querySelectorAll('.distance-display').forEach(el => el.innerText = "Activer GPS");
        });
    }

    // --- Envoi Asynchrone AJAX ---
    function reserver(idPharma, medocsString, idOrdonnance, event) {
        const btn = event.currentTarget;

        if (confirm("Voulez-vous envoyer une demande de réservation pour ces médicaments ?")) {
            const formData = new FormData();
            formData.append('id_pharmacie', idPharma);
            formData.append('medicaments', medocsString);
            formData.append('id_ordonnance', idOrdonnance);

            fetch('action_reserver_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error("Réponse réseau incorrecte");
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert("✅ " + data.message);
                    btn.innerText = "DEMANDÉ";
                    btn.removeAttribute('onclick');
                    btn.className = "bg-slate-200 text-slate-500 px-5 py-3 rounded-xl text-[10px] font-black uppercase tracking-wider cursor-not-allowed shadow-none";
                    btn.disabled = true;
                } else {
                    alert("❌ Erreur : " + (data.message || "Impossible de finaliser la réservation."));
                }
            })
            .catch(error => {
                console.error('Erreur rencontrée:', error);
                alert("Une erreur est survenue lors de la communication avec le serveur.");
            });
        }
    }
    </script>
</body>
</html>