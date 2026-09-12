<?php
/**
 * SALUVERA - Reportes de Plataforma (superadmin)
 * Solo metricas operativas, SIN datos financieros de los clientes.
 *
 * @version 2.37.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$periodo = $periodo ?? 'mes';
$desde = $desde ?? date('Y-m-01');
$hasta = $hasta ?? date('Y-m-t');
$nombrePeriodo = $nombrePeriodo ?? 'Mes actual';
$totalUsuarios = $totalUsuarios ?? 0;
$usuariosActivos = $usuariosActivos ?? 0;
$totalProfesionales = $totalProfesionales ?? 0;
$totalOrganizaciones = $totalOrganizaciones ?? 0;
$organizacionesActivas = $organizacionesActivas ?? 0;
$totalPacientes = $totalPacientes ?? 0;
$totalCitas = $totalCitas ?? 0;
$citasPorEstado = $citasPorEstado ?? [];
$totalConsultas = $totalConsultas ?? 0;
$pacientesPorOrganizacion = $pacientesPorOrganizacion ?? [];
$actividadReciente = $actividadReciente ?? [];

$badgeEstado = [
    'pendiente' => 'badge-pendiente',
    'confirmada' => 'badge-confirmada',
    'en_progreso' => 'badge-pendiente',
    'completada' => 'badge-completada',
    'cancelada' => 'badge-cancelada',
    'no_asistio' => 'badge-cancelada',
    'reprogramada' => 'badge-pendiente',
];

$estadoLabels = [];
$estadoData = [];
foreach ($citasPorEstado as $e) {
    $estadoLabels[] = ucwords(str_replace('_', ' ', $e['estado']));
    $estadoData[] = (int) $e['total'];
}
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Reportes de Plataforma</h2>
        <p class="page-subtitle">Metricas operativas globales &middot; <?= htmlspecialchars($nombrePeriodo) ?></p>
    </div>
    <a href="<?= url('/panel/reportes/exportar?periodo=' . urlencode($periodo) . '&desde=' . urlencode($desde) . '&hasta=' . urlencode($hasta)) ?>" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Exportar a Excel
    </a>
</div>

<div class="table-card">
    <form method="GET" action="<?= url('/panel/reportes') ?>" class="search-form">
        <select name="periodo" class="search-select" onchange="toggleFechas(this.value); this.form.submit()">
            <option value="hoy" <?= $periodo === 'hoy' ? 'selected' : '' ?>>Hoy</option>
            <option value="semana" <?= $periodo === 'semana' ? 'selected' : '' ?>>Ultimos 7 dias</option>
            <option value="mes" <?= $periodo === 'mes' ? 'selected' : '' ?>>Mes actual</option>
            <option value="anio" <?= $periodo === 'anio' ? 'selected' : '' ?>>Año actual</option>
            <option value="personalizado" <?= $periodo === 'personalizado' ? 'selected' : '' ?>>Personalizado</option>
        </select>
        <span id="fechasCustom" style="display:<?= $periodo === 'personalizado' ? 'inline-flex' : 'none' ?>; gap:8px">
            <input type="date" name="desde" value="<?= htmlspecialchars($desde) ?>" class="search-input search-input-sm">
            <input type="date" name="hasta" value="<?= htmlspecialchars($hasta) ?>" class="search-input search-input-sm">
        </span>
        <button type="submit" class="btn btn-outline">Aplicar</button>
    </form>
</div>

<div class="dash-section-title">Plataforma</div>
<div class="dash-stats">
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-teal"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg></div>
        <div class="dash-stat-value"><?= (int) $totalOrganizaciones ?></div>
        <div class="dash-stat-label">Organizaciones (<?= (int) $organizacionesActivas ?> activas)</div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-info"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <div class="dash-stat-value"><?= (int) $totalUsuarios ?></div>
        <div class="dash-stat-label">Usuarios (<?= (int) $usuariosActivos ?> activos)</div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-mint"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
        <div class="dash-stat-value"><?= (int) $totalProfesionales ?></div>
        <div class="dash-stat-label">Profesionales</div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-sand"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/></svg></div>
        <div class="dash-stat-value"><?= (int) $totalPacientes ?></div>
        <div class="dash-stat-label">Pacientes</div>
    </div>
</div>

<div class="dash-section-title">Actividad del periodo</div>
<div class="dash-stats">
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-teal"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
        <div class="dash-stat-value"><?= (int) $totalCitas ?></div>
        <div class="dash-stat-label">Citas totales</div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-mint"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
        <div class="dash-stat-value"><?= (int) $totalConsultas ?></div>
        <div class="dash-stat-label">Consultas realizadas</div>
    </div>
</div>

<div class="table-card reportes-chart-card">
    <div class="dash-section-title">Citas por estado</div>
    <?php if (empty($citasPorEstado)): ?>
        <div class="empty-state"><h3>Sin citas en el rango</h3></div>
    <?php else: ?>
        <canvas id="chartEstados" height="100"></canvas>
    <?php endif; ?>
</div>

<div class="table-card">
    <div class="dash-section-title">Pacientes por organizacion</div>
    <?php if (empty($pacientesPorOrganizacion)): ?>
        <div class="empty-state"><h3>Sin organizaciones</h3></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Organizacion</th><th>Estado</th><th style="text-align:right">Pacientes</th></tr></thead>
                <tbody>
                    <?php foreach ($pacientesPorOrganizacion as $o): ?>
                        <tr>
                            <td><?= htmlspecialchars($o['nombre']) ?></td>
                            <td><span class="badge-estado <?= (int) $o['activo'] === 1 ? 'badge-completada' : 'badge-cancelada' ?>"><?= (int) $o['activo'] === 1 ? 'Activa' : 'Inactiva' ?></span></td>
                            <td style="text-align:right"><strong><?= (int) $o['pacientes'] ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="table-card">
    <div class="dash-section-title">Actividad reciente</div>
    <?php if (empty($actividadReciente)): ?>
        <div class="empty-state"><h3>Sin actividad reciente</h3></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Fecha</th><th>Organizacion</th><th>Paciente</th><th>Estado</th></tr></thead>
                <tbody>
                    <?php foreach ($actividadReciente as $a): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($a['fecha_cita'])) ?></td>
                            <td><?= htmlspecialchars($a['organizacion']) ?></td>
                            <td><?= htmlspecialchars($a['paciente'] ?? 'Sin nombre') ?></td>
                            <td><span class="badge-estado <?= $badgeEstado[$a['estado']] ?? 'badge-pendiente' ?>"><?= ucwords(str_replace('_', ' ', $a['estado'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleFechas(v) {
    document.getElementById('fechasCustom').style.display = (v === 'personalizado') ? 'inline-flex' : 'none';
}
<?php if (!empty($citasPorEstado)): ?>
new Chart(document.getElementById('chartEstados'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($estadoLabels) ?>,
        datasets: [{
            label: 'Citas',
            data: <?= json_encode($estadoData) ?>,
            backgroundColor: ['#4FD1B5','#1C5345','#EED9A4','#E53E3E','#A8DDD0','#6B857F','#F6AD55'],
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
<?php endif; ?>
</script>
