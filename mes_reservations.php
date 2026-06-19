<?php
// C:\xampp\htdocs\ClickPharma\mes_reservations.php
session_start();
require_once 'config/db.php';

// On imagine que l'ID du patient connecté est stocké en session
if (!isset($_SESSION['id_patient'])) {
    // Rediriger vers la connexion patient si nécessaire
    // header('Location: login_patient.php');
}

// ID simulé ou récupéré pour le test
$id_patient = isset($_SESSION['id_patient']) ? $_SESSION['id_patient'] : 1; 

// Récupérer les réservations de ce patient
$query = "SELECT r.*, p.nom_pharmacie 
          FROM reservations r
          JOIN pharmacies p ON r.id_pharmacie = p.id
          WHERE r.id_patient = ? 
          ORDER BY r.date_commande DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([$id_patient]);
$mes_demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations | ClickPharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#f8fafc] min-h-screen">

    <nav class="bg-[#00966b] text-white px-8 py-4 shadow-sm flex justify-between items-center">
        <span class="text-xl font-extrabold tracking-tight">CLICKPHARMA</span>
        <span class="text-xs font-bold uppercase bg-[#00825c] px-4 py-2 rounded-full"><i class="fa-solid fa-user mr-1"></i> Espace Patient</span>
    </nav>

    <main class="max-w-4xl mx-auto p-8 space-y-6">
        <div>
            <h2 class="text-2xl font-extrabold text-[#1e293b]">Suivi de mes ordonnances</h2>
            <p class="text-xs text-slate-400 mt-0.5">Consultez l'état de validation de vos demandes auprès des pharmacies.</p>
        </div>

        <div class="space-y-4">
            <?php if(empty($mes_demandes)): ?>
                <div class="bg-white rounded-3xl p-8 text-center text-slate-400 border border-slate-100">
                    <i class="fa-solid fa-receipt text-4xl text-slate-200 mb-2 block"></i>
                    Aucune demande envoyée pour le moment.
                </div>
            <?php else: ?>
                <?php foreach($mes_demandes as $demande): 
                    $statut = trim($demande['statut']);
                    $is_traite = (strcasecmp($statut, 'Prêt') == 0 || strcasecmp($statut, 'Refusé') == 0 || strcasecmp($statut, 'accepte') == 0 || strcasecmp($statut, 'refuse') == 0);
                ?>
                    <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider"><i class="fa-regular fa-clock"></i> <?= date('d/m/Y à H:i', strtotime($demande['date_commande'])) ?></span>
                            <h3 class="font-bold text-slate-800 text-base"><?= htmlspecialchars($demande['nom_pharmacie']) ?></h3>
                            <p class="text-xs text-slate-500 font-mono bg-slate-50 px-2.5 py-1 rounded-xl border border-slate-100 inline-block"><?= htmlspecialchars($demande['medicament_demande']) ?></p>
                        </div>

                        <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
                            <!-- Affichage dynamique du badge de confirmation -->
                            <?php if(strcasecmp($statut, 'Prêt') == 0 || strcasecmp($statut, 'accepte') == 0): ?>
                                <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Disponible / Prêt
                                </span>
                            <?php elseif(strcasecmp($statut, 'Refusé') == 0 || strcasecmp($statut, 'refuse') == 0): ?>
                                <span class="bg-red-50 text-red-600 border border-red-100 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">
                                    <i class="fa-solid fa-circle-xmark mr-1"></i> Refusé
                                </span>
                            <?php else: ?>
                                <span class="bg-amber-50 text-amber-600 border border-amber-100 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide animate-pulse">
                                    <i class="fa-solid fa-spinner animate-spin mr-1"></i> En attente de validation
                                </span>
                            <?php endif; ?>

                            <!-- RÈGLE MÉTIER : Le bouton est désactivé/masqué si la demande est déjà traitée par le pharmacien -->
                            <?php if($is_traite): ?>
                                <button disabled class="bg-slate-100 text-slate-400 px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider cursor-not-allowed border border-slate-200/60">
                                    <i class="fa-solid fa-lock mr-1"></i> Réservé
                                </button>
                            <?php else: ?>
                                <button class="bg-[#00966b] hover:bg-[#00825c] text-white px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-sm">
                                    Relancer
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>