<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Demandes d'amitié</h5>
                <div>
                    <a href="<?= APP_URL ?>/friend" class="btn btn-outline-secondary btn-sm me-2">
                        <i class="fas fa-users"></i> Mes amis
                    </a>
                    <a href="<?= APP_URL ?>/friend/search" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i> Rechercher
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Onglets -->
                <ul class="nav nav-tabs mb-3" id="requestsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="received-tab" data-bs-toggle="tab" data-bs-target="#received" type="button" role="tab" aria-controls="received" aria-selected="true">
                            Reçues 
                            <?php if (!empty($receivedRequests)): ?>
                                <span class="badge bg-primary"><?= count($receivedRequests) ?></span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab" aria-controls="sent" aria-selected="false">
                            Envoyées
                            <?php if (!empty($sentRequests)): ?>
                                <span class="badge bg-secondary"><?= count($sentRequests) ?></span>
                            <?php endif; ?>
                        </button>
                    </li>
                </ul>
                
                <!-- Contenu des onglets -->
                <div class="tab-content" id="requestsTabContent">
                    <!-- Demandes reçues -->
                    <div class="tab-pane fade show active" id="received" role="tabpanel" aria-labelledby="received-tab">
                        <?php if (empty($receivedRequests)): ?>
                        <div class="alert alert-info">
                            <p class="mb-0">Vous n'avez pas de demandes d'amitié en attente.</p>
                        </div>
                        <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($receivedRequests as $request): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder me-3">
                                            <i class="fas fa-user fa-2x text-secondary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($request['username']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($request['name'] ?? 'Sans nom') ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <form action="<?= APP_URL ?>/friend/accept" method="post" class="me-1">
                                        <input type="hidden" name="relation_id" value="<?= $request['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Accepter
                                        </button>
                                    </form>
                                    <form action="<?= APP_URL ?>/friend/reject" method="post">
                                        <input type="hidden" name="relation_id" value="<?= $request['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-times"></i> Refuser
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Demandes envoyées -->
                    <div class="tab-pane fade" id="sent" role="tabpanel" aria-labelledby="sent-tab">
                        <?php if (empty($sentRequests)): ?>
                        <div class="alert alert-info">
                            <p class="mb-0">Vous n'avez pas de demandes d'amitié envoyées en attente.</p>
                        </div>
                        <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($sentRequests as $request): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder me-3">
                                            <i class="fas fa-user fa-2x text-secondary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($request['username']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($request['name'] ?? 'Sans nom') ?></small>
                                        </div>
                                    </div>
                                </div>
                                <span class="badge bg-secondary">En attente</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>