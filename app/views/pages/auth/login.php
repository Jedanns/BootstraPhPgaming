<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Connexion</h5>
            </div>
            <div class="card-body">
                <?php if (isset($errors['login'])): ?>
                <div class="alert alert-danger">
                    <?= $errors['login'] ?>
                </div>
                <?php endif; ?>
                
                <form action="<?= APP_URL ?>/auth/login" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email ou nom d'utilisateur</label>
                        <input type="text" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
                        <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['email'] ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password">
                        <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['password'] ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Se connecter</button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <p class="mb-0">Vous n'avez pas de compte ? <a href="<?= APP_URL ?>/auth/register">S'inscrire</a></p>
                </div>
            </div>
        </div>
    </div>
</div>