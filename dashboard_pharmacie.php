<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['id_pharmacie'])) {
    header('Location: login_pharmacie.php');
    exit();
}

$id_pharma = $_SESSION['id_pharmacie'];

try {
    // Récupération de l'ensemble des réservations pour cette pharmacie ordonnées par date
    $query = "SELECT id, nom_client, nss, telephone_client, medicament_demande, statut, date_commande 
              FROM reservations 
              WHERE id_pharmacie = ? 
              ORDER BY date_commande DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_pharma]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de récupération : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservations d'ordonnances | ClickPharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen p-6">

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">RÉSERVATIONS D'ORDONNANCES</h1>
                <p class="text-sm text-slate-400 font-medium mt-1">Gérez et validez les demandes envoyées par vos patients.</p>
            </div>
            <button onclick="window.location.reload();" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold px-4 py-2 rounded-xl text-xs uppercase tracking-wider shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-rotate"></i> Actualiser
            </button>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                            <th class="p-4">Date / Heure</th>
                            <th class="p-4">Patient</th>
                            <th class="p-4">NSS</th>
                            <th class="p-4">Téléphone</th>
                            <th class="p-4">Médicaments Demandés</th>
                            <th class="p-4 text-center">Statut</th>
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-semibold text-slate-700">
                        <?php if (empty($reservations)): ?>
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400 italic">Aucune demande de réservation reçue pour le moment.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservations as $row): 
                                $date_f = date('d/m/Y H:i', strtotime($row['date_commande']));
                                
                                // Styles des badges de statut
                                $status_classes = "bg-amber-50 text-amber-600";
                                if (strcasecmp($row['statut'], 'Validée') == 0) { $status_classes = "bg-emerald-50 text-emerald-600"; }
                                if (strcasecmp($row['statut'], 'Annulée') == 0) { $status_classes = "bg-red-50 text-red-600"; }
                            ?>
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="p-4 text-slate-500 font-medium"><?= $date_f ?></td>
                                    <td class="p-4 font-bold text-slate-800"><?= htmlspecialchars($row['nom_client']) ?></td>
                                    <td class="p-4 font-mono text-slate-400 tracking-wide"><?= htmlspecialchars($row['nss']) ?></td>
                                    <td class="p-4 font-mono text-slate-500"><?= htmlspecialchars($row['telephone_client'] ?? 'N/A') ?></td>
                                    <td class="p-4">
                                        <span class="bg-slate-100 px-2.5 py-1 rounded-md text-slate-700 border border-slate-200/60 font-mono text-[11px]">
                                            <?= htmlspecialchars($row['medicament_demande']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full <?= $status_classes ?>">
                                            <?= htmlspecialchars($row['statut']) ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <?php if ($row['statut'] === 'en_attente'): ?>
                                                <a href="traiter_reservation.php?id=<?= $row['id'] ?>&action=valider" class="bg-emerald-500 hover:bg-emerald-600 text-white w-8 h-8 rounded-xl transition flex items-center justify-center shadow-sm" title="Valider la réservation">
                                                    <i class="fa-solid fa-check text-sm"></i>
                                                </a>
                                                <a href="traiter_reservation.php?id=<?= $row['id'] ?>&action=annuler" class="bg-red-500 hover:bg-red-600 text-white w-8 h-8 rounded-xl transition flex items-center justify-center shadow-sm" title="Annuler la réservation">
                                                    <i class="fa-solid fa-xmark text-sm"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-[10px] text-slate-300 italic font-medium">Traité</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>