<?php
// C:\xampp\htdocs\ClickPharma\space_patient.php
require_once 'config/db.php';
$pdo->exec("SET NAMES utf8mb4");

session_start();

// 1. GESTION DE LA LANGUE (Par défaut Français)
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = ($_GET['lang'] === 'ar') ? 'ar' : 'fr';
}
$lang = $_SESSION['lang'] ?? 'fr';

// Protection de la page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header('Location: login_patient.php');
    exit();
}

$id_patient = $_SESSION['id_patient'];
$success_msg = "";
$error_msg = "";

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_profil'])) {
    $telephone = trim($_POST['telephone'] ?? '');
    $poids = trim($_POST['poids'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');

    if (!empty($telephone) && !empty($adresse)) {
        $stmt_update = $pdo->prepare("UPDATE patients SET telephone = ?, poids = ?, adresse = ? WHERE id_patient = ?");
        if ($stmt_update->execute([$telephone, $poids !== '' ? $poids : null, $adresse, $id_patient])) {
            $success_msg = ($lang === 'fr') ? "Votre profil a été mis à jour avec succès !" : "تم تحديث ملفكم الشخصي بنجاح !";
        } else {
            $error_msg = ($lang === 'fr') ? "Une erreur est survenue." : "حدث خطأ أثناء التحديث.";
        }
    } else {
        $error_msg = ($lang === 'fr') ? "Champs obligatoires vides." : "الرجاء ملء الحقول الإجبارية.";
    }
}

// Récupération des données du patient
$stmt = $pdo->prepare("SELECT * FROM patients WHERE id_patient = ?");
$stmt->execute([$id_patient]);
$patient = $stmt->fetch();

if (!$patient) {
    die("Erreur : Compte patient introuvable.");
}

$age = !empty($patient['date_naissance']) ? (new DateTime())->diff(new DateTime($patient['date_naissance']))->y . (($lang === 'fr') ? " ans" : " سنة") : "N/A";

$query_ord = "SELECT o.*, 
                     (SELECT r.statut 
                      FROM reservations r 
                      WHERE r.nss = ? 
                      AND o.contenu_medocs LIKE CONCAT('%', r.medicament_demande, '%') COLLATE utf8mb4_general_ci
                      LIMIT 1) AS statut_reservation
              FROM ordonnances o 
              WHERE o.id_patient = ? 
              ORDER BY o.date_creation DESC";

$stmt_ord = $pdo->prepare($query_ord);
$stmt_ord->execute([$patient['nss'], $id_patient]);
$ordonnances = $stmt_ord->fetchAll();

// Dictionnaire des traductions statiques
$txt = [
    'fr' => [
        'title' => 'Mon Espace Santé | ClickPharma',
        'sub' => 'Espace Patient',
        'welcome' => 'Bienvenue',
        'phone' => 'Téléphone',
        'weight' => 'Poids actuel (kg)',
        'address' => 'Adresse résidentielle',
        'save' => 'Enregistrer',
        'search_title' => 'Quel médicament recherchez-vous ?',
        'search_sub' => 'Les pharmacies les plus proches disposant de votre produit.',
        'placeholder' => 'Entrez le nom du médicament...',
        'btn_search' => 'Rechercher',
        'panel_title' => 'Vos Ordonnances Connectées',
        'total' => 'Total',
        'prescriber' => 'Médecin Prescripteur',
        'treatments' => 'Traitements prescrits :',
        'btn_view' => 'Ouvrir / QR',
        'btn_find' => 'Trouver Pharmacie',
        'btn_reserved' => 'Déjà Réservée / Validée',
        'empty' => 'Aucune ordonnance n’est disponible.',
        'modal_title' => "Scanner l'ordonnance",
        'modal_status_init' => "Initialisation de la caméra...",
        'modal_status_active' => "Caméra active : Alignez le QR Code",
        'modal_status_success' => "Code détecté !",
        'modal_err' => "Impossible d'accéder à la caméra. Vérifiez les autorisations.",
        'modal_close' => "Annuler et Fermer",
        'notif_title' => 'Mes Réservations'
    ],
    'ar' => [
        'title' => 'فضائي الصحي | ClickPharma',
        'sub' => 'فضاء المريض',
        'welcome' => 'مرحباً بك',
        'phone' => 'رقم الهاتف',
        'weight' => 'الوزن الحالي (كغ)',
        'address' => 'العنوان السكني',
        'save' => 'حفظ التغييرات',
        'search_title' => 'عن أي دواء تبحثون ؟',
        'search_sub' => 'الصيدليات الأقرب إليكم والتي تتوفر على منتجكم.',
        'placeholder' => 'أدخل اسم الدواء...',
        'btn_search' => 'بحث',
        'panel_title' => 'وصفاتكم الطبية الرقمية',
        'total' => 'المجموع',
        'prescriber' => 'الطبيب المعالج',
        'treatments' => 'الأدوية الموصوفة :',
        'btn_view' => 'فتح / رمز QR',
        'btn_find' => 'البحث عن صيدلية',
        'btn_reserved' => 'محجوزة / مقبولة بالفعل',
        'empty' => 'لا توجد أي وصفة طبية حالياً.',
        'modal_title' => "مسح الوصفة الطبية (QR)",
        'modal_status_init' => "جاري تشغيل الكاميرا...",
        'modal_status_active' => "الكاميرا مشغلة: ضع رمز QR في الإطار",
        'modal_status_success' => "تم التعرف على الرمز !",
        'modal_err' => "فشل الاتصال بالكاميرا. يرجى التحقق من الصلاحيات.",
        'modal_close' => "إلغاء وإغلاق",
        'notif_title' => 'حجوزاتي'
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
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        body { font-family: <?= ($lang === 'ar') ? "'Cairo', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>; }
        #reader video { border-radius: 1.5rem !important; object-fit: cover; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen antialiased">

    <nav class="bg-[#00966b] text-white p-4 shadow-md sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="images/Pharma (1).png" alt="Logo" class="h-10 w-10 bg-white p-0.5 rounded-xl object-contain shadow-inner" onerror="this.src='Pharma (1).png'">
                <div>
                    <h1 class="text-lg font-black tracking-tight leading-none">ClickPharma</h1>
                    <span class="text-emerald-100 text-[10px] font-semibold uppercase tracking-wider"><?= $txt[$lang]['sub'] ?></span>
                </div>
            </div>
            
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="bg-[#00825c] px-3 py-1.5 rounded-xl flex gap-3 text-xs font-bold shadow-inner">
                    <a href="?lang=fr" class="<?= ($lang === 'fr') ? 'text-white underline underline-offset-4' : 'text-emerald-200/70 hover:text-white' ?>">FR</a>
                    <span class="text-emerald-400">|</span>
                    <a href="?lang=ar" class="<?= ($lang === 'ar') ? 'text-white underline underline-offset-4' : 'text-emerald-200/70 hover:text-white' ?>">العربية</a>
                </div>

                <div class="relative">
                    <button onclick="togglePatientNotifs(event)" class="bg-[#00825c] hover:bg-[#006e4e] p-2.5 rounded-xl transition text-xs shadow-inner relative flex items-center justify-center">
                        <i class="fa-solid fa-bell text-sm"></i>
                        <span id="patientBadge" class="absolute -top-1 -right-1 bg-red-500 text-white font-bold text-[9px] w-4 h-4 rounded-full flex items-center justify-center border border-white hidden animate-bounce">0</span>
                    </button>
                    <div id="patientNotifPanel" class="absolute <?= ($lang === 'ar') ? 'left-0' : 'right-0' ?> mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200 p-4 hidden text-slate-800 z-50">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mb-3 border-b border-slate-100 pb-2"><?= $txt[$lang]['notif_title'] ?></h4>
                        <div id="patientNotifList" class="max-h-60 overflow-y-auto pr-1">
                            <p class="text-center py-6 text-xs text-slate-400 italic">Aucune nouvelle mise à jour</p>
                        </div>
                    </div>
                </div>

                <div class="text-right hidden sm:block">
                    <p class="text-[9px] uppercase font-bold text-emerald-100 tracking-wider"><?= $txt[$lang]['welcome'] ?></p>
                    <p class="font-bold text-xs"><?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></p>
                </div>
                
                <a href="logout.php" class="bg-[#00825c] hover:bg-red-600 p-2.5 rounded-xl transition text-xs shadow-inner">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-6 px-4 max-w-5xl">
        <?php if(!empty($success_msg)): ?>
            <div class="bg-emerald-50 border-l-4 border-[#00966b] text-emerald-700 p-4 rounded-xl text-xs font-semibold shadow-sm mb-2">
                <i class="fa-solid fa-circle-check mr-2 text-sm"></i> <?= $success_msg ?>
            </div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl text-xs font-semibold shadow-sm mb-2">
                <i class="fa-solid fa-circle-exclamation mr-2 text-sm"></i> <?= $error_msg ?>
            </div>
        <?php endif; ?>
    </div>

    <main class="container mx-auto p-4 max-w-5xl grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm h-fit">
            <div class="text-center border-b border-slate-100 pb-4 mb-4">
                <div class="w-16 h-16 bg-emerald-50 text-[#00966b] rounded-2xl flex items-center justify-center text-2xl font-black mx-auto mb-3">
                    <?= mb_substr($patient['prenom'], 0, 1, 'UTF-8') ?>
                </div>
                <h2 class="text-base font-extrabold text-slate-800"><?= htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']) ?></h2>
                <p class="text-[10px] text-slate-400 font-mono mt-1">NSS : <?= htmlspecialchars($patient['nss']) ?></p>
                <p class="text-xs text-slate-500 font-medium mt-1"><?= $age ?> (<?= date('d/m/Y', strtotime($patient['date_naissance'])) ?>)</p>
            </div>

            <form action="" method="POST" class="space-y-4">
                <input type="hidden" name="action_update_profil" value="1">
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1 <?= ($lang==='ar')?'mr-1':'ml-1' ?>"><?= $txt[$lang]['phone'] ?></label>
                    <input type="text" name="telephone" required value="<?= htmlspecialchars($patient['telephone'] ?? '') ?>" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold outline-none focus:border-[#00966b] bg-slate-50/50">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1 <?= ($lang==='ar')?'mr-1':'ml-1' ?>"><?= $txt[$lang]['weight'] ?></label>
                    <input type="number" step="0.1" name="poids" value="<?= htmlspecialchars($patient['poids'] ?? '') ?>" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold outline-none focus:border-[#00966b] bg-slate-50/50">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1 <?= ($lang==='ar')?'mr-1':'ml-1' ?>"><?= $txt[$lang]['address'] ?></label>
                    <input type="text" name="adresse" required value="<?= htmlspecialchars($patient['adresse'] ?? '') ?>" class="w-full p-2.5 rounded-xl border border-slate-200 text-xs font-semibold outline-none focus:border-[#00966b] bg-slate-50/50">
                </div>
                <button type="submit" class="w-full py-2.5 bg-[#00966b] hover:bg-[#00825c] text-white font-bold text-xs uppercase rounded-xl transition shadow-md">
                    <?= $txt[$lang]['save'] ?>
                </button>
            </form>
        </div>

        <div class="md:col-span-2 space-y-6">
            
            <div class="text-center py-4 space-y-2">
                <h2 class="text-[28px] font-extrabold text-slate-800 tracking-tight"><?= $txt[$lang]['search_title'] ?></h2>
                <p class="text-sm text-slate-400 font-medium"><?= $txt[$lang]['search_sub'] ?></p>

                <div class="flex items-center gap-3 max-w-2xl mx-auto pt-4">
                    <div class="relative flex-1 bg-white rounded-full border border-slate-200 shadow-md p-1.5 flex items-center">
                        <span class="px-3 text-slate-400 text-base"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" id="searchInputGlobal" onkeypress="if(event.key === 'Enter') lancerRechercheGlobal()" placeholder="<?= $txt[$lang]['placeholder'] ?>" class="w-full py-2 bg-transparent outline-none text-sm font-medium text-slate-800">
                        <button onclick="lancerRechercheGlobal()" class="bg-[#00966b] text-white font-extrabold px-6 py-2.5 rounded-full text-xs uppercase tracking-wider mx-1">
                            <?= $txt[$lang]['btn_search'] ?>
                        </button>
                    </div>
                    <button onclick="ouvrirScanner()" class="w-12 h-12 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center text-[#00966b] hover:bg-[#00966b] hover:text-white transition shadow-sm text-lg" title="Scanner QR Code">
                        <i class="fa-solid fa-qrcode"></i>
                    </button>
                </div>
            </div>

            <div id="zoneResultatsPharmacie" class="space-y-3"></div>

            <div class="space-y-3 pt-4">
                <div class="flex justify-between items-center bg-slate-900 text-white p-4 rounded-2xl shadow-sm">
                    <h3 class="text-xs font-black tracking-tight flex items-center gap-2 uppercase">
                        <i class="fa-solid fa-file-medical text-emerald-400"></i> <?= $txt[$lang]['panel_title'] ?>
                    </h3>
                    <span class="bg-emerald-600/20 text-emerald-300 text-[11px] font-bold px-2.5 py-0.5 rounded-md">
                        <?= count($ordonnances) ?> <?= $txt[$lang]['total'] ?>
                    </span>
                </div>

                <div class="space-y-3">
                    <?php if (empty($ordonnances)): ?>
                        <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center text-xs text-slate-400 italic shadow-sm">
                            <?= $txt[$lang]['empty'] ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ordonnances as $ord): 
                            $date_ord = date('d/m/Y', strtotime($ord['date_creation']));
                            $medocs_liste = json_decode($ord['contenu_medocs'], true);
                            $noms = array_map(function($m) { return $m['nom_medicament'] ?? ''; }, $medocs_liste ?? []);
                            $statut_trad = ($lang === 'ar') ? (($ord['statut'] === 'Validée') ? 'مقبولة' : 'قيد الانتظار') : $ord['statut'];
                            
                            $res_statut = $ord['statut_reservation'] ?? '';
                            $is_deja_reserve = (strcasecmp($res_statut, 'Prêt') == 0 || strcasecmp($res_statut, 'accepte') == 0 || strcasecmp($res_statut, 'accepter') == 0 || strcasecmp($res_statut, 'valide') == 0);
                        ?>
                            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-stretch md:items-center gap-5">
                                <div class="space-y-3 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-2">
                                        <span class="text-[11px] font-bold text-[#00966b] bg-emerald-50 px-2.5 py-0.5 rounded-md">ID : #<?= $ord['id_ordonnance'] ?></span>
                                        <span class="text-xs font-medium text-slate-400"><i class="fa-regular fa-calendar mx-1"></i><?= $date_ord ?></span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full <?= $ord['statut'] === 'Validée' ? 'bg-emerald-50 text-[#00966b]' : 'bg-amber-50 text-amber-600' ?>">
                                            <?= htmlspecialchars($statut_trad) ?>
                                        </span>
                                    </div>

                                    <div class="bg-slate-50/60 p-2.5 rounded-xl border border-slate-100">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider"><?= $txt[$lang]['prescriber'] ?></p>
                                        <p class="text-xs font-extrabold text-slate-800"><?= ($lang === 'ar' ? 'د. ' : 'Dr. ') . htmlspecialchars($ord['medecin_nom']) ?></p>
                                    </div>

                                    <div class="space-y-0.5 px-1">
                                        <div class="text-xs text-slate-600 font-medium">
                                            <span class="font-black text-slate-400 text-[10px] uppercase tracking-wider block mb-0.5"><?= $txt[$lang]['treatments'] ?></span>
                                            <?= htmlspecialchars(implode(($lang === 'ar' ? ' ، ' : ', '), $noms)); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-2 justify-center min-w-[200px]">
                                    <a href="voir_ordonnance.php?token=<?= urlencode($ord['qr_token']) ?>" class="w-full text-center bg-slate-50 hover:bg-[#00966b] text-slate-700 hover:text-white border border-slate-200 font-bold px-4 py-3 rounded-xl text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 shadow-sm">
                                        <i class="fa-solid fa-eye text-[#00966b]"></i> 
                                        <span><?= $txt[$lang]['btn_view'] ?></span>
                                    </a>

                                    <?php if ($is_deja_reserve): ?>
                                        <button disabled class="w-full text-center bg-slate-100 text-slate-400 border border-slate-200 font-bold px-4 py-3 rounded-xl text-xs uppercase tracking-wider cursor-not-allowed flex items-center justify-center gap-1.5 shadow-inner">
                                            <i class="fa-solid fa-lock text-slate-300"></i>
                                            <span><?= $txt[$lang]['btn_reserved'] ?></span>
                                        </button>
                                    <?php else: ?>
                                        <a href="recherche_pharmacie.php?id_ordonnance=<?= urlencode($ord['id_ordonnance']) ?>" class="w-full text-center bg-[#00966b] hover:bg-[#00825c] text-white font-extrabold px-4 py-3 rounded-xl text-xs uppercase tracking-wider transition shadow-md">
                                            <i class="fa-solid fa-map-location-dot mx-1"></i>
                                            <span><?= $txt[$lang]['btn_find'] ?></span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <div id="scanner-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl border border-slate-100 overflow-hidden relative p-6 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2 text-slate-800">
                    <i class="fa-solid fa-camera text-emerald-600 text-lg"></i>
                    <h3 class="font-extrabold text-lg tracking-tight"><?= $txt[$lang]['modal_title'] ?></h3>
                </div>
                <button onclick="fermerScanner()" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-50 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="w-full bg-slate-900 rounded-[1.5rem] overflow-hidden aspect-square relative border border-slate-200">
                <div id="reader" class="w-full h-full"></div>
            </div>

            <div id="scan-status" class="text-center text-xs font-bold text-amber-500 py-1 uppercase tracking-wider animate-pulse">
                <i class="fa-solid fa-circle-notch animate-spin text-emerald-600 mr-1"></i> <?= $txt[$lang]['modal_status_init'] ?>
            </div>

            <div class="pt-2">
                <button onclick="fermerScanner()" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 py-3 rounded-xl text-xs font-bold uppercase tracking-wider transition">
                    <?= $txt[$lang]['modal_close'] ?>
                </button>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    let html5QrCode = null;
    const currentLang = "<?= $lang ?>";

    // --- CORRECTION : AJOUT DE LA RECHERCHE TEXTUELLE GLOBALE VIA AJAX ---
    function lancerRechercheGlobal() {
        const input = document.getElementById('searchInputGlobal');
        const query = input.value.trim();
        const zone = document.getElementById('zoneResultatsPharmacie');

        if (query === "") {
            zone.innerHTML = "";
            return;
        }

        zone.innerHTML = `
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-center text-xs font-bold text-slate-500">
                <i class="fa-solid fa-circle-notch animate-spin text-[#00966b] mr-2 text-sm"></i>
                ${currentLang === 'ar' ? 'جاري البحث...' : 'Recherche en cours...'}
            </div>`;

        // Requête vers votre fichier existant de traitement
        fetch(`ajax_recherche.php?query=${encodeURIComponent(query)}`)
        .then(response => response.text())
        .then(html => {
            zone.innerHTML = html;
        })
        .catch(err => {
            console.error("Erreur de recherche:", err);
            zone.innerHTML = `
                <div class="bg-red-50 text-red-700 p-4 rounded-xl text-xs font-bold border border-red-100">
                    ${currentLang === 'ar' ? 'حدث خطأ أثناء البحث.' : 'Une erreur est survenue pendant la recherche.'}
                </div>`;
        });
    }

    // --- PARTIE 1 : SCANNER QR ---
    function ouvrirScanner() {
        const modal = document.getElementById('scanner-modal');
        modal.classList.remove('hidden');

        if (html5QrCode === null) {
            html5QrCode = new Html5Qrcode("reader");
        }

        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        html5QrCode.start(
            { facingMode: "environment" }, 
            config, 
            (decodedText) => {
                const destination = "recherche_qr.php?token=" + encodeURIComponent(decodedText);
                window.location.href = destination;
            },
            (errorMessage) => { /* Scan en cours... */ }
        ).catch(err => {
            console.error("Erreur caméra :", err);
            alert(currentLang === 'ar' ? "فشل الاتصال بالكاميرا." : "Accès caméra refusé ou indisponible.");
            modal.classList.add('hidden');
        });
    }

    function fermerScanner() {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                document.getElementById('scanner-modal').classList.add('hidden');
            }).catch(() => {
                document.getElementById('scanner-modal').classList.add('hidden');
            });
        } else {
            document.getElementById('scanner-modal').classList.add('hidden');
        }
    }

    // --- PARTIE 2 : SYSTEME DE NOTIFICATIONS OPÉRATIONNEL ---
    function togglePatientNotifs(e) {
        if(e) e.stopPropagation();
        const panel = document.getElementById('patientNotifPanel');
        const badge = document.getElementById('patientBadge');
        
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) {
            badge.classList.add('hidden'); 
        }
    }

    function checkMyReservations() {
        fetch('check_patient_notifications.php')
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('patientNotifList');
            const badge = document.getElementById('patientBadge');
            const isPanelHidden = document.getElementById('patientNotifPanel').classList.contains('hidden');
            
            if (data && data.length > 0) {
                if (isPanelHidden) {
                    badge.textContent = data.length;
                    badge.classList.remove('hidden');
                }
                
                let html = '';
                data.forEach(notif => {
                    const isValide = notif.statut === 'valide' || notif.statut === 'Prêt' || notif.statut === 'accepte';
                    const colorClass = isValide ? 'emerald' : 'red';
                    const statusText = isValide ? (currentLang === 'ar' ? 'مقبولة' : 'Confirmée') : (currentLang === 'ar' ? 'مرفوضة' : 'Refusée');

                    html += `
                    <div class="p-3 rounded-xl mb-2 border border-${colorClass}-100 bg-${colorClass}-50/30 text-left" dir="ltr">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[9px] font-black text-${colorClass}-600 uppercase tracking-wider">${statusText}</span>
                            <span class="text-[8px] text-slate-400">${notif.date_commande || ''}</span>
                        </div>
                        <p class="text-[11px] font-bold text-slate-800">${notif.medicament_demande}</p>
                        <p class="text-[9px] text-slate-500 mb-2">${notif.nom_pharmacie || ''}</p>
                        <button onclick="marquerCommeVu(${notif.id}, event)" class="text-[9px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-wider">
                            ${currentLang === 'ar' ? 'قرأت الرسالة' : "Ok, j'ai vu"}
                        </button>
                    </div>`;
                });
                list.innerHTML = html;
            } else {
                list.innerHTML = `<p class="text-center py-6 text-xs text-slate-400 italic">${currentLang === 'ar' ? 'لا توجد تحديثات جديدة' : 'Aucune nouvelle mise à jour'}</p>`;
                badge.classList.add('hidden');
            }
        })
        .catch(err => console.error("Erreur de vérification des notifications:", err));
    }

    function marquerCommeVu(idRes, event) {
        if(event) event.stopPropagation();
        fetch('marquer_vu_patient.php?id=' + idRes)
        .then(() => checkMyReservations()); 
    }

    // Fermeture automatique du panneau au clic n'importe où
    document.addEventListener('click', () => {
        document.getElementById('patientNotifPanel').classList.add('hidden');
    });

    // Interroger toutes les 10 secondes
    setInterval(checkMyReservations, 10000);
    checkMyReservations();
    </script>
</body>
</html>
