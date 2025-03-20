<?php
namespace App\Models;

use PDO;

/**
 * Modèle pour la gestion des messages
 */
class MessageModel extends BaseModel
{
    /**
     * Constructeur
     */
    public function __construct()
    {
        parent::__construct('messages');
    }
    
    /**
     * Envoie un message dans une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param int $userId ID de l'utilisateur qui envoie le message
     * @param string $content Contenu du message
     * @return int|false ID du message créé ou false en cas d'échec
     */
    public function sendMessage($conversationId, $userId, $content)
    {
        $data = [
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Créer le message
        $messageId = $this->create($data);
        
        if ($messageId) {
            // Mettre à jour la date de dernière lecture pour l'expéditeur
            $conversationModel = new ConversationModel();
            $conversationModel->markAsRead($conversationId, $userId);
            
            // Mettre à jour la date de mise à jour de la conversation
            $conversationModel->update($conversationId, [
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        return $messageId;
    }
    
    /**
     * Récupère les messages d'une conversation
     * 
     * @param int $conversationId ID de la conversation
     * @param int $limit Nombre maximal de messages à récupérer
     * @param int $offset Offset pour la pagination
     * @return array
     */
    public function getConversationMessages($conversationId, $limit = 50, $offset = 0)
    {
        $stmt = $this->db->prepare("
            SELECT m.*, u.username, u.email, u.name
            FROM {$this->table} m
            JOIN users u ON m.user_id = u.id
            WHERE m.conversation_id = :conversation_id
            ORDER BY m.created_at ASC
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindParam(':conversation_id', $conversationId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Compte le nombre de messages non lus dans une conversation pour un utilisateur
     * 
     * @param int $conversationId ID de la conversation
     * @param int $userId ID de l'utilisateur
     * @return int
     */
    public function countUnreadMessages($conversationId, $userId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM {$this->table} m
            JOIN conversation_participants cp ON m.conversation_id = cp.conversation_id
            WHERE m.conversation_id = :conversation_id
            AND cp.user_id = :user_id
            AND m.user_id != :user_id
            AND m.created_at > IFNULL(cp.last_read, '1970-01-01')
        ");
        
        $stmt->bindParam(':conversation_id', $conversationId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Récupère le nombre total de messages non lus pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return int
     */
    public function getTotalUnreadCount($userId)
    {
        $stmt = $this->db->prepare("
            SELECT SUM(
                (SELECT COUNT(*) FROM messages m 
                WHERE m.conversation_id = cp.conversation_id
                AND m.created_at > IFNULL(cp.last_read, '1970-01-01')
                AND m.user_id != :user_id)
            ) as total
            FROM conversation_participants cp
            WHERE cp.user_id = :user_id
        ");
        
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['total'] ? (int)$result['total'] : 0;
    }
    
    /**
     * Modifie un message
     * 
     * @param int $messageId ID du message
     * @param int $userId ID de l'utilisateur (pour vérifier qu'il est l'auteur)
     * @param string $content Nouveau contenu
     * @return bool
     */
    public function editMessage($messageId, $userId, $content)
    {
        // Vérifier que l'utilisateur est l'auteur du message
        $message = $this->getById($messageId);
        
        if (!$message || $message['user_id'] != $userId) {
            return false;
        }
        
        $data = [
            'content' => $content,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($messageId, $data);
    }
    
    /**
     * Supprime un message
     * 
     * @param int $messageId ID du message
     * @param int $userId ID de l'utilisateur (pour vérifier qu'il est l'auteur)
     * @return bool
     */
    public function deleteMessage($messageId, $userId)
    {
        // Vérifier que l'utilisateur est l'auteur du message
        $message = $this->getById($messageId);
        
        if (!$message || $message['user_id'] != $userId) {
            // Vérifier si l'utilisateur est admin de la conversation
            $conversationModel = new ConversationModel();
            if (!$conversationModel->isAdmin($message['conversation_id'], $userId)) {
                return false;
            }
        }
        
        return $this->delete($messageId);
    }
}