<?php
session_start();
require_once 'config/db.php';

// Sécurité : Vérifier si c'est bien une pharmacie
if (!isset($_SESSION['id_pharmacie'])) {
    header('Location: login_pharmacie.php');
    exit();
}

// Récupérer la liste de tous les médicaments disponibles dans le catalogue général
$stmt = $pdo->query("SELECT id_medoc, nom_medicament, forme FROM medicaments ORDER BY nom_medicament ASC");
$catalogue = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Médicament | ClickPharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen flex flex-col justify-between">

    <nav class="bg-[#00966b] text-white px-8 py-4 shadow-sm flex justify-between items-center">
        <span class="text-xl font-extrabold tracking-tight">CLICKPHARMA<span class="font-normal opacity-80 text-xs ml-0.5 uppercase tracking-widest">Pro</span></span>
        <a href="space_pharmacien.php" class="text-xs font-bold uppercase tracking-wider bg-[#00825c] hover:bg-[#006e4e] px-4 py-2 rounded-full transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Retour au Tableau de bord
        </a>
    </nav>

    <main class="max-w-lg w-full mx-auto p-6 my-8">
        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-8 space-y-6 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-2 bg-[#00966b]"></div>
            
            <div>
                <h2 class="text-2xl font-extrabold text-[#1e293b] tracking-tight">Ajouter au stock</h2>
                <p class="text-xs text-slate-400 font-medium mt-1">Saisissez les informations pour intégrer le produit à votre inventaire.</p>
            </div>

            <?php if(isset($_GET['success'])): ?>
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-2xl text-xs font-semibold">
                    <i class="fa-solid fa-circle-check mr-1"></i> Médicament ajouté avec succès au stock !
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-2xl text-xs font-semibold">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <form action="ajouter_medicament_action.php" method="POST" class="space-y-5">
                
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5 ml-1 tracking-wider">Choisir le Médicament</label>
                    <div class="relative">
                        <select name="id_medicament" required class="w-full bg-[#f1f5f9] px-4 py-3 rounded-2xl outline-none text-xs font-semibold text-slate-700 border border-transparent focus:border-slate-200 transition appearance-none cursor-pointer">
                            <option value="" disabled selected>-- Sélectionner dans le catalogue --</option>
                            <?php foreach($catalogue as $medoc): ?>
                                <option value="<?= $medoc['id_medoc'] ?>">
                                    <?= htmlspecialchars($medoc['nom_medicament']) ?> (<?= htmlspecialchars($medoc['forme']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-slate-400 text-xs">
                            <i class="fa-solid fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5 ml-1 tracking-wider">Quantité (Boîtes)</label>
                        <input type="number" name="quantite" min="1" required placeholder="Ex: 20" 
                               class="w-full bg-[#f1f5f9] px-4 py-3 rounded-2xl outline-none text-xs font-semibold text-slate-700 border border-transparent focus:border-slate-200 transition">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5 ml-1 tracking-wider">Prix Unitaire (DA)</label>
                        <input type="number" name="prix" step="0.01" min="0" required placeholder="Ex: 450.00" 
                               class="w-full bg-[#f1f5f9] px-4 py-3 rounded-2xl outline-none text-xs font-semibold text-slate-700 border border-transparent focus:border-slate-200 transition">
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 bg-[#00966b] hover:bg-[#00825c] text-white font-bold rounded-2xl text-xs uppercase tracking-wider shadow-sm transition mt-2">
                    Confirmer l'ajout au stock
                </button>
            </form>
        </div>
    </main>

    <footer class="text-center py-4 text-slate-400 text-[10px] font-medium">
        &copy; 2026 ClickPharma Pro. Tous droits réservés.
    </footer>
</body>
</html>