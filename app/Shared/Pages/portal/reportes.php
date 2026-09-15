<?php
if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$reportes = $reportes ?? [];
?>

<div class="portal-page-header">
    <div>
        <h2>Mis Reportes</h2>
        <p>Reportes clínicos que tu profesional ha compartido contigo.</p>
    </div>
</div>

<?php if (empty($reportes)): ?>
    <div class="portal-card">
        <div class="empty-state">
            <h3>No tienes reportes compartidos</h3>
            <p>Cuando tu profesional comparta un reporte firmado, aparecerá aquí.</p>
        </div>
    </div>
<?php else: ?>
    <div class="portal-reportes-grid">
        <?php foreach ($reportes as $r): ?>
            <article class="portal-reporte-card">
                <div class="portal-reporte-top">
                    <span class="portal-reporte-tipo"><?= htmlspecialchars($r['plantilla_nombre'] ?? 'Reporte clínico') ?></span>
                    <span class="badge-estado badge-completada">Firmado</span>
                </div>

                <h3><?= htmlspecialchars($r['titulo']) ?></h3>

                <div class="portal-reporte-meta">
                    <p><strong>Profesional:</strong> <?= htmlspecialchars($r['profesional_nombre'] ?? '') ?></p>
                    <?php if (!empty($r['especialidad_nombre'])): ?>
                        <p><strong>Especialidad:</strong> <?= htmlspecialchars($r['especialidad_nombre']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($r['firmado_en'])): ?>
                        <p><strong>Firmado:</strong> <?= date('d/m/Y H:i', strtotime($r['firmado_en'])) ?></p>
                    <?php endif; ?>
                </div>

                <a href="<?= url('/portal/reportes/' . $r['id'] . '/ver') ?>" class="btn btn-primary btn-block">
                    Ver / Descargar PDF
                </a>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
