<?php
/**
 * SALUVERA - Reportes de Plataforma (Superadmin)
 * 4 pestañas: Resumen, Usuarios, Organizaciones, Operativa
 *
 * @version 3.0.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$kpis = $kpis ?? [];
$crecimiento = $crecimiento ?? [];
$citasPorMes = $citasPorMes ?? [];
$usuariosPorRol = $usuariosPorRol ?? [];
$profesionalesPorEspecialidad = $profesionalesPorEspecialidad ?? [];
$pacientesPorGenero = $pacientesPorGenero ?? [];
$pacientesPorEdad = $pacientesPorEdad ?? [];
$pacientesPorCiudad = $pacientesPorCiudad ?? [];
$usuariosActivos = $usuariosActivos ?? ['activos_7d' => 0, 'activos_30d' => 0, 'activos_90d' => 0, 'total' => 0];
$ultimoAcceso = $ultimoAcceso ?? [];
$benchmark = $benchmark ?? [];
$ratios = $ratios ?? [];
$topProcesos = $topProcesos ?? [];
$noShowPorEspecialidad = $noShowPorEspecialidad ?? [];
$consultasPorEstado = $consultasPorEstado ?? [];
$documentosPorTipo = $documentosPorTipo ?? [];

// Preparar datos para gráficos
$crecLabels = array_map(fn($c) => date('M Y', strtotime($c['mes'] . '-01')), $crecimiento);
$crecData = array_column($crecimiento, 'total');

$citasLabels = array_map(fn($c) => date('M Y', strtotime($c['mes'] . '-01')), $citasPorMes);
$citasData = array_column($citasPorMes, 'total');

$rolLabels = array_column($usuariosPorRol, 'rol');
$rolData = array_column($usuariosPorRol, 'total');

$espLabels = array_column($profesionalesPorEspecialidad, 'especialidad');
$espData = array_column($profesionalesPorEspecialidad, 'total');

$genLabels = array_map(fn($g) => ucfirst(str_replace('prefiero_no_decir', 'Prefiero no decir', $g['genero'])), $pacientesPorGenero);
$genData = array_column($pacientesPorGenero, 'total');

$edadLabels = array_column($pacientesPorEdad, 'rango');
$edadData = array_column($pacientesPorEdad, 'total');

$procLabels = array_column($topProcesos, 'proceso');
$procData = array_column($topProcesos, 'total');
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Reportes de Plataforma</h2>
        <p class="page-subtitle">Analytics ejecutivo &middot; Sin datos financieros de clientes</p>
    </div>
    <a href="<?= url('/panel/reportes/exportar?periodo=mes') ?>" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Exportar
    </a>
</div>

<!-- TABS -->
<div class="platform-tabs" role="tablist">
    <button class="platform-tab active" data-tab="resumen" role="tab" aria-selected="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
        Resumen
    </button>
    <button class="platform-tab" data-tab="usuarios" role="tab" aria-selected="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Usuarios
    </button>
    <button class="platform-tab" data-tab="organizaciones" role="tab" aria-selected="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V2h12v20"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h2"/><path d="M18 18h2a2 2 0 0 0 2-2v-5a2 2 0 0 0-2-2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
        Organizaciones
    </button>
    <button class="platform-tab" data-tab="operativa" role="tab" aria-selected="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        Operativa
    </button>
</div>

<!-- ============================================ -->
<!-- TAB 1: RESUMEN EJECUTIVO -->
<!-- ============================================ -->
<div class="platform-tab-content active" id="tab-resumen" role="tabpanel">
    <div class="dash-stats">
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-teal"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V2h12v20"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h2"/><path d="M18 18h2a2 2 0 0 0 2-2v-5a2 2 0 0 0-2-2h-2"/></svg></div>
            <div class="dash-stat-value count-up" data-target="<?= $kpis['organizaciones'] ?>">0</div>
            <div class="dash-stat-label">Organizaciones (<?= $kpis['organizaciones_activas'] ?> activas)</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-info"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
            <div class="dash-stat-value count-up" data-target="<?= $kpis['usuarios'] ?>">0</div>
            <div class="dash-stat-label">Usuarios activos</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-mint"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
            <div class="dash-stat-value count-up" data-target="<?= $kpis['profesionales'] ?>">0</div>
            <div class="dash-stat-label">Profesionales</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-sand"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/></svg></div>
            <div class="dash-stat-value count-up" data-target="<?= $kpis['pacientes'] ?>">0</div>
            <div class="dash-stat-label">Pacientes</div>
        </div>
    </div>

    <div class="dash-stats">
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-teal"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
            <div class="dash-stat-value count-up" data-target="<?= $kpis['citas_mes'] ?>">0</div>
            <div class="dash-stat-label">Citas este mes</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-mint"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
            <div class="dash-stat-value count-up" data-target="<?= $kpis['consultas_mes'] ?>">0</div>
            <div class="dash-stat-label">Consultas este mes</div>
        </div>
    </div>

    <div class="table-card reportes-chart-card">
        <div class="dash-section-title">Citas por mes (últimos 12 meses)</div>
        <?php if (empty($citasPorMes)): ?>
            <div class="empty-state"><h3>Sin datos de citas</h3></div>
        <?php else: ?>
            <canvas id="chartCitasMes" height="80"></canvas>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================ -->
<!-- TAB 2: USUARIOS Y DEMOGRAFÍA -->
<!-- ============================================ -->
<div class="platform-tab-content" id="tab-usuarios" role="tabpanel">
    <div class="dash-stats">
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-teal"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <div class="dash-stat-value"><?= $usuariosActivos['activos_7d'] ?></div>
            <div class="dash-stat-label">Activos últimos 7 días</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-mint"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <div class="dash-stat-value"><?= $usuariosActivos['activos_30d'] ?></div>
            <div class="dash-stat-label">Activos últimos 30 días</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-sand"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <div class="dash-stat-value"><?= $usuariosActivos['activos_90d'] ?></div>
            <div class="dash-stat-label">Activos últimos 90 días</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-icon icon-info"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
            <div class="dash-stat-value"><?= $usuariosActivos['total'] ?></div>
            <div class="dash-stat-label">Total usuarios</div>
        </div>
    </div>

    <div class="table-card reportes-chart-card">
        <div class="dash-section-title">Usuarios por rol</div>
        <?php if (empty($usuariosPorRol)): ?>
            <div class="empty-state"><h3>Sin usuarios registrados</h3></div>
        <?php else: ?>
            <canvas id="chartRoles" height="80"></canvas>
        <?php endif; ?>
    </div>

    <div class="table-card reportes-chart-card">
        <div class="dash-section-title">Profesionales por especialidad</div>
        <?php if (empty($profesionalesPorEspecialidad)): ?>
            <div class="empty-state"><h3>Sin profesionales registrados</h3></div>
        <?php else: ?>
            <canvas id="chartEspecialidades" height="100"></canvas>
        <?php endif; ?>
    </div>

    <div class="table-card reportes-chart-card">
        <div class="dash-section-title">Pacientes por género</div>
        <?php if (empty($pacientesPorGenero)): ?>
            <div class="empty-state"><h3>Sin datos de género</h3></div>
        <?php else: ?>
            <canvas id="chartGenero" height="80"></canvas>
        <?php endif; ?>
    </div>

    <div class="table-card reportes-chart-card">
        <div class="dash-section-title">Pacientes por rango de edad</div>
        <?php if (empty($pacientesPorEdad)): ?>
            <div class="empty-state"><h3>Sin datos de edad</h3></div>
        <?php else: ?>
            <canvas id="chartEdad" height="80"></canvas>
        <?php endif; ?>
    </div>

    <div class="table-card">
        <div class="dash-section-title">Últimos accesos</div>
        <?php if (empty($ultimoAcceso)): ?>
            <div class="empty-state"><h3>Sin registros de acceso</h3></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Usuario</th><th>Rol</th><th>Último acceso</th><th>Estado</th></tr></thead>
                    <tbody>
                        <?php foreach ($ultimoAcceso as $u): ?>
                            <tr>
                                <td><?= htmlspecialchars(trim($u['nombre'] . ' ' . $u['apellidos'])) ?></td>
                                <td><?= htmlspecialchars($u['rol'] ?? 'Sin rol') ?></td>
                                <td><?= $u['ultimo_acceso_en'] ? date('d/m/Y H:i', strtotime($u['ultimo_acceso_en'])) : 'Nunca' ?></td>
                                <td><span class="badge-estado <?= (int) $u['activo'] === 1 ? 'badge-completada' : 'badge-cancelada' ?>"><?= (int) $u['activo'] === 1 ? 'Activo' : 'Inactivo' ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================ -->
<!-- TAB 3: ORGANIZACIONES -->
<!-- ============================================ -->
<div class="platform-tab-content" id="tab-organizaciones" role="tabpanel">
    <div class="table-card">
        <div class="dash-section-title">Benchmark por organización</div>
        <?php if (empty($benchmark)): ?>
            <div class="empty-state"><h3>Sin organizaciones</h3></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Organización</th><th>Estado</th><th style="text-align:right">Pacientes</th><th style="text-align:right">Profesionales</th><th style="text-align:right">Citas</th></tr></thead>
                    <tbody>
                        <?php foreach ($benchmark as $o): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($o['nombre']) ?></strong></td>
                                <td><span class="badge-estado <?= (int) $o['activo'] === 1 ? 'badge-completada' : 'badge-cancelada' ?>"><?= (int) $o['activo'] === 1 ? 'Activa' : 'Inactiva' ?></span></td>
                                <td style="text-align:right"><?= (int) $o['pacientes'] ?></td>
                                <td style="text-align:right"><?= (int) $o['profesionales'] ?></td>
                                <td style="text-align:right"><strong><?= (int) $o['citas'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-card">
        <div class="dash-section-title">Ratio pacientes / profesional</div>
        <?php if (empty($ratios)): ?>
            <div class="empty-state"><h3>Sin datos suficientes</h3></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Organización</th><th style="text-align:right">Pacientes</th><th style="text-align:right">Profesionales</th><th style="text-align:right">Ratio</th></tr></thead>
                    <tbody>
                        <?php foreach ($ratios as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['nombre']) ?></td>
                                <td style="text-align:right"><?= (int) $r['pacientes'] ?></td>
                                <td style="text-align:right"><?= (int) $r['profesionales'] ?></td>
                                <td style="text-align:right"><strong><?= $r['ratio'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================ -->
<!-- TAB 4: OPERATIVA -->
<!-- ============================================ -->
<div class="platform-tab-content" id="tab-operativa" role="tabpanel">
    <div class="table-card reportes-chart-card">
        <div class="dash-section-title">Top procesos realizados</div>
        <?php if (empty($topProcesos)): ?>
            <div class="empty-state"><h3>Sin datos</h3></div>
        <?php else: ?>
            <canvas id="chartProcesos" height="100"></canvas>
        <?php endif; ?>
    </div>

    <div class="table-card">
        <div class="dash-section-title">Tasa de no-show por especialidad</div>
        <?php if (empty($noShowPorEspecialidad)): ?>
            <div class="empty-state"><h3>Sin datos suficientes</h3></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Especialidad</th><th style="text-align:right">Citas</th><th style="text-align:right">No-show</th><th style="text-align:right">Tasa</th></tr></thead>
                    <tbody>
                        <?php foreach ($noShowPorEspecialidad as $n): ?>
                            <tr>
                                <td><?= htmlspecialchars($n['especialidad']) ?></td>
                                <td style="text-align:right"><?= (int) $n['total_citas'] ?></td>
                                <td style="text-align:right"><?= (int) $n['no_show'] ?></td>
                                <td style="text-align:right"><strong><?= $n['tasa_no_show'] ?>%</strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-card">
        <div class="dash-section-title">Consultas por estado</div>
        <?php if (empty($consultasPorEstado)): ?>
            <div class="empty-state"><h3>Sin consultas</h3></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Estado</th><th style="text-align:right">Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($consultasPorEstado as $c): ?>
                            <tr>
                                <td><?= ucfirst(str_replace('_', ' ', $c['estado'])) ?></td>
                                <td style="text-align:right"><strong><?= (int) $c['total'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ============================================
// TABS
// ============================================
document.querySelectorAll('.platform-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.platform-tab').forEach(t => {
            t.classList.remove('active');
            t.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('.platform-tab-content').forEach(c => c.classList.remove('active'));
        
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');
        document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
        history.replaceState(null, null, '#' + tab.dataset.tab);
    });
});

// Activar tab desde URL hash
const hash = window.location.hash.slice(1);
if (hash) {
    const tab = document.querySelector(`.platform-tab[data-tab="${hash}"]`);
    if (tab) tab.click();
}

// ============================================
// ANIMACIÓN DE CONTEO
// ============================================
function animateCountUp(el) {
    const target = parseInt(el.dataset.target) || 0;
    const duration = 1500;
    const start = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - start;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.round(target * eased);
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    requestAnimationFrame(update);
}

document.querySelectorAll('.count-up').forEach(el => animateCountUp(el));

// ============================================
// GRÁFICOS
// ============================================
const chartColors = ['#4FD1B5', '#1C5345', '#EED9A4', '#A8DDD0', '#6B857F', '#E53E3E', '#F6AD55', '#9F7AEA', '#4299E1', '#48BB78'];

<?php if (!empty($citasPorMes)): ?>
new Chart(document.getElementById('chartCitasMes'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($citasLabels) ?>,
        datasets: [{
            label: 'Citas',
            data: <?= json_encode($citasData) ?>,
            backgroundColor: '#4FD1B5',
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

<?php if (!empty($usuariosPorRol)): ?>
new Chart(document.getElementById('chartRoles'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($rolLabels) ?>,
        datasets: [{
            data: <?= json_encode($rolData) ?>,
            backgroundColor: chartColors
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'right' } }
    }
});
<?php endif; ?>

<?php if (!empty($profesionalesPorEspecialidad)): ?>
new Chart(document.getElementById('chartEspecialidades'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($espLabels) ?>,
        datasets: [{
            label: 'Profesionales',
            data: <?= json_encode($espData) ?>,
            backgroundColor: '#1C5345',
            borderRadius: 6
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
<?php endif; ?>

<?php if (!empty($pacientesPorGenero)): ?>
new Chart(document.getElementById('chartGenero'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($genLabels) ?>,
        datasets: [{
            data: <?= json_encode($genData) ?>,
            backgroundColor: ['#4299E1', '#ED64A6', '#9F7AEA', '#A0AEC0']
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'right' } }
    }
});
<?php endif; ?>

<?php if (!empty($pacientesPorEdad)): ?>
new Chart(document.getElementById('chartEdad'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($edadLabels) ?>,
        datasets: [{
            label: 'Pacientes',
            data: <?= json_encode($edadData) ?>,
            backgroundColor: '#EED9A4',
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

<?php if (!empty($topProcesos)): ?>
new Chart(document.getElementById('chartProcesos'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($procLabels) ?>,
        datasets: [{
            label: 'Veces',
            data: <?= json_encode($procData) ?>,
            backgroundColor: '#4FD1B5',
            borderRadius: 6
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
<?php endif; ?>
</script>
