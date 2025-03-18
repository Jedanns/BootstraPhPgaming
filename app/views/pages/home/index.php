<div class="jumbotron bg-light p-5 rounded">
    <h1 class="display-4">Bienvenue sur <?= APP_NAME ?></h1>
    <p class="lead">Une application MVC PHP moderne avec Bootstrap et Docker.</p>
    <hr class="my-4">
    <p>Cette application est conçue pour vous permettre de créer facilement des modules tels que des jeux, des compteurs, et des outils de gestion de tâches.</p>
    
    <?php if (!isset($_SESSION['user_id'])): ?>
    <div class="mt-4">
        <a href="<?= APP_URL ?>/auth/register" class="btn btn-primary me-2">S'inscrire</a>
        <a href="<?= APP_URL ?>/auth/login" class="btn btn-outline-secondary">Se connecter</a>
    </div>
    <?php else: ?>
    <div class="mt-4">
        <a href="<?= APP_URL ?>/home/dashboard" class="btn btn-primary">Accéder à mon tableau de bord</a>
    </div>
    <?php endif; ?>
</div>

<div class="row mt-5">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-gamepad me-2"></i>Jeux</h5>
                <p class="card-text">Profitez d'une variété de jeux divertissants pour passer le temps et vous amuser.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-stopwatch me-2"></i>Compteurs</h5>
                <p class="card-text">Utilisez nos compteurs pour suivre le temps, les scores ou toute autre valeur importante.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-tasks me-2"></i>Gestion de tâches</h5>
                <p class="card-text">Gardez une trace de vos tâches quotidiennes avec notre outil de gestion simple et efficace.</p>
            </div>
        </div>
    </div>
</div>