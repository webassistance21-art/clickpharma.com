<?php
session_start();
require_once 'config/db.php';

$query_text = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query_text)) {
    header('Location: space_patient.php');
    exit();
}

try {
    $sql = "SELECT p.id as id_pharma, p.nom_pharmacie, p.adresse, p.telephone, p.latitude, p.longitude, s.quantite, m.nom_medicament
            FROM pharmacies p
            JOIN stocks s ON p.id = s.id_pharmacie
            JOIN medicaments m ON s.id_medicament = m.id_medoc
            WHERE (m.nom_medicament LIKE ? OR m.nom_medicament LIKE ?) 
            AND s.quantite > 0
            ORDER BY s.quantite DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['%' . $query_text . '%', $query_text . '%']);
    $resultats = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats pour <?= htmlspecialchars($query_text) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-50 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <a href="space_patient.php" class="text-indigo-600 font-bold text-sm hover:underline">← Retour</a>
            <h1 class="text-2xl font-black text-slate-800 mt-2">Résultats pour "<?= htmlspecialchars($query_text) ?>"</h1>
        </div>

        <div class="grid gap-4">
            <?php if ($resultats): ?>
                <?php foreach($resultats as $r): ?>
                    <div class="pharma-item bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center"
     data-lat="<?= htmlspecialchars($r['latitude'] ?? '') ?>" 
     data-lon="<?= htmlspecialchars($r['longitude'] ?? '') ?>">
    
    <div>
        <h3 class="font-bold text-lg text-slate-800"><?= htmlspecialchars($r['nom_pharmacie']) ?></h3>
        <p class="text-sm text-slate-500 mb-3"><?= htmlspecialchars($r['adresse']) ?></p>
        
        <div class="flex flex-wrap gap-2 items-center">
            <span class="distance-display text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-xl italic">
                <i class="fa-solid fa-location-dot mr-1"></i> Calcul...
            </span>

            <span class="text-xs font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-xl border border-emerald-100 uppercase tracking-tight">
                <i class="fa-solid fa-pills mr-1 text-emerald-500"></i> Dispo : <?= htmlspecialchars($r['nom_medicament']) ?>
            </span>
        </div>
    </div>
    
    <div class="flex flex-col gap-2 items-end">
        <p class="text-xs font-bold text-slate-400">
            <i class="fa-solid fa-phone mr-1"></i> <?= htmlspecialchars($r['telephone']) ?>
        </p>
        
        <?php 
        $nom_patient = $_SESSION['nom_patient'] ?? 'Patient Anonyme';
        $nss_patient = $_SESSION['nss_patient'] ?? '';
        ?>
        <button onclick="envoyerNotification(<?= (int)$r['id_pharma'] ?>, '<?= addslashes($r['nom_medicament']) ?>', '<?= addslashes($nom_patient) ?>', '<?= addslashes($nss_patient) ?>')" 
                class="bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-emerald-700 transition flex items-center gap-2 shadow-md shadow-emerald-100">
            <i class="fa-solid fa-paper-plane"></i> Réserver
        </button>

        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($r['nom_pharmacie'] . ' ' . $r['adresse']) ?>" 
           target="_blank" class="text-slate-400 text-[10px] font-bold uppercase hover:text-indigo-600 hover:underline mt-1">
            <i class="fa-solid fa-map mr-1"></i> Voir sur la carte
        </a>
    </div>
</div>
                           
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-white p-12 rounded-3xl text-center border-2 border-dashed border-slate-200">
                    <p class="text-slate-400 font-bold">Aucune pharmacie ne possède ce médicament en stock.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<script>
function envoyerNotification(idPharma, medicament, nom, nss) {
    if (!confirm("Voulez-vous envoyer une demande de réservation pour : " + medicament + " ?")) {
        return;
    }

    const formData = new FormData();
    formData.append('id_pharma', idPharma);
    formData.append('medicament', medicament);
    formData.append('nom_client', nom);
    formData.append('nss', nss);

    fetch('envoyer_demande.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("✅ Félicitations " + nom + ", votre réservation a bien été envoyée !");
        } else {
            alert("❌ Erreur : " + (data.error || "Impossible d'envoyer la demande."));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert("Erreur de connexion au serveur.");
    });
}

// --- Logique Géographique (Haversine) ---
function deg2rad(deg) { 
    return deg * (Math.PI / 180); 
}

function getDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; 
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1); 
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * Math.sin(dLon / 2) * Math.sin(dLon / 2); 
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)); 
    return (R * c).toFixed(1); 
}

if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(position => {
        const uLat = position.coords.latitude;
        const uLon = position.coords.longitude;

        document.querySelectorAll('.pharma-item').forEach(item => {
            const pLat = parseFloat(item.dataset.lat);
            const pLon = parseFloat(item.dataset.lon);
            
            if (!isNaN(pLat) && !isNaN(pLon)) {
                const dist = getDistance(uLat, uLon, pLat, pLon);
                item.querySelector('.distance-display').innerHTML = `<i class="fa-solid fa-location-dot mr-1"></i> ${dist} km`;
            } else {
                item.querySelector('.distance-display').innerText = "Position non renseignée";
            }
        });
    }, error => {
        console.error("Erreur de géolocalisation :", error);
        document.querySelectorAll('.distance-display').forEach(el => {
            el.innerText = "Position indisponible";
        });
    });
} else {
    document.querySelectorAll('.distance-display').forEach(el => {
        el.innerText = "Navigateur non compatible";
    });
}
</script>
</body>
</html>