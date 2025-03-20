<?php
namespace App\Controllers;

use App\Models\FriendModel;
use App\Models\UserModel;
use App\Models\ConversationModel;

/**
 * Contrôleur pour la gestion des amis
 */
class FriendController extends BaseController
{
    private $friendModel;
    private $userModel;
    private $conversationModel;
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->friendModel = new FriendModel();
        $this->userModel = new UserModel();
        $this->conversationModel = new ConversationModel();
    }
    
    /**
     * Page principale - liste des amis
     */
    public function index()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        
        // Récupérer la liste des amis
        $friends = $this->friendModel->getFriends($userId);
        
        $data = [
            'title' => 'Mes amis',
            'friends' => $friends
        ];
        
        $this->view('friend/index', $data);
    }
    
    /**
     * Page des demandes d'amitié
     */
    public function requests()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        
        // Récupérer les demandes reçues et envoyées
        $receivedRequests = $this->friendModel->getPendingRequests($userId);
        $sentRequests = $this->friendModel->getSentRequests($userId);
        
        $data = [
            'title' => 'Demandes d\'amitié',
            'receivedRequests' => $receivedRequests,
            'sentRequests' => $sentRequests
        ];
        
        $this->view('friend/requests', $data);
    }
    
    /**
     * Recherche d'utilisateurs
     */
    public function search()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        $search = '';
        $results = [];
        
        // Traiter la recherche si soumise
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $search = trim($_POST['search'] ?? '');
            
            if (!empty($search)) {
                // Rechercher les utilisateurs
                $results = $this->userModel->searchUsers($search);
                
                // Filtrer l'utilisateur courant
                $results = array_filter($results, function($user) use ($userId) {
                    return $user['id'] != $userId;
                });
                
                // Pour chaque utilisateur, vérifier s'il y a déjà une relation
                foreach ($results as &$user) {
                    $relation = $this->friendModel->getRelation($userId, $user['id']);
                    if ($relation) {
                        $user['relation'] = $relation['status'];
                        $user['relation_id'] = $relation['id'];
                        
                        // Déterminer qui est l'initiateur
                        $user['is_sender'] = ($relation['user_id'] == $userId);
                    } else {
                        $user['relation'] = null;
                    }
                }
            }
        }
        
        $data = [
            'title' => 'Rechercher des amis',
            'search' => $search,
            'results' => $results
        ];
        
        $this->view('friend/search', $data);
    }
    
    /**
     * Envoyer une demande d'amitié
     */
    public function add()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $friendId = isset($_POST['friend_id']) ? (int)$_POST['friend_id'] : 0;
            
            // Vérifier que l'ami existe
            $friend = $this->userModel->getById($friendId);
            
            if ($friend && $friendId != $userId) {
                // Vérifier s'ils ne sont pas déjà amis
                if (!$this->friendModel->areFriends($userId, $friendId)) {
                    // Vérifier si l'utilisateur n'est pas bloqué
                    if (!$this->friendModel->isBlocked($friendId, $userId)) {
                        // Envoyer la demande
                        $result = $this->friendModel->sendRequest($userId, $friendId);
                        
                        if ($result) {
                            $_SESSION['flash_message'] = 'Demande d\'amitié envoyée avec succès.';
                        } else {
                            $_SESSION['flash_message'] = 'Vous avez déjà envoyé une demande à cet utilisateur.';
                        }
                    } else {
                        $_SESSION['flash_message'] = 'Vous ne pouvez pas envoyer de demande d\'amitié à cet utilisateur.';
                    }
                } else {
                    $_SESSION['flash_message'] = 'Vous êtes déjà ami avec cet utilisateur.';
                }
            } else {
                $_SESSION['flash_message'] = 'Utilisateur invalide.';
            }
            
            // Redirection
            $redirect = $_POST['redirect'] ?? 'friend/search';
            $this->redirect($redirect);
        } else {
            // Redirection par défaut
            $this->redirect('friend/search');
        }
    }
    
    /**
     * Accepter une demande d'amitié
     */
    public function accept()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $relationId = isset($_POST['relation_id']) ? (int)$_POST['relation_id'] : 0;
            
            // Récupérer la relation
            $stmt = $this->friendModel->db->prepare("
                SELECT * FROM friends WHERE id = :id AND friend_id = :user_id LIMIT 1
            ");
            $stmt->bindParam(':id', $relationId, \PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            
            $relation = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($relation) {
                // Accepter la demande
                $this->friendModel->updateStatus($relationId, 'accepted');
                
                $_SESSION['flash_message'] = 'Demande d\'amitié acceptée.';
            } else {
                $_SESSION['flash_message'] = 'Demande invalide.';
            }
            
            // Redirection
            $redirect = $_POST['redirect'] ?? 'friend/requests';
            $this->redirect($redirect);
        } else {
            // Redirection par défaut
            $this->redirect('friend/requests');
        }
    }
    
    /**
     * Rejeter une demande d'amitié
     */
    public function reject()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $relationId = isset($_POST['relation_id']) ? (int)$_POST['relation_id'] : 0;
            
            // Récupérer la relation
            $stmt = $this->friendModel->db->prepare("
                SELECT * FROM friends WHERE id = :id AND friend_id = :user_id LIMIT 1
            ");
            $stmt->bindParam(':id', $relationId, \PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            
            $relation = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($relation) {
                // Rejeter la demande
                $this->friendModel->updateStatus($relationId, 'rejected');
                
                $_SESSION['flash_message'] = 'Demande d\'amitié rejetée.';
            } else {
                $_SESSION['flash_message'] = 'Demande invalide.';
            }
            
            // Redirection
            $redirect = $_POST['redirect'] ?? 'friend/requests';
            $this->redirect($redirect);
        } else {
            // Redirection par défaut
            $this->redirect('friend/requests');
        }
    }
    
    /**
     * Supprimer un ami
     */
    public function remove()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $friendId = isset($_POST['friend_id']) ? (int)$_POST['friend_id'] : 0;
            
            // Vérifier qu'ils sont bien amis
            if ($this->friendModel->areFriends($userId, $friendId)) {
                // Supprimer l'ami
                $this->friendModel->removeFriend($userId, $friendId);
                
                $_SESSION['flash_message'] = 'Ami supprimé avec succès.';
            } else {
                $_SESSION['flash_message'] = 'Cet utilisateur n\'est pas dans votre liste d\'amis.';
            }
            
            // Redirection
            $redirect = $_POST['redirect'] ?? 'friend';
            $this->redirect($redirect);
        } else {
            // Redirection par défaut
            $this->redirect('friend');
        }
    }
    
    /**
     * Bloquer un utilisateur
     */
    public function block()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $blockId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            
            // Vérifier que l'utilisateur existe
            $user = $this->userModel->getById($blockId);
            
            if ($user && $blockId != $userId) {
                // Bloquer l'utilisateur
                $this->friendModel->blockUser($userId, $blockId);
                
                $_SESSION['flash_message'] = 'Utilisateur bloqué.';
            } else {
                $_SESSION['flash_message'] = 'Utilisateur invalide.';
            }
            
            // Redirection
            $redirect = $_POST['redirect'] ?? 'friend';
            $this->redirect($redirect);
        } else {
            // Redirection par défaut
            $this->redirect('friend');
        }
    }
    
    /**
     * Démarrer une conversation avec un ami
     */
    public function message()
    {
        // Vérifier si l'utilisateur est connecté
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $friendId = isset($_POST['friend_id']) ? (int)$_POST['friend_id'] : 0;
            
            // Vérifier qu'ils sont bien amis
            if ($this->friendModel->areFriends($userId, $friendId)) {
                // Récupérer les informations de l'ami
                $friend = $this->userModel->getById($friendId);
                
                if ($friend) {
                    // Vérifier si une conversation privée existe déjà
                    $conversationId = $this->conversationModel->findPrivateConversation($userId, $friendId);
                    
                    if (!$conversationId) {
                        // Créer une nouvelle conversation
                        $title = null; // Pas de titre pour une conversation privée
                        $type = 'private';
                        $participants = [$userId, $friendId];
                        
                        $conversationId = $this->conversationModel->createConversation($title, $type, $participants, $userId);
                    }
                    
                    // Rediriger vers la conversation
                    if ($conversationId) {
                        $this->redirect("message/viewConversation/{$conversationId}");
                        return;
                    }
                }
            }
            
            $_SESSION['flash_message'] = 'Impossible de démarrer une conversation avec cet utilisateur.';
            $this->redirect('friend');
        } else {
            // Redirection par défaut
            $this->redirect('friend');
        }
    }
}