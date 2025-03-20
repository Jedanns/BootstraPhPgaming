<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * Modèle pour la gestion des conversations
 */
class ConversationModel extends BaseModel
{
    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct('conversations');
    }
    
    /**
     * Crée une nouvelle conversation
     * 
     * @param string $title Titre de la conversation (optionnel)
     * @param string $type Type de conversation ('private' ou 'group')
     * @param array $participants IDs des participants
     * @param int $creatorId ID de l'utilisateur qui crée la conversation
     * @return int|false ID de la conversation créée ou false en cas d'échec
     */
    public function createConversation($title, $type, $participants, $creatorId)
    {
        $this->db->beginTransaction();
        
        try {
            // Créer la conversation
            $data = [
                'title' => $title,
                'type' => $type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $conversationId = $this->create($data);
            
            if (!$conversationId) {
                $this->db->rollBack();
                return false;
            }
            
            // Ajouter le créateur comme admin
            $this->addParticipant($conversationId, $creatorId, true);
            
            // Ajouter les autres participants
            foreach ($participants as $participantId) {
                if ($participantId != $creatorId) {
                    $this->addParticipant($conversationId, $participantId);
                }
            }
            
            $this->db->commit();
            return $conversationId;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('Error creating conversation: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajoute un participant à une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param int $userId ID de l'utilisateur
     * @param bool $isAdmin Si l'utilisateur est administrateur
     * @return bool
     */
    public function addParticipant($conversationId, $userId, $isAdmin = false)
    {
        try {
            // Vérifier si l'utilisateur est déjà participant
            $stmt = $this->db->prepare("
                SELECT id FROM conversation_participants 
                WHERE conversation_id = :conversation_id AND user_id = :user_id
                LIMIT 1
            ");
            
            $stmt->execute([
                ':conversation_id' => $conversationId, 
                ':user_id' => $userId
            ]);
            
            if ($stmt->fetch()) {
                return true; // Déjà participant
            }
            
            // Ajouter le participant
            $stmt = $this->db->prepare("
                INSERT INTO conversation_participants 
                (conversation_id, user_id, is_admin, created_at) 
                VALUES (:conversation_id, :user_id, :is_admin, :created_at)
            ");
            
            $now = date('Y-m-d H:i:s');
            $isAdminInt = $isAdmin ? 1 : 0;
            
            return $stmt->execute([
                ':conversation_id' => $conversationId,
                ':user_id' => $userId,
                ':is_admin' => $isAdminInt,
                ':created_at' => $now
            ]);
        } catch (PDOException $e) {
            error_log('Error adding participant: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retire un participant d'une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function removeParticipant($conversationId, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM conversation_participants 
                WHERE conversation_id = :conversation_id AND user_id = :user_id
            ");
            
            return $stmt->execute([
                ':conversation_id' => $conversationId, 
                ':user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log('Error removing participant: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifie si un utilisateur est participant à une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function isParticipant($conversationId, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM conversation_participants 
                WHERE conversation_id = :conversation_id AND user_id = :user_id
                LIMIT 1
            ");
            
            $stmt->execute([
                ':conversation_id' => $conversationId, 
                ':user_id' => $userId
            ]);
            
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            error_log('Error checking participant: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifie si un utilisateur est admin d'une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function isAdmin($conversationId, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM conversation_participants 
                WHERE conversation_id = :conversation_id AND user_id = :user_id AND is_admin = 1
                LIMIT 1
            ");
            
            $stmt->execute([
                ':conversation_id' => $conversationId, 
                ':user_id' => $userId
            ]);
            
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            error_log('Error checking admin: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Met à jour la date de dernière lecture pour un participant
     * 
     * @param int $conversationId ID de la conversation
     * @param int $userId ID de l'utilisateur
     * @return bool
     */
    public function markAsRead($conversationId, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE conversation_participants 
                SET last_read = :last_read 
                WHERE conversation_id = :conversation_id AND user_id = :user_id
            ");
            
            $now = date('Y-m-d H:i:s');
            
            return $stmt->execute([
                ':last_read' => $now,
                ':conversation_id' => $conversationId,
                ':user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log('Error marking conversation as read: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère toutes les conversations d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array
     */
    public function getUserConversations($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.*, 
                    cp.last_read,
                    (
                        SELECT COUNT(*) FROM messages m 
                        WHERE m.conversation_id = c.id
                        AND m.created_at > IFNULL(cp.last_read, '1970-01-01')
                        AND m.user_id != :user_id
                    ) as unread_count,
                    (
                        SELECT MAX(m.created_at) FROM messages m 
                        WHERE m.conversation_id = c.id 
                    ) as last_message_date,
                    (
                        SELECT m.content FROM messages m 
                        WHERE m.conversation_id = c.id 
                        ORDER BY m.created_at DESC LIMIT 1
                    ) as last_message
                FROM conversations c
                JOIN conversation_participants cp ON c.id = cp.conversation_id
                WHERE cp.user_id = :user_id
                ORDER BY last_message_date DESC
            ");
            
            $stmt->execute([':user_id' => $userId]);
            
            $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Pour chaque conversation, récupérer les participants
            foreach ($conversations as &$conversation) {
                $conversation['participants'] = $this->getConversationParticipants($conversation['id']);
            }
            
            return $conversations;
        } catch (PDOException $e) {
            error_log('Error getting user conversations: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère une conversation par son ID avec tous ses participants
     * 
     * @param int $conversationId ID de la conversation
     * @return array|false
     */
    public function getConversationWithParticipants($conversationId)
    {
        try {
            $conversation = $this->getById($conversationId);
            
            if ($conversation) {
                $conversation['participants'] = $this->getConversationParticipants($conversationId);
            }
            
            return $conversation;
        } catch (PDOException $e) {
            error_log('Error getting conversation with participants: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère tous les participants d'une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @return array
     */
    public function getConversationParticipants($conversationId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    u.id, u.username, u.email, u.name,
                    cp.is_admin, cp.muted, cp.last_read, cp.created_at as joined_at
                FROM conversation_participants cp
                JOIN users u ON cp.user_id = u.id
                WHERE cp.conversation_id = :conversation_id
            ");
            
            $stmt->execute([':conversation_id' => $conversationId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting conversation participants: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Trouve une conversation privée entre deux utilisateurs
     * 
     * @param int $userId1 ID du premier utilisateur
     * @param int $userId2 ID du deuxième utilisateur
     * @return int|false ID de la conversation ou false si non trouvée
     */
    public function findPrivateConversation($userId1, $userId2)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT cp1.conversation_id
                FROM conversation_participants cp1
                JOIN conversation_participants cp2 ON cp1.conversation_id = cp2.conversation_id
                JOIN conversations c ON c.id = cp1.conversation_id
                WHERE cp1.user_id = :user_id1
                AND cp2.user_id = :user_id2
                AND c.type = 'private'
                AND (
                    SELECT COUNT(*) FROM conversation_participants 
                    WHERE conversation_id = cp1.conversation_id
                ) = 2
            ");
            
            $stmt->execute([
                ':user_id1' => $userId1,
                ':user_id2' => $userId2
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['conversation_id'] : false;
        } catch (PDOException $e) {
            error_log('Error finding private conversation: ' . $e->getMessage());
            return false;
        }
    }
}