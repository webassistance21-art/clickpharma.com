<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['id_pharmacie'])) {
    header('Location: login_pharmacie.php');
    exit();
}

$id_session = $_SESSION['id_pharmacie'];

$stmt = $pdo->prepare("SELECT id, nom_pharmacie FROM pharmacies WHERE id = ? OR id_utilisateur = ?");
$stmt->execute([$id_session, $id_session]);
$pharma = $stmt->fetch();
$id_pharma_reel = $pharma ? $pharma['id'] : $id_session;

$query = "SELECT s.id_stock, s.quantite, s.prix, m.nom_medicament, m.forme 
          FROM stocks s 
          JOIN medicaments m ON s.id_medicament = m.id_medoc 
          WHERE s.id_pharmacie = ?
          ORDER BY m.nom_medicament ASC";
$stmt = $pdo->prepare($query);
$stmt->execute([$id_pharma_reel]);
$stocks = $stmt->fetchAll();

$total_produits = count($stocks);
$stock_faible = 0;
$ruptures = 0;

foreach ($stocks as $s) {
    if ($s['quantite'] == 0) {
        $ruptures++;
    } elseif ($s['quantite'] <= 5) {
        $stock_faible++;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord | ClickPharma Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#f8fafc] min-h-screen relative">

    <nav class="bg-[#00966b] text-white px-8 py-4 shadow-sm flex justify-between items-center sticky top-0 z-40">
        <span class="text-xl font-extrabold tracking-tight">CLICKPHARMA<span class="font-normal opacity-80 text-xs ml-0.5 uppercase tracking-widest">Pro</span></span>
        
        <div class="flex items-center gap-6">
            <div class="relative">
                <button onclick="toggleNotificationDropdown()" class="relative p-2 text-white/90 hover:text-white focus:outline-none transition bg-[#00825c] hover:bg-[#006e4e] rounded-full w-10 h-10 flex items-center justify-center">
                    <i class="fa-solid fa-bell text-lg"></i>
                    <span id="notif-badge" class="hidden absolute -top-1 -right-1 bg-red-500 text-white font-extrabold text-[10px] w-5 h-5 rounded-full flex items-center justify-center border-2 border-[#00966b] animate-bounce">
                        0
                    </span>
                </button>

                <div id="notif-dropdown" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-2xl border border-slate-100 shadow-xl overflow-hidden z-50 animate-in fade-in slide-in-from-top-3 duration-200">
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                        <span class="font-bold text-xs text-slate-700 uppercase tracking-wider">Demandes Récentes</span>
                        <span id="notif-header-count" class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full">0 en attente</span>
                    </div>
                    
                    <div id="notif-list" class="max-h-72 overflow-y-auto divide-y divide-slate-100">
                        <div class="p-4 text-center text-slate-400 text-xs font-medium">
                            <i class="fa-solid fa-circle-notch animate-spin text-base text-[#00966b] mb-1 block"></i>
                            Vérification des demandes...
                        </div>
                    </div>
                    
                    <div class="p-2 border-t border-slate-100 bg-slate-50 text-center">
                        <a href="dashboard_reservations.php" class="block w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[11px] font-bold uppercase tracking-wider transition">
                            Voir tout l'espace réservations
                        </a>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 bg-[#00825c] px-4 py-2 rounded-full text-xs font-bold">
                <i class="fa-solid fa-hospital-user text-sm"></i>
                <span><?= htmlspecialchars($pharma['nom_pharmacie'] ?? 'Pharmacie Connectée') ?></span>
            </div>
            <a href="logout.php" class="text-xs font-bold uppercase tracking-wider opacity-90 hover:opacity-100 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-power-off"></i> Déconnexion
            </a>
        </div>
    </nav>

    <main class="max-w-[1400px] mx-auto p-8 space-y-8">
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-2xl text-xs font-semibold">
                <i class="fa-solid fa-circle-check mr-1"></i> Stock mis à jour avec succès.
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-700 p-4 rounded-2xl text-xs font-semibold">
                <i class="fa-solid fa-trash-can mr-1"></i> Médicament retiré de votre inventaire.
            </div>
        <?php endif; ?>

        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-extrabold text-[#1e293b] tracking-tight uppercase">Tableau de bord</h2>
                <p class="text-sm text-slate-400 font-medium mt-0.5">Vue d'ensemble de votre inventaire.</p>
            </div>
            <a href="ajouter_medicament.php" class="bg-[#00966b] hover:bg-[#00825c] text-white px-5 py-3 rounded-2xl text-xs font-bold uppercase tracking-wider flex items-center gap-2 shadow-sm transition">
                <i class="fa-solid fa-plus text-sm"></i> Ajouter un médicament
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
                <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center text-xl"><i class="fa-solid fa-capsules"></i></div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Produits</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-0.5"><?= $total_produits ?></p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
                <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-xl"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Stock Faible</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-0.5"><?= $stock_faible ?></p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
                <div class="w-14 h-14 bg-red-50 text-red-500 rounded-2xl flex items-center justify-center text-xl"><i class="fa-solid fa-circle-xmark"></i></div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ruptures</p>
                    <p class="text-3xl font-extrabold text-slate-800 mt-0.5"><?= $ruptures ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm p-8 space-y-6">
            <div class="relative max-w-md">
                <span class="absolute inset-y-0 left-4 flex items-center text-slate-400"><i class="fa-solid fa-magnifying-glass text-xs"></i></span>
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Rechercher..." class="w-full bg-[#f1f5f9] pl-10 pr-4 py-2.5 rounded-full outline-none text-xs font-semibold text-slate-700 placeholder-slate-400 transition focus:ring-2 ring-emerald-500/20">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="stockTable">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider w-[40%]">Médicament</th>
                            <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center w-[20%]">Quantité</th>
                            <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center w-[20%]">Prix Unitaire</th>
                            <th class="pb-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right w-[20%]">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($stocks as $s): ?>
                            <tr class="border-b border-slate-100/70 hover:bg-slate-50/50 transition last:border-0 stock-row">
                                <td class="py-5">
                                    <p class="font-bold text-slate-800 text-base med-name"><?= htmlspecialchars($s['nom_medicament']) ?></p>
                                    <span class="inline-block mt-1 bg-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded"><?= htmlspecialchars($s['forme'] ?? 'COMPRIMÉ') ?></span>
                                </td>
                                <td class="py-5 text-center">
                                    <span class="inline-block px-3 py-1 rounded-full font-bold text-xs <?= $s['quantite'] > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' ?>">
                                        <?= $s['quantite'] ?> bte(s)
                                    </span>
                                </td>
                                <td class="py-5 text-center font-bold text-slate-700 text-sm"><?= number_format($s['prix'], 2, '.', '') ?> DA</td>
                                <td class="py-5 text-right">
                                    <button onclick="openEditModal(<?= $s['id_stock'] ?>, '<?= addslashes($s['nom_medicament']) ?>', <?= $s['quantite'] ?>, <?= $s['prix'] ?>)" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-4 py-2 rounded-xl border border-slate-200/50 tracking-wide transition flex items-center gap-1.5 ml-auto">
                                        <i class="fa-solid fa-pen text-[10px]"></i> Modifier
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="editModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-[2.5rem] max-w-md w-full p-8 border border-slate-100 shadow-2xl space-y-6 relative animate-in fade-in zoom-in-95 duration-200">
            <button onclick="closeEditModal()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
            
            <div>
                <h3 class="text-xl font-extrabold text-[#1e293b]" id="modalMedName">Modifier le produit</h3>
                <p class="text-xs text-slate-400 mt-1">Ajustez la quantité ou retirez l'article du stock.</p>
            </div>

            <form action="modifier_stock_action.php" method="POST" class="space-y-4">
                <input type="hidden" name="id_stock" id="modalStockId">
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1 tracking-wider">Quantité en Stock</label>
                    <input type="number" name="quantite" id="modalQuantite" min="0" required class="w-full bg-[#f1f5f9] px-4 py-2.5 rounded-2xl outline-none text-xs font-semibold text-slate-700">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1 tracking-wider">Prix de vente (DA)</label>
                    <input type="number" name="prix" id="modalPrix" step="0.01" min="0" required class="w-full bg-[#f1f5f9] px-4 py-2.5 rounded-2xl outline-none text-xs font-semibold text-slate-700">
                </div>
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button type="submit" name="action" value="delete" onclick="return confirm('Voulez-vous vraiment retirer ce médicament de votre stock ?')" class="py-3 bg-red-50 hover:bg-red-100 text-red-600 font-bold rounded-2xl text-xs uppercase tracking-wider transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-trash-can text-xs"></i> Supprimer
                    </button>
                    <button type="submit" name="action" value="update" class="py-3 bg-[#00966b] hover:bg-[#00825c] text-white font-bold rounded-2xl text-xs uppercase tracking-wider shadow-md transition">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // 1. Afficher / Masquer le menu des notifications
    function toggleNotificationDropdown() {
        const dropdown = document.getElementById('notif-dropdown');
        dropdown.classList.toggle('hidden');
    }

    // Fermer le dropdown si on clique en dehors
    window.addEventListener('click', function(e) {
        const dropdown = document.getElementById('notif-dropdown');
        const bellBtn = dropdown.previousElementSibling;
        if (!dropdown.contains(e.target) && !bellBtn.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // 2. Récupérer les données de check_new_requests.php de manière asynchrone
    function fetchNewReservations() {
        fetch('check_new_requests.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('notif-badge');
                const headerCount = document.getElementById('notif-header-count');
                const listContainer = document.getElementById('notif-list');

                // Mettre à jour les compteurs graphiques
                if (data.count > 0) {
                    badge.innerText = data.count;
                    badge.classList.remove('hidden');
                    headerCount.innerText = `${data.count} en attente`;
                    headerCount.className = "bg-red-50 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded-full";
                } else {
                    badge.classList.add('hidden');
                    headerCount.innerText = "0 en attente";
                    headerCount.className = "bg-slate-100 text-slate-500 text-[10px] font-bold px-2 py-0.5 rounded-full";
                }

                // Injecter la liste HTML des demandes reçues
                if (data.requests && data.requests.length > 0) {
                    let htmlContent = '';
                    data.requests.forEach(req => {
                        // Formater proprement l'heure reçue
                        const dateObj = new Date(req.date_commande);
                        const heureFormatee = isNaN(dateObj.getTime()) ? 'Récemment' : dateObj.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});

                        htmlContent += `
                            <a href="dashboard_reservations.php" class="block p-4 hover:bg-slate-50/80 transition group">
                                <div class="flex justify-between items-start gap-2">
                                    <p class="font-bold text-slate-700 text-xs group-hover:text-[#00966b] transition">${escapeHtml(req.nom_client)}</p>
                                    <span class="text-[10px] text-slate-400 font-medium whitespace-nowrap"><i class="fa-regular fa-clock mr-0.5"></i> ${heureFormatee}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1 truncate bg-slate-50 px-2 py-1 rounded border border-slate-100 font-mono text-xs">${escapeHtml(req.medicament_demande)}</p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-[10px] text-slate-400">NSS : ${escapeHtml(req.nss)}</span>
                                    <span class="text-[10px] font-bold text-[#00966b] flex items-center gap-1">Traiter <i class="fa-solid fa-arrow-right text-[9px]"></i></span>
                                </div>
                            </a>
                        `;
                    });
                    listContainer.innerHTML = htmlContent;
                } else {
                    listContainer.innerHTML = `
                        <div class="p-6 text-center text-slate-400 text-xs font-medium">
                            <i class="fa-solid fa-inbox text-xl text-slate-200 mb-1.5 block"></i>
                            Aucune nouvelle demande de réservation.
                        </div>
                    `;
                }
            })
            .catch(error => console.error('Erreur lors du chargement des notifications:', error));
    }

    // Sécurisation anti-XSS des chaînes textuelles dynamiques
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Filtrage du tableau de stock local
    function filterTable() {
        const input = document.getElementById("searchInput");
        const filter = input.value.toUpperCase();
        const rows = document.getElementsByClassName("stock-row");
        for (let i = 0; i < rows.length; i++) {
            const medNameCell = rows[i].getElementsByClassName("med-name")[0];
            if (medNameCell) {
                const textValue = medNameCell.textContent || medNameCell.innerText;
                rows[i].style.display = textValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
            }
        }
    }

    function openEditModal(id, nom, qte, prix) {
        document.getElementById('modalStockId').value = id;
        document.getElementById('modalMedName').innerText = nom;
        document.getElementById('modalQuantite').value = qte;
        document.getElementById('modalPrix').value = prix;
        document.getElementById('editModal').classList.remove('hidden');
    }

    // Lancement de la vérification automatique des réservations au chargement
    document.addEventListener("DOMContentLoaded", function() {
        fetchNewReservations();
        // Optionnel : Lance la vérification en tâche de fond toutes les 10 secondes
        setInterval(fetchNewReservations, 10000); 
    });

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
    </script>
</body>
</html>