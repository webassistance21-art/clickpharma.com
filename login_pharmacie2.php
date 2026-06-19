<?php
// 1. CONNEXION DIRECTE INTÉGRÉE POUR ÉVITER LES CONFLITS DE FICHIERS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$host = 'localhost';
$dbname = 'pharmalife_db'; // <-- REMPLACE PAR LE NOM EXACT DE TA BASE SI CE N'EST PAS PHARMAGO
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<div style='background:red;color:white;padding:20px;'>Erreur de connexion BDD : " . $e->getMessage() . "</div>");
}

$error = "";
$debug_msg = "";

// 2. INTERCEPTION ET AFFICHAGE FORCE DU DIAGNOSTIC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_saisi = trim($_POST['username'] ?? ''); 
    $password_saisi = trim($_POST['password'] ?? '');

    // Ce gros bloc bleu s'affichera obligatoirement en haut si le PHP se lance
    echo "<div style='background:#1e3a8a; color:#white; padding:15px; font-family:monospace; border-bottom:5px solid #f59e0b;'>";
    echo "<h3 style='color:#fbbf24;margin:0 0 10px 0;'>[DEBUG SYSTEM] Formulaire reçu !</h3>";
    echo "Identifiant écrit : [" . htmlspecialchars($login_saisi) . "]<br>";
    echo "Mot de passe écrit : [" . htmlspecialchars($password_saisi) . "]<br>";

    if (!empty($login_saisi) && !empty($password_saisi)) {
        
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE utilisateur = ? AND role = 'pharmacie'");
        $stmt->execute([$login_saisi]);
        $user = $stmt->fetch();

        if ($user) {
            echo "<span style='color:#34d399;'>✔ Utilisateur 'pharmacie' trouvé dans la table !</span><br>";
            
            $mot_de_passe_correct = false;
            if (str_starts_with($user['mot_de_passe'], '$2y$')) {
                if (password_verify($password_saisi, $user['mot_de_passe'])) { $mot_de_passe_correct = true; }
            } else {
                if ($password_saisi === $user['mot_de_passe']) { $mot_de_passe_correct = true; }
            }

            if ($mot_de_passe_correct) {
                echo "<span style='color:#34d399;'>✔ Mot de passe validé. Redirection en cours...</span><br>";
                $_SESSION['id_utilisateur'] = $user['id'];
                $_SESSION['nom_affichage'] = $user['utilisateur'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: space_pharmacien.php');
                exit();
            } else {
                echo "<span style='color:#f87171;'>❌ Mot de passe incorrect en base de données.</span><br>";
                $error = "Mot de passe incorrect.";
            }
        } else {
            echo "<span style='color:#f87171;'>❌ Identifiant inconnu avec le rôle 'pharmacie'.</span><br>";
            $error = "Identifiant introuvable ou mauvais rôle.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Pharmacie Autonome</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white p-8 rounded-3xl border border-slate-200 shadow-xl space-y-6">
        <div class="text-center">
            <h2 class="text-2xl font-black text-slate-800">Pharmago Officine</h2>
            <p class="text-xs text-slate-400 mt-1">Page de secours autonome pour le Pharmacien</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-red-50 text-red-700 p-3 rounded-xl text-xs font-semibold">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login_pharmacie.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Identifiant Pharmacie</label>
                <input type="text" name="username" required class="w-full px-4 py-2 rounded-xl border bg-slate-50 text-slate-800 text-xs">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Mot de passe</label>
                <input type="password" name="password" required class="w-full px-4 py-2 rounded-xl border bg-slate-50 text-slate-800 text-xs">
            </div>

            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs uppercase">
                Ouvrir la session pharmacie
            </button>
        </form>
    </div>

</body>
</html>