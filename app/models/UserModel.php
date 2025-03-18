<?php
namespace App\Models;

use PDO;

/**
 * Modèle pour la gestion des utilisateurs
 */
class UserModel extends BaseModel
{
    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct('users');
    }
    
    /**
     * Récupère un utilisateur par son email
     * 
     * @param string $email Email de l'utilisateur
     * @return array|false
     */
    public function getByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère un utilisateur par son nom d'utilisateur
     * 
     * @param string $username Nom d'utilisateur
     * @return array|false
     */
    public function getByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crée un nouvel utilisateur
     * 
     * @param array $data Données utilisateur
     * @return int|false
     */
    public function register($data)
    {
        // Hacher le mot de passe
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Ajouter la date de création
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }
    
    /**
     * Vérifie les identifiants de connexion
     * 
     * @param string $email Email ou nom d'utilisateur
     * @param string $password Mot de passe
     * @return array|false
     */
    public function login($email, $password)
    {
        // Essayer de trouver l'utilisateur par email
        $user = $this->getByEmail($email);
        
        // Si non trouvé, essayer par nom d'utilisateur
        if (!$user) {
            $user = $this->getByUsername($email);
        }
        
        // Vérifier si l'utilisateur existe et si le mot de passe est correct
        if ($user && password_verify($password, $user['password'])) {
            // Mettre à jour la dernière connexion
            $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            
            // Supprimer le mot de passe pour des raisons de sécurité
            unset($user['password']);
            
            return $user;
        }
        
        return false;
    }
}