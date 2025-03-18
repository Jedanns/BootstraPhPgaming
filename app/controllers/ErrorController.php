<?php
namespace App\Controllers;

/**
 * Contrôleur pour les pages d'erreur
 */
class ErrorController extends BaseController
{
    /**
     * Page 404 (non trouvé)
     */
    public function notFound()
    {
        $data = [
            'title' => 'Page non trouvée'
        ];
        
        // Définir le code d'état HTTP
        header("HTTP/1.0 404 Not Found");
        
        $this->view('error/404', $data);
    }
    
    /**
     * Page 403 (accès refusé)
     */
    public function forbidden()
    {
        $data = [
            'title' => 'Accès refusé'
        ];
        
        // Définir le code d'état HTTP
        header("HTTP/1.0 403 Forbidden");
        
        $this->view('error/403', $data);
    }
    
    /**
     * Page 500 (erreur serveur)
     */
    public function serverError()
    {
        $data = [
            'title' => 'Erreur serveur'
        ];
        
        // Définir le code d'état HTTP
        header("HTTP/1.0 500 Internal Server Error");
        
        $this->view('error/500', $data);
    }
}