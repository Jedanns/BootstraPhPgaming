<?php
namespace App\Controllers;

use App\Models\UserModel;

/**
 * Contrôleur pour l'authentification
 */
class AuthController extends BaseController
{
    private $userModel;
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    /**
     * Page de connexion
     */
    public function login()
    {
        // Si déjà connecté, rediriger vers le tableau de bord
        if ($this->isLoggedIn()) {
            $this->redirect('home/dashboard');
        }
        
        // Traitement du formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']) ? true : false;
            
            // Valider les données
            $errors = [];
            
            if (empty($email)) {
                $errors['email'] = 'L\'email ou le nom d\'utilisateur est requis';
            }
            
            if (empty($password)) {
                $errors['password'] = 'Le mot de passe est requis';
            }
            
            // Si pas d'erreurs, tenter la connexion
            if (empty($errors)) {
                $user = $this->userModel->login($email, $password);
                
                if ($user) {
                    // Créer la session utilisateur
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user'] = $user;
                    
                    // Si "se souvenir de moi" est coché, créer un cookie
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        // Stocker le token en base de données (non implémenté ici)
                        
                        // Créer le cookie (30 jours)
                        setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/', '', false, true);
                    }
                    
                    // Rediriger vers la page demandée ou le tableau de bord
                    $redirectUrl = $_SESSION['redirect_url'] ?? 'home/dashboard';
                    unset($_SESSION['redirect_url']);
                    
                    $this->redirect($redirectUrl);
                } else {
                    $errors['login'] = 'Email/nom d\'utilisateur ou mot de passe incorrect';
                }
            }
            
            // Si erreurs, afficher le formulaire avec les erreurs
            $data = [
                'title' => 'Connexion',
                'email' => $email,
                'errors' => $errors
            ];
            
            $this->view('auth/login', $data);
        } else {
            // Afficher le formulaire
            $data = [
                'title' => 'Connexion'
            ];
            
            $this->view('auth/login', $data);
        }
    }
    
    /**
     * Page d'inscription
     */
    public function register()
    {
        // Si déjà connecté, rediriger vers le tableau de bord
        if ($this->isLoggedIn()) {
            $this->redirect('home/dashboard');
        }
        
        // Traitement du formulaire d'inscription
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $name = $_POST['name'] ?? '';
            
            // Valider les données
            $errors = [];
            
            if (empty($username)) {
                $errors['username'] = 'Le nom d\'utilisateur est requis';
            } elseif (strlen($username) < 3 || strlen($username) > 50) {
                $errors['username'] = 'Le nom d\'utilisateur doit contenir entre 3 et 50 caractères';
            } elseif ($this->userModel->getByUsername($username)) {
                $errors['username'] = 'Ce nom d\'utilisateur est déjà utilisé';
            }
            
            if (empty($email)) {
                $errors['email'] = 'L\'email est requis';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'email est invalide';
            } elseif ($this->userModel->getByEmail($email)) {
                $errors['email'] = 'Cet email est déjà utilisé';
            }
            
            if (empty($password)) {
                $errors['password'] = 'Le mot de passe est requis';
            } elseif (strlen($password) < 6) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères';
            }
            
            if ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Les mots de passe ne correspondent pas';
            }
            
            // Si pas d'erreurs, créer l'utilisateur
            if (empty($errors)) {
                $userData = [
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'name' => $name
                ];
                
                $userId = $this->userModel->register($userData);
                
                if ($userId) {
                    // Afficher un message de succès
                    $_SESSION['flash_message'] = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
                    $this->redirect('auth/login');
                } else {
                    $errors['register'] = 'Erreur lors de l\'inscription';
                }
            }
            
            // Si erreurs, afficher le formulaire avec les erreurs
            $data = [
                'title' => 'Inscription',
                'username' => $username,
                'email' => $email,
                'name' => $name,
                'errors' => $errors
            ];
            
            $this->view('auth/register', $data);
        } else {
            // Afficher le formulaire
            $data = [
                'title' => 'Inscription'
            ];
            
            $this->view('auth/register', $data);
        }
    }
    
    /**
     * Déconnexion
     */
    public function logout()
    {
        // Supprimer les variables de session
        unset($_SESSION['user_id']);
        unset($_SESSION['user']);
        
        // Supprimer le cookie "se souvenir de moi"
        if (isset($_COOKIE['remember_token'])) {
            // Supprimer le token en base de données (non implémenté ici)
            
            // Supprimer le cookie
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        
        // Rediriger vers la page d'accueil
        $this->redirect('');
    }
}