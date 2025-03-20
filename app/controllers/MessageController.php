<?php
namespace App\Controllers;

use App\Models\ConversationModel;
use App\Models\MessageModel;
use App\Models\UserModel;
use App\Models\FriendModel;

/**
 * Contrôleur pour la gestion des messages
 */
class MessageController extends BaseController
{
    private $conversationModel;
    private $messageModel;
    private $userModel;
    private $friendModel;
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->conversationModel = new ConversationModel();
        $this->messageModel = new MessageModel();
        $this->userModel = new UserModel();
        $this->friendModel = new FriendModel();
    }
    
    /**
     * Page principale - liste des conversations
     */
    public function index()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        
        // Récupérer les conversations de l'utilisateur
        $conversations = $this->conversationModel->getUserConversations($userId);
        
        $data = [
            'title' => 'Mes conversations',
            'conversations' => $conversations
        ];
        
        $this->view('message/index', $data);
    }
    
    /**
     * Afficher une conversation
     * 
     * @param int $conversationId ID de la conversation
     */
    public function viewConversation($conversationId = 0)
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        $conversationId = (int)$conversationId;
        
        // Vérifier que l'utilisateur est participant à la conversation
        if (!$this->conversationModel->isParticipant($conversationId, $userId)) {
            $_SESSION['flash_message'] = 'Vous n\'avez pas accès à cette conversation.';
            $this->redirect('message');
            return;
        }
        
        // Récupérer les informations de la conversation
        $conversation = $this->conversationModel->getConversationWithParticipants($conversationId);
        
        if (!$conversation) {
            $_SESSION['flash_message'] = 'Conversation introuvable.';
            $this->redirect('message');
            return;
        }
        
        // Récupérer les messages de la conversation
        $messages = $this->messageModel->getConversationMessages($conversationId);
        
        // Marquer la conversation comme lue
        $this->conversationModel->markAsRead($conversationId, $userId);
        
        // Préparer les données pour la vue
        $data = [
            'title' => $conversation['title'] ?? 'Conversation',
            'conversation' => $conversation,
            'messages' => $messages,
            'userId' => $userId
        ];
        
        $this->view('message/conversation', $data);
    }
    
    /**
     * Créer une nouvelle conversation
     */
    public function create()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Récupérer la liste des amis pour ajouter à la conversation
            $friends = $this->friendModel->getFriends($userId);
            
            $data = [
                'title' => 'Nouvelle conversation',
                'friends' => $friends
            ];
            
            $this->view('message/create', $data);
        } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Traiter la création de la conversation
            $title = trim($_POST['title'] ?? '');
            $type = ($_POST['type'] ?? 'private') === 'group' ? 'group' : 'private';
            $participants = isset($_POST['participants']) ? (array)$_POST['participants'] : [];
            
            // Ajouter l'utilisateur courant aux participants
            if (!in_array($userId, $participants)) {
                $participants[] = $userId;
            }
            
            // Vérifier que tous les participants existent et sont amis avec l'utilisateur
            $validParticipants = [];
            
            foreach ($participants as $participantId) {
                $participantId = (int)$participantId;
                
                if ($participantId == $userId || $this->friendModel->areFriends($userId, $participantId)) {
                    $validParticipants[] = $participantId;
                }
            }
            
            // Vérifier qu'il y a au moins 2 participants
            if (count($validParticipants) < 2) {
                $_SESSION['flash_message'] = 'Une conversation doit avoir au moins 2 participants.';
                $this->redirect('message/create');
                return;
            }
            
            // Si conversation privée à 2, vérifier qu'elle n'existe pas déjà
            if ($type === 'private' && count($validParticipants) === 2) {
                $otherUserId = $validParticipants[0] == $userId ? $validParticipants[1] : $validParticipants[0];
                $existingConversationId = $this->conversationModel->findPrivateConversation($userId, $otherUserId);
                
                if ($existingConversationId) {
                    $this->redirect("message/viewConversation/{$existingConversationId}");
                    return;
                }
            }
            
            // Si type groupe, le titre est obligatoire
            if ($type === 'group' && empty($title)) {
                $_SESSION['flash_message'] = 'Le titre est obligatoire pour une conversation de groupe.';
                $this->redirect('message/create');
                return;
            }
            
            // Créer la conversation
            $conversationId = $this->conversationModel->createConversation($title, $type, $validParticipants, $userId);
            
            if ($conversationId) {
                $this->redirect("message/viewConversation/{$conversationId}");
            } else {
                $_SESSION['flash_message'] = 'Erreur lors de la création de la conversation.';
                $this->redirect('message');
            }
        }
    }
    
    /**
     * Envoyer un message
     */
    public function send()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        // Traiter uniquement les requêtes POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $conversationId = isset($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : 0;
            $content = trim($_POST['content'] ?? '');
            
            // Vérifier que le message n'est pas vide
            if (empty($content)) {
                $_SESSION['flash_message'] = 'Le message ne peut pas être vide.';
                $this->redirect("message/viewConversation/{$conversationId}");
                return;
            }
            
            // Vérifier que l'utilisateur est participant à la conversation
            if (!$this->conversationModel->isParticipant($conversationId, $userId)) {
                $_SESSION['flash_message'] = 'Vous n\'avez pas accès à cette conversation.';
                $this->redirect('message');
                return;
            }
            
            // Envoyer le message
            $messageId = $this->messageModel->sendMessage($conversationId, $userId, $content);
            
            if (!$messageId) {
                $_SESSION['flash_message'] = 'Erreur lors de l\'envoi du message.';
            }
            
            // Rediriger vers la conversation
            $this->redirect("message/viewConversation/{$conversationId}");
        } else {
            // Rediriger vers la liste des conversations
            $this->redirect('message');
        }
    }
    
    /**
     * Supprimer un message
     */
    public function delete()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        // Traiter uniquement les requêtes POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $messageId = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;
            $conversationId = isset($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : 0;
            
            // Vérifier que l'utilisateur est participant à la conversation
            if (!$this->conversationModel->isParticipant($conversationId, $userId)) {
                $_SESSION['flash_message'] = 'Vous n\'avez pas accès à cette conversation.';
                $this->redirect('message');
                return;
            }
            
            // Supprimer le message
            $success = $this->messageModel->deleteMessage($messageId, $userId);
            
            if (!$success) {
                $_SESSION['flash_message'] = 'Vous ne pouvez pas supprimer ce message.';
            } else {
                $_SESSION['flash_message'] = 'Message supprimé avec succès.';
            }
            
            // Rediriger vers la conversation
            $this->redirect("message/viewConversation/{$conversationId}");
        } else {
            // Rediriger vers la liste des conversations
            $this->redirect('message');
        }
    }
    
    /**
     * Quitter une conversation
     */
    public function leave()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        // Traiter uniquement les requêtes POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $conversationId = isset($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : 0;
            
            // Vérifier que l'utilisateur est participant à la conversation
            if (!$this->conversationModel->isParticipant($conversationId, $userId)) {
                $_SESSION['flash_message'] = 'Vous n\'avez pas accès à cette conversation.';
                $this->redirect('message');
                return;
            }
            
            // Récupérer les informations de la conversation
            $conversation = $this->conversationModel->getById($conversationId);
            
            if (!$conversation) {
                $_SESSION['flash_message'] = 'Conversation introuvable.';
                $this->redirect('message');
                return;
            }
            
            // Si c'est une conversation privée à 2, supprimer toute la conversation
            if ($conversation['type'] === 'private') {
                $participants = $this->conversationModel->getConversationParticipants($conversationId);
                
                if (count($participants) <= 2) {
                    // Supprimer tous les messages
                    $stmt = $this->messageModel->db->prepare("DELETE FROM messages WHERE conversation_id = :conversation_id");
                    $stmt->bindParam(':conversation_id', $conversationId, \PDO::PARAM_INT);
                    $stmt->execute();
                    
                    // Supprimer les participants
                    $stmt = $this->conversationModel->db->prepare("DELETE FROM conversation_participants WHERE conversation_id = :conversation_id");
                    $stmt->bindParam(':conversation_id', $conversationId, \PDO::PARAM_INT);
                    $stmt->execute();
                    
                    // Supprimer la conversation
                    $this->conversationModel->delete($conversationId);
                    
                    $_SESSION['flash_message'] = 'Conversation supprimée.';
                    $this->redirect('message');
                    return;
                }
            }
            
            // Sinon, juste retirer l'utilisateur de la conversation
            $this->conversationModel->removeParticipant($conversationId, $userId);
            
            $_SESSION['flash_message'] = 'Vous avez quitté la conversation.';
            $this->redirect('message');
        } else {
            // Rediriger vers la liste des conversations
            $this->redirect('message');
        }
    }
    
    /**
     * Ajouter un utilisateur à une conversation de groupe
     */
    public function addUser()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        // Traiter uniquement les requêtes POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $conversationId = isset($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : 0;
            $newUserId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            
            // Vérifier que l'utilisateur est admin de la conversation
            if (!$this->conversationModel->isAdmin($conversationId, $userId)) {
                $_SESSION['flash_message'] = 'Vous n\'avez pas les droits pour ajouter des membres à cette conversation.';
                $this->redirect("message/viewConversation/{$conversationId}");
                return;
            }
            
            // Vérifier que la conversation est de type groupe
            $conversation = $this->conversationModel->getById($conversationId);
            
            if (!$conversation || $conversation['type'] !== 'group') {
                $_SESSION['flash_message'] = 'Impossible d\'ajouter des membres à cette conversation.';
                $this->redirect("message/viewConversation/{$conversationId}");
                return;
            }
            
            // Vérifier que le nouvel utilisateur est ami avec l'utilisateur courant
            if (!$this->friendModel->areFriends($userId, $newUserId)) {
                $_SESSION['flash_message'] = 'Vous ne pouvez ajouter que vos amis à la conversation.';
                $this->redirect("message/viewConversation/{$conversationId}");
                return;
            }
            
            // Ajouter l'utilisateur à la conversation
            $this->conversationModel->addParticipant($conversationId, $newUserId);
            
            $_SESSION['flash_message'] = 'Utilisateur ajouté à la conversation.';
            $this->redirect("message/viewConversation/{$conversationId}");
        } else {
            // Rediriger vers la liste des conversations
            $this->redirect('message');
        }
    }
}