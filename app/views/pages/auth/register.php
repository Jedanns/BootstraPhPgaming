<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Inscription</h5>
            </div>
            <div class="card-body">
                <?php if (isset($errors['register'])): ?>
                <div class="alert alert-danger">
                    <?= $errors['register'] ?>
                </div>
                <?php endif; ?>
                
                <form action="<?= APP_URL ?>/auth/register" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" id="username" name="username" value="<?= htmlspecialchars($username ?? '') ?>">
                        <?php if (isset($errors['username'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['username'] ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
                        <?php if (isset($errors['email'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['email'] ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom complet (optionnel)</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>">
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
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" id="confirm_password" name="confirm_password">
                        <?php if (isset($errors['confirm_password'])): ?>
                        <div class="invalid-feedback">
                            <?= $errors['confirm_password'] ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">S'inscrire</button>
                    </div>
                </form>
                
                <div class="mt-3 text-center">
                    <p class="mb-0">Vous avez déjà un compte ? <a href="<?= APP_URL ?>/auth/login">Se connecter</a></p>
                </div>
            </div>
        </div>
    </div>
</div>