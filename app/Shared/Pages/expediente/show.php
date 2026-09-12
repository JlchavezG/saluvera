<?php
/**
 * SALUVERA - Expediente Clinico del paciente
 *
 * @version 2.23.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$paciente = $paciente ?? [];
$expediente = $expediente ?? null;
$timeline = $timeline ?? [];
$diagnosticos = $diagnosticos ?? [];
$tratamientos = $tratamientos ?? [];
$documentos = $documentos ?? [];

$nombre = trim($paciente['nombre'] . ' ' . $paciente['apellidos']);

$edad = null;
if (!empty($paciente['fecha_nacimiento'])) {
    try {
        $edad = (new DateTime($paciente['fecha_nacimiento']))->diff(new DateTime('now'))->y;
    } catch (Exception $e) {
        $edad = null;
    }
}

$badgeConsulta = [
    'borrador' => 'badge-pendiente',
    'completada' => 'badge-completada',
    'firmada' => 'badge-confirmada',
];

$camposAntecedentes = [
    'alergias' => 'Alergias',
    'enfermedades_cronicas' => 'Enfermedades cronicas',
    'cirugias' => 'Cirugias previas',
    'antecedentes_familiares' => 'Antecedentes familiares',
    'habitos' => 'Habitos',
    'medicamentos_actuales' => 'Medicamentos actuales',
    'notas' => 'Notas del expediente',
];

$hayAntecedentes = false;
if ($expediente !== null) {
    foreach ($camposAntecedentes as $key => $label) {
        if (!empty($expediente[$key])) {
            $hayAntecedentes = true;
            break;
        }
    }
}
?>
<div class="page-header">
    <div>
        <h2 class="page-title"><?= htmlspecialchars($nombre) ?></h2>
        <p class="page-subtitle">
            <?= $edad !== null ? $edad . ' anos' : 'Edad no registrada' ?>
            <?php if (!empty($paciente['tipo_sangre'])): ?>
                &middot; Sangre <?= htmlspecialchars($paciente['tipo_sangre']) ?>
            <?php endif; ?>
            <?php if (!empty($paciente['telefono'])): ?>
                &middot; <?= htmlspecialchars($paciente['telefono']) ?>
            <?php endif; ?>
        </p>
    </div>
    <div class="page-header-actions">
        <a href="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/consulta/nueva') ?>" class="btn btn-primary">+ Nueva Consulta</a>
        <a href="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/expediente/editar') ?>" class="btn btn-outline">Editar antecedentes</a>
        <a href="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/documentos/nuevo') ?>" class="btn btn-outline">Subir documento</a>
        <form method="POST" action="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/portal-acceso') ?>" class="inline-form">
            <?= Security::csrfField() ?>
            <button type="submit" class="btn btn-outline">Acceso portal</button>
        </form>
        <a href="<?= url('/panel/pacientes') ?>" class="btn btn-outline">Volver</a>
    </div>
</div>


<?php
$portalLink = Session::get('portal_paciente_' . $paciente['id']) ?? null;
// Tambien reconstruir si el paciente ya tiene token valido
if ($portalLink === null && !empty($paciente['portal_token']) && !empty($paciente['portal_token_expira'])) {
    if (strtotime($paciente['portal_token_expira']) > time()) {
        $portalLink = url('/portal/acceso/' . $paciente['portal_token']);
    }
}
?>

<?php if ($portalLink !== null): ?>
<div class="table-card portal-access-card">
    <div class="portal-access-head">
        <strong>Acceso al portal del paciente</strong>
        <span class="cell-muted">Valido por 30 dias. Compartelo por WhatsApp o correo.</span>
    </div>
    <div class="portal-access-row">
        <input type="text" class="form-input" value="<?= htmlspecialchars($portalLink) ?>" readonly onclick="this.select()" id="portalLinkInput">
        <button type="button" class="btn btn-primary" onclick="
            var el = document.getElementById('portalLinkInput');
            el.select();
            document.execCommand('copy');
            this.textContent = 'Copiado';
            var b = this;
            setTimeout(function(){ b.textContent = 'Copiar enlace'; }, 2000);
        ">Copiar enlace</button>
        <form method="POST" action="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/portal-acceso') ?>" class="inline-form" style="margin:0">
            <?= Security::csrfField() ?>
            <button type="submit" class="btn btn-outline" onclick="return confirm('Generar un nuevo enlace invalidara el anterior?')">Regenerar</button>
        </form>
    </div>
</div>
<?php endif; ?>
<div class="exp-grid">
    <!-- ============ ANTECEDENTES ============ -->
    <div class="table-card exp-card">
        <h3 class="exp-card-title">Antecedentes</h3>

        <?php if (!$hayAntecedentes): ?>
            <p class="cell-muted">Sin antecedentes registrados.</p>
            <a href="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/expediente/editar') ?>" class="btn btn-outline">Capturar antecedentes</a>
        <?php else: ?>
            <?php foreach ($camposAntecedentes as $key => $label): ?>
                <?php if (!empty($expediente[$key])): ?>
                    <div class="exp-item">
                        <span class="exp-label"><?= htmlspecialchars($label) ?></span>
                        <span class="exp-value"><?= nl2br(htmlspecialchars($expediente[$key])) ?></span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ============ ACTIVOS: DX + TX ============ -->
    <div class="exp-col">
        <div class="table-card exp-card">
            <h3 class="exp-card-title">Diagnosticos activos</h3>
            <?php if (empty($diagnosticos)): ?>
                <p class="cell-muted">Sin diagnosticos activos.</p>
            <?php else: ?>
                <?php foreach ($diagnosticos as $dx): ?>
                    <div class="dx-item">
                        <div class="dx-main">
                            <?php if (!empty($dx['codigo_diagnostico'])): ?>
                                <span class="dx-code"><?= htmlspecialchars($dx['codigo_diagnostico']) ?></span>
                            <?php endif; ?>
                            <strong><?= htmlspecialchars($dx['nombre_diagnostico']) ?></strong>
                        </div>
                        <div class="dx-badges">
                            <?php if ((int) $dx['es_principal'] === 1): ?>
                                <span class="badge-estado badge-confirmada">Principal</span>
                            <?php endif; ?>
                            <?php if ((int) $dx['es_cronico'] === 1): ?>
                                <span class="badge-estado badge-pendiente">Cronico</span>
                            <?php endif; ?>
                            <span class="badge-estado badge-progreso"><?= htmlspecialchars(ucfirst($dx['estado'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="table-card exp-card">
            <h3 class="exp-card-title">Tratamientos activos</h3>
            <?php if (empty($tratamientos)): ?>
                <p class="cell-muted">Sin tratamientos activos.</p>
            <?php else: ?>
                <?php foreach ($tratamientos as $tx): ?>
                    <div class="exp-item">
                        <span class="exp-label"><?= htmlspecialchars($tx['nombre_tratamiento']) ?></span>
                        <span class="exp-value">
                            <?= htmlspecialchars(trim(($tx['dosis'] ?? '') . ' ' . ($tx['frecuencia'] ?? '') . ' ' . ($tx['duracion'] ?? ''))) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- ============ DOCUMENTOS ADJUNTOS ============ -->
<div class="dash-section-title">Documentos adjuntos (<?= count($documentos) ?>)</div>

<?php if (empty($documentos)): ?>
    <div class="table-card empty-state">
        <h3>Sin documentos adjuntos</h3>
        <p>Sube resultados de laboratorio, imagenes o informes al expediente.</p>
        <a href="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/documentos/nuevo') ?>" class="btn btn-primary">Subir primer documento</a>
    </div>
<?php else: ?>
    <div class="docs-grid">
        <?php foreach ($documentos as $d): ?>
            <?php
            $ext = strtolower(pathinfo($d['nombre_archivo'] ?? '', PATHINFO_EXTENSION));
            $tamano = $d['tamano_archivo'] ? round($d['tamano_archivo'] / 1024, 0) . ' KB' : '';
            ?>
            <div class="doc-card">
                <div class="doc-icon doc-icon-<?= htmlspecialchars(Documento::iconoPorMime($d['tipo_mime'] ?? '')) ?>">
                    <?= strtoupper($ext ?: '?') ?>
                </div>
                <div class="doc-info">
                    <a href="<?= url('/panel/documentos/' . (int) $d['id'] . '/ver') ?>" target="_blank" rel="noopener" class="doc-title">
                        <?= htmlspecialchars($d['titulo']) ?>
                    </a>
                    <div class="doc-meta">
                        <span class="doc-type"><?= htmlspecialchars(Documento::nombreTipo($d['tipo_documento'])) ?></span>
                        <?php if ($tamano !== ''): ?>
                            <span class="cell-muted">&middot; <?= $tamano ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="doc-meta cell-muted">
                        <?= date('d/m/Y', strtotime($d['subido_en'])) ?>
                        <?php if (!empty($d['subio_nombre'])): ?>
                            &middot; <?= htmlspecialchars(trim($d['subio_nombre'] . ' ' . $d['subio_apellidos'])) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="doc-actions">
                    <a class="action-btn action-edit"
                       href="<?= url('/panel/documentos/' . (int) $d['id'] . '/ver') ?>"
                       target="_blank" rel="noopener" title="Ver">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </a>
                    <form method="POST" action="<?= url('/panel/documentos/' . (int) $d['id'] . '/eliminar') ?>" class="inline-form" onsubmit="return confirm('Eliminar este documento del expediente?');">
                        <?= Security::csrfField() ?>
                        <button type="submit" class="action-btn action-delete" title="Eliminar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ============ LINEA DE TIEMPO ============ -->
<div class="dash-section-title">Historial de consultas (<?= count($timeline) ?>)</div>

<?php if (empty($timeline)): ?>
    <div class="table-card empty-state">
        <h3>Sin consultas registradas</h3>
        <p>Cuando completes citas y registres consultas medicas apareceran aqui.</p>
        <a href="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/consulta/nueva') ?>" class="btn btn-primary">Registrar primera consulta</a>
    </div>
<?php else: ?>
    <div class="timeline">
        <?php foreach ($timeline as $c): ?>
            <?php
            $vit = json_decode($c['signos_vitales'] ?? '', true);
            $vit = is_array($vit) ? $vit : [];
            ?>
            <div class="timeline-item">
                <div class="table-card timeline-body">
                    <div class="timeline-head">
                        <div>
                            <strong class="timeline-fecha"><?= date('d/m/Y H:i', strtotime($c['fecha_consulta'])) ?></strong>
                            <span class="cell-muted">&middot; <?= htmlspecialchars(trim($c['prof_nombre'] . ' ' . $c['prof_apellidos'])) ?></span>
                        </div>
                        <div class="timeline-head-right">
                            <span class="badge-estado <?= $badgeConsulta[$c['estado']] ?? 'badge-pendiente' ?>">
                                <?= htmlspecialchars(ucfirst($c['estado'])) ?>
                            </span>
                            <a href="<?= url('/panel/consultas/' . (int) $c['id'] . '/editar') ?>" class="btn btn-outline btn-sm">Abrir consulta</a>
                        </div>
                    </div>

                    <?php if (!empty($c['motivo'])): ?>
                        <p class="timeline-motivo"><?= htmlspecialchars($c['motivo']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($vit)): ?>
                        <div class="vital-row">
                            <?php if (!empty($vit['pa_sis']) && !empty($vit['pa_dia'])): ?>
                                <span class="vital-chip">PA <?= (int) $vit['pa_sis'] ?>/<?= (int) $vit['pa_dia'] ?></span>
                            <?php endif; ?>
                            <?php if (!empty($vit['fc'])): ?>
                                <span class="vital-chip">FC <?= (int) $vit['fc'] ?> lpm</span>
                            <?php endif; ?>
                            <?php if (!empty($vit['fr'])): ?>
                                <span class="vital-chip">FR <?= (int) $vit['fr'] ?> rpm</span>
                            <?php endif; ?>
                            <?php if (!empty($vit['temp'])): ?>
                                <span class="vital-chip">Temp <?= htmlspecialchars($vit['temp']) ?> °C</span>
                            <?php endif; ?>
                            <?php if (!empty($vit['peso'])): ?>
                                <span class="vital-chip">Peso <?= htmlspecialchars($vit['peso']) ?> kg</span>
                            <?php endif; ?>
                            <?php if (!empty($vit['talla'])): ?>
                                <span class="vital-chip">Talla <?= htmlspecialchars($vit['talla']) ?> cm</span>
                            <?php endif; ?>
                            <?php if (!empty($vit['spo2'])): ?>
                                <span class="vital-chip">SpO2 <?= (int) $vit['spo2'] ?>%</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="timeline-counts">
                        <span class="cell-muted"><?= (int) $c['total_diagnosticos'] ?> diagnostico(s)</span>
                        <span class="cell-muted"><?= (int) $c['total_tratamientos'] ?> tratamiento(s)</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
