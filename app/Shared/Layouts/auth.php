<?php
/**
 * SALUVERA - Layout de Autenticacion
 *
 * @version 2.3.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$pageTitle = $pageTitle ?? 'SALUVERA';
$content = $content ?? '';
$flashMessages = Session::getFlashMessages();
$cssVersion = '2.3.0';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SALUVERA - Acceso seguro al sistema">
    <link rel="icon" type="image/svg+xml" href="<?= url('app-assets/img/favicon.svg') ?>">
    <title><?= htmlspecialchars($pageTitle) ?> | SALUVERA</title>
    <link rel="stylesheet" href="<?= url('app-assets/css/app.css') ?>?v=<?= $cssVersion ?>">
    <script>
        (function() {
            var savedTheme = localStorage.getItem('saluvera_theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
</head>
<body>
    <a href="#main-content" class="visually-hidden">Saltar al contenido principal</a>

    <div class="auth-wrapper">
        <div class="auth-topbar">
            <a href="<?= url('/') ?>" class="auth-topbar-logo">
                <div class="app-logo-icon"><img class="app-logo-mark" src="<?= url('app-assets/img/logo.svg') ?>" alt="SALUVERA" width="22" height="22"></div>
                <span class="app-logo-text">SALUVERA</span>
            </a>

            <button type="button"
                    class="theme-toggle"
                    id="themeToggle"
                    aria-label="Cambiar tema claro/oscuro"
                    role="switch"
                    aria-checked="false">
                <span class="theme-toggle-track">
                    <span class="theme-toggle-thumb">
                        <svg class="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/>
                            <line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg class="theme-icon-moon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </span>
                </span>
            </button>
        </div>

        <main class="auth-main" id="main-content">
            <div class="auth-content">
                <?php if (!empty($flashMessages)): ?>
                    <div class="flash-messages" role="alert" aria-live="polite">
                        <?php foreach ($flashMessages as $flash): ?>
                            <div class="flash-message flash-<?= htmlspecialchars($flash['type']) ?>">
                                <?= htmlspecialchars($flash['message']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </div>
        </main>

        <footer class="auth-footer">
            <p>SALUVERA &copy; <?= date('Y') ?> - Acceso seguro y cifrado</p>
        </footer>
    </div>

    <script>
        (function() {
            var toggle = document.getElementById('themeToggle');
            var html = document.documentElement;

            function updateAriaChecked() {
                var isDark = html.getAttribute('data-theme') === 'dark';
                toggle.setAttribute('aria-checked', isDark ? 'true' : 'false');
            }

            function toggleTheme() {
                var current = html.getAttribute('data-theme');
                var newTheme = current === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('saluvera_theme', newTheme);
                updateAriaChecked();
            }

            toggle.addEventListener('click', toggleTheme);

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem('saluvera_theme')) {
                    html.setAttribute('data-theme', e.matches ? 'dark' : 'light');
                    updateAriaChecked();
                }
            });

            updateAriaChecked();
        })();
    </script>
</body>
</html>
