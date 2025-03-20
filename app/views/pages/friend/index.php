<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Mes amis</h5>
                <div>
                    <a href="<?= APP_URL ?>/friend/requests" class="btn btn-outline-primary btn-sm me-2">
                        <i class="fas fa-user-plus"></i> Demandes
                    </a>
                    <a href="<?= APP_URL ?>/friend/search" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i> Rechercher
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($friends)): ?>
                <div class="alert alert-info">
                    <p class="mb-0">Vous n'avez pas encore d'amis. <a href="<?= APP_URL ?>/friend/search">Recherchez des utilisateurs</a> pour ajouter des amis.</p>
                </div>
                <?php else: ?>
                <div class="list-group">
                    <?php foreach ($friends as $friend): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="d-flex align-items-center">
                                <div class="avatar-placeholder me-3">
                                    <i class="fas fa-user fa-2x text-secondary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($friend['username']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($friend['name'] ?? 'Sans nom') ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="btn-group">
                            <form action="<?= APP_URL ?>/friend/message" method="post">
                                <input type="hidden" name="friend_id" value="<?= $friend['id'] ?>">
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-comment"></i> Message
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form action="<?= APP_URL ?>/friend/remove" method="post" class="dropdown-item">
                                        <input type="hidden" name="friend_id" value="<?= $friend['id'] ?>">
                                        <input type="hidden" name="redirect" value="friend">
                                        <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet ami ?')">
                                            <i class="fas fa-user-times"></i> Supprimer
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="<?= APP_URL ?>/friend/block" method="post" class="dropdown-item">
                                        <input type="hidden" name="user_id" value="<?= $friend['id'] ?>">
                                        <input type="hidden" name="redirect" value="friend">
                                        <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Êtes-vous sûr de vouloir bloquer cet utilisateur ?')">
                                            <i class="fas fa-ban"></i> Bloquer
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>