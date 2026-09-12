<?php
/**
 * SALUVERA - Portal: bandeja de notificaciones del paciente
 *
 * @version 2.33.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$notificaciones = $notificaciones ?? [];
$noLeidasIds = $noLeidasIds ?? [];
?>
<div class="portal-welcome">
    <h2>Mis Notificaciones</h2>
    <p>Avisos de tu clinica: citas, recordatorios y documentos.</p>
</div>

<?php if (empty($notificaciones)): ?>
    <div class="table-card empty-state">
        <h3>Sin notificaciones</h3>
        <p>Cuando tu clinica te envie avisos apareceran aqui.</p>
    </div>
<?php else: ?>
    <div class="portal-notif-list">
        <?php foreach ($notificaciones as $n): ?>
            <?php $esNueva = in_array((int) $n['id'], $noLeidasIds, true); ?>
            <div class="portal-notif-item<?= $esNueva ? ' portal-notif-nueva' : '' ?>">
                <div class="portal-notif-head">
                    <strong><?= htmlspecialchars($n['titulo']) ?></strong>
                    <?php if ($esNueva): ?><span class="portal-badge">Nueva</span><?php endif; ?>
                    <span class="cell-muted"><?= date('d/m/Y H:i', strtotime($n['creado_en'])) ?></span>
                </div>
                <p class="portal-notif-cuerpo"><?= nl2br(htmlspecialchars($n['cuerpo'] ?? '')) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
