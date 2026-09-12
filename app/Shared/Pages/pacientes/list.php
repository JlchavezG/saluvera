<?php
/**
 * SALUVERA - Listado de Pacientes con filtro de org para superadmin
 *
 * @version 2.15.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$pacientes = $pacientes ?? [];
$organizaciones = $organizaciones ?? [];
$orgFiltro = $orgFiltro ?? 0;
$q = $q ?? '';
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$soloMios = $soloMios ?? false;
$esSuperAdmin = $esSuperAdmin ?? false;
?>
<div class="page-header">
    <div>
        <h2 class="page-title"><?= $soloMios ? 'Mis Pacientes' : 'Pacientes' ?></h2>
        <p class="page-subtitle"><?= $total ?> paciente(s) encontrado(s)</p>
    </div>
    <a href="<?= url('/panel/pacientes/nuevo') ?>" class="btn btn-primary">+ Nuevo Paciente</a>
</div>

<div class="table-card">
    <form method="GET" action="<?= url('/panel/pacientes') ?>" class="search-form">
        <?php if ($esSuperAdmin && !empty($organizaciones)): ?>
            <select name="org_id" class="search-select" onchange="this.form.submit()">
                <option value="0">Todas las organizaciones</option>
                <?php foreach ($organizaciones as $o): ?>
                    <option value="<?= (int) $o['id'] ?>" <?= $orgFiltro == $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por nombre, apellido o correo..." class="search-input">
        <button type="submit" class="btn btn-outline">Buscar</button>
        <?php if ($q !== '' || $orgFiltro > 0): ?>
            <a href="<?= url('/panel/pacientes') ?>" class="btn btn-outline">Limpiar</a>
        <?php endif; ?>
    </form>

    <?php if (empty($pacientes)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <h3>No hay pacientes</h3>
            <?php if ($q !== '' || $orgFiltro > 0): ?>
                <p>No se encontraron resultados con los filtros aplicados.</p>
                <a href="<?= url('/panel/pacientes') ?>" class="btn btn-primary">Limpiar filtros</a>
            <?php else: ?>
                <p>Registra tu primer paciente para comenzar.</p>
                <a href="<?= url('/panel/pacientes/nuevo') ?>" class="btn btn-primary">Registrar paciente</a>
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
                        <th>Contacto</th>
                        <th>Edad</th>
                        <th>Genero</th>
                        <th>T. Sangre</th>
                        <th>Registrado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacientes as $p): ?>
                        <?php
                        $edad = null;
                        if (!empty($p['fecha_nacimiento'])) {
                            try {
                                $edad = (new DateTime($p['fecha_nacimiento']))->diff(new DateTime('now'))->y;
                            } catch (Exception $e) {
                                $edad = null;
                            }
                        }
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
                            <td>
                                <?= htmlspecialchars($p['correo'] ?? '—') ?>
                                <?php if (!empty($p['telefono'])): ?>
                                    <br><span class="cell-muted"><?= htmlspecialchars($p['telefono']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= $edad !== null ? $edad . ' anos' : '—' ?></td>
                            <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $p['genero'] ?? '—'))) ?></td>
                            <td><?= htmlspecialchars($p['tipo_sangre'] ?? '—') ?></td>
                            <td><?= date('d/m/Y', strtotime($p['creado_en'])) ?></td>
                            <td>
                                <div class="row-actions">
                                    <a class="action-btn action-view"
                                       href="<?= url('/panel/pacientes/' . (int) $p['id'] . '/expediente') ?>"
                                       title="Expediente clinico"
                                       aria-label="Expediente clinico de <?= htmlspecialchars($nombreCompleto) ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                                        </svg>
                                    </a>
                                    <a class="action-btn action-edit"
                                       href="<?= url('/panel/pacientes/' . (int) $p['id'] . '/editar') ?>"
                                       title="Editar"
                                       aria-label="Editar a <?= htmlspecialchars($nombreCompleto) ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>

                                    <?php if ($activo): ?>
                                        <form method="POST"
                                              action="<?= url('/panel/pacientes/' . (int) $p['id'] . '/eliminar') ?>"
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
                                              action="<?= url('/panel/pacientes/' . (int) $p['id'] . '/reactivar') ?>"
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
                    <a class="page-link" href="<?= url('/panel/pacientes?q=' . urlencode($q) . '&org_id=' . $orgFiltro . '&page=' . ($page - 1)) ?>">Anterior</a>
                <?php endif; ?>
                <span class="page-info">Pagina <?= $page ?> de <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="page-link" href="<?= url('/panel/pacientes?q=' . urlencode($q) . '&org_id=' . $orgFiltro . '&page=' . ($page + 1)) ?>">Siguiente</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
