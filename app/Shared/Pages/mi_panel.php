<?php
/**
 * SALUVERA - Mi Panel (panel personal del profesional)
 *
 * @version 2.9.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$citasHoy = $citasHoy ?? [];
$proximaCita = $proximaCita ?? null;
$citasHoyCount = $citasHoyCount ?? 0;
$misPacientes = $misPacientes ?? 0;
$user = $user ?? [];

$nombre = $user['nombre'] ?? 'Profesional';

$hora = (int) date('G');
if ($hora < 12) {
    $saludo = 'Buenos dias';
} elseif ($hora < 19) {
    $saludo = 'Buenas tardes';
} else {
    $saludo = 'Buenas noches';
}

$estadosBadge = [
    'pendiente' => 'badge-pendiente',
    'confirmada' => 'badge-confirmada',
    'en_progreso' => 'badge-progreso',
    'completada' => 'badge-completada',
    'cancelada' => 'badge-cancelada',
    'no_asistio' => 'badge-cancelada',
];
?>
<div class="dash-welcome">
    <div>
        <h2 class="dash-welcome-title"><?= htmlspecialchars($saludo) ?>, <?= htmlspecialchars($nombre) ?></h2>
        <p class="dash-welcome-sub">Este es tu resumen personal de hoy.</p>
    </div>
    <span class="dash-date"><?= date('d/m/Y') ?></span>
</div>

<div class="dash-stats">
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-teal">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $citasHoyCount ?></div>
        <div class="dash-stat-label">Citas hoy</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-mint">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $misPacientes ?></div>
        <div class="dash-stat-label">Mis pacientes</div>
    </div>
</div>

<div class="dash-section-title">Mi proxima cita</div>

<?php if ($proximaCita === null): ?>
    <div class="table-card empty-state">
        <h3>Sin citas proximas</h3>
        <p>Cuando tengas citas pendientes o confirmadas apareceran aqui.</p>
    </div>
<?php else: ?>
    <div class="table-card proxima-cita">
        <div class="proxima-cita-fecha">
            <?= date('d/m/Y', strtotime($proximaCita['fecha_cita'])) ?>
            <strong><?= substr($proximaCita['hora_inicio'], 0, 5) ?></strong>
        </div>
        <div class="proxima-cita-datos">
            <strong><?= htmlspecialchars($proximaCita['paciente_nombre'] . ' ' . $proximaCita['paciente_apellidos']) ?></strong>
            <span><?= htmlspecialchars($proximaCita['motivo'] ?? 'Sin motivo') ?></span>
        </div>
        <span class="badge-estado <?= $estadosBadge[$proximaCita['estado']] ?? 'badge-pendiente' ?>">
            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $proximaCita['estado']))) ?>
        </span>
    </div>
<?php endif; ?>

<div class="dash-section-title">Mi agenda de hoy</div>

<div class="table-card">
    <?php if (empty($citasHoy)): ?>
        <div class="empty-state">
            <h3>Sin citas para hoy</h3>
            <p>Tu agenda de hoy esta libre.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <th>Motivo</th>
                        <th>Consultorio</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citasHoy as $cita): ?>
                        <tr>
                            <td><strong><?= substr($cita['hora_inicio'], 0, 5) ?></strong></td>
                            <td><?= htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellidos']) ?></td>
                            <td><?= htmlspecialchars($cita['motivo'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($cita['consultorio_nombre'] ?? '—') ?></td>
                            <td>
                                <span class="badge-estado <?= $estadosBadge[$cita['estado']] ?? 'badge-pendiente' ?>">
                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $cita['estado']))) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
