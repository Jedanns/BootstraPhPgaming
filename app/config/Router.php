<?php
namespace App\Config;

/**
 * Routeur principal de l'application
 */
class Router
{
    /**
     * Analyse l'URL et dispatche vers le contrôleur approprié
     */
    public function dispatch()
    {
        // Récupérer l'URL
        $url = $this->getUrl();
        
        // Déterminer le contrôleur
        $controllerName = !empty($url[0]) ? ucfirst($url[0]) : DEFAULT_CONTROLLER;
        $controllerFile = CONTROLLER_PATH . "/{$controllerName}Controller.php";
        
        // Vérifier si le contrôleur existe
        if (file_exists($controllerFile)) {
            // Charger le contrôleur
            require_once $controllerFile;
            $controllerClass = "App\\Controllers\\{$controllerName}Controller";
            $controller = new $controllerClass();
            
            // Déterminer l'action
            array_shift($url); // Supprimer le contrôleur de l'URL
            $action = !empty($url[0]) ? $url[0] : DEFAULT_ACTION;
            array_shift($url); // Supprimer l'action de l'URL
            
            // Vérifier si l'action existe
            if (method_exists($controller, $action)) {
                // Appeler l'action avec les paramètres restants
                call_user_func_array([$controller, $action], $url);
            } else {
                // Action non trouvée, rediriger vers la page 404
                $this->redirect404();
            }
        } else {
            // Contrôleur non trouvé, rediriger vers la page 404
            $this->redirect404();
        }
    }
    
    /**
     * Récupère l'URL traitée
     * 
     * @return array
     */
    private function getUrl()
    {
        if (isset($_GET['url'])) {
            // Nettoyer l'URL
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            
            // Convertir l'URL en tableau
            return explode('/', $url);
        }
        
        return [];
    }
    
    /**
     * Redirige vers la page 404
     */
    private function redirect404()
    {
        // Charger le contrôleur Error
        require_once CONTROLLER_PATH . "/ErrorController.php";
        $controller = new \App\Controllers\ErrorController();
        $controller->notFound();
    }
}