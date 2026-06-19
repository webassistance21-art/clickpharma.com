<?php
require_once 'config/db.php'; // Connexion à ta base pharmalife_db
// Gestion de la langue (Par défaut : Français)
$lang = $_GET['lang'] ?? 'fr';
if (!in_array($lang, ['fr', 'ar'])) {
    $lang = 'fr';
}

// Traductions riches et professionnelles
$texts = [
    'fr' => [
        'title' => 'Portail National Interconnecté | ClickPharma',
        'sub' => 'Sécurisé & Géolocalisé',
        'heading' => 'Bienvenue sur ClickPharma',
        'desc' => 'L\'écosystème numérique qui connecte instantanément les patients, les médecins et les officines pour un accès fluide et sécurisé aux médicaments.',
        
        'patient_badge' => 'Accès Public',
        'patient_title' => 'Espace Patient',
        'patient_desc' => 'Recherchez vos traitements, géolocalisez les pharmacies disponibles à proximité et scannez le code QR de vos ordonnances.',
        'patient_btn' => 'Lancer une recherche',

        'doctor_badge' => 'Praticiens',
        'doctor_title' => 'Espace Médecin',
        'doctor_desc' => 'Générez des ordonnances numériques sécurisées, suivez l\'historique de vos patients et réduisez les erreurs de prescription.',
        'doctor_btn' => 'Accéder au cabinet',

        'pharma_badge' => 'Officines',
        'pharma_title' => 'Espace Pharmacien',
        'pharma_desc' => 'Gerez vos stocks en temps réel, authentifiez les ordonnances par QR code et optimisez vos délivrances.',
        'pharma_btn' => 'Ouvrir la session',

        // Section Contact
        'contact_title' => 'Contactez notre support',
        'contact_sub' => 'Une question ou une assistance technique ? Nos équipes vous répondent.',
        'label_name' => 'Nom complet',
        'label_email' => 'Adresse email',
        'label_msg' => 'Votre message',
        'btn_send' => 'Envoyer le message',

        'footer' => '© 2026 ClickPharma — Solution Numérique de Santé Intégrée.',
        'switch_lang' => 'العربية',
        'target_lang' => 'ar',
        'register_btn' => "S'inscrire"
    ],
    'ar' => [
        'title' => 'البوابة الوطنية الموحدة | ClickPharma',
        'sub' => 'آمن ومحدد الجغرافيا',
        'heading' => 'مرحباً بكم في ClickPharma',
        'desc' => 'النظام الرقمي المتكامل الذي يربط بين المرضى، الأطباء، والصيدليات لضمان وصول سريع وآمن للدواء في جميع الأوقات.',
        
        'patient_badge' => 'دخول عام',
        'patient_title' => 'فضاء المريض',
        'patient_desc' => 'ابحث عن أدويتك، حدد مواقع الصيدليات القريبة المتاحة، وامسح رمز الاستجابة السريعة لوصفاتك الطبية.',
        'patient_btn' => 'بدء البحث الآن',

        'doctor_badge' => 'الممارسون',
        'doctor_title' => 'فضاء الطبيب',
        'doctor_desc' => 'قم بتوليد وصفات طبية رقمية مؤمنة، تابع سجلات مرضاك، وقلل من أخطاء السحب الطبي بشكل فعال.',
        'doctor_btn' => 'دخول العيادة الرقمية',

        'pharma_badge' => 'الصيدليات',
        'pharma_title' => 'فضاء الصيدلي',
        'pharma_desc' => 'حدث مخزونك في الوقت الفعلي، تحقق من صحة الوصفات عبر مسح الرمز، ونظم عمليات البيع اليومية.',
        'pharma_btn' => 'فتح جلسة العمل',

        // Section Contact
        'contact_title' => 'اتصل بالدعم الفني',
        'contact_sub' => 'هل لديك سؤال أو تحتاج إلى مساعدة تقنية؟ فريقنا في خدمتكم.',
        'label_name' => 'الاسم الكامل',
        'label_email' => 'البريد الإلكتروني',
        'label_msg' => 'رسالتكم',
        'btn_send' => 'إرسال الرسالة',

        'footer' => '© 2026 ClickPharma — الحل الرقمي الموحد لقطاع الصحة.',
        'switch_lang' => 'Français',
        'target_lang' => 'fr',
        'register_btn' => 'إنشاء حساب'
    ]
];

$t = $texts[$lang];
$dir = ($lang === 'ar') ? 'rtl' : 'ltr';

$success_msg = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_contact'])) {
    $nom = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($nom) && !empty($email) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages_contact (nom, email, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$nom, $email, $message])) {
            $success_msg = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['title'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;700;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { 
            font-family: <?= ($lang === 'ar') ? "'Cairo', sans-serif" : "'Plus Jakarta Sans', sans-serif" ?>; 
        }
        .glow-bg {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(16, 185, 129, 0.04) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(14, 116, 144, 0.04) 0px, transparent 50%);
        }
    </style>
</head>
<body class="glow-bg min-h-screen flex flex-col justify-between antialiased">

    <nav class="w-full px-6 pt-6">
        <div class="max-w-6xl mx-auto flex justify-between items-center bg-white/70 backdrop-blur-xl border border-slate-200/50 px-6 py-3 rounded-3xl shadow-sm">
            
            <a href="index.php?lang=<?= $lang ?>" class="flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl overflow-hidden bg-white shadow-sm group-hover:scale-105 transition-all duration-200 flex items-center justify-center">
                    <img src="images\Pharma (1).png" alt="ClickPharma Logo" class="w-full h-full object-cover">
                </div>
                <span class="text-xl font-black text-slate-800 tracking-tight">CLICK<span class="text-[#00966b]">PHARMA</span></span>
            </a>
            
            <div class="flex items-center gap-3">
                <a href="register.php?lang=<?= $lang ?>" class="flex items-center gap-2 text-xs font-bold text-white bg-[#00966b] hover:bg-[#00825c] px-4 py-2.5 rounded-2xl shadow-sm transition-all duration-200 hover:scale-[1.02]">
                    <i class="fa-solid fa-user-plus"></i>
                    <span><?= $t['register_btn'] ?></span>
                </a>

                <a href="index.php?lang=<?= $t['target_lang'] ?>" class="flex items-center gap-2 text-xs font-bold text-slate-600 hover:text-[#00966b] bg-white border border-slate-200 px-4 py-2.5 rounded-2xl shadow-sm transition-all duration-200 hover:scale-[1.02]">
                    <i class="fa-solid fa-globe text-[#00966b]"></i>
                    <span><?= $t['switch_lang'] ?></span>
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl w-full mx-auto px-6 py-12 space-y-16">
        
        <div class="text-center space-y-3 max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-[#00966b] px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest border border-emerald-100">
                <i class="fa-solid fa-circle-check animate-pulse"></i> <?= $t['sub'] ?>
            </span>
            <h1 class="text-4xl font-black text-slate-800 tracking-tight sm:text-5xl leading-none">
                <?= $t['heading'] ?>
            </h1>
            <p class="text-sm font-medium text-slate-400 leading-relaxed pt-2">
                <?= $t['desc'] ?>
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">

            <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm flex flex-col justify-between space-y-8 hover:shadow-xl hover:border-blue-500/20 transition-all duration-300 relative group overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-all"></div>
                <div class="space-y-4 <?= ($lang === 'ar') ? 'text-right' : 'text-left' ?>">
                    <span class="inline-block bg-blue-50 text-blue-600 text-[10px] font-bold px-3 py-1 rounded-md uppercase"><?= $t['doctor_badge'] ?></span>
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 border border-blue-100 rounded-2xl flex items-center justify-center text-2xl shadow-inner"><i class="fa-solid fa-user-doctor"></i></div>
                    <h2 class="text-xl font-extrabold text-slate-800 tracking-tight"><?= $t['doctor_title'] ?></h2>
                    <p class="text-xs text-slate-400 font-medium leading-relaxed"><?= $t['doctor_desc'] ?></p>
                </div>
                <a href="login.php" class="w-full py-4 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs text-center rounded-2xl shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                    <span><?= $t['doctor_btn'] ?></span>
                    <i class="fa-solid <?= ($lang === 'ar') ? 'fa-arrow-left' : 'fa-arrow-right' ?> text-[10px]"></i>
                </a>
            </div>
            
            <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm flex flex-col justify-between space-y-8 hover:shadow-xl hover:border-emerald-500/20 transition-all duration-300 relative group overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
                <div class="space-y-4 <?= ($lang === 'ar') ? 'text-right' : 'text-left' ?>">
                    <span class="inline-block bg-emerald-50 text-[#00966b] text-[10px] font-bold px-3 py-1 rounded-md uppercase"><?= $t['patient_badge'] ?></span>
                    <div class="w-14 h-14 bg-emerald-50 text-[#00966b] border border-emerald-100 rounded-2xl flex items-center justify-center text-2xl shadow-inner"><i class="fa-solid fa-user-injured"></i></div>
                    <h2 class="text-xl font-extrabold text-slate-800 tracking-tight"><?= $t['patient_title'] ?></h2>
                    <p class="text-xs text-slate-400 font-medium leading-relaxed"><?= $t['patient_desc'] ?></p>
                </div>
                <a href="login_patient.php?lang=<?= $lang ?>" class="w-full py-4 bg-[#00966b] hover:bg-[#00825c] text-white font-bold text-xs text-center rounded-2xl shadow-md shadow-emerald-700/10 transition-all duration-200 flex items-center justify-center gap-2">
                    <span><?= $t['patient_btn'] ?></span>
                    <i class="fa-solid <?= ($lang === 'ar') ? 'fa-arrow-left' : 'fa-arrow-right' ?> text-[10px]"></i>
                </a>
            </div>

            <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm flex flex-col justify-between space-y-8 hover:shadow-xl hover:border-cyan-500/20 transition-all duration-300 relative group overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-cyan-500/5 rounded-full blur-2xl group-hover:bg-cyan-500/10 transition-all"></div>
                <div class="space-y-4 <?= ($lang === 'ar') ? 'text-right' : 'text-left' ?>">
                    <span class="inline-block bg-cyan-50 text-cyan-600 text-[10px] font-bold px-3 py-1 rounded-md uppercase"><?= $t['pharma_badge'] ?></span>
                    <div class="w-14 h-14 bg-cyan-50 text-cyan-600 border border-cyan-100 rounded-2xl flex items-center justify-center text-2xl shadow-inner"><i class="fa-solid fa-prescription-bottle-medical"></i></div>
                    <h2 class="text-xl font-extrabold text-slate-800 tracking-tight"><?= $t['pharma_title'] ?></h2>
                    <p class="text-xs text-slate-400 font-medium leading-relaxed"><?= $t['pharma_desc'] ?></p>
                </div>
                <a href="login_pharmacie.php" class="w-full py-4 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold text-xs text-center rounded-2xl shadow-sm transition-all duration-200 flex items-center justify-center gap-2">
                    <span><?= $t['pharma_btn'] ?></span>
                    <i class="fa-solid <?= ($lang === 'ar') ? 'fa-arrow-left' : 'fa-arrow-right' ?> text-[10px]"></i>
                </a>
            </div>
        </div>

        <section class="max-w-3xl mx-auto bg-white border border-slate-100 rounded-[2.5rem] p-8 md:p-10 shadow-sm space-y-6">
            <div class="text-center space-y-2">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight"><?= $t['contact_title'] ?></h2>
                <p class="text-xs font-semibold text-slate-400 max-w-md mx-auto leading-relaxed"><?= $t['contact_sub'] ?></p>
            </div>

            <?php if ($success_msg): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-[#00966b] text-center font-bold text-xs shadow-inner">
                    <i class="fa-solid fa-circle-check mr-1 ml-1 animate-bounce"></i>
                    <?= $lang === 'ar' ? 'تم ارسال رسالتكم بنجاح! شكراً لكم.' : 'Votre message a été envoyé avec succès ! Merci.' ?>
                </div>
            <?php endif; ?>

            <form action="index.php?lang=<?= $lang ?>" method="POST" class="space-y-4 font-semibold text-slate-700 text-xs">
                <input type="hidden" name="action_contact" value="1">
                
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5 <?= ($lang === 'ar') ? 'text-right' : 'text-left' ?>">
                        <label class="text-slate-400 px-1"><?= $t['label_name'] ?></label>
                        <input type="text" name="name" required class="w-full p-3.5 rounded-xl border border-slate-200 outline-none focus:ring-4 ring-emerald-500/5 transition">
                    </div>
                    <div class="space-y-1.5 <?= ($lang === 'ar') ? 'text-right' : 'text-left' ?>">
                        <label class="text-slate-400 px-1"><?= $t['label_email'] ?></label>
                        <input type="email" name="email" required class="w-full p-3.5 rounded-xl border border-slate-200 outline-none focus:ring-4 ring-emerald-500/5 transition">
                    </div>
                </div>
                <div class="space-y-1.5 <?= ($lang === 'ar') ? 'text-right' : 'text-left' ?>">
                    <label class="text-slate-400 px-1"><?= $t['label_msg'] ?></label>
                    <textarea name="message" rows="4" required class="w-full p-3.5 rounded-xl border border-slate-200 outline-none focus:ring-4 ring-emerald-500/5 transition resize-none"></textarea>
                </div>
                <div class="text-center pt-2">
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold px-8 py-3.5 rounded-xl transition-all duration-200 hover:scale-[1.01] shadow-sm">
                        <?= $t['btn_send'] ?>
                    </button>
                </div>
            </form>
        </section>
    </main>

    <footer class="w-full p-8 border-t border-slate-200/40 bg-white/40 backdrop-blur-md">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest order-2 md:order-1">
                <?= $t['footer'] ?>
            </p>
            
            <div class="flex items-center gap-4 text-slate-400 text-lg order-1 md:order-2">
                <a href="#" class="hover:text-[#00966b] transition-all transform hover:scale-110" title="Facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="#" class="hover:text-[#00966b] transition-all transform hover:scale-110" title="LinkedIn">
                    <i class="fa-brands fa-linkedin-in"></i>
                </a>
                <a href="#" class="hover:text-[#00966b] transition-all transform hover:scale-110" title="Twitter / X">
                    <i class="fa-brands fa-x-twitter"></i>
                </a>
                <a href="#" class="hover:text-[#00966b] transition-all transform hover:scale-110" title="Instagram">
                    <i class="fa-brands fa-instagram"></i>
                </a>
            </div>
            
        </div>
    </footer>

</body>
</html>