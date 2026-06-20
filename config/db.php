<?php
$host = 'mysql-clickpharma-webassistance21-9abf.b.aivencloud.com';
$port = '15899';
$dbname = 'defaultdb';
$username = 'avnadmin';
$password = 'AVNS_xkSJ6bwVpziBDucxm5I';

try {
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
