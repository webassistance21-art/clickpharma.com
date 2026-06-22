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

    // ⚡ Désactivation des protections d'Aiven pour l'import initial
    $db->exec("SET SESSION sql_require_primary_key = 0;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    echo "Sécurités temporairement désactivées.<br>";

    $sqlFiles = glob("*.sql");
    if (empty($sqlFiles)) {
        die("Erreur : Aucun fichier .sql trouvé à la racine de votre projet GitHub.");
    }
    
    $fileToImport = $sqlFiles[0];
    echo "Fichier trouvé : <b>$fileToImport</b><br>Traitement et importation en cours...<br>";

    $query = file_get_contents($fileToImport);
    
    // Nettoyage des commandes de base de données globales
    $query = preg_replace('/CREATE DATABASE.*?;\s*/i', '', $query);
    $query = preg_replace('/USE .*?;\s*/i', '', $query);

    // 🔥 LA MAGIE : On insère un DROP TABLE automatique juste avant chaque CREATE TABLE
    $query = preg_replace('/CREATE TABLE IF NOT EXISTS/i', 'DROP TABLE IF EXISTS', $query);
    $query = preg_replace('/CREATE TABLE (`?[a-zA-Z0-9_]+`?)/i', "DROP TABLE IF EXISTS $1;\nCREATE TABLE $1", $query);

    // Exécution de l'intégralité du script modifié
    $db->exec($query);
    
    // ⚡ Réactivation des sécurités d'Aiven
    $db->exec("SET SESSION sql_require_primary_key = 1;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "<h2 style='color:green;'>🎉 Victoire absolue ! Toutes vos tables ont été nettoyées, réinitialisées et importées avec succès !</h2>";

} catch (PDOException $e) {
    die("<h2 style='color:red;'>Erreur :</h2> " . $e->getMessage());
}
?>
