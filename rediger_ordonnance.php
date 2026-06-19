<?php
require_once 'config/db.php';

// FORCE L'UTF-8 : Indispensable pour éviter que les accents ne cassent les noms des médicaments
$pdo->exec("SET NAMES utf8mb4");

// 1. Vérification de la session et de la sécurité
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'medecin') {
    header('Location: login.php');
    exit();
}

$id_medecin = $_SESSION['id_utilisateur'] ?? 0;
$nom_affichage_medecin = $_SESSION['nom_affichage'] ?? 'Médecin';

// 2. Récupération de l'ID du patient depuis l'URL
$id_patient = isset($_GET['id_patient']) ? intval($_GET['id_patient']) : 0;

if ($id_patient === 0) {
    header('Location: space_medecin.php');
    exit();
}

// 3. Traitement du formulaire (Enregistrement + Mise à jour du poids + Token QR)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $medicaments = $_POST['medocs'] ?? [];
    $posologies = $_POST['posologies'] ?? [];
    $durees = $_POST['durees'] ?? [];
    $nouveau_poids = isset($_POST['poids_patient']) ? floatval($_POST['poids_patient']) : null;

    // Mise à jour du poids du patient si renseigné
    if ($nouveau_poids > 0) {
        $stmt_poids = $pdo->prepare("UPDATE patients SET poids = ? WHERE id_patient = ?");
        $stmt_poids->execute([$nouveau_poids, $id_patient]);
    }

    // Construction du tableau associatif pour le JSON
    $contenu_ordonnance = [];
    for ($i = 0; $i < count($medicaments); $i++) {
        $nom_saisi = isset($medicaments[$i]) ? trim($medicaments[$i]) : '';

        // Sécurité : évite les lignes vides accidentelles
        if (!empty($nom_saisi)) {
            $contenu_ordonnance[] = [
                'nom_medicament' => $nom_saisi,
                'posologie' => trim($posologies[$i] ?? ''),
                'duree' => trim($durees[$i] ?? '')
            ];
        }
    }

    if (!empty($contenu_ordonnance)) {
        // Encodage JSON avec conservation des caractères spéciaux (accents)
        $json_contenu = json_encode($contenu_ordonnance, JSON_UNESCAPED_UNICODE);
        
        // --- SÉCURITÉ CLICKPHARMA : Génération du Token Unique ---
        $qr_token = bin2hex(random_bytes(16));
        
        // Insertion en base de données avec le token généré
        $stmt_ins = $pdo->prepare("INSERT INTO ordonnances (id_patient, id_medecin, medecin_nom, contenu_medocs, qr_token, date_creation, statut) VALUES (?, ?, ?, ?, ?, NOW(), 'En attente')");
        
        $stmt_ins->execute([
            $id_patient, 
            $id_medecin, 
            $nom_affichage_medecin, 
            $json_contenu, 
            $qr_token
        ]);

        // Redirection directe vers la page de visualisation et d'impression avec le QR code
        header("Location: voir_ordonnance.php?token=" . $qr_token);
        exit();
    } else {
        $error = "Veuillez sélectionner au moins un médicament avec un nom valide.";
    }
}

// 4. Récupération des informations du patient
$stmt = $pdo->prepare("SELECT * FROM patients WHERE id_patient = ?");
$stmt->execute([$id_patient]);
$patient = $stmt->fetch();

if (!$patient) {
    die("Erreur : Patient introuvable.");
}

$age = !empty($patient['date_naissance']) ? (new DateTime())->diff(new DateTime($patient['date_naissance']))->y . " ans" : "N/A";

// 5. Récupération des médicaments pour le sélecteur
$liste_medicaments = $pdo->query("SELECT id_medoc, nom_medicament, forme FROM medicaments ORDER BY nom_medicament ASC")->fetchAll();

// Tableau PHP des posologies prédéfinies pour la liste déroulante
$choix_posologies = [
    "1 comp 3x/jour",
    "1 comp matin et soir",
    "1 comp le matin",
    "1 comp au coucher",
    "1 gélule 3x/jour",
    "1 gélule matin et soir",
    "1 cuillère à café 3x/jour",
    "1 cuillère à soupe 3x/jour",
    "1 sachet 2x/jour",
    "1 application soir",
    "Selon schéma thérapeutique"
];

// 6. Récupération de l'historique complet pour la colonne de droite
$stmt_hist = $pdo->prepare("SELECT * FROM ordonnances WHERE id_patient = ? ORDER BY date_creation DESC");
$stmt_hist->execute([$id_patient]);
$historique_ordonnances = $stmt_hist->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Prescription | Click Pharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="bg-indigo-700 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="images/Pharma (1).png" alt="Logo" class="h-11 w-11 rounded-xl bg-white p-0.5 object-contain shadow-inner" onerror="this.src='Pharma (1).png'">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight leading-none">Click Pharma</h1>
                    <span class="text-indigo-200 text-xs font-medium">Rédaction d'Ordonnance</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[9px] uppercase font-black text-indigo-200 tracking-wider">Médecin Prescripteur</p>
                    <p class="font-bold text-sm">Dr. <?= htmlspecialchars($nom_affichage_medecin) ?></p>
                </div>
                <a href="space_medecin.php" class="bg-indigo-800 hover:bg-indigo-600 p-2.5 rounded-xl transition text-xs shadow-inner">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
            </div>
        </div>
    </nav>

    <form action="" method="POST" class="container mx-auto mt-8 p-4 max-w-6xl grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between gap-4 bg-gradient-to-r from-white to-indigo-50/10">
                <div>
                    <span class="text-[10px] font-black uppercase text-indigo-600 tracking-widest block mb-1">Patient Sélectionné</span>
                    <h2 class="text-xl font-extrabold text-slate-800"><?= htmlspecialchars($patient['nom'] ?? '') ?> <?= htmlspecialchars($patient['prenom'] ?? '') ?></h2>
                    <p class="text-slate-500 text-xs mt-1 font-mono">NSS : <?= htmlspecialchars($patient['nss'] ?? '') ?></p>
                </div>
                
                <div class="flex items-center gap-4 text-xs bg-white px-4 py-2.5 rounded-2xl border border-slate-100 shadow-sm self-start sm:self-center">
                    <div>
                        <span class="block text-[9px] uppercase font-bold text-slate-400 mb-0.5">Âge</span>
                        <span class="font-bold text-slate-700"><?= $age ?></span>
                    </div>
                    <div class="border-l border-slate-200 h-6"></div>
                    
                    <div>
                        <label class="block text-[9px] uppercase font-bold text-indigo-600 mb-0.5">Poids (kg)</label>
                        <input type="number" name="poids_patient" step="0.1" min="1" max="250" 
                               value="<?= htmlspecialchars($patient['poids'] ?? '') ?>" 
                               class="w-16 px-2 py-0.5 font-bold text-slate-700 bg-indigo-50/50 border border-indigo-100 rounded-lg focus:bg-white focus:border-indigo-500 outline-none transition text-center">
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-slate-200 space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-extrabold text-slate-800">Traitements & Médicaments</h3>
                    <p class="text-xs text-slate-400">Sélectionnez les produits et configurez les posologies.</p>
                </div>

                <?php if(!empty($error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl text-xs font-semibold">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div id="conteneur-traitements" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-slate-50/60 p-4 rounded-2xl border border-slate-100 relative entry-row">
                        <div class="md:col-span-5">
                            <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5 ml-1">Nom du Médicament</label>
                            <select name="medocs[]" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 bg-white outline-none text-xs font-medium transition">
                                <option value="">-- Choisir un médicament --</option>
                                <?php foreach($liste_medicaments as $medoc): 
                                    $affichage_med = $medoc['nom_medicament'] . (!empty($medoc['forme']) ? " (" . $medoc['forme'] . ")" : "");
                                ?>
                                    <option value="<?= htmlspecialchars($affichage_med, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($affichage_med) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="md:col-span-4">
                            <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5 ml-1">Posologie</label>
                            <select name="posologies[]" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 bg-white outline-none text-xs font-medium transition">
                                <option value="">-- Posologie --</option>
                                <?php foreach($choix_posologies as $poso): ?>
                                    <option value="<?= htmlspecialchars($poso) ?>"><?= htmlspecialchars($poso) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5 ml-1">Durée</label>
                            <input type="text" name="durees[]" required placeholder="Ex: 7 jours" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 bg-white outline-none text-xs font-medium transition">
                        </div>
                        <div class="md:col-span-1 text-right">
                            <button type="button" onclick="supprimerLigne(this)" class="p-2.5 bg-white border border-slate-200 text-slate-400 hover:text-red-500 rounded-xl transition text-xs shadow-sm">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="ajouterLigne()" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 font-bold px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider transition">
                    <i class="fa-solid fa-plus text-xs"></i> Ajouter un médicament
                </button>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="space_medecin.php" class="px-5 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold text-xs uppercase hover:bg-slate-200 transition">Annuler</a>
                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow-md">
                        Enregistrer l'ordonnance
                    </button>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-slate-900 text-white p-5 rounded-3xl shadow-sm">
                <h3 class="text-sm font-extrabold tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-indigo-400"></i> Historique Médical
                </h3>
                <p class="text-[11px] text-slate-400 mt-1">Anciennes ordonnances délivrées à ce patient.</p>
            </div>

            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-1">
                <?php if (empty($historique_ordonnances)): ?>
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 text-center text-xs text-slate-400 italic">
                        Aucun antécédent d'ordonnance trouvé.
                    </div>
                <?php else: ?>
                    <?php foreach ($historique_ordonnances as $ord): 
                        $date_ord = date('d/m/Y', strtotime($ord['date_creation']));
                        $items = json_decode($ord['contenu_medocs'], true);
                    ?>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-2.5">
                            <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                                <div>
                                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md"><?= $date_ord ?></span>
                                    <p class="text-[10px] text-slate-400 mt-1">Par: Dr. <?= htmlspecialchars($ord['medecin_nom'] ?? 'Inconnu') ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] font-medium px-2 py-0.5 rounded-full block <?= $ord['statut'] === 'Validée' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' ?>">
                                        <?= htmlspecialchars($ord['statut']) ?>
                                    </span>
                                    <?php if(!empty($ord['qr_token'])): ?>
                                        <span class="text-[8px] text-slate-400 font-mono block mt-1"><i class="fa-solid fa-qrcode text-indigo-500"></i> Code Actif</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="space-y-1.5">
                                <?php if (!empty($items) && is_array($items)): ?>
                                    <?php foreach ($items as $item): 
                                        // Résolution dynamique du nom pour parer à tout changement de clé JSON
                                        if (!empty($item['nom_medicament'])) {
                                            $nom_med = $item['nom_medicament'];
                                        } elseif (!empty($item['medoc'])) {
                                            $nom_med = $item['medoc'];
                                        } elseif (!empty($item['nom'])) {
                                            $nom_med = $item['nom'];
                                        } else {
                                            $valeurs_filtres = array_filter($item);
                                            $nom_med = (!empty($valeurs_filtres)) ? current($valeurs_filtres) : 'Médicament';
                                        }
                                    ?>
                                        <div class="text-[11px] text-slate-700 bg-slate-50 p-2 rounded-lg border border-slate-100/50">
                                            <p class="font-bold text-slate-800">
                                                <i class="fa-solid fa-pills text-slate-400 mr-1"></i> 
                                                <?= htmlspecialchars($nom_med) ?>
                                            </p>
                                            <p class="text-slate-500 text-[10px] pl-4">
                                                Posologie: <?= htmlspecialchars($item['posologie'] ?? '') ?> (<?= htmlspecialchars($item['duree'] ?? '') ?>)
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </form>

    <script>
    const optionsMedicaments = `
        <option value="">-- Choisir un médicament --</option>
        <?php foreach($liste_medicaments as $medoc): 
            $affichage_med = $medoc['nom_medicament'] . (!empty($medoc['forme']) ? " (" . $medoc['forme'] . ")" : "");
        ?>
            <option value="<?= htmlspecialchars($affichage_med, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($affichage_med) ?></option>
        <?php endforeach; ?>
    `;

    const optionsPosologies = `
        <option value="">-- Posologie --</option>
        <?php foreach($choix_posologies as $poso): ?>
            <option value="<?= htmlspecialchars($poso) ?>"><?= htmlspecialchars($poso) ?></option>
        <?php endforeach; ?>
    `;

    function ajouterLigne() {
        const conteneur = document.getElementById('conteneur-traitements');
        const nouvelleLigne = document.createElement('div');
        nouvelleLigne.className = "grid grid-cols-1 md:grid-cols-12 gap-3 items-end bg-slate-50/60 p-4 rounded-2xl border border-slate-100 relative entry-row opacity-0 scale-95 transition-all duration-200";
        
        nouvelleLigne.innerHTML = `
            <div class="md:col-span-5">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5 ml-1">Nom du Médicament</label>
                <select name="medocs[]" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 bg-white outline-none text-xs font-medium transition">
                    ${optionsMedicaments}
                </select>
            </div>
            <div class="md:col-span-4">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5 ml-1">Posologie</label>
                <select name="posologies[]" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 bg-white outline-none text-xs font-medium transition">
                    ${optionsPosologies}
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5 ml-1">Durée</label>
                <input type="text" name="durees[]" required placeholder="Ex: 5 jours" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:border-indigo-500 bg-white outline-none text-xs font-medium transition">
            </div>
            <div class="md:col-span-1 text-right">
                <button type="button" onclick="supprimerLigne(this)" class="p-2.5 bg-white border border-slate-200 text-slate-400 hover:text-red-500 rounded-xl transition text-xs shadow-sm">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        `;
        
        conteneur.appendChild(nouvelleLigne);
        setTimeout(() => { nouvelleLigne.classList.remove('opacity-0', 'scale-95'); }, 10);
    }

    function supprimerLigne(bouton) {
        const lignes = document.querySelectorAll('.entry-row');
        if (lignes.length > 1) {
            const ligne = bouton.closest('.entry-row');
            ligne.classList.add('opacity-0', 'scale-95');
            setTimeout(() => { ligne.remove(); }, 200);
        } else {
            alert("Une ordonnance doit contenir au moins un médicament.");
        }
    }
    </script>
</body>
</html>