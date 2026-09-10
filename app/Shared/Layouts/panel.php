<?php
/**
 * SALUVERA - Layout del Panel (Sidebar + Topbar)
 *
 * @version 2.4.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$pageTitle = $pageTitle ?? 'Panel';
$content = $content ?? '';
$flashMessages = Session::getFlashMessages();
$user = Session::user() ?? [];
$cssVersion = '2.4.0';

$nombre = $user['nombre'] ?? 'Usuario';
$apellidos = $user['apellidos'] ?? '';
$rolNombre = $user['rol_nombre'] ?? '';
$iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellidos, 0, 1));

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

function navActive(string $currentPath, string $route): string {
    $suffix = '/saluvera/public' . $route;
    if ($route === '/panel') {
        return (substr($currentPath, -6) === '/panel' || substr($currentPath, -7) === '/panel/') ? ' active' : '';
    }
    return substr($currentPath, -strlen($suffix)) === $suffix ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SALUVERA - Panel de gestion">
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
    <div class="panel-wrapper">
        <aside class="panel-sidebar" id="panelSidebar">
            <div class="sidebar-logo">
                <img class="app-logo-img" src="<?= url('app-assets/img/logo.svg') ?>" alt="SALUVERA" width="38" height="38">
                <span class="sidebar-logo-text">SALUVERA</span>
            </div>

            <nav class="sidebar-nav" aria-label="Navegacion principal">
                <div class="nav-section-title">Principal</div>

                <a href="<?= url('/panel') ?>" class="nav-item<?= navActive($currentPath, '/panel') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    Dashboard
                </a>

                <a href="<?= url('/panel/pacientes') ?>" class="nav-item<?= navActive($currentPath, '/panel/pacientes') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Pacientes
                </a>

                <a href="<?= url('/panel/agenda') ?>" class="nav-item<?= navActive($currentPath, '/panel/agenda') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Agenda
                </a>

                <div class="nav-section-title">Administracion</div>

                <a href="<?= url('/panel/profesionales') ?>" class="nav-item<?= navActive($currentPath, '/panel/profesionales') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                    </svg>
                    Profesionales
                </a>

                <a href="<?= url('/panel/configuracion') ?>" class="nav-item<?= navActive($currentPath, '/panel/configuracion') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                    Configuracion
                </a>
            </nav>

            <div class="sidebar-user">
                <div class="sidebar-user-avatar"><?= htmlspecialchars($iniciales) ?></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?= htmlspecialchars($nombre . ' ' . $apellidos) ?></div>
                    <div class="sidebar-user-role"><?= htmlspecialchars($rolNombre) ?></div>
                </div>
                <a href="<?= url('/logout') ?>" class="sidebar-logout" aria-label="Cerrar sesion" title="Cerrar sesion">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </a>
            </div>
        </aside>

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <div class="panel-main">
            <header class="panel-topbar">
                <div class="topbar-left">
                    <button type="button" class="hamburger" id="hamburgerBtn" aria-label="Abrir menu">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <h1 class="topbar-title"><?= htmlspecialchars($pageTitle) ?></h1>
                </div>

                <div class="topbar-actions">
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
            </header>

            <main class="panel-content" id="main-content">
                <?php if (!empty($flashMessages)): ?>
                    <div class="flash-messages" role="alert" aria-live="polite" style="max-width:none;padding:0;margin-bottom:20px">
                        <?php foreach ($flashMessages as $flash): ?>
                            <div class="flash-message flash-<?= htmlspecialchars($flash['type']) ?>">
                                <?= htmlspecialchars($flash['message']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </main>
        </div>
    </div>

    <script>
        (function() {
            var toggle = document.getElementById('themeToggle');
            var html = document.documentElement;

            function updateAriaChecked() {
                var isDark = html.getAttribute('data-theme') === 'dark';
                toggle.setAttribute('aria-checked', isDark ? 'true' : 'false');
            }

            toggle.addEventListener('click', function() {
                var current = html.getAttribute('data-theme');
                var newTheme = current === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('saluvera_theme', newTheme);
                updateAriaChecked();
            });

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem('saluvera_theme')) {
                    html.setAttribute('data-theme', e.matches ? 'dark' : 'light');
                    updateAriaChecked();
                }
            });

            updateAriaChecked();

            var hamburger = document.getElementById('hamburgerBtn');
            var overlay = document.getElementById('sidebarOverlay');

            hamburger.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-open');
            });

            overlay.addEventListener('click', function() {
                document.body.classList.remove('sidebar-open');
            });
        })();
    </script>
</body>
</html>
