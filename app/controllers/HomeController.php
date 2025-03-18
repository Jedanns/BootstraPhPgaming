<?php
namespace App\Controllers;

/**
 * Contrôleur de la page d'accueil
 */
class HomeController extends BaseController
{
    /**
     * Page d'accueil
     */
    public function index()
    {
        $data = [
            'title' => 'Accueil',
            'user' => $_SESSION['user'] ?? null
        ];
        
        $this->view('home/index', $data);
    }
    
    /**
     * Page du tableau de bord (protégée)
     */
    public function dashboard()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        $data = [
            'title' => 'Tableau de bord',
            'user' => $_SESSION['user']
        ];
        
        $this->view('home/dashboard', $data);
    }
}