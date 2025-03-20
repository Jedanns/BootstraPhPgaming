<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <a href="<?= APP_URL ?>/message" class="btn btn-outline-secondary btn-sm me-3">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h5 class="card-title mb-0">Nouvelle conversation</h5>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($friends)): ?>
                <div class="alert alert-info">
                    <p class="mb-0">Vous n'avez pas encore d'amis. <a href="<?= APP_URL ?>/friend/search">Recherchez des utilisateurs</a> pour ajouter des amis avant de créer une conversation.</p>
                </div>
                <?php else: ?>
                <form action="<?= APP_URL ?>/message/create" method="post">
                    <div class="mb-3">
                        <label class="form-label">Type de conversation</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="typePrivate" value="private" checked>
                            <label class="form-check-label" for="typePrivate">
                                Privée (avec une personne)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="typeGroup" value="group">
                            <label class="form-check-label" for="typeGroup">
                                Groupe (plusieurs personnes)
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3" id="titleGroup" style="display: none;">
                        <label for="title" class="form-label">Titre du groupe</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Entrez un titre pour la conversation de groupe">
                        <div class="form-text">Un titre est requis pour les conversations de groupe.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="participants" class="form-label">Participants</label>
                        <select class="form-select" id="participants" name="participants[]" required>
                            <option value="">Choisir...</option>
                            <?php foreach ($friends as $friend): ?>
                            <option value="<?= $friend['id'] ?>"><?= htmlspecialchars($friend['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="participantsMultiple" style="display: none;">
                            <div class="form-text mb-2">Vous pouvez sélectionner plusieurs participants pour une conversation de groupe.</div>
                            <div id="selectedParticipants" class="mb-2"></div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="addParticipantBtn">
                                <i class="fas fa-plus"></i> Ajouter un autre participant
                            </button>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <a href="<?= APP_URL ?>/message" class="btn btn-outline-secondary me-2">Annuler</a>
                        <button type="submit" class="btn btn-primary">Créer la conversation</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typePrivate = document.getElementById('typePrivate');
    const typeGroup = document.getElementById('typeGroup');
    const titleGroup = document.getElementById('titleGroup');
    const participants = document.getElementById('participants');
    const participantsMultiple = document.getElementById('participantsMultiple');
    const selectedParticipants = document.getElementById('selectedParticipants');
    const addParticipantBtn = document.getElementById('addParticipantBtn');
    
    // Gérer le changement de type de conversation
    function handleConversationTypeChange() {
        if (typeGroup.checked) {
            titleGroup.style.display = 'block';
            participantsMultiple.style.display = 'block';
            participants.multiple = true;
        } else {
            titleGroup.style.display = 'none';
            participantsMultiple.style.display = 'none';
            participants.multiple = false;
        }
        
        // Mise à jour de l'affichage des participants sélectionnés
        updateSelectedParticipants();
    }
    
    // Mettre à jour l'affichage des participants sélectionnés
    function updateSelectedParticipants() {
        if (typeGroup.checked) {
            const selected = Array.from(participants.selectedOptions);
            
            if (selected.length > 0) {
                selectedParticipants.innerHTML = '';
                
                selected.forEach(option => {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-primary me-1 mb-1';
                    badge.textContent = option.text;
                    selectedParticipants.appendChild(badge);
                });
            } else {
                selectedParticipants.innerHTML = '<div class="text-muted">Aucun participant sélectionné</div>';
            }
        }
    }
    
    // Ajouter les écouteurs d'événements
    typePrivate.addEventListener('change', handleConversationTypeChange);
    typeGroup.addEventListener('change', handleConversationTypeChange);
    participants.addEventListener('change', updateSelectedParticipants);
    
    // Initialiser l'état
    handleConversationTypeChange();
    
    // Gérer le bouton d'ajout de participant
    addParticipantBtn.addEventListener('click', function() {
        participants.focus();
        participants.click();
    });
});
</script>