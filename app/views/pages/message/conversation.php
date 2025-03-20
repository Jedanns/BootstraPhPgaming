<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a href="<?= APP_URL ?>/message" class="btn btn-outline-secondary btn-sm me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h5 class="card-title mb-0">
                        <?php if ($conversation['type'] === 'group'): ?>
                            <?= htmlspecialchars($conversation['title']) ?>
                        <?php else: ?>
                            <?php
                            // Pour les conversations privées, afficher le nom de l'autre participant
                            $otherParticipant = null;
                            foreach ($conversation['participants'] as $participant) {
                                if ($participant['id'] != $userId) {
                                    $otherParticipant = $participant;
                                    break;
                                }
                            }
                            echo $otherParticipant ? htmlspecialchars($otherParticipant['username']) : 'Conversation';
                            ?>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm" type="button" id="conversationOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="conversationOptionsDropdown">
                        <?php if ($conversation['type'] === 'group'): ?>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#participantsModal"><i class="fas fa-users"></i> Participants</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php
                            // Vérifier si l'utilisateur est admin
                            $isAdmin = false;
                            foreach ($conversation['participants'] as $participant) {
                                if ($participant['id'] == $userId && $participant['is_admin']) {
                                    $isAdmin = true;
                                    break;
                                }
                            }
                            ?>
                            <?php if ($isAdmin): ?>
                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fas fa-user-plus"></i> Ajouter des membres</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php endif; ?>
                        <li>
                            <form action="<?= APP_URL ?>/message/leave" method="post" class="dropdown-item">
                                <input type="hidden" name="conversation_id" value="<?= $conversation['id'] ?>">
                                <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Êtes-vous sûr de vouloir quitter cette conversation ?')">
                                    <i class="fas fa-sign-out-alt"></i> Quitter la conversation
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Boîte de messages -->
            <div class="card-body" style="height: 400px; overflow-y: auto;" id="messages-container">
                <?php if (empty($messages)): ?>
                <div class="text-center py-5">
                    <p class="text-muted mb-0">Pas de messages. Commencez la conversation !</p>
                </div>
                <?php else: ?>
                <div class="messages">
                    <?php
                    $lastDate = null;
                    foreach ($messages as $message):
                        $messageDate = new DateTime($message['created_at']);
                        $today = new DateTime();
                        $yesterday = new DateTime('yesterday');
                        
                        // Afficher la date si elle est différente de la précédente
                        if (!$lastDate || $messageDate->format('Y-m-d') !== $lastDate->format('Y-m-d')):
                            $dateDisplay = '';
                            if ($messageDate->format('Y-m-d') === $today->format('Y-m-d')) {
                                $dateDisplay = 'Aujourd\'hui';
                            } else if ($messageDate->format('Y-m-d') === $yesterday->format('Y-m-d')) {
                                $dateDisplay = 'Hier';
                            } else {
                                $dateDisplay = $messageDate->format('d/m/Y');
                            }
                    ?>
                    <div class="text-center my-3">
                        <span class="badge bg-secondary"><?= $dateDisplay ?></span>
                    </div>
                    <?php
                            $lastDate = $messageDate;
                        endif;
                        
                        // Déterminer si c'est un message de l'utilisateur courant
                        $isCurrentUser = $message['user_id'] == $userId;
                    ?>
                    
                    <div class="message-row d-flex mb-3 <?= $isCurrentUser ? 'justify-content-end' : 'justify-content-start' ?>">
                        <?php if (!$isCurrentUser): ?>
                        <div class="avatar-placeholder bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <?php endif; ?>
                        
                        <div class="message-bubble <?= $isCurrentUser ? 'bg-primary text-white' : 'bg-light' ?>" style="max-width: 70%; border-radius: 18px; padding: 10px 15px;">
                            <?php if (!$isCurrentUser): ?>
                            <div class="message-sender">
                                <small class="fw-bold"><?= htmlspecialchars($message['username']) ?></small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="message-content">
                                <?= nl2br(htmlspecialchars($message['content'])) ?>
                            </div>
                            
                            <div class="message-time text-end">
                                <small class="<?= $isCurrentUser ? 'text-white-50' : 'text-muted' ?>"><?= $messageDate->format('H:i') ?></small>
                                
                                <?php if ($isCurrentUser): ?>
                                <span class="dropdown dropstart d-inline-block ms-1">
                                    <button class="btn btn-link btn-sm p-0 <?= $isCurrentUser ? 'text-white-50' : 'text-muted' ?>" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <form action="<?= APP_URL ?>/message/delete" method="post" class="dropdown-item">
                                                <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                                                <input type="hidden" name="conversation_id" value="<?= $conversation['id'] ?>">
                                                <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Supprimer ce message ?')">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Formulaire d'envoi de message -->
            <div class="card-footer">
                <form action="<?= APP_URL ?>/message/send" method="post" class="d-flex">
                    <input type="hidden" name="conversation_id" value="<?= $conversation['id'] ?>">
                    <textarea class="form-control me-2" name="content" id="message-input" rows="1" placeholder="Écrivez votre message..." required></textarea>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal des participants -->
<?php if ($conversation['type'] === 'group'): ?>
<div class="modal fade" id="participantsModal" tabindex="-1" aria-labelledby="participantsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="participantsModalLabel">Participants</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    <?php foreach ($conversation['participants'] as $participant): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="avatar-placeholder bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 36px; height: 36px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($participant['username']) ?></h6>
                                <?php if ($participant['is_admin']): ?>
                                <small class="text-muted">Administrateur</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($participant['id'] == $userId): ?>
                        <span class="badge bg-primary rounded-pill">Vous</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter des membres -->
<?php
// Vérifier si l'utilisateur est admin
$isAdmin = false;
foreach ($conversation['participants'] as $participant) {
    if ($participant['id'] == $userId && $participant['is_admin']) {
        $isAdmin = true;
        break;
    }
}

if ($isAdmin):
    // Liste des IDs des participants existants
    $participantIds = array_map(function($p) { return $p['id']; }, $conversation['participants']);
    
    // Instancier le modèle d'amis pour récupérer la liste des amis
    $friendModel = new \App\Models\FriendModel();
    $friends = $friendModel->getFriends($userId);
    
    // Filtrer pour ne garder que les amis qui ne sont pas déjà dans la conversation
    $availableFriends = array_filter($friends, function($friend) use ($participantIds) {
        return !in_array($friend['id'], $participantIds);
    });
?>
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Ajouter des membres</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($availableFriends)): ?>
                <div class="alert alert-info">
                    <p class="mb-0">Tous vos amis sont déjà membres de cette conversation ou vous n'avez pas d'amis.</p>
                </div>
                <?php else: ?>
                <form id="addUserForm" action="<?= APP_URL ?>/message/addUser" method="post">
                    <input type="hidden" name="conversation_id" value="<?= $conversation['id'] ?>">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Sélectionnez un ami à ajouter</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Choisir un ami...</option>
                            <?php foreach ($availableFriends as $friend): ?>
                            <option value="<?= $friend['id'] ?>"><?= htmlspecialchars($friend['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <?php if (!empty($availableFriends)): ?>
                <button type="submit" form="addUserForm" class="btn btn-primary">Ajouter</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Faire défiler automatiquement vers le bas
    const messagesContainer = document.getElementById('messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Redimensionner automatiquement la zone de texte
    const messageInput = document.getElementById('message-input');
    if (messageInput) {
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Réinitialiser la hauteur après l'envoi
        const messageForm = messageInput.closest('form');
        if (messageForm) {
            messageForm.addEventListener('submit', function() {
                setTimeout(function() {
                    messageInput.style.height = 'auto';
                }, 10);
            });
        }
    }
});
</script>