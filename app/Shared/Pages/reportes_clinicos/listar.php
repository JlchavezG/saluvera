<?php
/**
 * SALUVERA - Lista de Reportes Generados
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$reportes = $reportes ?? [];
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Mis Reportes Generados</h2>
        <p class="page-subtitle">Historial de reportes creados para tus pacientes</p>
    </div>
    <a href="<?= url('/panel/reportes-clinicos') ?>" class="btn btn-primary">Nueva plantilla</a>
</div>

<div class="table-card">
    <?php if (empty($reportes)): ?>
        <div class="empty-state">
            <h3>Sin reportes todavía</h3>
            <p>Crea tu primer reporte desde las plantillas.</p>
            <a href="<?= url('/panel/reportes-clinicos') ?>" class="btn btn-primary">Ver plantillas</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Paciente</th>
                        <th>Título</th>
                        <th>Plantilla</th>
                        <th>Estado</th>
                        <th>Portal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportes as $r): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($r['creado_en'])) ?></td>
                            <td><?= htmlspecialchars($r['paciente_nombre'] ?? '') ?></td>
                            <td><strong><?= htmlspecialchars($r['titulo']) ?></strong></td>
                            <td><?= htmlspecialchars($r['plantilla_nombre'] ?? 'Personalizado') ?></td>
                            <td>
                                <span class="badge-estado <?= $r['estado'] === 'firmado' ? 'badge-completada' : ($r['estado'] === 'entregado' ? 'badge-confirmada' : 'badge-pendiente') ?>">
                                    <?= ucfirst($r['estado']) ?>
                                </span>
                            </td>
                            <td><?= (int) $r['compartido_portal'] === 1 ? '✓ Compartido' : '—' ?></td>
                            <td>
                                <div class="acciones-reporte"><?php if ($r['estado'] === 'borrador'): ?><a href="<?= url('/panel/reportes-clinicos/editar-reporte/' . $r['id']) ?>" class="btn btn-outline btn-sm btn-accion">Editar</a><?php endif; ?><a href="<?= url('/panel/reportes-clinicos/ver/' . $r['id']) ?>" class="btn btn-outline btn-sm btn-accion">Ver</a>
                                <?php 
                                    $msgElim = $r['estado'] === 'borrador' 
                                        ? '¿Eliminar este borrador?' 
                                        : '⚠️ Reporte FIRMADO. ¿Eliminar permanentemente?';
                                ?>
                                <form method="POST" action="<?= url('/panel/reportes-clinicos/eliminar-reporte') ?>" style="display:inline" onsubmit="return confirm('<?= $msgElim ?>')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm btn-accion btn-danger-text">Eliminar</button>
                                </form></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
