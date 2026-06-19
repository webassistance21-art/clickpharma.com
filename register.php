<?php
session_start();
require_once 'config/db.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'patient';
    $email = trim($_POST['username'] ?? '');
    
    // Correction du Warning : assignation propre de la variable attendue au traitement
    $motDePasseSaisi = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($motDePasseSaisi)) {
        try {
            $pdo->beginTransaction();

            // 1. Vérification d'unicité de l'identifiant
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE utilisateur = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception("Cet identifiant est déjà utilisé.");
            }

            // Gestion du téléversement du fichier justificatif
            $fichierDestination = null;
            $uploadErreur = $_FILES['document_joint']['error'] ?? UPLOAD_ERR_NO_FILE;

            if ($uploadErreur === UPLOAD_ERR_OK) {
                $nomFichier = $_FILES['document_joint']['name'];
                $fichierTmp = $_FILES['document_joint']['tmp_name'];
                $ext = strtolower(pathinfo($nomFichier, PATHINFO_EXTENSION));
                
                $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'pdf'];
                if (!in_array($ext, $extensionsAutorisees)) {
                    throw new Exception("Format de fichier invalide (Uniquement JPG, PNG, PDF).");
                }

                $nouveauNomFichier = time() . '_' . uniqid() . '.' . $ext;
                $dossierCible = 'uploads/' . $nouveauNomFichier;

                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }

                if (move_uploaded_file($fichierTmp, $dossierCible)) {
                    $fichierDestination = $dossierCible;
                } else {
                    throw new Exception("Erreur lors de l'enregistrement du document.");
                }
            } else {
                throw new Exception("Veuillez joindre le document ou justificatif obligatoire.");
            }

            // 2. Insertion dans la table centrale utilisateurs
            $motDePasseGlobal = ($role === 'patient') ? 'VIA_PATIENT_TABLE' : $motDePasseSaisi;
            $stmtUser = $pdo->prepare("INSERT INTO utilisateurs (utilisateur, mot_de_passe, role) VALUES (?, ?, ?)");
            $stmtUser->execute([$email, $motDePasseGlobal, $role]);
            $id_utilisateur = $pdo->lastInsertId();

            // 3. Insertion selon le rôle sélectionné
            if ($role === 'patient') {
                $nom = trim($_POST['patient_nom'] ?? '');
                $prenom = trim($_POST['patient_prenom'] ?? '');
                $nss = trim($_POST['patient_nss'] ?? '');
                $date_naissance = $_POST['patient_date_naissance'] ?? null;
                $phone = trim($_POST['patient_phone'] ?? '');
                $adresse = trim($_POST['patient_adresse'] ?? '');
                $ville = trim($_POST['patient_ville'] ?? '');

                if (empty($nom) || empty($prenom) || empty($nss) || empty($date_naissance)) {
                    throw new Exception("Veuillez remplir tous les champs obligatoires du patient.");
                }

                $stmtCheckNSS = $pdo->prepare("SELECT id_patient FROM patients WHERE nss = ?");
                $stmtCheckNSS->execute([$nss]);
                if ($stmtCheckNSS->fetch()) {
                    throw new Exception("Ce numéro de sécurité sociale est déjà enregistré.");
                }

                $queryPatient = "INSERT INTO patients (id_utilisateur, nss, password, nom, prenom, date_naissance, telephone, adresse, ville) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtSpecific = $pdo->prepare($queryPatient);
                $stmtSpecific->execute([$id_utilisateur, $nss, $motDePasseSaisi, $nom, $prenom, $date_naissance, $phone, $adresse, $ville]);

            } else {
                // Récupération du moyen de paiement choisi pour les professionnels
                $moyenPaiement = $_POST['moyen_paiement'] ?? 'non_specifie';
                
                if ($role === 'medecin') {
                    $nom = trim($_POST['medecin_nom'] ?? '');
                    $prenom = trim($_POST['medecin_prenom'] ?? '');
                    $matricule = trim($_POST['medecin_matricule'] ?? '');
                    $specialite = trim($_POST['medecin_specialite'] ?? '');
                    $phone = trim($_POST['medecin_phone'] ?? '');
                    $adresse = trim($_POST['medecin_adresse'] ?? '');
                    $ville = trim($_POST['medecin_ville'] ?? '');

                    if (empty($nom) || empty($prenom) || empty($matricule) || empty($specialite)) {
                        throw new Exception("Veuillez remplir les informations médicales obligatoires.");
                    }

                    $stmtSpecific = $pdo->prepare("INSERT INTO medecins (id_utilisateur, matricule_medical, nom, prenom, specialite, telephone, adresse_cabinet, ville) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmtSpecific->execute([$id_utilisateur, $matricule, $nom, $prenom, $specialite, $phone, $adresse, $ville]);

                } elseif ($role === 'pharmacie') {
                    $nom_pharma = trim($_POST['pharma_nom'] ?? '');
                    $titulaire = trim($_POST['pharma_titulaire'] ?? '');
                    $phone = trim($_POST['pharma_phone'] ?? '');
                    $adresse = trim($_POST['pharma_adresse'] ?? '');
                    $ville = trim($_POST['pharma_ville'] ?? '');

                    if (empty($nom_pharma) || empty($titulaire) || empty($adresse) || empty($ville)) {
                        throw new Exception("Veuillez remplir les informations de l'officine.");
                    }

                    $stmtSpecific = $pdo->prepare("INSERT INTO pharmacies (id_utilisateur, nom_pharmacie, nom_titulaire, telephone, adresse, ville) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmtSpecific->execute([$id_utilisateur, $nom_pharma, $titulaire, $phone, $adresse, $ville]);
                }
            }

            $pdo->commit();
            $success = "Inscription effectuée avec succès !";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            if (!empty($fichierDestination) && file_exists($fichierDestination)) {
                unlink($fichierDestination);
            }
            $error = $e->getMessage();
        }
    } else {
        $error = "Veuillez renseigner un identifiant et un mot de passe.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr" id="mainHtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClickPharma | Création de compte</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f0f4f8;
            background-image: radial-gradient(at 50% 0%, rgba(59, 130, 246, 0.04) 0px, transparent 50%);
            min-height: 100vh;
        }
        html[lang="ar"] body { font-family: 'Cairo', sans-serif; }
        .glass-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .input-style {
            width: 100%;
            padding: 0.85rem 1.25rem;
            background-color: white;
            border: 1px solid rgba(226, 232, 240, 1);
            border-radius: 1rem;
            outline: none;
            font-size: 13px;
            transition: all 0.2s ease;
        }
        .input-style:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.06);
        }
        /* Style personnalisé pour masquer le bouton radio natif et créer une carte cliquable premium */
        .payment-card input[type="radio"] {
            display: none;
        }
        .payment-card input[type="radio"]:checked + div {
            border-color: #3b82f6;
            background-color: rgba(59, 130, 246, 0.03);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body class="flex flex-col justify-between min-h-screen p-4 antialiased">

    <div class="w-full max-w-2xl mx-auto flex justify-end p-2">
        <button onclick="toggleLang()" id="langBtn" class="px-4 py-2 bg-white rounded-xl border border-slate-200 shadow-sm font-bold text-xs text-slate-700 hover:text-blue-600 transition">
            <i class="fa-solid fa-globe text-blue-500 mr-1 ml-1"></i> vulnerability العربية
        </button>
    </div>

    <div class="max-w-2xl w-full glass-container p-6 md:p-8 rounded-[2.5rem] shadow-xl mx-auto my-auto relative overflow-hidden">
        
        <div class="text-center mb-6 space-y-2">
            <div class="inline-block p-1 bg-white rounded-2xl shadow-sm border border-slate-100">
                <img src="images/Pharma (1).png" alt="Logo ClickPharma" class="h-16 w-16 object-contain rounded-2xl">
            </div>
            <h1 id="title" class="text-2xl font-black text-slate-800 tracking-tight">Rejoindre ClickPharma</h1>
            <p id="subtitle" class="text-xs font-semibold text-slate-400">Sélectionnez votre profil pour configurer votre espace dédié</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="bg-red-50 border border-red-100 text-red-600 p-3 mb-4 rounded-xl text-xs font-bold text-center">
                <i class="fa-solid fa-circle-exclamation mr-1 ml-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-600 p-3 mb-4 rounded-xl text-xs font-bold text-center">
                <i class="fa-solid fa-circle-check mr-1 ml-1"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-3 gap-2 p-1.5 bg-slate-100 rounded-2xl mb-6 font-bold text-xs text-slate-500">
            <button type="button" onclick="switchRole('patient')" id="tab-patient" class="tab-btn py-3 rounded-xl transition flex items-center justify-center gap-1 bg-slate-800 text-white shadow-sm">
                <i class="fa-solid fa-user-injured text-[10px]"></i> <span class="tab-text-p">Patient</span>
            </button>
            <button type="button" onclick="switchRole('medecin')" id="tab-medecin" class="tab-btn py-3 rounded-xl transition flex items-center justify-center gap-1">
                <i class="fa-solid fa-user-doctor text-[10px]"></i> <span class="tab-text-m">Médecin</span>
            </button>
            <button type="button" onclick="switchRole('pharmacie')" id="tab-pharmacie" class="tab-btn py-3 rounded-xl transition flex items-center justify-center gap-1">
                <i class="fa-solid fa-prescription-bottle-medical text-[10px]"></i> <span class="tab-text-ph">Pharmacie</span>
            </button>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-4 font-semibold text-slate-700 text-xs">
            
            <input type="hidden" name="role" id="roleInput" value="patient">

            <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100/80 space-y-4">
                <span id="sectionAuthTitle" class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block"><i class="fa-solid fa-lock text-[9px]"></i> Informations de Connexion</span>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label id="userLabel" class="text-slate-400 px-1">Identifiant unique (Email)</label>
                        <input type="text" name="username" required class="input-style font-normal" placeholder="nom@exemple.com">
                    </div>
                    <div class="space-y-1">
                        <label id="passLabel" class="text-slate-400 px-1">Mot de passe</label>
                        <input type="password" name="password" required class="input-style font-normal" placeholder="••••••••">
                    </div>
                </div>
            </div>

            <div id="form-patient" class="role-fields space-y-4">
                <span id="sectionPatientTitle" class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block"><i class="fa-solid fa-address-card text-[9px]"></i> Profil Patient</span>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="space-y-1"><label class="text-slate-400 px-1">Nom</label><input type="text" name="patient_nom" required class="input-style font-normal"></div>
                    <div class="space-y-1"><label class="text-slate-400 px-1">Prénom</label><input type="text" name="patient_prenom" required class="input-style font-normal"></div>
                    <div class="space-y-1"><label class="text-slate-400 px-1">N° Sécurité Sociale (NSS)</label><input type="text" name="patient_nss" required class="input-style font-normal" placeholder="21 0045 8874 12"></div>
                    <div class="space-y-1"><label class="text-slate-400 px-1">Date de naissance</label><input type="date" name="patient_date_naissance" required class="input-style font-normal"></div>
                    <div class="space-y-1"><label class="text-slate-400 px-1">Téléphone personnel</label><input type="text" name="patient_phone" class="input-style font-normal"></div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1"><label class="text-slate-400 px-1">Adresse</label><input type="text" name="patient_adresse" class="input-style font-normal"></div>
                        <div class="space-y-1"><label class="text-slate-400 px-1">Ville</label><input type="text" name="patient_ville" class="input-style font-normal"></div>
                    </div>
                </div>
            </div>

            <div id="form-medecin" class="role-fields hidden space-y-4">
                <span id="sectionMedTitle" class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block"><i class="fa-solid fa-stethoscope text-[9px]"></i> Cabinet Praticien</span>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="space-y-1"><label class="text-slate-400 px-1">Nom</label><input type="text" name="medecin_nom" class="input-style font-normal"></div>
                    <div class="space-y-1"><label class="text-slate-400 px-1">Prénom</label><input type="text" name="medecin_prenom" class="input-style font-normal"></div>
                    <div class="space-y-1"><label class="text-slate-400 px-1">Matricule Médical</label><input type="text" name="medecin_matricule" class="input-style font-normal" placeholder="MED-XXXXX"></div>
                    <div class="space-y-1"><label class="text-slate-400 px-1">Spécialité</label><input type="text" name="medecin_specialite" class="input-style font-normal" placeholder="Cardiologue..."></div>
                    <div class="space-y-1"><label class="text-slate-400 px-1">Téléphone Cabinet</label><input type="text" name="medecin_phone" class="input-style font-normal"></div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1"><label class="text-slate-400 px-1">Adresse</label><input type="text" name="medecin_adresse" class="input-style font-normal"></div>
                        <div class="space-y-1"><label class="text-slate-400 px-1">Ville</label><input type="text" name="medecin_ville" class="input-style font-normal"></div>
                    </div>
                </div>
            </div>

            <div id="form-pharmacie" class="role-fields hidden space-y-4">
                <span id="sectionPharmaTitle" class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block"><i class="fa-solid fa-house-medical text-[9px]"></i> Identification Officine</span>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="space-y-1"><label class="text-slate-400 px-1">Nom de la Pharmacie</label><input type="text" name="pharma_nom" class="input-style font-normal" placeholder="Pharmacie Centrale"></div>
                    <div class="space-y-1"><label class="text-slate-400 px-1">Nom du Titulaire</label><input type="text" name="pharma_titulaire" class="input-style font-normal"></div>
                    <div class="space-y-1"><label class="text-slate-400 px-1">Téléphone Officine</label><input type="text" name="pharma_phone" class="input-style font-normal"></div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1"><label class="text-slate-400 px-1">Adresse</label><input type="text" name="pharma_adresse" class="input-style font-normal"></div>
                        <div class="space-y-1"><label class="text-slate-400 px-1">Ville</label><input type="text" name="pharma_ville" class="input-style font-normal"></div>
                    </div>
                </div>
            </div>

            <div id="paymentSelectionBlock" class="hidden bg-slate-50 border border-slate-200/60 p-5 rounded-2xl space-y-4">
                <span id="paymentTitle" class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block"><i class="fa-solid fa-credit-card text-[9px]"></i> Sélectionner un moyen de paiement</span>
                
                <div class="grid grid-cols-2 gap-4">
                    <label class="payment-card cursor-pointer">
                        <input type="radio" name="moyen_paiement" value="baridimob" checked>
                        <div class="border border-slate-200 bg-white rounded-xl p-4 flex flex-col items-center justify-center gap-2 hover:border-blue-300 transition duration-200 min-h-[100px]">
                            <img src="images/baridimob.png" alt="Baridimob logo" class="h-10 object-contain">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Baridimob</span>
                        </div>
                    </label>

                    <label class="payment-card cursor-pointer">
                        <input type="radio" name="moyen_paiement" value="cib">
                        <div class="border border-slate-200 bg-white rounded-xl p-4 flex flex-col items-center justify-center gap-2 hover:border-blue-300 transition duration-200 min-h-[100px]">
                            <img src="images/carteCIB.png" alt="Carte CIB logo" class="h-10 object-contain">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Carte CIB</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 space-y-2">
                <label id="fileLabel" class="block text-slate-700 font-bold mb-1"><i class="fa-solid fa-paperclip text-blue-600"></i> Pièce jointe (Carte Chifa / Identité)</label>
                <p id="fileHelp" class="text-[11px] text-slate-400 font-medium leading-tight mb-2">Veuillez transmettre un scan lisible au format JPG, PNG ou PDF.</p>
                <input type="file" name="document_joint" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-100 file:text-blue-600 hover:file:bg-blue-200 cursor-pointer">
            </div>

            <button type="submit" id="submitBtn" class="w-full py-3.5 bg-slate-800 hover:bg-slate-900 text-white rounded-2xl font-bold text-xs transition duration-200 shadow-md mt-2">
                Créer mon compte
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 text-center">
            <p id="hasAccount" class="text-slate-400 text-xs font-medium">Vous possédez déjà un compte ?</p>
            <a href="login.php" id="loginLink" class="inline-block mt-1 font-bold text-slate-800 hover:text-blue-600 transition">Se connecter</a>
        </div>
    </div>

    <footer class="w-full text-center text-[10px] font-bold text-slate-400 uppercase tracking-wider p-4">
        © 2026 ClickPharma — Solution Numérique de Santé.
    </footer>

    <script>
        function switchRole(role) {
            document.getElementById('roleInput').value = role;
            
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('bg-slate-800', 'text-white', 'shadow-sm');
            });
            document.getElementById('tab-' + role).classList.add('bg-slate-800', 'text-white', 'shadow-sm');

            document.querySelectorAll('.role-fields').forEach(form => {
                form.classList.add('hidden');
            });
            document.getElementById('form-' + role).classList.remove('hidden');

            // Gérer les champs obligatoires textuels
            document.querySelectorAll('.role-fields input').forEach(input => {
                input.required = false;
            });
            document.getElementById('form-' + role).querySelectorAll('input').forEach(input => {
                if(!input.name.includes('phone') && !input.name.includes('adresse') && !input.name.includes('ville')) {
                    input.required = true;
                }
            });

            // Afficher ou masquer le bloc de paiement
            const paymentBlock = document.getElementById('paymentSelectionBlock');
            if (role === 'patient') {
                paymentBlock.classList.add('hidden');
            } else {
                paymentBlock.classList.remove('hidden');
            }

            // Mettre à jour l'intitulé de la pièce jointe
            updateFileLabels(role);
        }

        function updateFileLabels(role) {
            const fLabel = document.getElementById('fileLabel');
            const fHelp = document.getElementById('fileHelp');
            const d = labels[currentLang];

            if (role === 'patient') {
                fLabel.innerHTML = d.fileLabelPatient;
                fHelp.innerText = d.fileHelpPatient;
            } else {
                fLabel.innerHTML = d.fileLabelPro;
                fHelp.innerText = d.fileHelpPro;
            }
        }

        let currentLang = 'fr';
        const labels = {
            fr: {
                title: "Rejoindre ClickPharma", subtitle: "Sélectionnez votre profil pour configurer votre espace dédié",
                tabP: "Patient", tabM: "Médecin", tabPh: "Pharmacie", submit: "Créer mon compte",
                hasAcc: "Vous possédez déjà un compte ?", log: "Se connecter",
                userLbl: "Identifiant unique (Email)", passLbl: "Mot de passe",
                authTitle: "<i class='fa-solid fa-lock text-[9px]'></i> Informations de Connexion",
                pTitle: "<i class='fa-solid fa-address-card text-[9px]'></i> Profil Patient",
                mTitle: "<i class='fa-solid fa-stethoscope text-[9px]'></i> Cabinet Praticien",
                phTitle: "<i class='fa-solid fa-house-medical text-[9px]'></i> Identification Officine",
                payTitle: "<i class='fa-solid fa-credit-card text-[9px]'></i> Sélectionner un moyen de paiement",
                fileLabelPatient: "<i class='fa-solid fa-paperclip text-blue-600'></i> Pièce jointe (Carte Chifa / Identité)",
                fileHelpPatient: "Veuillez transmettre un scan lisible au format JPG, PNG ou PDF.",
                fileLabelPro: "<i class='fa-solid fa-file-invoice-dollar text-blue-600'></i> Reçu ou preuve de versement",
                fileHelpPro: "Téléversez le reçu généré après votre transaction Baridimob ou CIB pour valider l'adhésion."
            },
            ar: {
                title: "الانضمام إلى كليك فارما", subtitle: "اختر نوع حسابك لتكوين فضائك المخصص",
                tabP: "مريض", tabM: "طبيب", tabPh: "صيدلية", submit: "إنشاء حسابي الآن",
                hasAcc: "لديك حساب بالفعل؟", log: "تسجيل الدخول",
                userLbl: "المعرف الفريد (البريد الإلكتروني)", passLbl: "كلمة المرور",
                authTitle: "<i class='fa-solid fa-lock text-[9px]'></i> معلومات تسجيل الدخول",
                pTitle: "<i class='fa-solid fa-address-card text-[9px]'></i> ملف المريض",
                mTitle: "<i class='fa-solid fa-stethoscope text-[9px]'></i> معلومات الطبيب",
                phTitle: "<i class='fa-solid fa-house-medical text-[9px]'></i> معلومات الصيدلية",
                payTitle: "<i class='fa-solid fa-credit-card text-[9px]'></i> اختر طريقة الدفع",
                fileLabelPatient: "<i class='fa-solid fa-paperclip text-blue-600'></i> وثيقة إثبات الهوية (بطاقة الشفاء / الهوية)",
                fileHelpPatient: "يرجى تحميل نسخة واضحة بصيغة JPG، PNG أو PDF.",
                fileLabelPro: "<i class='fa-solid fa-file-invoice-dollar text-blue-600'></i> وصل تأكيد عملية الدفع",
                fileHelpPro: "يرجى تحميل الوصل الناتج عن عملية الدفع عبر بريديموب أو البطاقة النقدية لتفعيل الحساب."
            }
        };

        function toggleLang() {
            currentLang = currentLang === 'fr' ? 'ar' : 'fr';
            const d = labels[currentLang];
            const activeRole = document.getElementById('roleInput').value;
            
            document.getElementById('mainHtml').setAttribute('lang', currentLang);
            document.getElementById('mainHtml').setAttribute('dir', currentLang === 'ar' ? 'rtl' : 'ltr');

            document.getElementById('title').innerText = d.title;
            document.getElementById('subtitle').innerText = d.subtitle;
            document.querySelector('.tab-text-p').innerText = d.tabP;
            document.querySelector('.tab-text-m').innerText = d.tabM;
            document.querySelector('.tab-text-ph').innerText = d.tabPh;
            document.getElementById('userLabel').innerText = d.userLbl;
            document.getElementById('passLabel').innerText = d.passLbl;
            document.getElementById('sectionAuthTitle').innerHTML = d.authTitle;
            document.getElementById('sectionPatientTitle').innerHTML = d.pTitle;
            document.getElementById('sectionMedTitle').innerHTML = d.mTitle;
            document.getElementById('sectionPharmaTitle').innerHTML = d.phTitle;
            document.getElementById('paymentTitle').innerHTML = d.payTitle;
            document.getElementById('submitBtn').innerText = d.submit;
            document.getElementById('hasAccount').innerText = d.hasAcc;
            document.getElementById('loginLink').innerText = d.log;
            document.getElementById('langBtn').innerHTML = currentLang === 'fr' ? "<i class='fa-solid fa-globe text-blue-500 mr-1 ml-1'></i> العربية" : "<i class='fa-solid fa-globe text-blue-500 mr-1 ml-1'></i> Français";

            updateFileLabels(activeRole);

            document.querySelectorAll('label').forEach(lbl => {
                if(currentLang === 'ar') { lbl.classList.add('text-right'); lbl.classList.remove('text-left'); }
                else { lbl.classList.add('text-left'); lbl.classList.remove('text-right'); }
            });
        }
    </script>
</body>
</html>