<?php
require_once 'config/db.php';
$pdo->exec("SET NAMES utf8mb4");

session_start();

// Gestion de la langue
$lang = $_SESSION['lang'] ?? 'fr';

// Récupération du terme recherché (ex: "DOL" depuis l'input de image_3dce7e.png)
$query = trim($_GET['query'] ?? '');

$medicaments = [];

if (!empty($query)) {
    // Requête SQL pour chercher le médicament et les pharmacies qui le possèdent en stock > 0
    // S'adapte à ta structure : médicaments liés aux stocks des pharmacies
    $sql = "SELECT m.id_medicament, m.nom_medicament, m.dosage, m.forme,
                   p.nom_pharmacie, p.adresse_pharmacie, p.telephone_pharmacie,
                   s.quantite_stock
            FROM medicaments m
            INNER JOIN stocks s ON m.id_medicament = s.id_medicament
            INNER JOIN pharmacies p ON s.id_pharmacie = p.id_pharmacie
            WHERE (m.nom_medicament LIKE :query OR m.code_barre LIKE :query)
              AND s.quantite_stock > 0
            ORDER BY s.quantite_stock DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['query' => '%' . $query . '%']);
    $medicaments = $stmt->fetchAll();
}

// Dictionnaire bilingue
$txt = [
    'fr' => [
        'title' => 'Résultats de recherche | ClickPharma',
        'back' => 'Retour à l’espace patient',
        'results_for' => 'Résultats pour la recherche :',
        'pharmacy' => 'Pharmacie',
        'address' => 'Adresse',
        'phone' => 'Téléphone',
        'stock' => 'Quantité disponible',
        'no_res' => 'Aucune pharmacie ne dispose de ce médicament pour le moment.',
        'btn_contact' => 'Contacter',
        'dosage' => 'Dosage'
    ],
    'ar' => [
        'title' => 'نتائج البحث | ClickPharma',
        'back' => 'العودة إلى فضاء المريض',
        'results_for' => 'نتائج البحث عن :',
        'pharmacy' => 'الصيدلية',
        'address' => 'العنوان',
        'phone' => 'الهاتف',
        'stock' => 'الكمية المتوفرة',
        'no_res' => 'لا توجد أي صيدلية توفر هذا الدواء حالياً.',
        'btn_contact' => 'اتصال',
        'dosage' => 'الجرعة'
    ]
];
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= ($lang === 'ar') ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $txt[$lang]['title'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: <?= ($lang === 'ar') ? "'Cairo', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen antialiased">

    <!-- En-tête minimalist -->
    <header class="bg-white border-b border-slate-200 p-4 sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto flex justify-between items-center max-w-5xl">
            <div class="flex items-center gap-3">
                <img src="images/Pharma (1).png" alt="Logo" class="h-9 w-9 bg-emerald-50 p-1 rounded-xl object-contain">
                <span class="text-base font-black text-slate-800 tracking-tight">ClickPharma</span>
            </div>
            <a href="espace_patient.php" class="text-xs font-bold text-[#00966b] hover:text-[#00825c] transition flex items-center gap-2">
                <i class="fa-solid <?= ($lang === 'ar') ? 'fa-arrow-right' : 'fa-arrow-left' ?>"></i>
                <span><?= $txt[$lang]['back'] ?></span>
            </a>
        </div>
    </header>

    <main class="container mx-auto p-4 max-w-3xl mt-6">
        
        <!-- Titre de la recherche -->
        <div class="mb-6">
            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider"><?= $txt[$lang]['results_for'] ?></p>
            <h2 class="text-2xl font-black text-slate-800 mt-1">
                "<?= htmlspecialchars($query) ?>"
            </h2>
        </div>

        <!-- Zone des résultats -->
        <div class="space-y-4">
            <?php if (empty($medicaments)): ?>
                <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center text-xs text-slate-400 italic shadow-sm">
                    <i class="fa-solid fa-triangle-exclamation text-3xl text-amber-400 block mb-3"></i>
                    <?= $txt[$lang]['no_res'] ?>
                </div>
            <?php else: ?>
                <?php 
                // Groupement par médicament pour un affichage propre
                $current_med = null;
                foreach ($medicaments as $med): 
                    if ($current_med !== $med['id_medicament']):
                        $current_med = $med['id_medicament'];
                ?>
                    <!-- Badge du Médicament Trouvé -->
                    <div class="bg-slate-900 text-white p-4 rounded-2xl shadow-sm mt-6 first:mt-0 flex justify-between items-center">
                        <div>
                            <h3 class="text-sm font-black tracking-tight uppercase">
                                <i class="fa-solid fa-pills text-emerald-400 mr-1 ml-1"></i> <?= htmlspecialchars($med['nom_medicament']) ?>
                            </h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                <?= $txt[$lang]['dosage'] ?> : <?= htmlspecialchars($med['dosage'] ?? 'N/A') ?> │ <?= htmlspecialchars($med['forme'] ?? '') ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                    <!-- Carte de la Pharmacie qui possède le stock -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-300 transition flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="space-y-1.5">
                            <h4 class="text-sm font-extrabold text-slate-800">
                                <i class="fa-solid fa-house-medical text-[#00966b] mr-1 ml-1"></i> <?= htmlspecialchars($med['nom_pharmacie']) ?>
                            </h4>
                            <p class="text-xs text-slate-500">
                                <strong><?= $txt[$lang]['address'] ?> :</strong> <?= htmlspecialchars($med['adresse_pharmacie']) ?>
                            </p>
                            <?php if(!empty($med['telephone_pharmacie'])): ?>
                                <p class="text-xs text-slate-500">
                                    <strong><?= $txt[$lang]['phone'] ?> :</strong> <?= htmlspecialchars($med['telephone_pharmacie']) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Badge Stock & Action -->
                        <div class="w-full sm:w-auto text-right flex sm:flex-col items-center sm:items-end justify-between gap-2 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100">
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold block uppercase"><?= $txt[$lang]['stock'] ?></span>
                                <span class="text-xs font-black bg-emerald-50 text-[#00966b] px-2.5 py-1 rounded-md inline-block mt-0.5">
                                    <?= $med['quantite_stock'] ?>
                                </span>
                            </div>
                            
                            <?php if(!empty($med['telephone_pharmacie'])): ?>
                                <a href="tel:<?= $med['telephone_pharmacie'] ?>" class="bg-slate-100 hover:bg-[#00966b] text-slate-700 hover:text-white text-xs font-bold px-3 py-1.5 rounded-xl transition flex items-center gap-1">
                                    <i class="fa-solid fa-phone-flip text-[10px]"></i> <?= $txt[$lang]['btn_contact'] ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>