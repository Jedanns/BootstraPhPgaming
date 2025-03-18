<?php
namespace App\Controllers;

/**
 * Contrôleur de base dont tous les contrôleurs héritent
 */
class BaseController
{
    /**
     * Charge et affiche une vue
     * 
     * @param string $view Nom de la vue
     * @param array $data Données à passer à la vue
     * @param string $layout Layout à utiliser
     * @return void
     */
    protected function view($view, $data = [], $layout = 'default')
    {
        // Extraire les données pour qu'elles soient accessibles dans la vue
        extract($data);
        
        // Chemin de la vue
        $viewFile = VIEW_PATH . "/pages/{$view}.php";
        
        // Vérifier si la vue existe
        if (file_exists($viewFile)) {
            // Démarrer la mise en mémoire tampon
            ob_start();
            
            // Inclure la vue
            include_once $viewFile;
            
            // Récupérer le contenu mis en mémoire tampon
            $content = ob_get_clean();
            
            // Inclure le layout avec le contenu
            include_once VIEW_PATH . "/layouts/{$layout}.php";
        } else {
            // Vue non trouvée
            die("Vue '{$view}' non trouvée");
        }
    }
    
    /**
     * Rediriger vers une URL
     * 
     * @param string $url URL de redirection
     * @return void
     */
    protected function redirect($url)
    {
        header("Location: " . APP_URL . '/' . $url);
        exit;
    }
    
    /**
     * Vérifie si l'utilisateur est connecté
     * 
     * @return bool
     */
    protected function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Protège une route pour les utilisateurs authentifiés uniquement
     * 
     * @return void
     */
    protected function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            // Sauvegarder l'URL demandée
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            
            // Rediriger vers la page de connexion
            $this->redirect('auth/login');
            exit;
        }
    }
}