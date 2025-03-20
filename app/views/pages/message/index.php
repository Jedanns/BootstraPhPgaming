<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Mes conversations</h5>
                <div>
                    <a href="<?= APP_URL ?>/message/create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Nouvelle conversation
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($conversations)): ?>
                <div class="alert alert-info">
                    <p class="mb-0">Vous n'avez pas encore de conversations. <a href="<?= APP_URL ?>/message/create">Démarrez une nouvelle conversation</a> ou <a href="<?= APP_URL ?>/friend">envoyez un message à un ami</a>.</p>
                </div>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($conversations as $conversation): ?>
                    <a href="<?= APP_URL ?>/message/viewConversation/<?= $conversation['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <?php if ($conversation['type'] === 'group'): ?>
                            <div class="avatar-placeholder bg-info text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 48px; height: 48px;">
                                <i class="fas fa-users"></i>
                            </div>
                            <?php else: ?>
                            <div class="avatar-placeholder bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 48px; height: 48px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <?php endif; ?>
                            
                            <div>
                                <h6 class="mb-0">
                                    <?php if ($conversation['type'] === 'group'): ?>
                                        <?= htmlspecialchars($conversation['title']) ?>
                                    <?php else: ?>
                                        <?php
                                        // Pour les conversations privées, afficher le nom de l'autre participant
                                        $otherParticipant = null;
                                        foreach ($conversation['participants'] as $participant) {
                                            if ($participant['id'] != $_SESSION['user_id']) {
                                                $otherParticipant = $participant;
                                                break;
                                            }
                                        }
                                        echo $otherParticipant ? htmlspecialchars($otherParticipant['username']) : 'Conversation';
                                        ?>
                                    <?php endif; ?>
                                </h6>
                                <small class="text-muted">
                                    <?php if (!empty($conversation['last_message'])): ?>
                                        <?= htmlspecialchars(mb_strimwidth($conversation['last_message'], 0, 50, '...')) ?>
                                    <?php else: ?>
                                        Pas de messages
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="d-flex flex-column align-items-end">
                            <?php if (!empty($conversation['last_message_date'])): ?>
                            <small class="text-muted">
                                <?= (new DateTime($conversation['last_message_date']))->format('d/m/Y H:i') ?>
                            </small>
                            <?php endif; ?>
                            
                            <?php if ($conversation['unread_count'] > 0): ?>
                            <span class="badge bg-primary rounded-pill mt-1"><?= $conversation['unread_count'] ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>