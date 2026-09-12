<?php
/**
 * SALUVERA - Portal: mis documentos
 *
 * @version 2.26.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$documentos = $documentos ?? [];
?>
<div class="portal-welcome">
    <h2>Mis Documentos</h2>
    <p>Resultados, imagenes e informes compartidos por tu clinica.</p>
</div>

<?php if (empty($documentos)): ?>
    <div class="table-card empty-state">
        <h3>Sin documentos</h3>
        <p>Cuando tu clinica comparta documentos contigo apareceran aqui.</p>
    </div>
<?php else: ?>
    <div class="docs-grid">
        <?php foreach ($documentos as $d): ?>
            <div class="doc-card">
                <div class="doc-icon doc-icon-<?= htmlspecialchars(Documento::iconoPorMime($d['tipo_mime'] ?? '')) ?>">
                    <?= strtoupper(pathinfo($d['nombre_archivo'] ?? '', PATHINFO_EXTENSION) ?: '?') ?>
                </div>
                <div class="doc-info">
                    <a href="<?= url('/portal/documentos/' . (int) $d['id'] . '/ver') ?>" target="_blank" rel="noopener" class="doc-title">
                        <?= htmlspecialchars($d['titulo']) ?>
                    </a>
                    <div class="doc-meta">
                        <span class="doc-type"><?= htmlspecialchars(Documento::nombreTipo($d['tipo_documento'])) ?></span>
                    </div>
                    <div class="doc-meta cell-muted">
                        <?= date('d/m/Y', strtotime($d['subido_en'])) ?>
                        <?php if (!empty($d['descripcion'])): ?>
                            &middot; <?= htmlspecialchars(mb_strimwidth($d['descripcion'], 0, 60, '...')) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="doc-actions">
                    <a class="action-btn action-edit"
                       href="<?= url('/portal/documentos/' . (int) $d['id'] . '/ver') ?>"
                       target="_blank" rel="noopener" title="Ver documento">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
