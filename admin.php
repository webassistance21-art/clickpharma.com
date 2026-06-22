<?php
// On inclut votre fichier de connexion (qui contient $pdo et $db)
require_once 'config/db.php';

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
    <title>Console d'Administration - Tables</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 20px; }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .container { display: flex; gap: 20px; margin-top: 20px; }
        .sidebar { width: 250px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); height: fit-content; }
        .sidebar h3 { margin-top: 0; color: #7f8c8d; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar li { margin: 8px 0; }
        .sidebar a { display: block; padding: 8px 12px; color: #34495e; text-decoration: none; border-radius: 4px; transition: 0.2s; font-weight: 500; }
        .sidebar a:hover, .sidebar a.active { background-color: #3498db; color: white; }
        .content { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #e0e0e0; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; color: #2c3e50; font-weight: 600; }
        tr:nth-child(even) { background-color: #fdfdfd; }
        tr:hover { background-color: #f1f2f6; }
        .empty-msg { color: #95a5a6; font-style: italic; }
    </style>
</head>
<body>

    <h1>📊 Base de données : `defaultdb` (Aiven)</h1>

    <div class="container">
        <div class="sidebar">
            <h3>Tables disponibles</h3>
            <ul>
                <?php foreach ($tables as $table): ?>
                    <li>
                        <a href="?table=<?php echo urlencode($table); ?>" 
                           class="<?php echo ($table_selectionnee === $table) ? 'active' : ''; ?>">
                            📁 <?php echo htmlspecialchars($table); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="content">
            <?php if ($table_selectionnee && in_array($table_selectionnee, $tables)): ?>
                <h2>Contenu de la table : <code><?php echo htmlspecialchars($table_selectionnee); ?></code></h2>
                
                <?php
                try {
                    // On récupère toutes les lignes de la table sélectionnée
                    $stmt_data = $pdo->query("SELECT * FROM `" . $table_selectionnee . "`");
                    $rows = $stmt_data->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($rows) > 0): 
                        // Récupération dynamique des noms de colonnes (les clés du premier tableau)
                        $colonnes = array_keys($rows[0]);
                    ?>
                        <table>
                            <thead>
                                <tr>
                                    <?php foreach ($colonnes as $colonne): ?>
                                        <th><?php echo htmlspecialchars($colonne); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $valeur): ?>
                                            <td>
                                                <?php 
                                                    // Gestion de l'affichage des valeurs nulles ou vides
                                                    echo ($valeur === null) ? '<i>NULL</i>' : htmlspecialchars($valeur); 
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="empty-msg">Cette table est actuellement vide (aucune donnée enregistrée).</p>
                    <?php endif; ?>

                <?php
                } catch (PDOException $e) {
                    echo "<p style='color:red;'>Erreur de lecture : " . $e->getMessage() . "</p>";
                }
                ?>

            <?php else: ?>
                <p class="empty-msg">👈 Veuillez sélectionner une table dans le menu de gauche pour visualiser son contenu.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
