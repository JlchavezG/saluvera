<?php
/**
 * SALUVERA - Listado de Consultorios (sin equipamiento, con direccion)
 *
 * @version 2.21.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$consultorios = $consultorios ?? [];
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
        <h2 class="page-title">Consultorios</h2>
        <p class="page-subtitle"><?= $total ?> consultorio(s) encontrado(s)</p>
    </div>
    <a href="<?= url('/panel/consultorios/nuevo') ?>" class="btn btn-primary">+ Nuevo Consultorio</a>
</div>

<div class="table-card">
    <form method="GET" action="<?= url('/panel/consultorios') ?>" class="search-form">
        <?php if ($esSuperAdmin && !empty($organizaciones)): ?>
            <select name="org_id" class="search-select" onchange="this.form.submit()">
                <option value="0">Todas las organizaciones</option>
                <?php foreach ($organizaciones as $o): ?>
                    <option value="<?= (int) $o['id'] ?>" <?= $orgFiltro == $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nombre, ubicacion o direccion..." class="search-input">
        <button type="submit" class="btn btn-outline">Buscar</button>
        <?php if ($q !== '' || $orgFiltro > 0): ?>
            <a href="<?= url('/panel/consultorios') ?>" class="btn btn-outline">Limpiar</a>
        <?php endif; ?>
    </form>

    <?php if (empty($consultorios)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <h3>No hay consultorios</h3>
            <?php if ($q !== '' || $orgFiltro > 0): ?>
                <p>No se encontraron resultados con los filtros aplicados.</p>
                <a href="<?= url('/panel/consultorios') ?>" class="btn btn-primary">Limpiar filtros</a>
            <?php else: ?>
                <p>Registra tu primer consultorio para poder agendar citas.</p>
                <a href="<?= url('/panel/consultorios/nuevo') ?>" class="btn btn-primary">Registrar consultorio</a>
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
                        <th>Direccion</th>
                        <th>Ubicacion interna</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($consultorios as $c): ?>
                        <?php $activo = (int) $c['activo'] === 1; ?>
                        <tr>
                            <td>
                                <div class="cell-name">
                                    <span class="avatar-mini"><?= strtoupper(substr($c['nombre'], 0, 1)) ?></span>
                                    <strong><?= htmlspecialchars($c['nombre']) ?></strong>
                                    <?php if (!$activo): ?>
                                        <span class="badge-inactive">Inactivo</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php if ($esSuperAdmin): ?>
                                <td><span class="badge-org"><?= htmlspecialchars($c['organizacion_nombre'] ?? '—') ?></span></td>
                            <?php endif; ?>
                            <td>
                                <?php if (!empty($c['direccion'])): ?>
                                    <?= htmlspecialchars(mb_strlen($c['direccion']) > 50 ? mb_substr($c['direccion'], 0, 50) . '...' : $c['direccion']) ?>
                                <?php else: ?>
                                    <span class="cell-muted">Sin direccion</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($c['ubicacion'] ?? '—') ?></td>
                            <td>
                                <div class="row-actions">
                                    <a class="action-btn action-edit"
                                       href="<?= url('/panel/consultorios/' . (int) $c['id'] . '/editar') ?>"
                                       title="Editar"
                                       aria-label="Editar <?= htmlspecialchars($c['nombre']) ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>

                                    <?php if ($activo): ?>
                                        <form method="POST"
                                              action="<?= url('/panel/consultorios/' . (int) $c['id'] . '/eliminar') ?>"
                                              class="inline-form"
                                              onsubmit="return confirm('Dar de baja <?= htmlspecialchars($c['nombre']) ?>?');">
                                            <?= Security::csrfField() ?>
                                            <button type="submit" class="action-btn action-delete" title="Dar de baja" aria-label="Dar de baja <?= htmlspecialchars($c['nombre']) ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                </svg>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST"
                                              action="<?= url('/panel/consultorios/' . (int) $c['id'] . '/reactivar') ?>"
                                              class="inline-form"
                                              onsubmit="return confirm('Reactivar <?= htmlspecialchars($c['nombre']) ?>?');">
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
                    <a class="page-link" href="<?= url('/panel/consultorios?q=' . urlencode($q) . '&org_id=' . $orgFiltro . '&page=' . ($page - 1)) ?>">Anterior</a>
                <?php endif; ?>
                <span class="page-info">Pagina <?= $page ?> de <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="page-link" href="<?= url('/panel/consultorios?q=' . urlencode($q) . '&org_id=' . $orgFiltro . '&page=' . ($page + 1)) ?>">Siguiente</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
