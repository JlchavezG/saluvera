<?php
/**
 * SALUVERA - Mis Reportes (profesional) con metricas clinicas
 *
 * @version 2.36.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$periodo = $periodo ?? 'mes';
$desde = $desde ?? date('Y-m-01');
$hasta = $hasta ?? date('Y-m-t');
$nombrePeriodo = $nombrePeriodo ?? 'Mes actual';
$consultas = $consultas ?? 0;
$ingresos = $ingresos ?? 0;
$tasaNoShow = $tasaNoShow ?? 0;
$pacientes = $pacientes ?? 0;
$canceladas = $canceladas ?? 0;
$minutosTrabajados = $minutosTrabajados ?? 0;
$diagnosticos = $diagnosticos ?? [];
$porMes = $porMes ?? [];

$fmt = function ($m) { return '$' . number_format((float) $m, 2); };
$horas = intdiv($minutosTrabajados, 60);
$mins = $minutosTrabajados % 60;
$horasTxt = $horas . 'h ' . $mins . 'm';

$mesesLabels = [];
$mesesData = [];
foreach ($porMes as $m) {
    $mesesLabels[] = date('M Y', strtotime($m['mes'] . '-01'));
    $mesesData[] = (float) $m['total'];
}
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Mis Reportes</h2>
        <p class="page-subtitle">Tus metricas de <?= htmlspecialchars($nombrePeriodo) ?></p>
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

<div class="dash-section-title">Resumen economico</div>
<div class="dash-stats">
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-sand"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="dash-stat-value"><?= $fmt($ingresos) ?></div>
        <div class="dash-stat-label">Ingresos</div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-teal"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
        <div class="dash-stat-value"><?= (int) $consultas ?></div>
        <div class="dash-stat-label">Consultas completadas</div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-info"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="dash-stat-value"><?= $horasTxt ?></div>
        <div class="dash-stat-label">Horas trabajadas</div>
    </div>
</div>

<div class="dash-section-title">Actividad clinica</div>
<div class="dash-stats">
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-info"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
        <div class="dash-stat-value"><?= (int) $pacientes ?></div>
        <div class="dash-stat-label">Pacientes atendidos</div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-mint"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div class="dash-stat-value"><?= (int) $consultas ?></div>
        <div class="dash-stat-label">Terminadas</div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-warning"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
        <div class="dash-stat-value"><?= (int) $canceladas ?></div>
        <div class="dash-stat-label">Canceladas</div>
    </div>
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-error"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
        <div class="dash-stat-value"><?= (int) $tasaNoShow ?>%</div>
        <div class="dash-stat-label">No asistieron</div>
    </div>
</div>

<div class="table-card reportes-chart-card">
    <div class="dash-section-title">Ingresos por mes</div>
    <?php if (empty($porMes)): ?>
        <div class="empty-state"><h3>Sin datos en el rango</h3></div>
    <?php else: ?>
        <canvas id="chartMes" height="100"></canvas>
    <?php endif; ?>
</div>

<div class="table-card">
    <div class="dash-section-title">Diagnosticos mas frecuentes</div>
    <?php if (empty($diagnosticos)): ?>
        <div class="empty-state"><h3>Sin diagnosticos registrados</h3></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Diagnostico</th><th style="text-align:right">Veces</th></tr></thead>
                <tbody>
                    <?php foreach ($diagnosticos as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['nombre_diagnostico']) ?></td>
                            <td style="text-align:right"><strong><?= (int) $d['veces'] ?></strong></td>
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
<?php if (!empty($porMes)): ?>
new Chart(document.getElementById('chartMes'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($mesesLabels) ?>,
        datasets: [{
            label: 'Ingresos',
            data: <?= json_encode($mesesData) ?>,
            backgroundColor: '#4FD1B5',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
<?php endif; ?>
</script>
