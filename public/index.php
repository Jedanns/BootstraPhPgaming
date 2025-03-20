<?php
/**
 * Front Controller - Point d'entrée unique
 */

// Forcer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définir le chemin racine
define('ROOT_PATH', dirname(__DIR__));

// Charger l'autoloader de Composer
require_once ROOT_PATH . '/vendor/autoload.php';

// Charger les variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

// Charger la configuration de base
require_once ROOT_PATH . '/app/config/config.php';

// Inclure le routeur
require_once ROOT_PATH . '/app/config/Router.php';

// Initialiser le routeur et traiter la requête
$router = new App\Config\Router();
$router->dispatch();