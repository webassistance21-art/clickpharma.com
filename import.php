<?php
// On augmente le temps maximum d'exécution au cas où le fichier SQL soit gros
ini_set('max_execution_time', 300);

$host = 'mysql-clickpharma-webassistance21-9abf.b.aivencloud.com';
$port = '15899'; // Remplacez par votre vrai port Aiven
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$dbname = 'defaultdb';
$username = 'avnadmin';
$password = 'AVNS_xkSJ6bwVpziBDucxm5I';

try {
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h3>Connexion réussie à Aiven !</h3>";

    // Recherche automatique du fichier .sql dans le projet
    $sqlFiles = glob("*.sql");
    if (empty($sqlFiles)) {
        die("Erreur : Aucun fichier .sql trouvé à la racine de votre projet GitHub.");
    }
    
    $fileToImport = $sqlFiles[0];
    echo "Fichier trouvé : <b>$fileToImport</b><br>Importation en cours...<br>";

    $query = file_get_contents($fileToImport);
    
    // Suppression des lignes de création/utilisation de base de données locales qui feraient planter Aiven
    $query = preg_replace('/CREATE DATABASE.*?;\s*/i', '', $query);
    $query = preg_replace('/USE .*?;\s*/i', '', $query);

    // Exécution globale du script SQL
    $db->exec($query);
    
    echo "<h2 style='color:green;'>🎉 Victoire ! Vos tables ont été importées avec succès dans defaultdb !</h2>";
    echo "Vous pouvez maintenant retourner sur votre site et supprimer ce fichier import.php.";

} catch (PDOException $e) {
    die("<h2 style='color:red;'>Erreur :</h2> " . $e->getMessage());
}
?>
