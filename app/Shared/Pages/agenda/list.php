<?php
/**
 * SALUVERA - Agenda de Citas (listado con filtros y cambio de estado)
 *
 * @version 2.17.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$citas = $citas ?? [];
$filtros = $filtros ?? ['fecha' => '', 'estado' => '', 'q' => '', 'profesional_id' => 0];
$orgFiltro = $orgFiltro ?? 0;
$organizaciones = $organizaciones ?? [];
$profesionales = $profesionales ?? [];
$estados = $estados ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$rol = $rol ?? '';
$esSuperAdmin = $esSuperAdmin ?? false;
$bloqueosProximos = $bloqueosProximos ?? [];
$esProfessional = ($rol === 'professional');
$consultaPorCita = $consultaPorCita ?? [];
$esProfessional = $rol === 'professional';

$badgePorEstado = [
    'pendiente' => 'badge-pendiente',
    'confirmada' => 'badge-confirmada',
    'en_progreso' => 'badge-progreso',
    'completada' => 'badge-completada',
    'cancelada' => 'badge-cancelada',
    'no_asistio' => 'badge-cancelada',
    'reprogramada' => 'badge-pendiente',
];

$qsParts = array_filter([
    'fecha' => $filtros['fecha'],
    'estado' => $filtros['estado'],
    'q' => $filtros['q'],
    'profesional_id' => $filtros['profesional_id'] ?: null,
    'org_id' => $orgFiltro ?: null,
]);
$qs = http_build_query($qsParts);

$hoy = date('Y-m-d');
$manana = date('Y-m-d', strtotime('+1 day'));

function urlAgenda(string $qs, int $page = 0): string {
    $base = '/panel/agenda';
    $sep = '?';
    $url = $base;
    if ($qs !== '') {
        $url .= $sep . $qs;
        $sep = '&';
    }
    if ($page > 0) {
        $url .= $sep . 'page=' . $page;
    }
    return url($url);
}
?>
<div class="page-header">
    <div>
        <h2 class="page-title"><?= $esProfessional ? 'Mi Agenda' : 'Agenda' ?></h2>
        <p class="page-subtitle"><?= $total ?> cita(s) encontrada(s)</p>
    </div>
    <a href="<?= url('/panel/agenda/nueva') ?>" class="btn btn-primary">+ Nueva Cita</a>
</div>

<div class="table-card">
    <form method="GET" action="<?= url('/panel/agenda') ?>" class="search-form">
        <?php if ($esSuperAdmin && !empty($organizaciones)): ?>
            <select name="org_id" class="search-select">
                <option value="0">Todas las organizaciones</option>
                <?php foreach ($organizaciones as $o): ?>
                    <option value="<?= (int) $o['id'] ?>" <?= $orgFiltro == $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if (!$esProfessional && !empty($profesionales)): ?>
            <select name="profesional_id" class="search-select">
                <option value="0">Todos los profesionales</option>
                <?php foreach ($profesionales as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (int) $filtros['profesional_id'] === (int) $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <input type="date" name="fecha" value="<?= htmlspecialchars($filtros['fecha']) ?>" class="search-input search-input-sm" aria-label="Filtrar por fecha">

        <select name="estado" class="search-select">
            <option value="">Todos los estados</option>
            <?php foreach ($estados as $valor => $nombre): ?>
                <option value="<?= htmlspecialchars($valor) ?>" <?= $filtros['estado'] === $valor ? 'selected' : '' ?>><?= htmlspecialchars($nombre) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="search" name="q" value="<?= htmlspecialchars($filtros['q']) ?>" placeholder="Buscar paciente, profesional o motivo..." class="search-input">
        <button type="submit" class="btn btn-outline">Filtrar</button>
        <a href="<?= url('/panel/agenda') ?>" class="btn btn-outline">Limpiar</a>
    </form>

<?php if (!empty($bloqueosProximos)): ?>
    <div class="bloqueos-banner">
        <div class="bloqueos-banner-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="10" y1="14" x2="14" y2="18"/><line x1="14" y1="14" x2="10" y2="18"/>
            </svg>
            Dias bloqueados proximos (no se puede agendar)
        </div>
        <div class="bloqueos-chips">
            <?php foreach ($bloqueosProximos as $b): ?>
                <span class="bloqueo-chip" title="<?= htmlspecialchars($b['motivo'] ?? 'Sin motivo') ?>">
                    <?= date('d/m', strtotime($b['fecha_inicio'])) ?><?= $b['fecha_fin'] !== $b['fecha_inicio'] ? ' al ' . date('d/m', strtotime($b['fecha_fin'])) : '' ?>
                    <?php if (!$esProfessional): ?>
                        &middot; <?= htmlspecialchars(trim($b['prof_nombre'] . ' ' . $b['prof_apellidos'])) ?>
                    <?php endif; ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

    <div class="agenda-quick">
        <a href="<?= urlAgenda('fecha=' . $hoy) ?>" class="agenda-quick-link <?= $filtros['fecha'] === $hoy ? 'active' : '' ?>">Hoy</a>
        <a href="<?= urlAgenda('fecha=' . $manana) ?>" class="agenda-quick-link <?= $filtros['fecha'] === $manana ? 'active' : '' ?>">Manana</a>
        <a href="<?= urlAgenda('') ?>" class="agenda-quick-link <?= $filtros['fecha'] === '' ? 'active' : '' ?>">Todas</a>
    </div>

    <?php if (empty($citas)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <h3>No hay citas</h3>
            <?php if ($filtros['fecha'] !== '' || $filtros['estado'] !== '' || $filtros['q'] !== '' || $filtros['profesional_id'] > 0 || $orgFiltro > 0): ?>
                <p>No se encontraron citas con los filtros aplicados.</p>
                <a href="<?= url('/panel/agenda') ?>" class="btn btn-primary">Limpiar filtros</a>
            <?php else: ?>
                <p>Crea la primera cita para comenzar.</p>
                <a href="<?= url('/panel/agenda/nueva') ?>" class="btn btn-primary">Nueva cita</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <?php if (!$esProfessional): ?>
                            <th>Profesional</th>
                        <?php endif; ?>
                        <?php if ($esSuperAdmin): ?>
                            <th>Organizacion</th>
                        <?php endif; ?>
                        <th>Consultorio</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $cita): ?>
                        <?php
                        $estado = $cita['estado'];
                        $badge = $badgePorEstado[$estado] ?? 'badge-pendiente';
                        $nombreEstado = $estados[$estado] ?? ucfirst($estado);
                        $paciente = trim($cita['paciente_nombre'] . ' ' . $cita['paciente_apellidos']);
                        ?>
                        <tr>
                            <td>
                                <?php if ($cita['fecha_cita'] === $hoy): ?>
                                    <strong>Hoy</strong>
                                <?php else: ?>
                                    <?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?>
                                <?php endif; ?>
                            </td>
                            <td><span class="agenda-hora"><?= substr($cita['hora_inicio'], 0, 5) ?></span></td>
                            <td><strong><?= htmlspecialchars($paciente) ?></strong></td>
                            <?php if (!$esProfessional): ?>
                                <td><?= htmlspecialchars(trim(($cita['prof_nombre'] ?? '') . ' ' . ($cita['prof_apellidos'] ?? '')) ?: '—') ?></td>
                            <?php endif; ?>
                            <?php if ($esSuperAdmin): ?>
                                <td><span class="badge-org"><?= htmlspecialchars($cita['organizacion_nombre'] ?? '—') ?></span></td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($cita['consultorio_nombre'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($cita['motivo'] ?? '—') ?></td>
                            <td><span class="badge-estado <?= $badge ?>"><?= htmlspecialchars($nombreEstado) ?></span></td>
                            <td>
                                <div class="agenda-acciones">
                                    <?php if (isset($consultaPorCita[(int) $cita['id']])): ?>
                                        <a class="btn-status btn-status-complete"
                                           href="<?= url('/panel/consultas/' . $consultaPorCita[(int) $cita['id']] . '/editar') ?>">Consulta</a>
                                    <?php elseif ($estado === 'completada'): ?>
                                        <a class="btn-status btn-status-confirm"
                                           href="<?= url('/panel/citas/' . (int) $cita['id'] . '/consulta/nueva') ?>">+ Consulta</a>
                                    <?php endif; ?>
                                    <?php if ($estado === 'pendiente'): ?>
                                        <form method="POST" action="<?= url('/panel/agenda/' . (int) $cita['id'] . '/estado/confirmada') ?>" class="inline-form">
                                            <?= Security::csrfField() ?>
                                            <button type="submit" class="btn-status btn-status-confirm" title="Confirmar">Confirmar</button>
                                        </form>
                                        <form method="POST" action="<?= url('/panel/agenda/' . (int) $cita['id'] . '/estado/cancelada') ?>" class="inline-form" onsubmit="return confirm('Cancelar esta cita?');">
                                            <?= Security::csrfField() ?>
                                            <button type="submit" class="btn-status btn-status-cancel" title="Cancelar">Cancelar</button>
                                        </form>
                                    <?php elseif ($estado === 'confirmada'): ?>
                                        <form method="POST" action="<?= url('/panel/agenda/' . (int) $cita['id'] . '/estado/completada') ?>" class="inline-form">
                                            <?= Security::csrfField() ?>
                                            <button type="submit" class="btn-status btn-status-complete" title="Completar">Completar</button>
                                        </form>
                                        <form method="POST" action="<?= url('/panel/agenda/' . (int) $cita['id'] . '/estado/no_asistio') ?>" class="inline-form" onsubmit="return confirm('Marcar como no asistio?');">
                                            <?= Security::csrfField() ?>
                                            <button type="submit" class="btn-status btn-status-noshow" title="No asistio">No asistio</button>
                                        </form>
                                        <form method="POST" action="<?= url('/panel/agenda/' . (int) $cita['id'] . '/estado/cancelada') ?>" class="inline-form" onsubmit="return confirm('Cancelar esta cita?');">
                                            <?= Security::csrfField() ?>
                                            <button type="submit" class="btn-status btn-status-cancel" title="Cancelar">Cancelar</button>
                                        </form>
                                    <?php elseif ($estado === 'en_progreso'): ?>
                                        <form method="POST" action="<?= url('/panel/agenda/' . (int) $cita['id'] . '/estado/completada') ?>" class="inline-form">
                                            <?= Security::csrfField() ?>
                                            <button type="submit" class="btn-status btn-status-complete" title="Completar">Completar</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="cell-muted">—</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a class="page-link" href="<?= urlAgenda($qs, $page - 1) ?>">Anterior</a>
                <?php endif; ?>
                <span class="page-info">Pagina <?= $page ?> de <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="page-link" href="<?= urlAgenda($qs, $page + 1) ?>">Siguiente</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
