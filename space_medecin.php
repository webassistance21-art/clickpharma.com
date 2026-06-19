<?php
require_once 'config/setup.php';
require_once 'config/db.php';

// Sécurité d'accès
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'medecin') {
    header('Location: login.php');
    exit();
}

$id_medecin = $_SESSION['id_utilisateur'] ?? 0;
$nom_affichage_medecin = $_SESSION['nom_affichage'] ?? 'Médecin';

// Statistiques
$stmt_today = $pdo->prepare("SELECT COUNT(*) FROM ordonnances WHERE id_medecin = ? AND DATE(date_creation) = CURDATE()");
$stmt_today->execute([$id_medecin]);
$count_today = $stmt_today->fetchColumn();

$total_patients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();

// 5 récents patients par défaut
$stmt_recents = $pdo->query("SELECT id_patient, nom, prenom, nss, date_naissance, poids FROM patients ORDER BY id_patient DESC LIMIT 5");
$patients_recents = $stmt_recents->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Médecin | <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="bg-indigo-700 text-white p-4 shadow-lg sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="<?= LOGO_PATH ?>" alt="Logo" class="h-11 w-11 rounded-xl bg-white p-0.5 object-contain shadow-inner">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight leading-none"><?= APP_NAME ?></h1>
                    <span class="text-indigo-200 text-xs font-medium">Espace Médical</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[9px] uppercase font-black text-indigo-200 tracking-wider">Session Professionnelle</p>
                    <p class="font-bold text-sm">Dr. <?= e($nom_affichage_medecin) ?></p>
                </div>
                <a href="logout.php" class="bg-indigo-800 hover:bg-red-600 p-2.5 rounded-xl transition text-xs shadow-inner" title="Déconnexion">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8 p-4 max-w-5xl space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white p-6 rounded-2xl flex flex-col justify-between shadow-md">
                <div>
                    <h2 class="text-lg font-bold">Cabinet de Consultation</h2>
                    <p class="text-slate-400 text-xs mt-1">Gérez vos dossiers de prescriptions en direct.</p>
                </div>
                <span class="text-2xl self-end mt-2">🩺</span>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Prescriptions (Aujourd'hui)</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5"><?= $count_today ?></h3>
                </div>
                <div class="bg-indigo-50 text-indigo-600 p-3.5 rounded-xl text-base"><i class="fa-solid fa-file-medical"></i></div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Patients (Total Base)</span>
                    <h3 class="text-2xl font-black text-slate-800 mt-0.5"><?= $total_patients ?></h3>
                </div>
                <div class="bg-emerald-50 text-emerald-600 p-3.5 rounded-xl text-base"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-slate-200 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
                <div>
                    <h3 class="text-base font-extrabold text-slate-800">Dossiers et Recherche Patients</h3>
                    <p class="text-xs text-slate-400">Tapez un mot-clé pour filtrer instantanément la base patient.</p>
                </div>
                <a href="ajouter_patient.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider transition shadow-sm text-center">
                    + Nouveau Patient
                </a>
            </div>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" id="barre-recherche" oninput="effectuerRecherche()" 
                       placeholder="Rechercher par nom, prénom ou Numéro de Sécurité Sociale (NSS)..." 
                       class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-150 outline-none transition bg-slate-50 text-slate-800 text-sm font-medium">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black uppercase text-slate-400 border-b border-slate-100">
                            <th class="pb-2.5 pl-2">Identité Patient</th>
                            <th class="pb-2.5">NSS</th>
                            <th class="pb-2.5">Âge</th>
                            <th class="pb-2.5">Poids</th>
                            <th class="pb-2.5 text-right pr-2">Prescription</th>
                        </tr>
                    </thead>
                    <tbody id="liste-patients-corps" class="divide-y divide-slate-100 text-xs">
                        <?php foreach ($patients_recents as $p): 
                            $age = !empty($p['date_naissance']) ? (new DateTime())->diff(new DateTime($p['date_naissance']))->y . " ans" : "N/A";
                        ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 pl-2 font-bold text-slate-800"><?= e($p['nom']) ?> <?= e($p['prenom']) ?></td>
                                <td class="py-3.5 font-mono text-slate-500 text-[11px]"><?= e($p['nss']) ?></td>
                                <td class="py-3.5 text-slate-600 font-medium"><?= $age ?></td>
                                <td class="py-3.5 text-slate-600"><span class="font-bold text-slate-700"><?= e($p['poids'] ?? '--') ?></span> kg</td>
                                <td class="py-3.5 text-right pr-2">
                                    <a href="rediger_ordonnance.php?id_patient=<?= $p['id_patient'] ?>" class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white px-2.5 py-1.5 rounded-lg font-bold transition">
                                        <i class="fa-solid fa-file-pen"></i> Prescrire
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div id="recherche-vide" class="hidden text-center py-6 text-slate-400 italic text-xs">
                <i class="fa-solid fa-user-slash block text-lg mb-1 text-slate-300"></i> Aucun dossier trouvé.
            </div>
        </div>
    </div>

    <script>
    function effectuerRecherche() {
        const query = document.getElementById('barre-recherche').value.trim();
        const corpsTableau = document.getElementById('liste-patients-corps');
        const messageVide = document.getElementById('recherche-vide');

        fetch(`rechercher_patients.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                corpsTableau.innerHTML = '';
                if(data.length === 0) { messageVide.classList.remove('hidden'); return; }
                messageVide.classList.add('hidden');

                data.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.className = "hover:bg-slate-50/80 transition";
                    tr.innerHTML = `
                        <td class="py-3.5 pl-2 font-bold text-slate-800">${escapeHtml(p.nom)} ${escapeHtml(p.prenom)}</td>
                        <td class="py-3.5 font-mono text-slate-500 text-[11px]">${escapeHtml(p.nss)}</td>
                        <td class="py-3.5 text-slate-600 font-medium">${p.age}</td>
                        <td class="py-3.5 text-slate-600"><span class="font-bold text-slate-700">${p.poids ? escapeHtml(p.poids.toString()) : '--'}</span> kg</td>
                        <td class="py-3.5 text-right pr-2">
                            <a href="rediger_ordonnance.php?id_patient=${p.id_patient}" class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white px-2.5 py-1.5 rounded-lg font-bold transition">
                                <i class="fa-solid fa-file-pen"></i> Prescrire
                            </a>
                        </td>
                    `;
                    corpsTableau.appendChild(tr);
                });
            }).catch(err => console.error("Erreur AJAX :", err));
    }

    function escapeHtml(text) {
        if(!text) return '';
        return text.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;");
    }
    </script>
</body>
</html>