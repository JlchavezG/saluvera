<?php
/**
 * SALUVERA - Layout del Portal del Paciente
 *
 * @version 2.26.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$pageTitle = $pageTitle ?? 'Portal';
$content = $content ?? '';
$pacientePortal = $pacientePortal ?? null;

$nombre = $pacientePortal !== null
    ? trim($pacientePortal['nombre'] . ' ' . $pacientePortal['apellidos'])
    : 'Paciente';

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

function portalActive(string $currentPath, string $route): string {
    $suffix = '/saluvera/public' . $route;
    return substr($currentPath, -strlen($suffix)) === $suffix ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SALUVERA - Portal del paciente">
    <link rel="icon" type="image/svg+xml" href="<?= url('app-assets/img/favicon.svg') ?>">
    <title><?= htmlspecialchars($pageTitle) ?> | Portal SALUVERA</title>
    <link rel="stylesheet" href="<?= url('app-assets/css/app.css') ?>?v=2.26.0">
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
<body class="portal-body">
    <header class="portal-topbar">
        <div class="portal-topbar-inner">
            <div class="portal-brand">
                <img src="<?= url('app-assets/img/logo.svg') ?>" alt="SALUVERA" width="32" height="32">
                <span class="portal-brand-name">SALUVERA</span>
                <span class="portal-brand-tag">Portal del Paciente</span>
            </div>
            <div class="portal-topbar-right">
                <span class="portal-user"><?= htmlspecialchars($nombre) ?></span>
                <a href="<?= url('/portal/salir') ?>" class="portal-logout" title="Cerrar sesion">Salir</a>
            </div>
        </div>
    </header>

    <nav class="portal-nav" aria-label="Navegacion del portal">
        <div class="portal-nav-inner">
            <a href="<?= url('/portal/inicio') ?>" class="portal-nav-link<?= portalActive($currentPath, '/portal/inicio') ?>">Inicio</a>
            <a href="<?= url('/portal/citas') ?>" class="portal-nav-link<?= portalActive($currentPath, '/portal/citas') ?>">Mis Citas</a>
            <a href="<?= url('/portal/expediente') ?>" class="portal-nav-link<?= portalActive($currentPath, '/portal/expediente') ?>">Mi Expediente</a>
            <a href="<?= url('/portal/documentos') ?>" class="portal-nav-link<?= portalActive($currentPath, '/portal/documentos') ?>">Mis Documentos</a>
            <a href="<?= url('/portal/reportes') ?>" class="portal-nav-link<?= portalActive($currentPath, '/portal/reportes') ?>">Mis Reportes</a>
        </div>
    </nav>

    <main class="portal-main">
        <?= $content ?>
    </main>

    <footer class="portal-footer">
        Informacion medica confidencial &middot; Protegida por secreto medico &middot; SALUVERA
    </footer>
</body>
</html>
