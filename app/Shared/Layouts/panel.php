<?php
/**
 * SALUVERA - Layout del Panel
 * Sidebar plegable + menu por rol + dark mode
 *
 * @version 2.10.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$pageTitle = $pageTitle ?? 'Panel';
$content = $content ?? '';
$flashMessages = Session::getFlashMessages();
$user = Session::user() ?? [];
$cssVersion = '2.10.0';

$nombre = $user['nombre'] ?? 'Usuario';
$apellidos = $user['apellidos'] ?? '';
$rolNombre = $user['rol_nombre'] ?? '';
$rolSlug = $user['rol_slug'] ?? '';
$iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellidos, 0, 1));

$esAdmin = in_array($rolSlug, ['superadmin', 'clinic_admin'], true);
$esProfesional = $rolSlug === 'professional';

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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>
<body>
    <script>
        if (localStorage.getItem('saluvera_sidebar') === 'mini') {
            document.body.classList.add('sidebar-mini');
        }
    </script>

    <div class="panel-wrapper">
        <aside class="panel-sidebar" id="panelSidebar">
            <div class="sidebar-logo">
                <img class="app-logo-img" src="<?= url('app-assets/img/logo.svg') ?>" alt="SALUVERA" width="38" height="38">
                <span class="sidebar-logo-text">SALUVERA</span>
            </div>

            <nav class="sidebar-nav" aria-label="Navegacion principal">
                <div class="nav-section-title">Principal</div>

                <?php if ($esAdmin): ?>
                    <a href="<?= url('/panel') ?>" class="nav-item<?= navActive($currentPath, '/panel') ?>" title="Dashboard">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                        </svg>
                        <span class="nav-label">Dashboard</span>
                    </a>
                <?php endif; ?>

                <?php if ($esProfesional): ?>
                    <a href="<?= url('/panel/mi-panel') ?>" class="nav-item<?= navActive($currentPath, '/panel/mi-panel') ?>" title="Mi Panel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span class="nav-label">Mi Panel</span>
                    </a>
                <?php endif; ?>

                <a href="<?= url('/panel/pacientes') ?>" class="nav-item<?= navActive($currentPath, '/panel/pacientes') ?>" title="<?= $esProfesional ? 'Mis Pacientes' : 'Pacientes' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span class="nav-label"><?= $esProfesional ? 'Mis Pacientes' : 'Pacientes' ?></span>
                </a>

                <a href="<?= url('/panel/agenda') ?>" class="nav-item<?= navActive($currentPath, '/panel/agenda') ?>" title="Agenda">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span class="nav-label">Agenda</span>
                </a>

                <?php if ($rolSlug !== 'receptionist'): ?>
                <a href="<?= url('/panel/bloqueos') ?>" class="nav-item<?= navActive($currentPath, '/panel/bloqueos') ?>" title="Bloqueos de agenda">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                    </svg>
                    <span class="nav-label">Bloqueos</span>
                </a>
                <?php endif; ?>

                <?php if ($rolSlug !== 'receptionist'): ?>
                <a href="<?= url('/panel/mis-horarios') ?>" class="nav-item<?= navActive($currentPath, '/panel/mis-horarios') ?>" title="Horarios de atencion">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span class="nav-label">Horarios</span>
                </a>
                <?php endif; ?>

                <?php if ($rolSlug === 'professional'): ?>
                <a href="<?= url('/panel/mis-ganancias') ?>" class="nav-item<?= navActive($currentPath, '/panel/mis-ganancias') ?>" title="Mis Ganancias">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    <span class="nav-label">Mis Ganancias</span>
                </a>
                <?php endif; ?>

                <a href="<?= url('/panel/mi-perfil') ?>" class="nav-item<?= navActive($currentPath, '/panel/mi-perfil') ?>" title="Mi Perfil">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span class="nav-label">Mi Perfil</span>
                </a>

                <a href="<?= url('/panel/notificaciones') ?>" class="nav-item<?= navActive($currentPath, '/panel/notificaciones') ?>" title="Notificaciones">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="nav-label">Notificaciones</span>
                </a>

                <?php if ($esAdmin): ?>
                    <div class="nav-section-title">Administracion</div>

                    <?php if ($rolSlug === 'superadmin'): ?>
                    <a href="<?= url('/panel/organizaciones') ?>" class="nav-item<?= navActive($currentPath, '/panel/organizaciones') ?>" title="Organizaciones">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span class="nav-label">Organizaciones</span>
                    </a>
                    <?php endif; ?>

                    <a href="<?= url('/panel/consultorios') ?>" class="nav-item<?= navActive($currentPath, '/panel/consultorios') ?>" title="Consultorios">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span class="nav-label">Consultorios</span>
                    </a>

                    <a href="<?= url('/panel/profesionales') ?>" class="nav-item<?= navActive($currentPath, '/panel/profesionales') ?>" title="Profesionales">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                        </svg>
                        <span class="nav-label">Profesionales</span>
                    </a>

                    <a href="<?= url('/panel/configuracion') ?>" class="nav-item<?= navActive($currentPath, '/panel/configuracion') ?>" title="Configuracion">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                                                <span class="nav-label">Configuracion</span>
                    </a>

                    <a href="<?= url('/panel/organizacion') ?>" class="nav-item<?= navActive($currentPath, '/panel/organizacion') ?>" title="Mi Organizacion">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5"/>
                        </svg>
                        <span class="nav-label">Mi Organizacion</span>
                    </a>
                    </a>
                <?php endif; ?>
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
                    <button type="button" class="sidebar-collapse" id="sidebarCollapse" aria-label="Colapsar o expandir menu" title="Colapsar / Expandir">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/>
                        </svg>
                    </button>

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

            var collapseBtn = document.getElementById('sidebarCollapse');

            collapseBtn.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-mini');
                var isMini = document.body.classList.contains('sidebar-mini');
                localStorage.setItem('saluvera_sidebar', isMini ? 'mini' : 'full');
            });
        })();
    </script>

    <script src="<?= url('app-assets/js/form-ux.js') ?>?v=1"></script>
</body>
</html>
