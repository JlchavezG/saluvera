<?php
/**
 * SALUVERA - Generador de Reportes Clínicos (UX V2)
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$plantilla = $plantilla ?? null;
$estructura = $estructura ?? [];
$paciente = $paciente ?? null;
$pacientes = $pacientes ?? [];
$plantillaId = $plantillaId ?? 0;
$pacienteId = $pacienteId ?? 0;

$totalPacientes = count($pacientes);
$reporteEditando = $reporteEditando ?? null;
$contenidoExistente = $contenidoExistente ?? [];
$modoEdicion = $reporteEditando !== null;

// Icono por tipo
$iconosTipo = [
    'informe' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
    'evolucion' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
    'evaluacion' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
    'certificado' => '<circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>',
    'justificante' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
    'consentimiento' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    'receta' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7z"/>',
    'referencia' => '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>',
    'otro' => '<circle cx="12" cy="12" r="10"/>',
];
?>

<div class="page-header">
    <div>
        <h2 class="page-title"><?= $modoEdicion ? 'Editar Reporte' : 'Crear Reporte' ?></h2>
        <p class="page-subtitle">Paso 1: Paciente · Paso 2: Llenar · Paso 3: Firmar</p>
    </div>
    <a href="<?= url('/panel/reportes-clinicos') ?>" class="btn btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Volver a plantillas
    </a>
</div>

<?php if (!$plantilla): ?>
    <div class="table-card">
        <div class="empty-state">
            <h3>Sin plantilla seleccionada</h3>
            <p>Elige primero una plantilla desde el catálogo.</p>
            <a href="<?= url('/panel/reportes-clinicos') ?>" class="btn btn-primary">Ver plantillas</a>
        </div>
    </div>
    <?php return; ?>
<?php endif; ?>

<!-- TARJETA DE PLANTILLA ACTIVA -->
<div class="plantilla-activa-card">
    <div class="plantilla-activa-top">
        <div class="plantilla-activa-info">
            <div class="plantilla-activa-icono tipo-bg-<?= htmlspecialchars($plantilla['tipo'] ?? 'otro') ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <?= $iconosTipo[$plantilla['tipo']] ?? $iconosTipo['otro'] ?>
                </svg>
            </div>
            <div>
                <div class="plantilla-activa-nombre"><?= htmlspecialchars($plantilla['nombre']) ?></div>
                <div class="plantilla-activa-tipo"><?= htmlspecialchars($plantilla['descripcion'] ?? '') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- PASO 1: SELECCIONAR PACIENTE -->
<div class="table-card generador-paso">
    <div class="generador-paso-header">
        <div class="generador-paso-numero <?= $pacienteId ? 'paso-completado' : 'paso-activo' ?>">1</div>
        <div class="generador-paso-titulo">
            <h3>Selecciona el paciente</h3>
            <?php if ($totalPacientes > 0): ?>
                <p class="generador-paso-hint">
                    <?= $totalPacientes ?> paciente<?= $totalPacientes > 1 ? 's' : '' ?> disponible<?= $totalPacientes > 1 ? 's' : '' ?> en tu clínica
                </p>
            <?php else: ?>
                <p class="generador-paso-hint generador-paso-error">
                    ⚠ No hay pacientes registrados. <a href="<?= url('/panel/pacientes') ?>">Agrega uno desde Pacientes</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <form method="GET" action="<?= url('/panel/reportes-clinicos/crear') ?>" class="generador-paciente-form">
        <input type="hidden" name="plantilla_id" value="<?= $plantillaId ?>">
        <div class="form-group" style="margin-bottom:0;flex:1">
            <select name="paciente_id" class="search-select" style="font-size:15px;padding:14px" onchange="svSubmit(this.form)">
                <option value="">-- Selecciona un paciente --</option>
                <?php foreach ($pacientes as $pac): ?>
                    <option value="<?= $pac['id'] ?>" <?= $pacienteId == $pac['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(trim($pac['nombre'] . ' ' . $pac['apellidos'])) ?>
                        <?php if ($pac['fecha_nacimiento']): ?>
                            · <?= date('Y') - (int) date('Y', strtotime($pac['fecha_nacimiento'])) ?> años
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<?php if (!$paciente): ?>
    <?php return; ?>
<?php endif; ?>

<!-- FORMULARIO DEL REPORTE -->
<form method="POST" action="<?= url('/panel/reportes-clinicos/guardar') ?>" id="formReporte">
        <?php if ($modoEdicion): ?><input type="hidden" name="reporte_id" value="<?= (int) $reporteEditando['id'] ?>"><?php endif; ?>
    <?= csrf_field() ?>
    <input type="hidden" name="plantilla_id" value="<?= $plantillaId ?>">
    <input type="hidden" name="paciente_id" value="<?= $pacienteId ?>">

    <!-- PASO 2: DATOS DEL PACIENTE -->
    <div class="table-card generador-paso">
        <div class="generador-paso-header">
            <div class="generador-paso-numero paso-completado">2</div>
            <div class="generador-paso-titulo">
                <h3>Datos del reporte</h3>
                <p class="generador-paso-hint">Los datos del paciente se cargan automáticamente</p>
            </div>
        </div>

        <div class="generador-datos-paciente">
            <div class="generador-dato">
                <span class="dato-label">Paciente</span>
                <span class="dato-valor"><?= htmlspecialchars(trim($paciente['nombre'] . ' ' . $paciente['apellidos'])) ?></span>
            </div>
            <?php if (!empty($paciente['edad'])): ?>
                <div class="generador-dato">
                    <span class="dato-label">Edad</span>
                    <span class="dato-valor"><?= (int) $paciente['edad'] ?> años</span>
                </div>
            <?php endif; ?>
            <?php if (!empty($paciente['genero'])): ?>
                <div class="generador-dato">
                    <span class="dato-label">Género</span>
                    <span class="dato-valor"><?= ucfirst(htmlspecialchars($paciente['genero'])) ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($paciente['fecha_nacimiento'])): ?>
                <div class="generador-dato">
                    <span class="dato-label">Fecha nacimiento</span>
                    <span class="dato-valor"><?= date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group" style="margin-top:16px">
            <label class="form-label">Título del reporte *</label>
            <input type="text" name="titulo" class="form-input" required 
                   value="<?= htmlspecialchars($modoEdicion ? $reporteEditando['titulo'] : ($plantilla['nombre'] . ' - ' . trim($paciente['nombre'] . ' ' . $paciente['apellidos']) . ' - ' . date('d/m/Y'))) ?>">
        </div>
    </div>

    <!-- PASO 3: SECCIONES DEL REPORTE -->
    <?php if (!empty($estructura['secciones'])): ?>
    <div class="table-card generador-paso">
        <div class="generador-paso-header">
            <div class="generador-paso-numero paso-activo">3</div>
            <div class="generador-paso-titulo">
                <h3>Contenido del reporte</h3>
                <p class="generador-paso-hint">Completa las secciones requeridas. Las variables como <code>{{nombre_paciente}}</code>, <code>{{fecha}}</code>, <code>{{edad}}</code> se reemplazan automáticamente.</p>
            </div>
        </div>

        <?php foreach ($estructura['secciones'] as $seccion): ?>
            <div class="form-group generador-seccion">
                <label class="form-label generador-label">
                    <?= htmlspecialchars($seccion['titulo']) ?>
                    <?php if (!empty($seccion['requerido'])): ?><span class="generador-requerido">*</span><?php endif; ?>
                </label>
                
                <?php if (($seccion['tipo'] ?? 'texto_largo') === 'texto_largo'): ?>
                    <textarea name="contenido[<?= htmlspecialchars($seccion['id']) ?>]" 
                              class="form-input generador-textarea" rows="5" 
                              <?= !empty($seccion['requerido']) ? 'required' : '' ?>
                              placeholder="<?= htmlspecialchars($seccion['placeholder'] ?? '') ?>"><?= $modoEdicion ? htmlspecialchars($contenidoExistente[$seccion['id']] ?? '') : '' ?></textarea>
                <?php elseif (($seccion['tipo'] ?? '') === 'fecha'): ?>
                    <input type="date" name="contenido[<?= htmlspecialchars($seccion['id']) ?>]" 
                           class="form-input" 
                           <?= !empty($seccion['requerido']) ? 'required' : '' ?>
                           value="<?= date('Y-m-d') ?>">
                <?php else: ?>
                    <input type="text" name="contenido[<?= htmlspecialchars($seccion['id']) ?>]" 
                           class="form-input" 
                           <?= !empty($seccion['requerido']) ? 'required' : '' ?>
                           placeholder="<?= htmlspecialchars($seccion['placeholder'] ?? '') ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ACCIONES FINALES -->
    <div class="table-card generador-acciones">
        <div class="generador-acciones-info">
            <p>¿Listo para generar el reporte?</p>
            <small>Puedes guardar como borrador y firmarlo después, o firmarlo directamente.</small>
        </div>
        <div class="generador-acciones-botones">
            <button type="submit" name="firmar" value="0" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Guardar borrador
            </button>
            <button type="submit" name="firmar" value="1" class="btn btn-primary" onclick="return confirm('Al firmar el reporte ya no podrá editarse. ¿Continuar?')">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                Firmar y finalizar
            </button>
        </div>
    </div>
</form>
