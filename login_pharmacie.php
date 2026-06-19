<?php
// 1. AUCUN REQUIRE_ONCE DE CONFIG ICI POUR ÉVITER LES REDIRECTIONS AUTOMATIQUES
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 2. CONNEXION LOCALE À TA BASE
$host = 'localhost';
$dbname = 'pharmalife_db'; 
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$error = "";
$debug_msg = "";

// 3. TRAITEMENT DE LA CONNEXION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_saisi = trim($_POST['username'] ?? ''); 
    $password_saisi = trim($_POST['password'] ?? '');

    if (!empty($login_saisi) && !empty($password_saisi)) {
        
        // Recherche de l'utilisateur avec le rôle pharmacie
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE utilisateur = ? AND role = 'pharmacie'");
        $stmt->execute([$login_saisi]);
        $user = $stmt->fetch();

        if ($user) {
            $mot_de_passe_correct = false;

            // Vérification du mot de passe (Haché ou Texte clair)
            if (str_starts_with($user['mot_de_passe'], '$2y$')) {
                if (password_verify($password_saisi, $user['mot_de_passe'])) {
                    $mot_de_passe_correct = true;
                }
            } else {
                if ($password_saisi === $user['mot_de_passe']) {
                    $mot_de_passe_correct = true;
                }
            }

            if ($mot_de_passe_correct) {
                // CORRECTION MAJEURE : On stocke la session EXACTEMENT comme ton fichier l'attend !
                // On utilise 'id_pharmacie' (en prenant l'ID du compte ou l'ID lié à ta table pharmacies)
                $_SESSION['id_pharmacie'] = $user['id']; 
                $_SESSION['role'] = $user['role'];
                
                // Redirection directe vers ton fichier
                header('Location: space_pharmacien.php');
                exit();
            } else {
                $error = "Mot de passe incorrect.";
                $debug_msg = "Le compte existe mais le mot de passe ne correspond pas.";
            }
        } else {
            $error = "Identifiant introuvable.";
            $debug_msg = "Aucun utilisateur trouvé avec le rôle 'pharmacie' pour ce nom.";
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
    <title>Connexion Pharmacie | ClickPharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }
        .glow-bg {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.04) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(14, 116, 144, 0.04) 0px, transparent 50%);
        }
    </style>
</head>
<body class="glow-bg min-h-screen flex flex-col items-center justify-center p-4 gap-4 antialiased">

    <div class="max-w-md w-full text-left mb-2">
        <a href="index.php" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-[#00966b] transition">
            <i class="fa-solid fa-arrow-left"></i> Retour à l'accueil
        </a>
    </div>

    <div class="max-w-md w-full bg-white p-8 rounded-[2.5rem] border border-slate-200/60 shadow-xl space-y-6 relative overflow-hidden group">
        <div class="absolute top-0 left-0 right-0 h-2 bg-[#00966b]"></div>

        <div class="text-center flex flex-col items-center space-y-4">
            <div class="w-16 h-16 rounded-2xl overflow-hidden bg-white shadow-md border border-slate-100 group-hover:scale-105 transition-all duration-200 flex items-center justify-center">
                <img src="images/Pharma (1).png" alt="ClickPharma Logo" class="w-full h-full object-cover">
            </div>
            
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Click<span class="text-[#00966b]">Pharma</span> Officine</h2>
                <p class="text-xs font-semibold text-slate-400 mt-1">Connexion Synchronisée avec votre Espace</p>
            </div>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 p-3.5 rounded-2xl text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-red-500"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login_pharmacie.php" method="POST" class="space-y-4 font-semibold text-slate-700 text-xs">
            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 ml-1">Identifiant Pharmacie</label>
                <input type="text" name="username" required placeholder="Nom d'utilisateur" 
                       class="w-full p-3.5 rounded-xl border border-slate-200 outline-none focus:ring-4 ring-emerald-500/5 bg-slate-50 text-slate-800 transition">
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase text-slate-400 mb-1.5 ml-1">Mot de passe</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       class="w-full p-3.5 rounded-xl border border-slate-200 outline-none focus:ring-4 ring-emerald-500/5 bg-slate-50 text-slate-800 transition">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-4 bg-[#00966b] hover:bg-[#00825c] text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md shadow-emerald-700/10 transition-all duration-200 hover:scale-[1.01]">
                    Ouvrir la session pharmacie
                </button>
            </div>
        </form>
    </div>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($debug_msg)): ?>
        <div class="max-w-md w-full bg-slate-900 text-slate-200 p-4 rounded-2xl shadow-xl text-[11px] font-mono border border-slate-700">
            <p class="text-amber-400 font-bold mb-1"><i class="fa-solid fa-bug"></i> Débug connexion :</p>
            <p class="text-emerald-400 bg-slate-950 p-2.5 rounded-xl border border-slate-800"><?= $debug_msg ?></p>
        </div>
    <?php endif; ?>

</body>
</html>