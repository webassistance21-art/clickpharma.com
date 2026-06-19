<?php
require_once 'config/db.php';
$pdo->exec("SET NAMES utf8mb4");

session_start();

$lang = $_SESSION['lang'] ?? 'fr';
$query = trim($_GET['query'] ?? '');

if (empty($query)) {
    exit();
}

try {
    // ÉTAPE 1 : Recherche du médicament par nom ou forme dans la table 'medicaments'
    $stmt_med = $pdo->prepare("SELECT * FROM medicaments WHERE nom_medicament LIKE :q1 OR forme LIKE :q2");
    $stmt_med->execute([
        ':q1' => '%' . $query . '%',
        ':q2' => '%' . $query . '%'
    ]);
    $medocs = $stmt_med->fetchAll(PDO::FETCH_ASSOC);

    if (empty($medocs)) {
        afficherMessageVide($lang);
        exit();
    }

    // Récupération des IDs des médicaments trouvés (clé primaire : id_medoc)
    $ids_medoc = [];
    foreach ($medocs as $m) {
        if (!empty($m['id_medoc'])) {
            $ids_medoc[] = (int)$m['id_medoc'];
        }
    }

    if (empty($ids_medoc)) {
        afficherMessageVide($lang);
        exit();
    }

    // ÉTAPE 2 : Recherche dans la table 'stocks' (avec un S) et jointure sur pharmacies via 'p.id'
    $in_clause = implode(',', array_fill(0, count($ids_medoc), '?'));
    
    $sql = "SELECT s.*, p.* FROM stocks s
            JOIN pharmacies p ON s.id_pharmacie = p.id
            WHERE s.id_medicament IN ($in_clause) AND s.quantite > 0";

    $stmt_stock = $pdo->prepare($sql);
    $stmt_stock->execute($ids_medoc);
    $stocks = $stmt_stock->fetchAll(PDO::FETCH_ASSOC);

    if (empty($stocks)) {
        afficherMessageVide($lang);
        exit();
    }

    // Affichage du nombre de résultats trouvés
    $count = count($stocks);
    if ($lang === 'ar') {
        echo '<p class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1 mb-2">الصيدليات القريبة (' . $count . ')</p>';
    } else {
        echo '<p class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1 mb-2">Pharmacies à proximité (' . $count . ')</p>';
    }

    // ÉTAPE 3 : Génération des cartes HTML pour la zone de résultats
    foreach ($stocks as $row) {
        $nom_medicament_affiche = "Médicament";
        $forme_medicament_affiche = "";
        
        // On associe le stock au bon médicament pour l'affichage
        foreach ($medocs as $m) {
            if ($m['id_medoc'] == $row['id_medicament']) {
                $nom_medicament_affiche = $m['nom_medicament'];
                $forme_medicament_affiche = $m['forme'];
                break;
            }
        }

        $nom_pharma = htmlspecialchars($row['nom_pharmacie'] ?? 'Pharmacie');
        $adresse_pharma = htmlspecialchars($row['adresse'] ?? 'Adresse non spécifiée');
        
        $badge_dispo = ($lang === 'ar') ? 'متوفر' : 'Disponible';
        $distance_txt = ($lang === 'ar') ? 'على بعد 7 km منك' : 'À 7 km de vous';

        echo '
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition hover:border-emerald-200 mb-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="text-sm font-black text-slate-800">' . htmlspecialchars($nom_medicament_affiche) . '</h4>
                    ' . (!empty($forme_medicament_affiche) ? '<span class="text-[10px] font-bold uppercase bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md">' . htmlspecialchars($forme_medicament_affiche) . '</span>' : '') . '
                </div>
                <div class="space-y-0.5 text-xs text-slate-500 font-medium">
                    <p class="text-slate-700 font-bold flex items-center gap-1.5">
                        <i class="fa-solid fa-house-medical text-[#00966b]"></i> ' . $nom_pharma . '
                    </p>
                    <p class="flex items-center gap-1.5 text-slate-400">
                        <i class="fa-solid fa-map-pin text-slate-300"></i> ' . $adresse_pharma . '
                    </p>
                </div>
                <p class="text-[11px] font-bold text-emerald-600 flex items-center gap-1 pt-1">
                    <i class="fa-solid fa-route text-xs"></i> ' . $distance_txt . '
                </p>
            </div>
            
            <span class="bg-emerald-50 text-[#00966b] text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider self-end sm:self-center flex items-center gap-1">
                <i class="fa-solid fa-circle-check text-xs"></i> ' . $badge_dispo . '
            </span>
        </div>';
    }

} catch (PDOException $e) {
    echo '<div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl text-xs font-semibold shadow-sm">';
    echo '  <i class="fa-solid fa-circle-exclamation mr-2"></i> Une erreur de structure SQL est survenue.';
    echo '  <div class="text-[10px] font-mono mt-2 text-red-500 bg-white p-2 rounded border border-red-200 font-bold">Message phpMyAdmin : ' . htmlspecialchars($e->getMessage()) . '</div>';
    echo '</div>';
}

function afficherMessageVide($lang) {
    if ($lang === 'ar') {
        echo '<div class="bg-white p-6 rounded-2xl border border-slate-200 text-center text-xs text-slate-400 italic shadow-sm">لم يتم العثور على أي صيدلية توفر هذا الدواء.</div>';
    } else {
        echo '<div class="bg-white p-6 rounded-2xl border border-slate-200 text-center text-xs text-slate-400 italic shadow-sm">Aucune pharmacie ne dispose de ce médicament à proximité.</div>';
    }
}
?>