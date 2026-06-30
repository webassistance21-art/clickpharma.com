<?php
session_start();
require_once 'config/db.php';
 
$error = ""; 
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifiantSaisi = trim($_POST['username'] ?? '');
    $motDePasseSaisi = trim($_POST['password'] ?? '');
 
    if (!empty($identifiantSaisi) && !empty($motDePasseSaisi)) {
        
        // Requête propre sur ta table utilisateurs
        $stmt = $pdo->prepare("SELECT id, utilisateur, mot_de_passe, role FROM utilisateurs WHERE utilisateur = ?");
        $stmt->execute([$identifiantSaisi]);
        $user = $stmt->fetch();
 
        // CORRECTION DU BUG : Utilisation de la bonne variable $motDePasseSaisi
            if ($user && password_verify($motDePasseSaisi, $user['mot_de_passe'])) {
            $_SESSION['id_utilisateur'] = $user['id'];
            $_SESSION['utilisateur'] = $user['utilisateur'];
            
            $role = $user['role'] ?? '';
            $_SESSION['role'] = $role; 
            $_SESSION['nom_affichage'] = $user['utilisateur'];
 
            // Redirection stricte selon le rôle vers tes fichiers réels
            if ($role === 'pharmacie') {
                header('Location: dashboard_pharmacie.php');
                exit();
            } elseif ($role === 'patient') {
                header('Location: recherche_patient.php'); // Redirige vers ton hub patient réel
                exit();
            } elseif ($role === 'medecin') {
                header('Location: space_medecin.php'); // Redirige vers ton dashboard médecin réel
                exit();
            } else {
                $error = "Rôle utilisateur non reconnu.";
            }
 
        } else {
            $error = "Identifiants ou mot de passe incorrects.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?> 
 
<!DOCTYPE html>
<html lang="fr" dir="ltr" id="mainHtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClickPharma | Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8fafc;
            background-image: radial-gradient(at 50% 0%, rgba(16, 185, 129, 0.05) 0px, transparent 50%);
            min-height: 100vh;
        }
        html[lang="ar"] body { font-family: 'Cairo', sans-serif; }
        
        .glass-container {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
 
        .input-style {
            width: 100%;
            padding: 1rem 1.25rem;
            background-color: white;
            border: 1px solid rgba(226, 232, 240, 1);
            border-radius: 1.25rem;
            outline: none;
            transition: all 0.2s ease;
        }
        .input-style:focus {
            border-color: #00966b;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.06);
        }
    </style>
</head>
<body class="flex flex-col justify-between min-h-screen p-4 antialiased">
 
    <div class="w-full max-w-6xl mx-auto flex justify-end p-2">
        <button onclick="toggleLang()" id="langBtn" class="px-4 py-2 bg-white rounded-xl border border-slate-200 shadow-sm font-bold text-xs text-slate-700 hover:text-[#00966b] transition duration-200">
            <i class="fa-solid fa-globe text-[#00966b] mr-1 ml-1"></i> العربية
        </button>
    </div>
 
    <div class="max-w-md w-full glass-container p-8 md:p-10 rounded-[2.5rem] shadow-xl mx-auto my-auto relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl"></div>
        
        <div class="text-center mb-8 space-y-3">
            <div class="inline-block p-1 bg-white rounded-2xl shadow-md border border-slate-100">
                <img src="images/Pharma (1).png" alt="Logo ClickPharma" class="h-14 w-14 object-cover rounded-2xl">
            </div>
            <h1 id="title" class="text-2xl font-black text-slate-800 tracking-tight">Ravi de vous revoir</h1>
            <p id="subtitle" class="text-xs font-semibold text-slate-400 max-w-xs mx-auto leading-relaxed">Accédez à votre compte Click Pharma</p>
        </div>
 
        <?php if(!empty($error)): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 p-4 mb-6 rounded-2xl text-xs font-bold text-center">
                <i class="fa-solid fa-circle-exclamation mr-1 ml-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
 
        <form action="" method="POST" class="space-y-4 font-semibold text-slate-700 text-xs">
            <div id="usernameContainer">
                <label id="userLabel" class="block text-slate-400 mb-2 ml-1 px-1">Identifiant</label>
                <input type="text" name="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required class="input-style font-normal" placeholder="nom@exemple.com">
            </div>
 
            <div id="passwordContainer">
                <label id="passLabel" class="block text-slate-400 mb-2 ml-1 px-1">Mot de passe</label>
                <input type="password" name="password" required class="input-style" placeholder="••••••••">
            </div>
 
            <div class="flex justify-end">
                <a href="#" id="forgotPass" class="text-xs font-bold text-[#00966b] hover:underline">Mot de passe oublié ?</a>
            </div>
 
            <button type="submit" id="submitBtn" class="w-full py-4 bg-slate-800 text-white rounded-2xl font-bold text-xs hover:bg-slate-900 transition duration-200 shadow-md">
                Se connecter
            </button>
        </form>
 
        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <p id="noAccount" class="text-slate-400 text-xs font-medium">Vous n'avez pas de compte ?</p>
            <a href="register.php" id="registerLink" class="inline-block mt-1 font-bold text-slate-800 hover:text-[#00966b] transition">Créer un compte gratuitement</a>
        </div>
    </div>
 
    <footer class="w-full text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider p-4">
        © 2026 ClickPharma — Solution Numérique de Santé.
    </footer>
 
    <script>
        let currentLang = 'fr';
        const labels = {
            fr: {
                title: "Ravi de vous revoir", subtitle: "Accédez à votre compte Click Pharma",
                user: "Identifiant", pass: "Mot de passe", forgot: "Mot de passe oublié ?",
                submit: "Se connecter", noAcc: "Vous n'avez pas de compte ?", reg: "Créer un compte gratuitement", lang: "<i class='fa-solid fa-globe text-[#00966b] mr-1 ml-1'></i> العربية"
            },
            ar: {
                title: "مرحباً بك من جديد", subtitle: "الدخول إلى حساب كليك فارما الخاص بك",
                user: "اسم المستخدم", pass: "كلمة المرور", forgot: "نسيت كلمة المرور؟",
                submit: "تسجيل الدخول", noAcc: "ليس لديك حساب؟", reg: "إنشاء حساب مجاناً", lang: "<i class='fa-solid fa-globe text-[#00966b] mr-1 ml-1'></i> Français"
            }
        };
 
        function toggleLang() {
            currentLang = currentLang === 'fr' ? 'ar' : 'fr';
            const d = labels[currentLang];
            
            // Inversion de la direction globale
            document.getElementById('mainHtml').setAttribute('lang', currentLang);
            document.getElementById('mainHtml').setAttribute('dir', currentLang === 'ar' ? 'rtl' : 'ltr');
            
            // Alignements dynamiques des labels pour le mode RTL
            const aligns = ['usernameContainer', 'passwordContainer'];
            aligns.forEach(id => {
                const el = document.getElementById(id).querySelector('label');
                if(currentLang === 'ar') {
                    el.classList.remove('text-left', 'ml-1');
                    el.classList.add('text-right', 'mr-1');
                } else {
                    el.classList.remove('text-right', 'mr-1');
                    el.classList.add('text-left', 'ml-1');
                }
            });

            // Application des textes
            document.getElementById('title').innerText = d.title;
            document.getElementById('subtitle').innerText = d.subtitle;
            document.getElementById('userLabel').innerText = d.user;
            document.getElementById('passLabel').innerText = d.pass;
            document.getElementById('forgotPass').innerText = d.forgot;
            document.getElementById('submitBtn').innerText = d.submit;
            document.getElementById('noAccount').innerText = d.noAcc;
            document.getElementById('registerLink').innerText = d.reg;
            document.getElementById('langBtn').innerHTML = d.lang;
        }
    </script>
</body>
</html>
