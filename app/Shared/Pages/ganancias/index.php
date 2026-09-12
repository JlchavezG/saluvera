<?php
/**
 * SALUVERA - Balance de ganancias del profesional (con modo privado)
 *
 * @version 2.30.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$rango = $rango ?? ['total_citas' => 0, 'total_monto' => 0];
$mesActual = $mesActual ?? ['total_citas' => 0, 'total_monto' => 0];
$historico = $historico ?? ['total_citas' => 0, 'total_monto' => 0];
$citas = $citas ?? [];
$desde = $desde ?? date('Y-m-01');
$hasta = $hasta ?? date('Y-m-d');
$promedio = $promedio ?? 0;

$fmt = function ($monto) {
    return '$' . number_format((float) $monto, 2);
};

// Span de monto con valor real en data-monto (para enmascarar/desenmascarar)
$m = function ($monto) use ($fmt) {
    $real = $fmt($monto);
    return '<span class="monto-valor" data-monto="' . htmlspecialchars($real) . '">' . htmlspecialchars($real) . '</span>';
};
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Mis Ganancias</h2>
        <p class="page-subtitle">Balance de consultas completadas</p>
    </div>
    <button type="button" class="btn btn-outline btn-toggle-montos" id="btnMontos" aria-pressed="false"
            title="Ocultar o mostrar los montos en pantalla">
        <svg class="icono-ojo-abierto" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
        <svg class="icono-ojo-cerrado" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
        </svg>
        <span id="btnMontosTexto">Ocultar montos</span>
    </button>
</div>

<div class="dash-stats">
    <div class="dash-stat-card ganancia-card">
        <div class="dash-stat-icon icon-sand">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $m($mesActual['total_monto']) ?></div>
        <div class="dash-stat-label">Mes actual (<?= (int) $mesActual['total_citas'] ?> consultas)</div>
    </div>

    <div class="dash-stat-card ganancia-card ganancia-destacada">
        <div class="dash-stat-icon icon-teal">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $m($rango['total_monto']) ?></div>
        <div class="dash-stat-label">Rango filtrado (<?= (int) $rango['total_citas'] ?> consultas)</div>
    </div>

    <div class="dash-stat-card ganancia-card">
        <div class="dash-stat-icon icon-mint">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $m($historico['total_monto']) ?></div>
        <div class="dash-stat-label">Historico total (<?= (int) $historico['total_citas'] ?> consultas)</div>
    </div>

    <div class="dash-stat-card ganancia-card">
        <div class="dash-stat-icon icon-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= $m($promedio) ?></div>
        <div class="dash-stat-label">Promedio por consulta (rango)</div>
    </div>
</div>

<div class="table-card">
    <form method="GET" action="<?= url('/panel/mis-ganancias') ?>" class="search-form">
        <label class="form-label" for="desde">Desde</label>
        <input type="date" name="desde" id="desde" value="<?= htmlspecialchars($desde) ?>" class="search-input search-input-sm">
        <label class="form-label" for="hasta">Hasta</label>
        <input type="date" name="hasta" id="hasta" value="<?= htmlspecialchars($hasta) ?>" class="search-input search-input-sm">
        <button type="submit" class="btn btn-outline">Filtrar</button>
        <a href="<?= url('/panel/mis-ganancias') ?>" class="btn btn-outline">Mes actual</a>
    </form>
</div>

<div class="dash-section-title">Consultas completadas en el rango (<?= count($citas) ?>)</div>

<?php if (empty($citas)): ?>
    <div class="table-card empty-state">
        <h3>Sin consultas completadas en este rango</h3>
        <p>Cuando completes citas apareceran aqui con su monto.</p>
    </div>
<?php else: ?>
    <div class="table-card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <th>Motivo</th>
                        <th style="text-align:right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $c): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($c['fecha_cita'])) ?></td>
                            <td><span class="agenda-hora"><?= substr($c['hora_inicio'], 0, 5) ?></span></td>
                            <td><?= htmlspecialchars(trim($c['paciente_nombre'] . ' ' . $c['paciente_apellidos'])) ?></td>
                            <td><?= htmlspecialchars($c['motivo'] ?? '—') ?></td>
                            <td style="text-align:right"><strong class="monto-celda"><?= $m($c['monto_consulta'] ?? 0) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right"><strong>Total del rango:</strong></td>
                        <td style="text-align:right"><strong class="monto-celda"><?= $m($rango['total_monto']) ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php endif; ?>

<script>
(function () {
    var KEY = 'saluvera_montos_ocultos';
    var btn = document.getElementById('btnMontos');
    var txt = document.getElementById('btnMontosTexto');
    var oculto = localStorage.getItem(KEY) === '1';

    function aplicar() {
        document.querySelectorAll('.monto-valor').forEach(function (el) {
            el.textContent = oculto ? '••••••' : el.dataset.monto;
        });
        if (txt) txt.textContent = oculto ? 'Mostrar montos' : 'Ocultar montos';
        if (btn) {
            btn.setAttribute('aria-pressed', oculto ? 'true' : 'false');
            btn.classList.toggle('active', oculto);
        }
        document.body.classList.toggle('montos-ocultos', oculto);
    }

    if (btn) {
        btn.addEventListener('click', function () {
            oculto = !oculto;
            localStorage.setItem(KEY, oculto ? '1' : '0');
            aplicar();
        });
    }

    aplicar();
})();
</script>
