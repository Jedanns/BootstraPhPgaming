<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * Modèle pour la gestion des relations d'amitié
 */
class FriendModel extends BaseModel
{
    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct('friends');
    }
    
    /**
     * Envoie une demande d'amitié
     * 
     * @param int $userId ID de l'utilisateur qui envoie la demande
     * @param int $friendId ID de l'utilisateur qui reçoit la demande
     * @return int|false ID de la relation créée ou false en cas d'échec
     */
    public function sendRequest($userId, $friendId)
    {
        // Vérifier si une relation existe déjà dans un sens ou dans l'autre
        $existingRelation = $this->getRelation($userId, $friendId);
        
        if ($existingRelation) {
            // Si l'utilisateur a déjà été invité par l'ami, on accepte la demande
            if ($existingRelation['user_id'] == $friendId && $existingRelation['status'] == 'pending') {
                return $this->updateStatus($existingRelation['id'], 'accepted');
            }
            
            // Sinon, la relation existe déjà, pas besoin de la recréer
            return false;
        }
        
        // Créer une nouvelle relation
        $data = [
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($data);
    }
    
    /**
     * Met à jour le statut d'une relation d'amitié
     * 
     * @param int $relationId ID de la relation
     * @param string $status Nouveau statut ('accepted', 'rejected', 'blocked')
     * @return bool
     */
    public function updateStatus($relationId, $status)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($relationId, $data);
    }
    
    /**
     * Récupère une relation d'amitié entre deux utilisateurs
     * 
     * @param int $userId Premier utilisateur
     * @param int $friendId Deuxième utilisateur
     * @return array|false
     */
    public function getRelation($userId, $friendId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table} 
                WHERE (user_id = :user_id AND friend_id = :friend_id)
                OR (user_id = :friend_id AND friend_id = :user_id)
                LIMIT 1
            ");
            
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':friend_id', $friendId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error or handle exception
            error_log('Error in getRelation: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifie si deux utilisateurs sont amis
     * 
     * @param int $userId Premier utilisateur
     * @param int $friendId Deuxième utilisateur
     * @return bool
     */
    public function areFriends($userId, $friendId)
    {
        $relation = $this->getRelation($userId, $friendId);
        
        return $relation && $relation['status'] == 'accepted';
    }
    
    /**
     * Récupère toutes les demandes d'amitié en attente pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array
     */
    public function getPendingRequests($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT f.*, u.username, u.email, u.name 
                FROM {$this->table} f
                JOIN users u ON f.user_id = u.id
                WHERE f.friend_id = :user_id AND f.status = 'pending'
            ");
            
            $stmt->execute([':user_id' => $userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error or handle exception
            error_log('Error in getPendingRequests: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère toutes les demandes d'amitié envoyées par un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array
     */
    public function getSentRequests($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT f.*, u.username, u.email, u.name 
                FROM {$this->table} f
                JOIN users u ON f.friend_id = u.id
                WHERE f.user_id = :user_id AND f.status = 'pending'
            ");
            
            $stmt->execute([':user_id' => $userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error or handle exception
            error_log('Error in getSentRequests: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère tous les amis d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array
     */
    public function getFriends($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    u.id, u.username, u.email, u.name, u.created_at,
                    CASE 
                        WHEN f.user_id = :bind_user_id THEN f.friend_id
                        ELSE f.user_id
                    END AS friend_id,
                    f.status, f.created_at as friendship_date
                FROM {$this->table} f
                JOIN users u ON (
                    (f.user_id = :bind_user_id AND f.friend_id = u.id) OR
                    (f.friend_id = :bind_user_id AND f.user_id = u.id)
                )
                WHERE (f.user_id = :bind_user_id OR f.friend_id = :bind_user_id)
                AND f.status = 'accepted'
            ");
            
            $stmt->execute([':bind_user_id' => $userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error or handle exception
            error_log('Error in getFriends: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Supprime une relation d'amitié
     * 
     * @param int $userId ID du premier utilisateur
     * @param int $friendId ID du deuxième utilisateur
     * @return bool
     */
    public function removeFriend($userId, $friendId)
    {
        $relation = $this->getRelation($userId, $friendId);
        
        if ($relation) {
            try {
                return $this->delete($relation['id']);
            } catch (PDOException $e) {
                // Log error or handle exception
                error_log('Error in removeFriend: ' . $e->getMessage());
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Bloque un utilisateur
     * 
     * @param int $userId ID de l'utilisateur qui bloque
     * @param int $blockedId ID de l'utilisateur bloqué
     * @return bool
     */
    public function blockUser($userId, $blockedId)
    {
        $relation = $this->getRelation($userId, $blockedId);
        
        try {
            if ($relation) {
                // Si la relation existe déjà, on la met à jour
                if ($relation['user_id'] == $userId) {
                    // L'utilisateur est déjà l'initiateur, on peut bloquer directement
                    return $this->updateStatus($relation['id'], 'blocked');
                } else {
                    // L'utilisateur est le destinataire, on doit d'abord supprimer la relation
                    $this->delete($relation['id']);
                }
            }
            
            // Créer une nouvelle relation de blocage
            $data = [
                'user_id' => $userId,
                'friend_id' => $blockedId,
                'status' => 'blocked',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->create($data) ? true : false;
        } catch (PDOException $e) {
            // Log error or handle exception
            error_log('Error in blockUser: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifie si un utilisateur est bloqué par un autre
     * 
     * @param int $userId ID de l'utilisateur qui pourrait bloquer
     * @param int $checkedId ID de l'utilisateur à vérifier
     * @return bool
     */
    public function isBlocked($userId, $checkedId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table} 
                WHERE 
                    (user_id = :user_id AND friend_id = :checked_id AND status = 'blocked')
                OR 
                    (user_id = :checked_id AND friend_id = :user_id AND status = 'blocked')
                LIMIT 1
            ");
            
            $stmt->execute([
                ':user_id' => $userId,
                ':checked_id' => $checkedId
            ]);
            
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            // Log error or handle exception
            error_log('Error in isBlocked: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les utilisateurs bloqués par un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Liste des utilisateurs bloqués
     */
    public function getBlockedUsers($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.username, u.email, u.name
                FROM {$this->table} f
                JOIN users u ON f.friend_id = u.id
                WHERE f.user_id = :user_id AND f.status = 'blocked'
            ");
            
            $stmt->execute([':user_id' => $userId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error or handle exception
            error_log('Error in getBlockedUsers: ' . $e->getMessage());
            return [];
        }
    }
}