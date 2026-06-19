<?php
session_start();
require_once 'config/db.php';

// Sécurité : Vérification de la session de l'admin (table administrateurs)
if (!isset($_SESSION['id_admin'])) {
    // header('Location: login_admin.php');
    // exit;
}

$message = "";

// Action de validation d'un compte professionnel par l'administrateur
if (isset($_GET['action']) && $_GET['action'] === 'valider' && isset($_GET['id']) && isset($_GET['type'])) {
    $id_user = intval($_GET['id']); // Utilisation de l'id_utilisateur global
    $type = $_GET['type']; // 'medecin' ou 'pharmacie'
    
    if ($type === 'medecin') {
        $stmt = $pdo->prepare("UPDATE medecins SET statut = 'actif' WHERE id_utilisateur = ?");
    } else {
        $stmt = $pdo->prepare("UPDATE pharmacies SET statut = 'actif' WHERE id_utilisateur = ?");
    }
    
    if ($stmt->execute([$id_user])) {
        $message = "Le compte professionnel a été validé et activé avec succès !";
    }
}

// Récupérer les demandes en attente de validation (Médecins ET Pharmacies) via un UNION
// ASTUCE : On retire le champ conflictuel pour la pharmacie et on harmonise l'affichage
$queryDemandesPro = "
    SELECT id_utilisateur AS id_specifique, nom, prenom, 'medecin' AS role, document_justificatif 
    FROM medecins 
    WHERE statut = 'en_attente'
    UNION
    SELECT id_utilisateur AS id_specifique, nom_pharmacie AS nom, '' AS prenom, 'pharmacie' AS role, document_justificatif 
    FROM pharmacies 
    WHERE statut = 'en_attente'
";
$demandesPro = $pdo->query($queryDemandesPro)->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste de TOUS les patients avec leur pièce jointe (Carte Chifa / CNI)
$queryPatients = "SELECT id_utilisateur, nom, prenom, nss, telephone, document_justificatif FROM patients ORDER BY id_utilisateur DESC";
$patientsList = $pdo->query($queryPatients)->fetchAll(PDO::FETCH_ASSOC);

// Statistiques globales pour les compteurs
$totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalMedecins = $pdo->query("SELECT COUNT(*) FROM medecins WHERE statut = 'actif'")->fetchColumn();
$totalPharmacies = $pdo->query("SELECT COUNT(*) FROM pharmacies WHERE statut = 'actif'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClickPharma | Espace Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

    <header class="bg-slate-900 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="images/Pharma (1).png" alt="Logo ClickPharma" class="h-10 w-10 object-contain rounded-xl bg-white p-0.5">
                <div>
                    <h1 class="text-sm font-black tracking-tight uppercase">ClickPharma</h1>
                    <span class="text-[10px] font-bold text-blue-400 uppercase tracking-widest block">Administration Centrale</span>
                </div>
            </div>
            <a href="logout_admin.php" class="text-xs font-bold bg-slate-800 hover:bg-red-600 px-4 py-2 rounded-xl transition duration-200">
                <i class="fa-solid fa-power-off mr-1"></i> Déconnexion
            </a>
        </div>
    </header>

    <main class="max-w-7xl w-full mx-auto p-6 flex-grow space-y-8">

        <?php if (!empty($message)): ?>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 p-4 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-circle-check text-base"></i> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Patients Globaux</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1"><?= $totalPatients ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg"><i class="fa-solid fa-user-injured"></i></div>
            </div>
            
            <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Médecins Validés</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1"><?= $totalMedecins ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg"><i class="fa-solid fa-user-doctor"></i></div>
            </div>

            <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pharmacies Actives</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1"><?= $totalPharmacies ?></h3>
                </div>
                <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg"><i class="fa-solid fa-house-medical"></i></div>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-black text-slate-800 uppercase tracking-tight"><i class="fa-solid fa-receipt text-blue-500 mr-2"></i>Abonnements Médecins & Pharmacies en attente</h2>
                <p class="text-[11px] text-slate-400 font-semibold mt-0.5">Vérifiez les reçus Baridimob ou CIB avant d'activer l'accès.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-extrabold uppercase tracking-wider bg-slate-50/30">
                            <th class="p-4">Nom / Structure</th>
                            <th class="p-4">Corps Médical</th>
                            <th class="p-4 text-center">Justificatif Paiement</th>
                            <th class="p-4 text-right">Décision</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
                        <?php if (count($demandesPro) === 0): ?>
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400 font-medium">Aucun reçu de paiement en attente.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($demandesPro as $demande): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-4 font-bold text-slate-800">
                                        <?= htmlspecialchars(trim($demande['nom'] . ' ' . $demande['prenom'])) ?>
                                    </td>
                                    <td class="p-4">
                                        <?php if ($demande['role'] === 'medecin'): ?>
                                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-bold uppercase"><i class="fa-solid fa-user-doctor text-[9px] mr-1"></i> Médecin</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-[10px] font-bold uppercase"><i class="fa-solid fa-house-medical text-[9px] mr-1"></i> Pharmacie</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php if (!empty($demande['document_justificatif'])): ?>
                                            <a href="<?= htmlspecialchars($demande['document_justificatif']) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition text-[11px]">
                                                <i class="fa-solid fa-file-invoice-dollar"></i> Ouvrir le reçu
                                            </a>
                                        <?php else: ?>
                                            <span class="text-red-400 font-normal italic">Aucun document</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="admin_dashboard.php?action=valider&id=<?= $demande['id_specifique'] ?>&type=<?= $demande['role'] ?>" onclick="return confirm('Activer ce compte pro ?');" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-sm transition">
                                            <i class="fa-solid fa-check text-[10px]"></i> Activer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-black text-slate-800 uppercase tracking-tight"><i class="fa-solid fa-address-card text-teal-600 mr-2"></i>Registre des Documents Patients</h2>
                <p class="text-[11px] text-slate-400 font-semibold mt-0.5">Consultez les fichiers d'identité et cartes Chifa téléversés par vos assurés.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-extrabold uppercase tracking-wider bg-slate-50/30">
                            <th class="p-4">Patient</th>
                            <th class="p-4">N° Sécurité Sociale (NSS)</th>
                            <th class="p-4">Téléphone</th>
                            <th class="p-4 text-center">Pièce Justificative</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-semibold text-slate-700">
                        <?php if (count($patientsList) === 0): ?>
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400 font-medium">Aucun patient inscrit pour le moment.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($patientsList as $patient): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($patient['nom'] . ' ' . $patient['prenom']) ?></td>
                                    <td class="p-4 text-slate-500 font-mono"><?= htmlspecialchars($patient['nss']) ?></td>
                                    <td class="p-4 text-slate-400 font-normal"><?= htmlspecialchars($patient['telephone'] ?: 'Non renseigné') ?></td>
                                    <td class="p-4 text-center">
                                        <?php if (!empty($patient['document_justificatif'])): ?>
                                            <a href="<?= htmlspecialchars($patient['document_justificatif']) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-50 text-teal-600 rounded-xl hover:bg-teal-100 transition text-[11px]">
                                                <i class="fa-solid fa-id-card"></i> Voir la carte / ID
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-400 font-normal italic">Aucun document joint</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="w-full text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider p-6 border-t border-slate-200 bg-white mt-auto">
        © 2026 ClickPharma — Espace d'Administration Centralisé.
    </footer>

</body>
</html>