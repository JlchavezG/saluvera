<?php
/**
 * SALUVERA - Dashboard con estadisticas reales
 *
 * @version 2.5.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$stats = $stats ?? ['pacientes' => 0, 'citas' => 0, 'profesionales' => 0, 'usuarios' => 0];
$user = $user ?? [];
$nombre = $user['nombre'] ?? 'Usuario';

$hora = (int) date('G');
if ($hora < 12) {
    $saludo = 'Buenos dias';
} elseif ($hora < 19) {
    $saludo = 'Buenas tardes';
} else {
    $saludo = 'Buenas noches';
}
?>
<div class="dash-welcome">
    <div>
        <h2 class="dash-welcome-title"><?= htmlspecialchars($saludo) ?>, <?= htmlspecialchars($nombre) ?></h2>
        <p class="dash-welcome-sub">Este es el resumen de la actividad en SALUVERA.</p>
    </div>
    <span class="dash-date"><?= date('d/m/Y') ?></span>
</div>

<div class="dash-stats">
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-mint">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $stats['pacientes'] ?></div>
        <div class="dash-stat-label">Pacientes</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-teal">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $stats['citas'] ?></div>
        <div class="dash-stat-label">Citas</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-sand">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $stats['profesionales'] ?></div>
        <div class="dash-stat-label">Profesionales</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $stats['usuarios'] ?></div>
        <div class="dash-stat-label">Usuarios activos</div>
    </div>
</div>

<div class="dash-section-title">Accesos rapidos</div>

<div class="dash-modules">
    <a href="<?= url('/panel/pacientes') ?>" class="module-card">
        <h3>Pacientes</h3>
        <p>Gestiona expedientes y datos de pacientes</p>
    </a>
    <a href="<?= url('/panel/agenda') ?>" class="module-card">
        <h3>Agenda</h3>
        <p>Administra citas y horarios de consulta</p>
    </a>
    <a href="<?= url('/panel/profesionales') ?>" class="module-card">
        <h3>Profesionales</h3>
        <p>Equipo medico y especialidades</p>
    </a>
</div>
