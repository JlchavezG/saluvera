<?php
/**
 * SALUVERA - Portal: inicio
 *
 * @version 2.26.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$pacientePortal = $pacientePortal ?? [];
$proximas = $proximas ?? [];
$documentos = $documentos ?? [];
$diagnosticos = $diagnosticos ?? [];
$tratamientos = $tratamientos ?? [];

$nombrePila = htmlspecialchars($pacientePortal['nombre'] ?? 'Paciente');
$proxima = $proximas[0] ?? null;

$hora = (int) date('G');
$saludo = $hora < 12 ? 'Buenos dias' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
?>
<div class="portal-welcome">
    <h2><?= $saludo ?>, <?= $nombrePila ?></h2>
    <p>Este es tu espacio personal de salud.</p>
</div>

<div class="dash-stats">
    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-teal">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= count($proximas) ?></div>
        <div class="dash-stat-label">Proximas citas</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-mint">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= count($diagnosticos) ?></div>
        <div class="dash-stat-label">Diagnosticos activos</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-sand">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= count($tratamientos) ?></div>
        <div class="dash-stat-label">Tratamientos activos</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-icon icon-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
            </svg>
        </div>
        <div class="dash-stat-value"><?= count($documentos) ?></div>
        <div class="dash-stat-label">Documentos recientes</div>
    </div>
</div>

<?php if ($proxima !== null): ?>
    <div class="dash-section-title">Tu proxima cita</div>
    <div class="table-card proxima-cita">
        <div class="proxima-cita-fecha">
            <?= date('d/m/Y', strtotime($proxima['fecha_cita'])) ?>
            <strong><?= substr($proxima['hora_inicio'], 0, 5) ?></strong>
        </div>
        <div class="proxima-cita-datos">
            <strong><?= htmlspecialchars(trim(($proxima['prof_nombre'] ?? '') . ' ' . ($proxima['prof_apellidos'] ?? '')) ?: 'Por asignar') ?></strong>
            <span><?= htmlspecialchars($proxima['consultorio_nombre'] ?? 'Consultorio por confirmar') ?></span>
        </div>
        <span class="badge-estado badge-confirmada"><?= htmlspecialchars(ucfirst($proxima['estado'])) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($tratamientos)): ?>
    <div class="dash-section-title">Tus tratamientos activos</div>
    <div class="table-card exp-card">
        <?php foreach ($tratamientos as $tx): ?>
            <div class="exp-item">
                <span class="exp-label"><?= htmlspecialchars($tx['nombre_tratamiento']) ?></span>
                <span class="exp-value"><?= htmlspecialchars(trim(($tx['dosis'] ?? '') . ' ' . ($tx['frecuencia'] ?? '') . ' ' . ($tx['duracion'] ?? ''))) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($documentos)): ?>
    <div class="dash-section-title">Documentos recientes</div>
    <div class="docs-grid">
        <?php foreach (array_slice($documentos, 0, 3) as $d): ?>
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
                        <span class="cell-muted">&middot; <?= date('d/m/Y', strtotime($d['subido_en'])) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
