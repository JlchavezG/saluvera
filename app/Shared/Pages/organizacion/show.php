<?php
/**
 * SALUVERA - Vista de datos de la organizacion
 *
 * @version 2.13.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$org = $org ?? [];
$deps = $deps ?? [];
$planes = $planes ?? [];
$estados = $estados ?? [];

$nombrePlan = $planes[$org['plan_suscripcion'] ?? ''] ?? ucfirst($org['plan_suscripcion'] ?? '');
$nombreEstado = $estados[$org['estado_suscripcion'] ?? ''] ?? ucfirst($org['estado_suscripcion'] ?? '');
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Mi Organizacion</h2>
        <p class="page-subtitle">Datos principales del negocio y plan contratado</p>
    </div>
    <a href="<?= url('/panel/organizacion/editar') ?>" class="btn btn-primary">Editar datos</a>
</div>

<div class="org-info-card">
    <div class="org-info-header">
        <div class="org-logo-box">
            <?php if (!empty($org['logo'])): ?>
                <img src="<?= htmlspecialchars($org['logo']) ?>" alt="<?= htmlspecialchars($org['nombre']) ?>">
            <?php else: ?>
                <span class="org-logo-fallback"><?= strtoupper(substr($org['nombre'] ?? 'O', 0, 2)) ?></span>
            <?php endif; ?>
        </div>
        <div class="org-info-title">
            <h3><?= htmlspecialchars($org['nombre']) ?></h3>
            <span class="org-slug"><?= htmlspecialchars($org['slug'] ?? '') ?></span>
        </div>
    </div>

    <div class="org-info-grid">
        <div class="org-info-item">
            <span class="org-info-label">Direccion</span>
            <span class="org-info-value"><?= htmlspecialchars($org['direccion'] ?? '—') ?></span>
        </div>

        <div class="org-info-item">
            <span class="org-info-label">Correo</span>
            <span class="org-info-value"><?= htmlspecialchars($org['correo'] ?? '—') ?></span>
        </div>

        <div class="org-info-item">
            <span class="org-info-label">Telefono</span>
            <span class="org-info-value"><?= htmlspecialchars($org['telefono'] ?? '—') ?></span>
        </div>

        <div class="org-info-item">
            <span class="org-info-label">Zona horaria</span>
            <span class="org-info-value"><?= htmlspecialchars($org['zona_horaria'] ?? '—') ?></span>
        </div>

        <div class="org-info-item">
            <span class="org-info-label">Plan</span>
            <span class="org-info-value"><span class="badge-plan"><?= htmlspecialchars($nombrePlan) ?></span></span>
        </div>

        <div class="org-info-item">
            <span class="org-info-label">Estado de suscripcion</span>
            <span class="org-info-value"><span class="badge-estado badge-<?= htmlspecialchars($org['estado_suscripcion'] ?? 'trial') ?>"><?= htmlspecialchars($nombreEstado) ?></span></span>
        </div>
    </div>
</div>

<div class="dash-section-title">Datos del sistema</div>

<div class="dash-stats">
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-mint">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $deps['pacientes'] ?></div>
        <div class="dash-stat-label">Pacientes activos</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-teal">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $deps['profesionales'] ?></div>
        <div class="dash-stat-label">Profesionales activos</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-sand">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $deps['consultorios'] ?></div>
        <div class="dash-stat-label">Consultorios activos</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $deps['citas'] ?></div>
        <div class="dash-stat-label">Citas totales</div>
    </div>
</div>
