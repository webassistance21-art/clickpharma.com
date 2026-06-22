<?php
$host = 'mysql-clickpharma-webassistance21-9abf.b.aivencloud.com';
$port = '15899';
$dbname = 'defaultdb';
$username = 'avnadmin';
$password = 'AVNS_xkSJ6bwVpziBDucxm5I';

try {
    // Ajout des options pour forcer la connexion SSL
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // Permet de se connecter sans configurer le fichier .pem localement
    ];

    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password, $options);
    
    // Petit message de validation (optionnel, à retirer plus tard)
    // echo "Connexion réussie !"; 
    
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
