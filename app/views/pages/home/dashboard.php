<h1 class="mb-4">Tableau de bord</h1>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Profil</h5>
            </div>
            <div class="card-body">
                <p><strong>Nom d'utilisateur:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Nom:</strong> <?= htmlspecialchars($user['name'] ?? 'Non défini') ?></p>
                <p><strong>Inscrit le:</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
                
                <a href="<?= APP_URL ?>/user/profile" class="btn btn-outline-primary">Modifier mon profil</a>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Modules disponibles</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <i class="fas fa-gamepad fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-0">Jeux</h6>
                                <small class="text-muted">Divertissez-vous avec nos jeux</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <i class="fas fa-stopwatch fa-2x text-success me-3"></i>
                            <div>
                                <h6 class="mb-0">Compteurs</h6>
                                <small class="text-muted">Gérez différents compteurs</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <i class="fas fa-tasks fa-2x text-warning me-3"></i>
                            <div>
                                <h6 class="mb-0">Tâches</h6>
                                <small class="text-muted">Gardez une trace de vos tâches</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <i class="fas fa-sticky-note fa-2x text-info me-3"></i>
                            <div>
                                <h6 class="mb-0">Pense-bête</h6>
                                <small class="text-muted">Notez vos idées rapidement</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Activité récente</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Aucune activité récente à afficher.</p>
            </div>
        </div>
    </div>
</div>