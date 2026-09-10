<?php
/**
 * SALUVERA - Pagina de Bienvenida (Dashboard)
 *
 * @version 2.0.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

// Obtener estadisticas reales de la base de datos
$stats = [
    'roles' => 0,
    'especialidades' => 0,
    'permisos' => 0,
];

try {
    $db = Database::getInstance();
    $stats['roles'] = $db->count('roles');
    $stats['especialidades'] = $db->count('especialidades');
    $stats['permisos'] = $db->count('permisos');
} catch (Exception $e) {
    // Si hay error, mostrar ceros
}
?>
<div class="app-container">
    <div class="welcome-card">
        <div class="welcome-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>

        <h1 class="welcome-title">Sistema Operativo</h1>
        <p class="welcome-subtitle">
            SALUVERA esta funcionando correctamente. La conexion a la base de datos
            esta activa y todos los componentes del Core estan listos.
        </p>

        <div class="welcome-stats">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['roles'] ?></div>
                <div class="stat-label">Roles</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['especialidades'] ?></div>
                <div class="stat-label">Especialidades</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['permisos'] ?></div>
                <div class="stat-label">Permisos</div>
            </div>
        </div>

        <div class="welcome-actions">
            <a href="<?= url('/api/db-test') ?>" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <ellipse cx="12" cy="5" rx="9" ry="3"/>
                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                </svg>
                Probar Base de Datos
            </a>
            <a href="<?= url('/api/session-test') ?>" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Probar Sesion
            </a>
        </div>
    </div>
</div>