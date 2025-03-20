<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/**
 * Configuration de l'application
 */

// Configuration de la base de données
define('DB_HOST', $_ENV['MYSQL_HOST'] ?? 'db');
define('DB_NAME', $_ENV['MYSQL_DATABASE'] ?? $_ENV['DB_NAME']);
define('DB_USER', $_ENV['MYSQL_USER'] ?? $_ENV['DB_USER']);
define('DB_PASSWORD', $_ENV['MYSQL_PASSWORD'] ?? $_ENV['DB_PASSWORD']);

// Configuration de l'application
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Mon Application');

// Détection automatique de l'URL du serveur
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
define('APP_URL', $protocol . $host);

define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? true);

// Configuration des chemins
define('APP_PATH', ROOT_PATH . '/app');
define('CONTROLLER_PATH', APP_PATH . '/controllers');
define('MODEL_PATH', APP_PATH . '/models');
define('VIEW_PATH', APP_PATH . '/views');
define('HELPER_PATH', APP_PATH . '/helpers');

// Clé de sécurité pour les sessions
define('APP_KEY', $_ENV['APP_KEY'] ?? 'default_insecure_key');

// Configuration des contrôleurs et actions par défaut
define('DEFAULT_CONTROLLER', 'Home');
define('DEFAULT_ACTION', 'index');

// Gestion des erreurs
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
}

// Démarrer la session
session_start();