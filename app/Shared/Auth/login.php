<?php
/**
 * SALUVERA - Vista de Login
 *
 * @version 2.2.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$error = $error ?? null;
$oldEmail = $oldEmail ?? '';
?>
<div class="auth-card">
    <div class="auth-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
    </div>

    <h1 class="auth-title">Bienvenido de nuevo</h1>
    <p class="auth-subtitle">Ingresa tus credenciales para acceder a tu cuenta</p>

    <?php if (!empty($error)): ?>
        <div class="auth-error" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('/login') ?>" novalidate>
        <?= Security::csrfField() ?>

        <div class="form-group">
            <label class="form-label" for="email">Correo electronico</label>
            <input
                class="form-input"
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($oldEmail) ?>"
                placeholder="tu@correo.com"
                required
                autocomplete="email"
                autofocus>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Contrasena</label>
            <div class="password-wrapper">
                <input
                    class="form-input"
                    type="password"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                    minlength="6">
                <button type="button" class="password-toggle" id="passwordToggle" aria-label="Mostrar contrasena">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="form-row">
            <label class="form-checkbox">
                <input type="checkbox" name="remember" value="1">
                Recordarme
            </label>
            <a href="#" class="form-link">Olvide mi contrasena</a>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Iniciar Sesion
        </button>
    </form>

    <p class="auth-alt">
        <a href="<?= url('/') ?>" class="form-link">Volver al inicio</a>
    </p>
</div>

<script>
    (function() {
        var toggle = document.getElementById('passwordToggle');
        var input = document.getElementById('password');

        toggle.addEventListener('click', function() {
            var isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            toggle.setAttribute('aria-label', isPassword ? 'Ocultar contrasena' : 'Mostrar contrasena');
        });
    })();
</script>