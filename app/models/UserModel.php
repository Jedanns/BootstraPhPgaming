<?php
namespace App\Models;

use PDO;
use PDOException;

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
     * Récupère un utilisateur par son ID
     * 
     * @param int $id ID de l'utilisateur
     * @return array|false
     */
    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting user by ID: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère un utilisateur par son email
     * 
     * @param string $email Email de l'utilisateur
     * @return array|false
     */
    public function getByEmail($email)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting user by email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère un utilisateur par son nom d'utilisateur
     * 
     * @param string $username Nom d'utilisateur
     * @return array|false
     */
    public function getByUsername($username)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting user by username: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crée un nouvel utilisateur
     * 
     * @param array $data Données utilisateur
     * @return int|false
     */
    public function register($data)
    {
        try {
            // Vérifier si l'email ou le nom d'utilisateur existe déjà
            if ($this->getByEmail($data['email'])) {
                throw new \Exception('Cet email est déjà utilisé.');
            }
            
            if ($this->getByUsername($data['username'])) {
                throw new \Exception('Ce nom d\'utilisateur est déjà utilisé.');
            }
            
            // Hacher le mot de passe
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Ajouter la date de création
            $data['created_at'] = date('Y-m-d H:i:s');
            
            return $this->create($data);
        } catch (PDOException $e) {
            error_log('Error registering user: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log('User registration error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Recherche des utilisateurs par nom d'utilisateur, email ou nom
     * 
     * @param string $search Terme de recherche
     * @return array
     */
    public function searchUsers($search)
    {
        try {
            // Trim and handle short search terms
            $search = trim($search);
            
            // If search is too short, return empty array
            if (strlen($search) < 2) {
                return [];
            }
            
            $searchParam = "%{$search}%";
            
            $stmt = $this->db->prepare("
                SELECT 
                    id, 
                    username, 
                    email, 
                    name, 
                    created_at,
                    CASE 
                        WHEN username LIKE :exact_username THEN 1
                        WHEN name LIKE :exact_name THEN 2
                        WHEN email LIKE :exact_email THEN 3
                        ELSE 4
                    END as relevance
                FROM {$this->table}
                WHERE 
                    username LIKE :search 
                    OR email LIKE :search 
                    OR name LIKE :search
                ORDER BY relevance, username
                LIMIT 50
            ");
            
            $stmt->execute([
                ':search' => $searchParam,
                ':exact_username' => $search,
                ':exact_name' => $search,
                ':exact_email' => $search
            ]);
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
        } catch (PDOException $e) {
            error_log('Error searching users: ' . $e->getMessage());
            return [];
        }
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
        try {
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
        } catch (PDOException $e) {
            error_log('Error during login: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour le profil d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param array $data Données à mettre à jour
     * @return bool
     */
    public function updateProfile($userId, $data)
    {
        try {
            // Vérifier si l'email est en cours de modification
            if (isset($data['email']) && $data['email'] !== $this->getById($userId)['email']) {
                // Vérifier si le nouvel email existe déjà
                if ($this->getByEmail($data['email'])) {
                    throw new \Exception('Cet email est déjà utilisé.');
                }
            }
            
            // Mettre à jour le profil
            return $this->update($userId, $data);
        } catch (PDOException $e) {
            error_log('Error updating user profile: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log('Profile update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Change le mot de passe d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $currentPassword Mot de passe actuel
     * @param string $newPassword Nouveau mot de passe
     * @return bool
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        try {
            // Récupérer l'utilisateur
            $user = $this->getById($userId);
            
            // Vérifier le mot de passe actuel
            if (!$user || !password_verify($currentPassword, $user['password'])) {
                throw new \Exception('Mot de passe actuel incorrect.');
            }
            
            // Hacher le nouveau mot de passe
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Mettre à jour le mot de passe
            return $this->update($userId, ['password' => $hashedPassword]);
        } catch (PDOException $e) {
            error_log('Error changing password: ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log('Password change error: ' . $e->getMessage());
            return false;
        }
    }
}