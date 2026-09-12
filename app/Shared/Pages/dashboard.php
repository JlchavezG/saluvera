<?php
/**
 * SALUVERA - Dashboard (con metricas globales para superadmin)
 *
 * @version 2.16.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$stats = $stats ?? [];
$scope = $scope ?? 'org';
$esSuperAdmin = $esSuperAdmin ?? false;
$user = $user ?? [];
$ultimasOrgs = $ultimasOrgs ?? [];

$nombre = $user['nombre'] ?? 'Usuario';
$apellidos = $user['apellidos'] ?? '';
$rolNombre = $user['rol_nombre'] ?? '';
$correo = $user['correo'] ?? '';
$orgNombre = $user['organizacion_nombre'] ?? '';

$hora = (int) date('G');
if ($hora < 12) {
    $saludo = 'Buenos dias';
} elseif ($hora < 19) {
    $saludo = 'Buenas tardes';
} else {
    $saludo = 'Buenas noches';
}

$planes = ['free' => 'Gratis', 'professional' => 'Profesional', 'clinic' => 'Clinica'];
$estados = ['trial' => 'Prueba', 'active' => 'Activa', 'suspended' => 'Suspendida', 'cancelled' => 'Cancelada'];
?>
<div class="dash-welcome">
    <div>
        <h2 class="dash-welcome-title"><?= htmlspecialchars($saludo) ?>, <?= htmlspecialchars($nombre . ' ' . $apellidos) ?></h2>
        <p class="dash-welcome-sub">
            <?= htmlspecialchars($rolNombre) ?>
            <?php if (!$esSuperAdmin && $orgNombre !== ''): ?>
                &middot; <?= htmlspecialchars($orgNombre) ?>
            <?php endif; ?>
        </p>
        <?php if ($esSuperAdmin): ?>
            <p class="dash-welcome-scope">Vista global de la plataforma SALUVERA</p>
        <?php endif; ?>
    </div>
    <span class="dash-date"><?= date('d/m/Y') ?></span>
</div>

<?php if ($esSuperAdmin): ?>
    <!-- =============================================================== -->
    <!-- DASHBOARD SUPERADMIN: metricas globales de toda la plataforma  -->
    <!-- =============================================================== -->
    <div class="dash-stats">
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-mint">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                </svg>
            </div>
            <div class="dash-stat-value"><?= $stats['organizaciones'] ?></div>
            <div class="dash-stat-label">Organizaciones activas</div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-teal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div class="dash-stat-value"><?= $stats['usuarios'] ?></div>
            <div class="dash-stat-label">Usuarios activos</div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-sand">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
            </div>
            <div class="dash-stat-value"><?= $stats['profesionales'] ?></div>
            <div class="dash-stat-label">Profesionales activos</div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="dash-stat-value"><?= $stats['pacientes'] ?></div>
            <div class="dash-stat-label">Pacientes activos</div>
        </div>
    </div>

    <div class="dash-stats">
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-teal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="dash-stat-value"><?= $stats['citas_hoy'] ?></div>
            <div class="dash-stat-label">Citas hoy (global)</div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-sand">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="dash-stat-value"><?= $stats['citas'] ?></div>
            <div class="dash-stat-label">Citas totales</div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-mint">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                </svg>
            </div>
            <div class="dash-stat-value"><?= $stats['consultorios'] ?></div>
            <div class="dash-stat-label">Consultorios activos</div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v5"/>
                </svg>
            </div>
            <div class="dash-stat-value"><?= $stats['organizaciones_total'] ?></div>
            <div class="dash-stat-label">Total organizaciones</div>
        </div>
    </div>

    <!-- Accesos rapidos del superadmin -->
    <div class="dash-section-title">Accesos rapidos</div>
    <div class="dash-modules">
        <a href="<?= url('/panel/organizaciones') ?>" class="module-card">
            <h3>Gestionar Organizaciones</h3>
            <p>Crear, editar y administrar los clientes de la plataforma</p>
        </a>
        <a href="<?= url('/panel/profesionales') ?>" class="module-card">
            <h3>Gestionar Profesionales</h3>
            <p>Administrar profesionales en cualquier organizacion</p>
        </a>
        <a href="<?= url('/panel/pacientes') ?>" class="module-card">
            <h3>Gestionar Pacientes</h3>
            <p>Acceso global a todos los pacientes del sistema</p>
        </a>
        <a href="<?= url('/panel/consultorios') ?>" class="module-card">
            <h3>Gestionar Consultorios</h3>
            <p>Espacios fisicos de todas las organizaciones</p>
        </a>
    </div>

    <!-- Ultimas organizaciones creadas -->
    <?php if (!empty($ultimasOrgs)): ?>
        <div class="dash-section-title">Organizaciones recientes</div>
        <div class="table-card">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Organizacion</th>
                            <th>Plan</th>
                            <th>Estado</th>
                            <th>Usuarios</th>
                            <th>Pacientes</th>
                            <th>Creada</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimasOrgs as $o): ?>
                            <tr>
                                <td>
                                    <div class="cell-name">
                                        <span class="avatar-mini"><?= strtoupper(substr($o['nombre'], 0, 1)) ?></span>
                                        <strong><?= htmlspecialchars($o['nombre']) ?></strong>
                                    </div>
                                </td>
                                <td><span class="badge-plan"><?= htmlspecialchars($planes[$o['plan_suscripcion']] ?? $o['plan_suscripcion']) ?></span></td>
                                <td><span class="badge-estado badge-<?= htmlspecialchars($o['estado_suscripcion']) ?>"><?= htmlspecialchars($estados[$o['estado_suscripcion']] ?? $o['estado_suscripcion']) ?></span></td>
                                <td><?= (int) $o['total_usuarios'] ?></td>
                                <td><?= (int) $o['total_pacientes'] ?></td>
                                <td><?= date('d/m/Y', strtotime($o['creado_en'])) ?></td>
                                <td>
                                    <a href="<?= url('/panel/organizaciones/' . (int) $o['id'] . '/editar') ?>" class="action-btn action-edit" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- =============================================================== -->
    <!-- DASHBOARD CLINIC_ADMIN: metricas de su organizacion             -->
    <!-- =============================================================== -->
    <div class="dash-stats">
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-mint">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="dash-stat-value"><?= $stats['pacientes'] ?></div>
            <div class="dash-stat-label">Pacientes activos</div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-teal">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="dash-stat-value"><?= $stats['citas_hoy'] ?></div>
            <div class="dash-stat-label">Citas hoy</div>
        </div>

        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-sand">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
            </div>
            <div class="dash-stat-value"><?= $stats['profesionales'] ?></div>
            <div class="dash-stat-label">Profesionales activos</div>
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
<?php endif; ?>
