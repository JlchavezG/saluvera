<?php
/**
 * SALUVERA - Horarios de atencion
 *
 * @version 2.31.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$horarios = $horarios ?? [];
$profesionales = $profesionales ?? [];
$profFiltro = $profFiltro ?? 0;
$esProfessional = $esProfessional ?? false;
$esSuperAdmin = $esSuperAdmin ?? false;
$dias = $dias ?? [];
?>
<div class="page-header">
    <div>
        <h2 class="page-title"><?= $esProfessional ? 'Mis Horarios' : 'Horarios de Atencion' ?></h2>
        <p class="page-subtitle">Franjas semanales en las que se pueden agendar citas</p>
    </div>
    <a href="<?= url('/panel/mis-horarios/nuevo') ?>" class="btn btn-primary">+ Nuevo Horario</a>
</div>

<?php if (!$esProfessional && !empty($profesionales)): ?>
<div class="table-card">
    <form method="GET" action="<?= url('/panel/mis-horarios') ?>" class="search-form">
        <select name="profesional_id" class="search-select" onchange="this.form.submit()">
            <option value="0">Selecciona un profesional...</option>
            <?php foreach ($profesionales as $p): ?>
                <option value="<?= (int) $p['id'] ?>" <?= $profFiltro == $p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(trim($p['nombre'] . ' ' . $p['apellidos'])) ?>
                    <?= !empty($p['org_nombre']) ? ' (' . htmlspecialchars($p['org_nombre']) . ')' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<?php endif; ?>

<div class="table-card">
    <?php if ($horarios === [] && !$esProfessional && $profFiltro === 0): ?>
        <div class="empty-state">
            <h3>Selecciona un profesional</h3>
            <p>Elige un profesional arriba para ver o gestionar sus horarios.</p>
        </div>
    <?php elseif (empty($horarios)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <h3>Sin horarios definidos</h3>
            <p>Mientras no haya horarios, se puede agendar en cualquier momento. Define tus franjas para restringir la agenda.</p>
            <a href="<?= url('/panel/mis-horarios/nuevo') ?>" class="btn btn-primary">Agregar horario</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Dia</th>
                        <th>Franja</th>
                        <th>Consultorio</th>
                        <th>Vigencia</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($horarios as $h): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($dias[$h['dia_semana']] ?? ('Dia ' . $h['dia_semana'])) ?></strong></td>
                            <td><span class="agenda-hora"><?= substr($h['hora_inicio'], 0, 5) ?> – <?= substr($h['hora_fin'], 0, 5) ?></span></td>
                            <td><?= htmlspecialchars($h['consultorio_nombre'] ?? '—') ?></td>
                            <td>
                                <?php if ((int) $h['es_recurrente'] === 1): ?>
                                    <span class="badge-estado badge-confirmada">Recurrente</span>
                                <?php else: ?>
                                    <?= htmlspecialchars(($h['valido_desde'] ?? '—') . ' a ' . ($h['valido_hasta'] ?? '—')) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int) $h['activo'] === 1): ?>
                                    <span class="badge-estado badge-completada">Activo</span>
                                <?php else: ?>
                                    <span class="badge-estado badge-cancelada">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="action-btn action-edit" href="<?= url('/panel/mis-horarios/' . (int) $h['id'] . '/editar') ?>" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="<?= url('/panel/mis-horarios/' . (int) $h['id'] . '/eliminar') ?>" class="inline-form" onsubmit="return confirm('Eliminar este horario?');">
                                        <?= Security::csrfField() ?>
                                        <button type="submit" class="action-btn action-delete" title="Eliminar">
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
