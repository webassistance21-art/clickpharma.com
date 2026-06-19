<?php
require_once 'config/db.php';
$pdo->exec("SET NAMES utf8mb4");

session_start();

// 1. Récupération du jeton QR depuis l'URL
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    die("Erreur : Ordonnance introuvable ou lien invalide.");
}

// 2. Récupération de l'ordonnance, du patient ET des détails du médecin
$stmt = $pdo->prepare("
    SELECT o.*, 
           p.nom AS patient_nom, p.prenom AS patient_prenom, p.date_naissance, p.nss, p.poids,
           m.nom AS medecin_nom_reel, m.prenom AS medecin_prenom_reel, m.specialite, m.telephone, m.adresse_cabinet, m.ville
    FROM ordonnances o
    JOIN patients p ON o.id_patient = p.id_patient
    LEFT JOIN medecins m ON o.id_medecin = m.id_utilisateur
    WHERE o.qr_token = ?
");
$stmt->execute([$token]);
$ordonnance = $stmt->fetch();

if (!$ordonnance) {
    die("Erreur : Aucune ordonnance ne correspond à ce code de sécurité.");
}

// Reconstruction de l'identité du médecin
$nom_complet_medecin = !empty($ordonnance['medecin_nom_reel']) ? "Dr. " . $ordonnance['medecin_nom_reel'] . " " . $ordonnance['medecin_prenom_reel'] : "Dr. " . ($ordonnance['medecin_nom'] ?? 'Médecin Prescripteur');
$specialite = !empty($ordonnance['specialite']) ? $ordonnance['specialite'] : "Médecin Généraliste";
$telephone = !empty($ordonnance['telephone']) ? $ordonnance['telephone'] : "Non renseigné";
$adresse = !empty($ordonnance['adresse_cabinet']) ? $ordonnance['adresse_cabinet'] . ", " . $ordonnance['ville'] : "Structure ClickPharma";

// Calcul de l'âge du patient
$age = !empty($ordonnance['date_naissance']) ? (new DateTime())->diff(new DateTime($ordonnance['date_naissance']))->y . " ans" : "N/A";
$date_prescription = date('d/m/Y à H:i', strtotime($ordonnance['date_creation']));

// Décodage des médicaments du format JSON pour l'affichage HTML
$medicaments = json_decode($ordonnance['contenu_medocs'], true);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordonnance_<?= htmlspecialchars($ordonnance['patient_nom']) ?>_ClickPharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .print-card { border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 print:py-0">

    <div class="max-w-2xl mx-auto mb-6 px-4 flex flex-col sm:flex-row justify-between items-center gap-4 no-print">
        <a href="space_medecin.php" class="w-full sm:w-auto text-center text-slate-600 hover:text-indigo-600 font-bold text-xs uppercase bg-white px-4 py-2.5 rounded-xl border transition shadow-sm">
            <i class="fa-solid fa-arrow-left"></i> Retour Espace
        </a>
        
        <div class="flex w-full sm:w-auto gap-3">
            <button onclick="window.print()" class="flex-1 sm:flex-none bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-md transition">
                <i class="fa-solid fa-print"></i> Imprimer
            </button>
            <button onclick="telechargerPDF()" class="flex-1 sm:flex-none bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl flex items-center justify-center gap-2 shadow-md transition">
                <i class="fa-solid fa-download"></i> Sauvegarder (PDF)
            </button>
        </div>
    </div>

    <div id="ordonnance-zone" class="max-w-2xl mx-auto bg-white p-8 md:p-12 rounded-3xl border border-slate-200 shadow-xl print:shadow-none print:border-none print:p-0 relative min-h-[840px] flex flex-col justify-between print-card">
        
        <div>
            <div class="flex justify-between items-start border-b-2 border-slate-100 pb-6">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight"><?= htmlspecialchars($nom_complet_medecin) ?></h1>
                    <p class="text-xs text-indigo-600 font-semibold mt-0.5"><?= htmlspecialchars($specialite) ?></p>
                    
                    <p class="text-[11px] text-slate-500 mt-3 font-medium leading-relaxed space-y-1">
                        <span class="block"><i class="fa-solid fa-location-dot text-indigo-500/70 mr-1.5 w-3"></i>Cabinet : <?= htmlspecialchars($adresse) ?></span>
                        <span class="block"><i class="fa-solid fa-phone text-indigo-500/70 mr-1.5 w-3"></i>Tel : <?= htmlspecialchars($telephone) ?></span>
                    </p>
                </div>
                
                <div class="text-right">
                    <p class="text-[10px] text-slate-400 font-mono">Ordonnance N° : #<?= $ordonnance['id_ordonnance'] ?></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 my-8 bg-slate-50/70 p-5 rounded-2xl border border-slate-100">
                <div>
                    <span class="text-[9px] font-bold uppercase text-slate-400 block tracking-wider mb-1">Patient</span>
                    <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($ordonnance['patient_nom']) ?> <?= htmlspecialchars($ordonnance['patient_prenom']) ?></p>
                    <p class="text-xs text-slate-500 font-medium mt-1"><?= $age ?> — <?= htmlspecialchars($ordonnance['poids'] ?? 'N/A') ?> kg</p>
                    <p class="text-[10px] text-slate-400 font-mono mt-0.5">NSS : <?= htmlspecialchars($ordonnance['nss'] ?? '') ?></p>
                </div>
                <div class="text-right">
                    <span class="text-[9px] font-bold uppercase text-slate-400 block tracking-wider mb-1">Délivrée le</span>
                    <p class="text-xs font-bold text-slate-700"><?= $date_prescription ?></p>
                    <div class="mt-3">
                        <span class="inline-block px-2.5 py-0.5 text-[10px] font-bold rounded-full <?= $ordonnance['statut'] === 'Validée' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-amber-50 text-amber-600 border border-amber-200' ?>">
                            Statut : <?= htmlspecialchars($ordonnance['statut']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-8 space-y-6 pl-4">
                <h2 class="text-xs font-black uppercase text-slate-400 tracking-widest mb-4">Ordonnance :</h2>
                
                <?php if (!empty($medicaments) && is_array($medicaments)): ?>
                    <?php $count = 1; foreach ($medicaments as $medoc): ?>
                        <div class="relative pl-6">
                            <span class="absolute left-0 top-0 text-xs font-bold text-indigo-600"><?= $count++ ?>.</span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 capitalize"><?= htmlspecialchars($medoc['nom_medicament'] ?? 'Médicament') ?></h3>
                                <p class="text-xs text-slate-600 font-medium mt-1">
                                    <span class="text-slate-400">Posologie :</span> <?= htmlspecialchars($medoc['posologie'] ?? '') ?>
                                </p>
                                <p class="text-xs text-slate-500 font-medium">
                                    <span class="text-slate-400">Durée :</span> <?= htmlspecialchars($medoc['duree'] ?? '') ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-sm text-slate-500 italic">Aucun traitement spécifié sur cette ordonnance.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="border-t-2 border-slate-100 pt-6 mt-12 flex justify-between items-end">
            <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                <div id="qrcode" class="w-28 h-28 bg-white p-1 rounded-xl shadow-inner border flex items-center justify-center"></div>
                <div class="max-w-[220px]">
                    <span class="text-[9px] font-black uppercase text-indigo-600 tracking-wider block"><i class="fa-solid fa-shield-halved"></i> Validation ClickPharma</span>
                    <p class="text-[10px] text-slate-500 mt-1 font-medium leading-tight">Scanner ce code sécurisé en pharmacie pour authentifier la prescription et vérifier la disponibilité des stocks.</p>
                    <p class="text-[8px] font-mono text-slate-400 mt-1.5 break-all">Token de sécurité : <?= htmlspecialchars($token) ?></p>
                </div>
            </div>

            <div class="text-right pb-4 pr-4">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Signature & Cachet</p>
                <div class="h-16 w-32 border-b border-dashed border-slate-300 my-1 mx-auto"></div>
                <p class="text-[11px] font-bold text-slate-700"><?= htmlspecialchars($nom_complet_medecin) ?></p>
            </div>
        </div>

    </div>

    <script type="text/javascript">
        // 1. Préparation de l'objet complet converti depuis PHP (JSON embarqué)
        var donneesOrdonnance = {
            "id_ordonnance": <?= intval($ordonnance['id_ordonnance']) ?>,
            "id_medecin": <?= intval($ordonnance['id_medecin']) ?>,
            "id_patient": <?= intval($ordonnance['id_patient']) ?>,
            "medecin_nom": "<?= htmlspecialchars($nom_complet_medecin, ENT_QUOTES, 'UTF-8') ?>",
            "date_creation": "<?= htmlspecialchars($ordonnance['date_creation'], ENT_QUOTES, 'UTF-8') ?>",
            "statut": "<?= htmlspecialchars($ordonnance['statut'], ENT_QUOTES, 'UTF-8') ?>",
            "qr_token": "<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>",
            "medicaments": <?= json_encode($medicaments, JSON_UNESCAPED_UNICODE) ?>
        };

        // Conversion en chaîne de texte brute JSON pour l'injecter dans le QR code
        var qrContentString = JSON.stringify(donneesOrdonnance);
        var nomPatient = "<?= htmlspecialchars($ordonnance['patient_nom'], ENT_QUOTES, 'UTF-8') ?>";

        // 2. Génération dynamique du QR code complet
        try {
            var qrcodeContainer = document.getElementById("qrcode");
            qrcodeContainer.innerHTML = ""; // Sécurité de nettoyage
            
            new QRCode(qrcodeContainer, {
                text: qrContentString, 
                width: 104, // Ajusté pour s'intégrer harmonieusement dans le conteneur Tailwind
                height: 104,
                colorDark : "#0f172a",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M // Tolérance intermédiaire (15%), idéale pour la quantité de données
            });
        } catch (e) {
            // Repli vers l'API externe si plantage JS avec encodage complet de l'URL
            var fallbackUrl = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" + encodeURIComponent(qrContentString);
            document.getElementById("qrcode").innerHTML = `<img src="${fallbackUrl}" alt="QR" class="w-full h-full object-contain">`;
        }

        // 3. Téléchargement du fichier PDF (Garde sa mise en page parfaite)
        function telechargerPDF() {
            var element = document.getElementById('ordonnance-zone');
            var opt = {
                margin:       0.2,
                filename:     'Ordonnance_' + nomPatient + '.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>

</body>
</html>