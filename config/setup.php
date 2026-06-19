<?php
// config/setup.php

// 1. Activation sécurisée de la session si elle n'est pas déjà lancée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Encapsulation globale pour la sécurité anti-XSS dans l'affichage HTML
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// 3. Constantes de l'application
define('APP_NAME', 'Click Pharma');
define('LOGO_PATH', 'images/Pharma (1).png');