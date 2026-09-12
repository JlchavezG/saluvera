<?php
/**
 * SALUVERA - Portal: mi expediente (sin notas privadas)
 *
 * @version 2.26.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$detalle = $detalle ?? [];
$expediente = $expediente ?? null;
?>
<div class="portal-welcome">
    <h2>Mi Expediente</h2>
    <p>Resumen de tus consultas, diagnosticos e indicaciones.</p>
</div>

<?php if ($expediente !== null && !empty($expediente['alergias'])): ?>
    <div class="portal-alert">
        <strong>Alergias registradas:</strong> <?= nl2br(htmlspecialchars($expediente['alergias'])) ?>
    </div>
<?php endif; ?>

<?php if (empty($detalle)): ?>
    <div class="table-card empty-state">
        <h3>Aun no tienes consultas registradas</h3>
        <p>Cuando tu medico registre consultas podras verlas aqui.</p>
    </div>
<?php else: ?>
    <div class="timeline">
        <?php foreach ($detalle as $item): ?>
            <?php
            $c = $item['consulta'];
            $vit = $item['vitales'];
            $dxs = $item['diagnosticos'];
            $txs = $item['tratamientos'];
            ?>
            <div class="timeline-item">
                <div class="table-card timeline-body">
                    <div class="timeline-head">
                        <div>
                            <strong class="timeline-fecha"><?= date('d/m/Y', strtotime($c['fecha_consulta'])) ?></strong>
                            <span class="cell-muted">&middot; <?= htmlspecialchars(trim(($c['prof_nombre'] ?? '') . ' ' . ($c['prof_apellidos'] ?? ''))) ?></span>
                        </div>
                        <span class="badge-estado badge-completada"><?= htmlspecialchars(ucfirst($c['estado'])) ?></span>
                    </div>

                    <?php if (!empty($c['motivo'])): ?>
                        <p class="timeline-motivo"><strong>Motivo:</strong> <?= htmlspecialchars($c['motivo']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($vit)): ?>
                        <div class="vital-row">
                            <?php if (!empty($vit['pa_sis']) && !empty($vit['pa_dia'])): ?>
                                <span class="vital-chip">PA <?= (int) $vit['pa_sis'] ?>/<?= (int) $vit['pa_dia'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($vit['fc'])): ?><span class="vital-chip">FC <?= (int) $vit['fc'] ?></span><?php endif; ?>
                            <?php if (!empty($vit['temp'])): ?><span class="vital-chip">Temp <?= htmlspecialchars($vit['temp']) ?> °C</span><?php endif; ?>
                            <?php if (!empty($vit['peso'])): ?><span class="vital-chip">Peso <?= htmlspecialchars($vit['peso']) ?> kg</span><?php endif; ?>
                            <?php if (!empty($vit['talla'])): ?><span class="vital-chip">Talla <?= htmlspecialchars($vit['talla']) ?> cm</span><?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($c['evaluacion'])): ?>
                        <div class="exp-item">
                            <span class="exp-label">Impresion clinica</span>
                            <span class="exp-value"><?= nl2br(htmlspecialchars($c['evaluacion'])) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($dxs)): ?>
                        <div class="exp-item">
                            <span class="exp-label">Diagnosticos</span>
                            <?php foreach ($dxs as $dx): ?>
                                <span class="exp-value">
                                    <?php if (!empty($dx['codigo_diagnostico'])): ?>
                                        <span class="dx-code"><?= htmlspecialchars($dx['codigo_diagnostico']) ?></span>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($dx['nombre_diagnostico']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($c['plan'])): ?>
                        <div class="exp-item">
                            <span class="exp-label">Indicaciones</span>
                            <span class="exp-value"><?= nl2br(htmlspecialchars($c['plan'])) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($txs)): ?>
                        <div class="exp-item">
                            <span class="exp-label">Tratamientos</span>
                            <?php foreach ($txs as $tx): ?>
                                <span class="exp-value">
                                    <?= htmlspecialchars($tx['nombre_tratamiento']) ?>
                                    <?php if (!empty($tx['dosis'])): ?> &middot; <?= htmlspecialchars($tx['dosis']) ?><?php endif; ?>
                                    <?php if (!empty($tx['frecuencia'])): ?> &middot; <?= htmlspecialchars($tx['frecuencia']) ?><?php endif; ?>
                                    <?php if (!empty($tx['duracion'])): ?> &middot; <?= htmlspecialchars($tx['duracion']) ?><?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
