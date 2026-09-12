<?php
/**
 * SALUVERA - Listado de Organizaciones (multi-tenant)
 *
 * @version 2.14.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$orgs = $orgs ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$total = $total ?? 0;
$planes = $planes ?? [];
$estados = $estados ?? [];
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Organizaciones</h2>
        <p class="page-subtitle"><?= $total ?> organizacion(es) en la plataforma</p>
    </div>
    <a href="<?= url('/panel/organizaciones/nueva') ?>" class="btn btn-primary">+ Nueva Organizacion</a>
</div>

<div class="table-card">
    <?php if (empty($orgs)): ?>
        <div class="empty-state">
            <h3>No hay organizaciones</h3>
            <p>Crea la primera organizacion para comenzar.</p>
            <a href="<?= url('/panel/organizaciones/nueva') ?>" class="btn btn-primary">Crear organizacion</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Organizacion</th>
                        <th>Plan</th>
                        <th>Estado</th>
                        <th>Usuarios</th>
                        <th>Pacientes</th>
                        <th>Citas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orgs as $o): ?>
                        <?php $activo = (int) $o['activo'] === 1; ?>
                        <tr>
                            <td>
                                <div class="cell-name">
                                    <span class="avatar-mini"><?= strtoupper(substr($o['nombre'], 0, 1)) ?></span>
                                    <div>
                                        <strong><?= htmlspecialchars($o['nombre']) ?></strong>
                                        <?php if (!$activo): ?>
                                            <span class="badge-inactive">Suspendida</span>
                                        <?php endif; ?>
                                        <br><span class="cell-muted"><?= htmlspecialchars($o['slug']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge-plan"><?= htmlspecialchars($planes[$o['plan_suscripcion']] ?? $o['plan_suscripcion']) ?></span></td>
                            <td><span class="badge-estado badge-<?= htmlspecialchars($o['estado_suscripcion']) ?>"><?= htmlspecialchars($estados[$o['estado_suscripcion']] ?? $o['estado_suscripcion']) ?></span></td>
                            <td><?= (int) $o['total_usuarios'] ?></td>
                            <td><?= (int) $o['total_pacientes'] ?></td>
                            <td><?= (int) $o['total_citas'] ?></td>
                            <td>
                                <div class="row-actions">
                                    <a class="action-btn action-edit"
                                       href="<?= url('/panel/organizaciones/' . (int) $o['id'] . '/editar') ?>"
                                       title="Editar" aria-label="Editar <?= htmlspecialchars($o['nombre']) ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>

                                    <?php if ($activo): ?>
                                        <form method="POST"
                                              action="<?= url('/panel/organizaciones/' . (int) $o['id'] . '/suspender') ?>"
                                              class="inline-form"
                                              onsubmit="return confirm('Suspender <?= htmlspecialchars($o['nombre']) ?>? Los usuarios no podran acceder.');">
                                            <?= Security::csrfField() ?>
                                            <button type="submit" class="action-btn action-delete" title="Suspender" aria-label="Suspender <?= htmlspecialchars($o['nombre']) ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                                </svg>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST"
                                              action="<?= url('/panel/organizaciones/' . (int) $o['id'] . '/activar') ?>"
                                              class="inline-form"
                                              onsubmit="return confirm('Activar <?= htmlspecialchars($o['nombre']) ?>?');">
                                            <?= Security::csrfField() ?>
                                            <button type="submit" class="action-btn action-restore action-wide" title="Activar">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                                                </svg>Activar
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
                    <a class="page-link" href="<?= url('/panel/organizaciones?page=' . ($page - 1)) ?>">Anterior</a>
                <?php endif; ?>
                <span class="page-info">Pagina <?= $page ?> de <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="page-link" href="<?= url('/panel/organizaciones?page=' . ($page + 1)) ?>">Siguiente</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
