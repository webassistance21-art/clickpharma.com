<?php
// On inclut votre fichier de connexion (qui contient $pdo et $db)
require_once 'config/db.php';
$pdo->exec("SET NAMES utf8mb4");

// Si une table est sélectionnée dans l'URL, on récupère son nom
$table_selectionnee = isset($_GET['table']) ? $_GET['table'] : null;

try {
    // 1. Requête magique pour lister toutes les tables de la base de données
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Erreur lors de la récupération des tables : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Console d'Administration - ClickPharma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }
        /* Personnalisation de la barre de défilement pour les grands tableaux */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen antialiased">

    <!-- NAV BAR PATRON STYLE CLICKPHARMA -->
    <nav class="bg-[#00966b] text-white p-4 shadow-md sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="images/Pharma (1).png" alt="Logo" class="h-10 w-10 bg-white p-0.5 rounded-xl object-contain shadow-inner" onerror="this.src='Pharma (1).png'">
                <div>
                    <h1 class="text-lg font-black tracking-tight leading-none">ClickPharma</h1>
                    <span class="text-emerald-100 text-[10px] font-semibold uppercase tracking-wider">Console d'Administration</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="bg-[#00825c] text-emerald-100 text-xs font-bold px-3 py-1.5 rounded-xl shadow-inner flex items-center gap-2">
                    <i class="fa-solid fa-server text-emerald-400"></i> defaultdb (Aiven)
                </span>
            </div>
        </div>
    </nav>

    <!-- ZONE PRINCIPALE -->
    <main class="container mx-auto p-4 sm:p-6 max-w-7xl">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <!-- BARRE LATERALE : MENUS DES TABLES -->
            <div class="lg:col-span-1 bg-white p-5 rounded-3xl border border-slate-200 shadow-sm h-fit">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                    <span>Tables disponibles</span>
                    <span class="bg-slate-100 text-slate-600 font-mono text-[10px] px-2 py-0.5 rounded-md"><?= count($tables) ?></span>
                </h3>
                
                <ul class="space-y-1.5 max-h-[70vh] overflow-y-auto pr-1">
                    <?php foreach ($tables as $table): 
                        $is_active = ($table_selectionnee === $table);
                    ?>
                        <li>
                            <a href="?table=<?= urlencode($table); ?>" 
                               class="group flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold tracking-tight transition <?= $is_active ? 'bg-[#00966b] text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
                                <i class="fa-regular fa-folder-open text-sm <?= $is_active ? 'text-emerald-200' : 'text-slate-400 group-hover:text-[#00966b]' ?>"></i>
                                <span class="truncate"><?= htmlspecialchars($table); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- ZONE DE CONTENU : APERÇU DES DONNÉES -->
            <div class="lg:col-span-3 space-y-4">
                <?php if ($table_selectionnee && in_array($table_selectionnee, $tables)): ?>
                    
                    <!-- Entête de la table -->
                    <div class="flex flex-wrap items-center justify-between gap-3 bg-slate-900 text-white p-4 rounded-2xl shadow-sm">
                        <h2 class="text-xs font-black tracking-tight flex items-center gap-2 uppercase">
                            <i class="fa-solid fa-table text-emerald-400 text-sm"></i>
                            <span>Contenu de la table :</span>
                            <code class="bg-slate-800 text-emerald-300 px-2 py-0.5 rounded-md text-xs lowercase font-mono font-bold font-normal"><?= htmlspecialchars($table_selectionnee); ?></code>
                        </h2>
                    </div>

                    <!-- Conteneur de tableau dynamique -->
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <?php
                        try {
                            // On récupère toutes les lignes de la table sélectionnée
                            $stmt_data = $pdo->query("SELECT * FROM `" . $table_selectionnee . "`");
                            $rows = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($rows) > 0): 
                                // Récupération dynamique des noms de colonnes
                                $colonnes = array_keys($rows[0]);
                            ?>
                                <div class="overflow-x-auto w-full">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-slate-50 border-b border-slate-200">
                                                <?php foreach ($colonnes as $colonne): ?>
                                                    <th class="px-4 py-3.5 text-[11px] font-black uppercase text-slate-500 tracking-wider font-mono">
                                                        <?= htmlspecialchars($colonne); ?>
                                                    </th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 text-xs">
                                            <?php foreach ($rows as $row): ?>
                                                <tr class="hover:bg-slate-50/80 transition">
                                                    <?php foreach ($row as $valeur): ?>
                                                        <td class="px-4 py-3 font-medium text-slate-700 max-w-xs truncate">
                                                            <?php 
                                                                if ($valeur === null) {
                                                                    echo '<span class="text-slate-400 italic font-normal text-[10px] bg-slate-100 px-1.5 py-0.5 rounded">NULL</span>';
                                                                } else {
                                                                    echo htmlspecialchars($valeur); 
                                                                }
                                                            ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="p-12 text-center text-xs text-slate-400 font-medium italic">
                                    <i class="fa-solid fa-folder-minus text-2xl block text-slate-300 mb-2 not-italic"></i>
                                    Cette table est actuellement vide (aucune donnée enregistrée).
                                </div>
                            <?php endif; ?>

                        <?php
                        } catch (PDOException $e) {
                            echo "
                            <div class='p-4 bg-red-50 text-red-700 text-xs font-semibold flex items-center gap-2 m-4 rounded-xl border border-red-100'>
                                <i class='fa-solid fa-triangle-exclamation text-base'></i>
                                <span>Erreur lors de la lecture des données : " . htmlspecialchars($e->getMessage()) . "</span>
                            </div>";
                        }
                        ?>
                    </div>

                <?php else: ?>
                    <!-- État initial sans table sélectionnée -->
                    <div class="bg-white p-16 rounded-3xl border border-slate-200 text-center shadow-sm flex flex-col items-center justify-center space-y-3">
                        <div class="w-16 h-16 bg-emerald-50 text-[#00966b] rounded-2xl flex items-center justify-center text-xl shadow-inner">
                            <i class="fa-solid fa-arrow-left animate-pulse"></i>
                        </div>
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Sélection requise</p>
                        <p class="text-sm text-slate-500 font-medium max-w-md">
                            Veuillez sélectionner une table dans le menu de gauche pour inspecter et visualiser ses données en temps réel.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

</body>
</html>
