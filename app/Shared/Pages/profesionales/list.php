<?php
/**
 * SALUVERA - Listado de Profesionales (vista global para superadmin)
 *
 * @version 2.15.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$profesionales = $profesionales ?? [];
$organizaciones = $organizaciones ?? [];
$orgFiltro = $orgFiltro ?? 0;
$q = $q ?? '';
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$esSuperAdmin = $esSuperAdmin ?? false;
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Profesionales</h2>
        <p class="page-subtitle"><?= $total ?> profesional(es) encontrado(s)</p>
    </div>
    <a href="<?= url('/panel/profesionales/nuevo') ?>" class="btn btn-primary">+ Nuevo Profesional</a>
</div>

<div class="table-card">
    <form method="GET" action="<?= url('/panel/profesionales') ?>" class="search-form">
        <?php if ($esSuperAdmin && !empty($organizaciones)): ?>
            <select name="org_id" class="search-select" onchange="this.form.submit()">
                <option value="0">Todas las organizaciones</option>
                <?php foreach ($organizaciones as $o): ?>
                    <option value="<?= (int) $o['id'] ?>" <?= $orgFiltro == $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nombre, correo o cedula..." class="search-input">
        <button type="submit" class="btn btn-outline">Buscar</button>
        <?php if ($q !== '' || $orgFiltro > 0): ?>
            <a href="<?= url('/panel/profesionales') ?>" class="btn btn-outline">Limpiar</a>
        <?php endif; ?>
    </form>

    <?php if (empty($profesionales)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
            <h3>No hay profesionales</h3>
            <?php if ($q !== '' || $orgFiltro > 0): ?>
                <p>No se encontraron resultados con los filtros aplicados.</p>
                <a href="<?= url('/panel/profesionales') ?>" class="btn btn-primary">Limpiar filtros</a>
            <?php else: ?>
                <p>Registra a tu primer profesional de la salud.</p>
                <a href="<?= url('/panel/profesionales/nuevo') ?>" class="btn btn-primary">Registrar profesional</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <?php if ($esSuperAdmin): ?>
                            <th>Organizacion</th>
                        <?php endif; ?>
                        <th>Correo</th>
                        <th>Especialidad</th>
                        <th>Cedula</th>
                        <th>Consulta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($profesionales as $p): ?>
                        <?php
                        $activo = (int) $p['activo'] === 1;
                        $nombreCompleto = $p['nombre'] . ' ' . $p['apellidos'];
                        ?>
                        <tr>
                            <td>
                                <div class="cell-name">
                                    <span class="avatar-mini"><?= strtoupper(substr($p['nombre'], 0, 1)) ?></span>
                                    <strong><?= htmlspecialchars($nombreCompleto) ?></strong>
                                    <?php if (!$activo): ?>
                                        <span class="badge-inactive">Inactivo</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php if ($esSuperAdmin): ?>
                                <td><span class="badge-org"><?= htmlspecialchars($p['organizacion_nombre'] ?? '—') ?></span></td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($p['correo']) ?></td>
                            <td><?= htmlspecialchars($p['especialidad_nombre'] ?? 'Sin especialidad') ?></td>
                            <td><?= htmlspecialchars($p['numero_cedula'] ?? '—') ?></td>
                            <td>
                                <?= (int) $p['duracion_consulta'] ?> min
                                <?php if ($p['costo_consulta'] !== null): ?>
                                    <br><span class="cell-muted">$<?= number_format((float) $p['costo_consulta'], 2) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="action-btn action-edit"
                                       href="<?= url('/panel/profesionales/' . (int) $p['id'] . '/editar') ?>"
                                       title="Editar"
                                       aria-label="Editar a <?= htmlspecialchars($nombreCompleto) ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>

                                    <?php if ($activo): ?>
                                        <form method="POST"
                                              action="<?= url('/panel/profesionales/' . (int) $p['id'] . '/eliminar') ?>"
                                              class="inline-form"
                                              onsubmit="return confirm('Dar de baja a <?= htmlspecialchars($nombreCompleto) ?>?');">
                                            <?= Security::csrfField() ?>
                                            <button type="submit" class="action-btn action-delete" title="Dar de baja" aria-label="Dar de baja a <?= htmlspecialchars($nombreCompleto) ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                </svg>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST"
                                              action="<?= url('/panel/profesionales/' . (int) $p['id'] . '/reactivar') ?>"
                                              class="inline-form"
                                              onsubmit="return confirm('Reactivar a <?= htmlspecialchars($nombreCompleto) ?>?');">
                                            <?= Security::csrfField() ?>
                                            <button type="submit" class="action-btn action-restore action-wide" title="Reactivar">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                                                </svg>Reactivar
                                            </button>
                                        </form>
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
                    <a class="page-link" href="<?= url('/panel/profesionales?q=' . urlencode($q) . '&org_id=' . $orgFiltro . '&page=' . ($page - 1)) ?>">Anterior</a>
                <?php endif; ?>
                <span class="page-info">Pagina <?= $page ?> de <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="page-link" href="<?= url('/panel/profesionales?q=' . urlencode($q) . '&org_id=' . $orgFiltro . '&page=' . ($page + 1)) ?>">Siguiente</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
