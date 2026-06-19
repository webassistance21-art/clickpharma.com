<?php
require_once 'config/db.php';
// Forcer l'encodage UTF-8 pour éviter les problèmes d'accents
$pdo->exec("SET NAMES utf8mb4");

session_start();

// Si le patient est déjà connecté, on le redirige directement vers son espace
if (isset($_SESSION['role']) && $_SESSION['role'] === 'patient') {
    header('Location: space_patient.php');
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des données saisies
    $nss_saisi = trim($_POST['nss'] ?? '');
    $password_saisi = trim($_POST['password'] ?? '');

    if (!empty($nss_saisi) && !empty($password_saisi)) {
        
        // Requête de recherche par le Numéro de Sécurité Sociale (NSS)
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE nss = ?");
        $stmt->execute([$nss_saisi]);
        $patient = $stmt->fetch();

        // VERIFICATION DU MOT DE PASSE
        // Remarque : Si vos mots de passe en BDD sont en texte brut (ex: "123"), 
        // remplacez la ligne ci-dessous par : if ($patient && $password_saisi === $patient['password'])
        if ($patient && $password_saisi === $patient['password']) {
            
            // Initialisation des variables de session pour le patient
            $_SESSION['id_patient'] = $patient['id_patient'];
            $_SESSION['nss'] = $patient['nss'];
            $_SESSION['nom'] = $patient['nom'];
            $_SESSION['prenom'] = $patient['prenom'];
            $_SESSION['role'] = 'patient';

            // Redirection vers son tableau de bord personnel
            header('Location: space_patient.php');
            exit();
        } else {
            $error = "Numéro NSS ou mot de passe incorrect.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Patient | ClickPharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white p-8 rounded-3xl border border-slate-200 shadow-xl space-y-6 relative overflow-hidden">
        
        <!-- Indicateur de couleur pour l'espace Patient (Vert Émeraude ou Indigo) -->
        <div class="absolute top-0 left-0 right-0 h-2 bg-emerald-500"></div>

        <!-- En-tête du formulaire -->
        <div class="text-center">
            <div class="flex items-center justify-center gap-2.5 mb-2">
                <img src="images/Pharma (1).png" alt="Logo ClickPharma" class="h-12 w-12 object-contain rounded-xl" onerror="this.src='Pharma (1).png'">
                <span class="text-2xl font-black text-slate-800 tracking-tight">ClickPharma</span>
            </div>
            <h2 class="text-base font-extrabold text-slate-700">Espace Personnel Patient</h2>
            <p class="text-xs text-slate-400 mt-1">Connectez-vous pour consulter et télécharger vos ordonnances.</p>
        </div>

        <!-- Notification d'erreur -->
        <?php if(!empty($error)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded-xl text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-sm"></i> 
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Formulaire de Connexion -->
        <form action="" method="POST" class="space-y-4">
            
            <!-- Champ NSS -->
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 ml-1 tracking-wider">Numéro de Sécurité Sociale (NSS)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-3 text-slate-400 text-xs">
                        <i class="fa-solid fa-id-card"></i>
                    </span>
                    <input type="text" name="nss" required 
                           placeholder="Entrez votre NSS" 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-xs font-medium transition bg-slate-50/50 focus:bg-white text-slate-800">
                </div>
            </div>

            <!-- Champ Mot de Passe -->
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 ml-1 tracking-wider">Mot de passe</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-3 text-slate-400 text-xs">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" required 
                           placeholder="••••••••" 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-emerald-500 outline-none text-xs font-medium transition bg-slate-50/50 focus:bg-white text-slate-800">
                </div>
            </div>

            <!-- Bouton de Soumission -->
            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow-md mt-2 flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i> Accéder à mon espace
            </button>
        </form>

        <!-- Lien de secours ou retour -->
        <div class="text-center pt-2 border-t border-slate-100">
            <a href="login.php" class="text-[11px] text-slate-400 hover:text-slate-600 transition font-medium">
                <i class="fa-solid fa-user-doctor mr-1"></i> Vous êtes un professionnel de santé ? Connectez-vous ici
            </a>
        </div>
    </div>

</body>
</html>