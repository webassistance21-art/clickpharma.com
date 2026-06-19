<?php
require_once 'config/db.php';

// Gestion de la langue (Par défaut : Français)
$lang = $_GET['lang'] ?? 'fr';
if (!in_array($lang, ['fr', 'ar'])) {
    $lang = 'fr';
}

$search = trim($_GET['q'] ?? '');
$lat_patient = floatval($_GET['lat'] ?? 0);
$lng_patient = floatval($_GET['lng'] ?? 0);

$resultats = [];

if (!empty($search)) {
    // Si la géolocalisation est active (Formule de Haversine)
    if ($lat_patient != 0 && $lng_patient != 0) {
        $query = "SELECT m.nom_medicament, m.forme, p.nom_pharmacie, p.adresse, p.latitude, p.longitude,
                  (6371 * acos(cos(radians(?)) * cos(radians(p.latitude)) * cos(radians(p.longitude) - radians(?)) + sin(radians(?)) * sin(radians(p.latitude)))) AS distance
                  FROM stocks s
                  JOIN medicaments m ON s.id_medicament = m.id_medoc
                  JOIN pharmacies p ON s.id_pharmacie = p.id
                  WHERE (m.nom_medicament LIKE ? OR m.forme LIKE ?) AND s.quantite > 0
                  GROUP BY s.id_pharmacie, m.id_medoc
                  ORDER BY distance ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$lat_patient, $lng_patient, $lat_patient, "%$search%", "%$search%"]);
    } else {
        // Fallback si la position GPS n'est pas encore partagée
        $query = "SELECT m.nom_medicament, m.forme,  p.nom_pharmacie, p.adresse, NULL as distance
                  FROM stocks s
                  JOIN medicaments m ON s.id_medicament = m.id_medoc
                  JOIN pharmacies p ON s.id_pharmacie = p.id
                  WHERE (m.nom_medicament LIKE ? OR m.forme LIKE ?) AND s.quantite > 0
                  GROUP BY s.id_pharmacie, m.id_medoc";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute(["%$search%", "%$search%"]);
    }
    
    $resultats = $stmt->fetchAll();
}

// Traductions dynamiques selon la langue choisie
$texts = [
    'fr' => [
        'title' => 'Trouver mon médicament | ClickPharma',
        'space' => 'Espace Patient',
        'heading' => 'Quel médicament recherchez-vous ?',
        'subheading' => 'Les pharmacies les plus proches disposant de votre produit.',
        'placeholder' => 'Entrez le nom du médicament...',
        'btn_search' => 'Rechercher',
        'results_title' => 'Pharmacies à proximité',
        'distance' => 'À %d km de vous',
        'available' => 'Disponible',
        'no_results' => 'Désolé, ce médicament n\'est disponible dans aucune officine à proximité.',
        'switch_lang' => 'العربية',
        'target_lang' => 'ar',
        'scan_title' => 'Scanner le Code QR du Médicament',
        'scan_close' => 'Fermer la caméra'
    ],
    'ar' => [
        'title' => 'البحث عن دواء | ClickPharma',
        'space' => 'فضاء المريض',
        'heading' => 'عن أي دواء تبحث؟',
        'subheading' => 'اكتشف الصيدليات الأقرب إليك والتي يتوفر لديها دواؤك حالياً.',
        'placeholder' => 'أدخل اسم الدواء هنا...',
        'btn_search' => 'بحث',
        'results_title' => 'الصيدليات القريبة المتاحة',
        'distance' => 'تبعد عنك حوالي %d كم',
        'available' => 'متوفر في المخزن',
        'no_results' => 'عذراً، هذا الدواء غير متوفر حالياً في أي صيدلية قريبة.',
        'switch_lang' => 'Français',
        'target_lang' => 'fr',
        'scan_title' => 'امسح رمز الاستجابة السريعة (QR)',
        'scan_close' => 'إغلاق الكاميرا'
    ]
];

$t = $texts[$lang];
$dir = ($lang === 'ar') ? 'rtl' : 'ltr';
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['title'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        body { 
            font-family: <?= ($lang === 'ar') ? "'Cairo', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>; 
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <header class="bg-white border-b border-slate-100 py-6 px-8 shadow-sm">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <span class="text-2xl font-black text-[#00966b] tracking-tight">CLICKPHARMA</span>
            
            <div class="flex items-center gap-4">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest"><?= $t['space'] ?></span>
                <a href="recherche_patient.php?lang=<?= $t['target_lang'] ?>&q=<?= urlencode($search) ?>&lat=<?= $lat_patient ?>&lng=<?= $lng_patient ?>" 
                   class="bg-slate-100 hover:bg-slate-200 transition text-slate-700 text-xs font-bold px-4 py-2 rounded-full">
                    <?= $t['switch_lang'] ?>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto p-6 mt-10 space-y-8">
        
        <div class="text-center space-y-3">
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight"><?= $t['heading'] ?></h1>
            <p class="text-sm text-slate-500 font-medium"><?= $t['subheading'] ?></p>
        </div>

        <div id="qr-container" class="hidden max-w-xl mx-auto bg-white border border-slate-100 p-6 rounded-[2.5rem] shadow-md text-center space-y-4">
            <div class="flex justify-between items-center px-2">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider"><?= $t['scan_title'] ?></h3>
                <button onclick="stopQRScanner()" class="text-xs font-bold text-red-500 hover:text-red-700 flex items-center gap-1 bg-red-50 px-3 py-1.5 rounded-full transition">
                    <i class="fa-solid fa-camera-rotate"></i> <?= $t['scan_close'] ?>
                </button>
            </div>
            <div id="reader" class="overflow-hidden rounded-2xl bg-slate-900 border border-slate-100"></div>
        </div>

        <div class="max-w-xl mx-auto flex items-center gap-3">
            <form action="recherche_patient.php" method="GET" id="searchForm" class="relative flex-1">
                <input type="hidden" name="lang" value="<?= $lang ?>">
                <input type="hidden" name="lat" id="lat_input" value="<?= $lat_patient ?>">
                <input type="hidden" name="lng" id="lng_input" value="<?= $lng_patient ?>">
                
                <span class="absolute inset-y-0 <?= ($lang === 'ar') ? 'right-5' : 'left-5' ?> flex items-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                
                <input type="text" name="q" id="search_input" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="<?= $t['placeholder'] ?>" 
                       class="w-full <?= ($lang === 'ar') ? 'pr-12 pl-32' : 'pl-12 pr-32' ?> py-4 rounded-full border border-slate-200 outline-none focus:ring-4 ring-emerald-500/10 shadow-sm transition font-semibold text-slate-800 text-sm" required>
                
                <button type="submit" class="absolute <?= ($lang === 'ar') ? 'left-2' : 'right-2' ?> top-2 bottom-2 bg-[#00966b] hover:bg-[#00825c] text-white font-bold text-xs uppercase px-6 rounded-full tracking-wider transition">
                    <?= $t['btn_search'] ?>
                </button>
            </form>

            <button onclick="startQRScanner()" class="w-14 h-14 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/50 text-[#00966b] rounded-full flex items-center justify-center shadow-sm text-xl transition shrink-0" title="Scanner un QR Code">
                <i class="fa-solid fa-qrcode"></i>
            </button>
        </div>

        <?php if(!empty($search)): ?>
            <div class="space-y-4">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider <?= ($lang === 'ar') ? 'pr-2' : 'pl-2' ?>">
                    <?= $t['results_title'] ?> (<?= count($resultats) ?>)
                </h2>
                
                <div class="space-y-3">
                    <?php foreach($resultats as $r): ?>
                        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex justify-between items-center hover:border-emerald-500/30 transition">
                            <div class="space-y-2 <?= ($lang === 'ar') ? 'text-right' : 'text-left' ?>">
                                <div class="flex items-baseline gap-2">
                                    <h3 class="font-extrabold text-slate-800 text-lg"><?= htmlspecialchars($r['nom_medicament']) ?></h3>
                                    <span class="text-[10px] font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded uppercase">
                                        <?= htmlspecialchars($r['forme']) ?>
                                    </span>
                                </div>
                                
                                <div class="text-xs font-semibold text-slate-500 flex items-center gap-1.5">
                                    <i class="fa-solid fa-prescription-bottle-medical text-[#00966b]"></i>
                                    <span class="text-slate-700 font-bold"><?= htmlspecialchars($r['nom_pharmacie']) ?></span>
                                    <span class="text-slate-300">•</span>
                                    <i class="fa-solid fa-location-dot"></i> <span><?= htmlspecialchars($r['adresse'] ?? '---') ?></span>
                                </div>
                                
                                <?php if($r['distance'] !== null): ?>
                                    <p class="text-xs font-bold text-emerald-600 flex items-center gap-1 mt-1">
                                        <i class="fa-solid fa-route"></i> 
                                        <?= sprintf($t['distance'], number_format($r['distance'], 1, '.', '')) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="<?= ($lang === 'ar') ? 'text-left' : 'text-right' ?> space-y-1">
                                
                                <span class="inline-block text-[10px] font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">
                                    <i class="fa-solid fa-circle-check mr-0.5"></i> <?= $t['available'] ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if(empty($resultats)): ?>
                    <div class="bg-white p-12 text-center rounded-3xl border border-slate-100 shadow-sm space-y-2">
                        <i class="fa-solid fa-face-frown text-4xl text-slate-300"></i>
                        <p class="text-slate-700 font-bold text-sm"><?= $t['no_results'] ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>

    <script>
    let html5QrcodeScanner = null;

    // 1. Initialiser et lancer la caméra pour scanner le QR Code
    function startQRScanner() {
        document.getElementById('qr-container').classList.remove('hidden');
        
        html5QrcodeScanner = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        
        // On cible la caméra arrière de préférence sur smartphone
        html5QrcodeScanner.start(
            { facingMode: "environment" }, 
            config, 
            onScanSuccess
        ).catch(err => {
            alert("Impossible d'accéder à l'appareil photo : " + err);
            document.getElementById('qr-container').classList.add('hidden');
        });
    }

    // Action lors du scan réussi
    function onScanSuccess(decodedText, decodedResult) {
        // Le code QR doit contenir le nom ou la chaîne du médicament
        stopQRScanner();
        document.getElementById('search_input').value = decodedText;
        // Soumettre automatiquement la recherche géolocalisée
        document.getElementById('searchForm').submit();
    }

    // Arrêter proprement la caméra
    function stopQRScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then(() => {
                document.getElementById('qr-container').classList.add('hidden');
            }).catch(err => {
                console.error("Erreur lors de l'arrêt de la caméra :", err);
            });
        }
    }

    // 2. Géolocalisation Native
    document.addEventListener("DOMContentLoaded", function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('lat_input').value = position.coords.latitude;
                document.getElementById('lng_input').value = position.coords.longitude;
                
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('q') && (!urlParams.has('lat') || urlParams.get('lat') == '0')) {
                    document.getElementById('searchForm').submit();
                }
            }, function(error) {
                console.log("Géolocalisation refusée ou indisponible.");
            });
        }
    });
    </script>
</body>
</html>