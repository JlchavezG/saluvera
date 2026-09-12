<?php
/**
 * SALUVERA - Bloqueos de agenda
 *
 * @version 2.27.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$bloqueos = $bloqueos ?? [];
$rol = $rol ?? '';
$esSuperAdmin = $esSuperAdmin ?? false;
$esProfessional = $esProfessional ?? false;

$hoy = date('Y-m-d');
?>
<div class="page-header">
    <div>
        <h2 class="page-title"><?= $esProfessional ? 'Mis Bloqueos de Agenda' : 'Bloqueos de Agenda' ?></h2>
        <p class="page-subtitle">
            <?= $esProfessional
                ? 'Dias en los que no estas disponible para citas'
                : 'Dias no disponibles por profesional' ?>
        </p>
    </div>
    <a href="<?= url('/panel/bloqueos/nuevo') ?>" class="btn btn-primary">+ Bloquear dias</a>
</div>

<div class="table-card">
    <?php if (empty($bloqueos)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            </svg>
            <h3>No hay bloqueos registrados</h3>
            <p>Cuando bloquees dias (vacaciones, congresos, asuntos personales), nadie podra agendar citas en ese periodo.</p>
            <a href="<?= url('/panel/bloqueos/nuevo') ?>" class="btn btn-primary">Bloquear dias</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Periodo</th>
                        <?php if (!$esProfessional): ?>
                            <th>Profesional</th>
                        <?php endif; ?>
                        <?php if ($esSuperAdmin): ?>
                            <th>Organizacion</th>
                        <?php endif; ?>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bloqueos as $b): ?>
                        <?php
                        if ($b['fecha_fin'] < $hoy) {
                            $estado = 'Pasado';
                            $badge = 'badge-cancelada';
                        } elseif ($b['fecha_inicio'] > $hoy) {
                            $estado = 'Proximo';
                            $badge = 'badge-pendiente';
                        } else {
                            $estado = 'En curso';
                            $badge = 'badge-progreso';
                        }
                        ?>
                        <tr>
                            <td>
                                <strong><?= date('d/m/Y', strtotime($b['fecha_inicio'])) ?></strong>
                                <?php if ($b['fecha_fin'] !== $b['fecha_inicio']): ?>
                                    <span class="cell-muted">a</span>
                                    <strong><?= date('d/m/Y', strtotime($b['fecha_fin'])) ?></strong>
                                <?php endif; ?>
                            </td>
                            <?php if (!$esProfessional): ?>
                                <td><?= htmlspecialchars(trim($b['prof_nombre'] . ' ' . $b['prof_apellidos'])) ?></td>
                            <?php endif; ?>
                            <?php if ($esSuperAdmin): ?>
                                <td><span class="badge-org"><?= htmlspecialchars($b['organizacion_nombre']) ?></span></td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($b['motivo'] ?? '—') ?></td>
                            <td><span class="badge-estado <?= $badge ?>"><?= $estado ?></span></td>
                            <td>
                                <div class="row-actions">
                                    <a class="action-btn action-edit"
                                       href="<?= url('/panel/bloqueos/' . (int) $b['id'] . '/editar') ?>"
                                       title="Editar" aria-label="Editar bloqueo">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>
                                    <form method="POST"
                                          action="<?= url('/panel/bloqueos/' . (int) $b['id'] . '/eliminar') ?>"
                                          class="inline-form"
                                          onsubmit="return confirm('Eliminar este bloqueo? El periodo volvera a estar disponible para citas.');">
                                        <?= Security::csrfField() ?>
                                        <button type="submit" class="action-btn action-delete" title="Eliminar" aria-label="Eliminar bloqueo">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
