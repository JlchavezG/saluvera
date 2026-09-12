<?php
/**
 * SALUVERA - Portal: mis citas
 *
 * @version 2.26.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$proximas = $proximas ?? [];
$historial = $historial ?? [];

$badgePorEstado = [
    'pendiente' => 'badge-pendiente',
    'confirmada' => 'badge-confirmada',
    'en_progreso' => 'badge-progreso',
    'completada' => 'badge-completada',
    'cancelada' => 'badge-cancelada',
    'no_asistio' => 'badge-cancelada',
    'reprogramada' => 'badge-pendiente',
];
?>
<div class="portal-welcome">
    <h2>Mis Citas</h2>
    <p>Consultas programadas e historial de atencion.</p>
</div>

<div class="dash-section-title">Proximas citas (<?= count($proximas) ?>)</div>
<?php if (empty($proximas)): ?>
    <div class="table-card empty-state">
        <h3>No tienes citas proximas</h3>
        <p>Contacta a tu clinica para agendar una consulta.</p>
    </div>
<?php else: ?>
    <div class="table-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Profesional</th>
                        <th>Consultorio</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proximas as $c): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($c['fecha_cita'])) ?></td>
                            <td><span class="agenda-hora"><?= substr($c['hora_inicio'], 0, 5) ?></span></td>
                            <td><?= htmlspecialchars(trim(($c['prof_nombre'] ?? '') . ' ' . ($c['prof_apellidos'] ?? '')) ?: 'Por asignar') ?></td>
                            <td><?= htmlspecialchars($c['consultorio_nombre'] ?? '—') ?></td>
                            <td><span class="badge-estado <?= $badgePorEstado[$c['estado']] ?? 'badge-pendiente' ?>"><?= htmlspecialchars(ucfirst($c['estado'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="dash-section-title">Historial (<?= count($historial) ?>)</div>
<?php if (empty($historial)): ?>
    <div class="table-card empty-state">
        <h3>Sin historial de citas</h3>
    </div>
<?php else: ?>
    <div class="table-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Profesional</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $c): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($c['fecha_cita'])) ?></td>
                            <td><?= htmlspecialchars(trim(($c['prof_nombre'] ?? '') . ' ' . ($c['prof_apellidos'] ?? '')) ?: '—') ?></td>
                            <td><?= htmlspecialchars($c['motivo'] ?? '—') ?></td>
                            <td><span class="badge-estado <?= $badgePorEstado[$c['estado']] ?? 'badge-pendiente' ?>"><?= htmlspecialchars(ucfirst($c['estado'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
