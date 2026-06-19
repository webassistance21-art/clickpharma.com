<?php
// C:\xampp\htdocs\ClickPharma\dashboard_reservations.php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['id_pharmacie'])) {
    header('Location: login_pharmacie.php');
    exit();
}

$id_session = $_SESSION['id_pharmacie'];

// 1. Récupérer le bon ID réel de la pharmacie (gestion de la double clé id/id_utilisateur)
$stmt = $pdo->prepare("SELECT id, nom_pharmacie FROM pharmacies WHERE id = ? OR id_utilisateur = ?");
$stmt->execute([$id_session, $id_session]);
$pharma = $stmt->fetch();
$id_pharma_reel = $pharma ? $pharma['id'] : $id_session;

// 2. Récupérer TOUTES les réservations de cette pharmacie
try {
    $query = "SELECT id, nom_client, nss, telephone_client, medicament_demande, statut, date_commande 
              FROM reservations 
              WHERE id_pharmacie = ? 
              ORDER BY date_commande DESC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_pharma_reel]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erreur_sql = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations | ClickPharma Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#f8fafc] min-h-screen">

    <nav class="bg-[#00966b] text-white px-8 py-4 shadow-sm flex justify-between items-center">
        <div class="flex items-center gap-4">
            <a href="dashboard_pharmacie.php" class="text-white/80 hover:text-white transition text-sm flex items-center gap-1.5 font-bold uppercase tracking-wider">
                <i class="fa-solid fa-arrow-left"></i> Stock
            </a>
            <span class="text-xl font-extrabold tracking-tight">CLICKPHARMA<span class="font-normal opacity-80 text-xs ml-0.5 uppercase tracking-widest">Pro</span></span>
        </div>
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2 bg-[#00825c] px-4 py-2 rounded-full text-xs font-bold">
                <i class="fa-solid fa-hospital-user text-sm"></i>
                <span><?= htmlspecialchars($pharma['nom_pharmacie'] ?? 'Pharmacie Connectée') ?></span>
            </div>
            <a href="login_patient.php" class="text-xs font-bold uppercase tracking-wider opacity-90 hover:opacity-100 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-power-off"></i> Déconnexion
            </a>
        </div>
    </nav>

    <main class="max-w-[1400px] mx-auto p-8 space-y-8">
        
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-extrabold text-[#1e293b] tracking-tight uppercase">Réservations d'Ordonnances</h2>
                <p class="text-sm text-slate-400 font-medium mt-0.5">Gérez et validez les demandes envoyées par vos patients.</p>
            </div>
            <button onclick="location.reload()" class="bg-white hover:bg-slate-50 text-slate-700 px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold uppercase tracking-wider flex items-center gap-2 shadow-sm transition">
                <i class="fa-solid fa-rotate"></i> Actualiser
            </button>
        </div>

        <?php if (isset($erreur_sql)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-2xl text-xs font-semibold">
                <strong>Erreur SQL :</strong> <?= htmlspecialchars($erreur_sql); ?>
            </div>
        <?php elseif (empty($reservations)): ?>
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-12 text-center text-slate-400">
                <i class="fa-solid fa-inbox text-5xl text-slate-200 mb-3 block"></i>
                <p class="font-medium text-sm">Aucune demande de réservation reçue pour le moment.</p>
            </div>
        <?php else: ?>
            
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="pb-4 w-[15%]">Date / Heure</th>
                                <th class="pb-4 w-[20%]">Patient</th>
                                <th class="pb-4 w-[15%]">NSS</th>
                                <th class="pb-4 w-[15%]">Téléphone</th>
                                <th class="pb-4 w-[20%]">Médicaments Demandés</th>
                                <th class="pb-4 text-center w-[10%]">Statut</th>
                                <th class="pb-4 text-right w-[15%]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100/70">
                            <?php foreach ($reservations as $res): 
                                $status = trim(strtolower($res['statut']));
                                
                                // ALIGNEMENT STRICT AVEC LES VALEURS DE LA BDD
                                if ($status === 'valide') {
                                    $badge_style = "bg-emerald-50 text-emerald-600 border border-emerald-100";
                                    $label = "Prêt / Validé";
                                } elseif ($status === 'annule') {
                                    $badge_style = "bg-red-50 text-red-600 border border-red-100";
                                    $label = "Refusé / Annulé";
                                } else {
                                    $badge_style = "bg-amber-50 text-amber-600 border border-amber-100 animate-pulse";
                                    $label = "En attente";
                                }

                                $date_formatee = date('d/m/Y H:i', strtotime($res['date_commande']));
                            ?>
                                <tr class="hover:bg-slate-50/40 transition">
                                    <td class="py-5 font-semibold text-slate-700 text-sm"><?= $date_formatee ?></td>
                                    <td class="py-5 font-bold text-slate-800 text-sm"><?= htmlspecialchars($res['nom_client']) ?></td>
                                    <td class="py-5 text-slate-500 font-mono text-xs"><?= htmlspecialchars($res['nss']) ?></td>
                                    <td class="py-5 text-slate-600 text-sm"><?= htmlspecialchars($res['telephone_client']) ?></td>
                                    <td class="py-5">
                                        <span class="inline-block bg-slate-50 text-slate-700 text-xs font-mono px-2 py-1 rounded border border-slate-100 max-w-xs truncate" title="<?= htmlspecialchars($res['medicament_demande']) ?>">
                                            <?= htmlspecialchars($res['medicament_demande']) ?>
                                        </span>
                                    </td>
                                    <td class="py-5 text-center">
                                        <span id="badge-<?= $res['id'] ?>" class="inline-block px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider <?= $badge_style ?>">
                                            <?= $label ?>
                                        </span>
                                    </td>
                                    <td class="py-5 text-right" id="actions-<?= $res['id'] ?>">
                                        <?php if ($status === 'en_attente' || empty($status)): ?>
                                            <div class="flex justify-end gap-2">
                                                <button onclick="modifierStatut(<?= $res['id'] ?>, 'accepter')" class="bg-emerald-500 hover:bg-emerald-600 text-white p-2 rounded-xl text-xs font-bold transition flex items-center gap-1">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                                <button onclick="modifierStatut(<?= $res['id'] ?>, 'refuser')" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-xl text-xs font-bold transition flex items-center gap-1">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400 font-semibold"><i class="fa-solid fa-circle-check"></i> Traité</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
    function modifierStatut(idReservation, action) {
        const confirmation = confirm(`Voulez-vous vraiment ${action === 'accepter' ? 'accepter' : 'refuser'} cette demande ?`);
        if (!confirmation) return;

        const formData = new FormData();
        formData.append('id_reservation', idReservation);
        formData.append('action', action);

        fetch('action_reservation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById(`badge-${idReservation}`);
                badge.innerText = data.nouveau_statut;
                
                if (action === 'accepter') {
                    badge.className = 'inline-block px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-600 border border-emerald-100';
                } else {
                    badge.className = 'inline-block px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-red-50 text-red-600 border border-red-100';
                }

                const cellActions = document.getElementById(`actions-${idReservation}`);
                cellActions.innerHTML = '<span class="text-xs text-slate-400 font-semibold"><i class="fa-solid fa-circle-check"></i> Traité</span>';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert("Une erreur est survenue lors de la communication avec le serveur.");
        });
    }
    </script>
</body>
</html>