<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Rechercher des amis</h5>
                <div>
                    <a href="<?= APP_URL ?>/friend" class="btn btn-outline-secondary btn-sm me-2">
                        <i class="fas fa-users"></i> Mes amis
                    </a>
                    <a href="<?= APP_URL ?>/friend/requests" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-user-plus"></i> Demandes
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Formulaire de recherche -->
                <form action="<?= APP_URL ?>/friend/search" method="post" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" id="search-input" placeholder="Rechercher par nom d'utilisateur, email ou nom..." value="<?= htmlspecialchars($search) ?>" required>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                    </div>
                </form>
                
                <!-- Indicateur de chargement -->
                <div id="search-loading" class="text-center py-3 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2 text-muted">Recherche en cours...</p>
                </div>
                
                <!-- Résultats de recherche -->
                <div id="search-results">
                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                        <?php if (empty($results)): ?>
                        <div class="alert alert-info">
                            <p class="mb-0">Aucun utilisateur trouvé pour "<?= htmlspecialchars($search) ?>".</p>
                        </div>
                        <?php else: ?>
                        <h6 class="mb-3">Résultats de recherche pour "<?= htmlspecialchars($search) ?>":</h6>
                        <div class="list-group">
                            <?php foreach ($results as $user): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder me-3">
                                            <i class="fas fa-user fa-2x text-secondary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($user['username']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($user['name'] ?? 'Sans nom') ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <?php if (isset($user['relation'])): ?>
                                        <?php if ($user['relation'] === 'accepted'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check"></i> Amis</span>
                                        <?php elseif ($user['relation'] === 'pending'): ?>
                                            <?php if ($user['is_sender']): ?>
                                                <span class="badge bg-secondary"><i class="fas fa-clock"></i> Invitation envoyée</span>
                                            <?php else: ?>
                                                <div class="btn-group">
                                                    <form action="<?= APP_URL ?>/friend/accept" method="post" class="me-1">
                                                        <input type="hidden" name="relation_id" value="<?= $user['relation_id'] ?>">
                                                        <input type="hidden" name="redirect" value="friend/search">
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="fas fa-check"></i> Accepter
                                                        </button>
                                                    </form>
                                                    <form action="<?= APP_URL ?>/friend/reject" method="post">
                                                        <input type="hidden" name="relation_id" value="<?= $user['relation_id'] ?>">
                                                        <input type="hidden" name="redirect" value="friend/search">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                                            <i class="fas fa-times"></i> Refuser
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif ($user['relation'] === 'blocked'): ?>
                                            <span class="badge bg-danger"><i class="fas fa-ban"></i> Bloqué</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <form action="<?= APP_URL ?>/friend/add" method="post">
                                            <input type="hidden" name="friend_id" value="<?= $user['id'] ?>">
                                            <input type="hidden" name="redirect" value="friend/search">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-user-plus"></i> Ajouter
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <p class="text-muted">Recherchez des utilisateurs pour les ajouter en amis.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    const searchLoading = document.getElementById('search-loading');
    let searchTimeout = null;

    // Fonction pour effectuer la recherche AJAX
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        
        // Ne rien faire si la recherche est trop courte
        if (searchTerm.length < 2) {
            searchResults.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Entrez au moins 2 caractères pour lancer la recherche.</p>
                </div>
            `;
            return;
        }
        
        // Afficher l'indicateur de chargement
        searchResults.classList.add('d-none');
        searchLoading.classList.remove('d-none');
        
        // Préparer les données du formulaire
        const formData = new FormData();
        formData.append('search', searchTerm);
        formData.append('ajax', '1'); // Indicateur pour le serveur
        
        // Envoyer la requête AJAX
        fetch('<?= APP_URL ?>/friend/search', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau: ' + response.status);
            }
            return response.text();
        })
        .then(html => {
            // Cacher l'indicateur de chargement
            searchLoading.classList.add('d-none');
            searchResults.classList.remove('d-none');
            
            // Afficher les résultats
            searchResults.innerHTML = html;
        })
        .catch(error => {
            console.error('Erreur:', error);
            searchLoading.classList.add('d-none');
            searchResults.classList.remove('d-none');
            searchResults.innerHTML = `
                <div class="alert alert-danger">
                    <p class="mb-0">Une erreur s'est produite lors de la recherche. Veuillez réessayer.</p>
                </div>
            `;
        });
    }

    // Empêcher la soumission du formulaire par défaut si c'est depuis l'input
    form.addEventListener('submit', function(e) {
        if (e.submitter && e.submitter.tagName === 'BUTTON') {
            return true; // Permettre si on clique sur le bouton
        }
        e.preventDefault();
        performSearch();
    });

    // Écouter les changements dans l'input de recherche avec debounce
    searchInput.addEventListener('input', function() {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        
        searchTimeout = setTimeout(performSearch, 500); // 500ms de délai
    });
});
</script>