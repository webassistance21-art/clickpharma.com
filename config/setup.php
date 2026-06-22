<?php
// Informations de connexion Aiven MySQL
$host     = 'mysql-clickpharma-webassistance21-9abf.b.aivencloud.com';
$port     = '15899';
$dbname   = 'defaultdb';
$username = 'avnadmin';
$password = 'AVNS_xkSJ6bwVpziBDucxm5I';

try {
    // Configuration obligatoire pour la sécurité SSL d'Aiven
    $options = [
        PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // Évite d'avoir à uploader le fichier ca.pem
    ];

    // Initialisation de la connexion PDO
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password, $options);

    // Optionnel : vous pouvez décommenter la ligne suivante pour tester, 
    // mais supprimez-la après pour ne pas bloquer l'affichage de vos pages.
    // echo "Connexion Aiven réussie !";

} catch (PDOException $e) {
    // En cas d'erreur, on affiche le message pour comprendre ce qui bloque
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
