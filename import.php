<?php
ini_set('max_execution_time', 300);

$host = 'mysql-clickpharma-webassistance21-9abf.b.aivencloud.com';
$port = '15899';
$dbname = 'defaultdb';
$username = 'avnadmin';
$password = 'AVNS_xkSJ6bwVpziBDucxm5I';

try {
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h3>Connexion réussie à Aiven !</h3>";

    // ⚡ Désactivation des contraintes pour tout écraser sans erreur
    $db->exec("SET SESSION sql_require_primary_key = 0;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    echo "Sécurités temporairement désactivées.<br>";

    $sqlFiles = glob("*.sql");
    if (empty($sqlFiles)) {
        die("Erreur : Aucun fichier .sql trouvé à la racine de votre projet GitHub.");
    }
    
    $fileToImport = $sqlFiles[0];
    echo "Fichier trouvé : <b>$fileToImport</b><br>Importation en cours...<br>";

    $query = file_get_contents($fileToImport);
    
    $query = preg_replace('/CREATE DATABASE.*?;\s*/i', '', $query);
    $query = preg_replace('/USE .*?;\s*/i', '', $query);

    // ⚡ On ajoute automatiquement "DROP TABLE IF EXISTS" avant chaque création de table
    $query = preg_replace('/CREATE TABLE/i', 'DROP TABLE IF EXISTS', $query) . "\n" . $query;
    // Note: Pour éviter un doublon complexe, on va plutôt exécuter le script directement, 
    // mais pour faire au plus simple et le plus propre, voici la version corrigée globale :
    
    // Suppression propre de la table qui bloque
    $db->exec("DROP TABLE IF EXISTS `administrateurs`, `utilisateurs`, `patients`, `ordonnances`, `medicaments`;");

    // Exécution globale du script SQL
    $db->exec(file_get_contents($fileToImport));
    
    // ⚡ Réactivation des sécurités
    $db->exec("SET SESSION sql_require_primary_key = 1;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "<h2 style='color:green;'>🎉 Victoire absolue ! Toutes vos tables ont été importées avec succès !</h2>";

} catch (PDOException $e) {
    die("<h2 style='color:red;'>Erreur :</h2> " . $e->getMessage());
}
?>
