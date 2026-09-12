<?php
/**
 * SALUVERA - Consulta medica (nota SOAP)
 *
 * @version 2.24.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$paciente = $paciente ?? [];
$cita = $cita ?? null;
$consulta = $consulta ?? null;
$profesionales = $profesionales ?? [];
$profesionalUnico = $profesionalUnico ?? null;
$diagnosticos = $diagnosticos ?? [];
$tratamientos = $tratamientos ?? [];
$valores = $valores ?? [];
$esProfessional = $esProfessional ?? false;
$errores = $errores ?? [];
$old = $old ?? [];

$esEdicion = $consulta !== null;
$firmada = $esEdicion && $consulta['estado'] === 'firmada';
$nombrePaciente = trim($paciente['nombre'] . ' ' . $paciente['apellidos']);

$flat = [];
foreach ($errores as $field => $msgs) {
    foreach ((array) $msgs as $m) {
        $flat[] = $m;
    }
}

$val = function ($key, $default = '') use ($old, $valores) {
    if (isset($old[$key]) && $old[$key] !== '') {
        return htmlspecialchars($old[$key]);
    }
    if (isset($valores[$key]) && $valores[$key] !== null) {
        return htmlspecialchars($valores[$key]);
    }
    return htmlspecialchars($default);
};

$accion = $esEdicion
    ? url('/panel/consultas/' . (int) $consulta['id'] . '/editar')
    : url('/panel/consultas');

$vuelta = $esEdicion
    ? url('/panel/pacientes/' . (int) $consulta['paciente_id'] . '/expediente')
    : url('/panel/pacientes/' . (int) $paciente['id'] . '/expediente');
?>
<div class="page-header">
    <div>
        <h2 class="page-title"><?= $esEdicion ? 'Consulta del ' . date('d/m/Y', strtotime($consulta['fecha_consulta'])) : 'Nueva Consulta' ?></h2>
        <p class="page-subtitle">
            <?= htmlspecialchars($nombrePaciente) ?>
            <?php if ($esEdicion): ?>
                &middot; Estado: <strong><?= htmlspecialchars(ucfirst($consulta['estado'])) ?></strong>
            <?php endif; ?>
            <?php if ($firmada): ?>
                &middot; <strong>Firmada (solo lectura clinica)</strong>
            <?php endif; ?>
        </p>
    </div>
    <a href="<?= $vuelta ?>" class="btn btn-outline">Ver expediente</a>
</div>

<?php if (!empty($flat)): ?>
    <div class="form-error-box" role="alert">
        <?= implode('<br>', array_map('htmlspecialchars', $flat)) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= $accion ?>" class="table-card form-card" novalidate>
    <?= Security::csrfField() ?>
    <input type="hidden" name="paciente_id" value="<?= (int) $paciente['id'] ?>">
    <?php if ($cita !== null): ?>
        <input type="hidden" name="cita_id" value="<?= (int) $cita['id'] ?>">
    <?php endif; ?>

    <!-- ============ PROFESIONAL ============ -->
    <div class="form-section-title">Profesional que atiende</div>
    <div class="form-grid">
        <div class="form-group form-full">
            <label class="form-label" for="profesional_id">Profesional *</label>
            <?php if ($esProfessional && $profesionalUnico !== null): ?>
                <input class="form-input" type="text" value="<?= htmlspecialchars($profesionalUnico['nombre'] . ' ' . $profesionalUnico['apellidos']) ?> (tu)" disabled>
                <input type="hidden" name="profesional_id" value="<?= (int) $profesionalUnico['id'] ?>">
            <?php else: ?>
                <select class="form-input" id="profesional_id" name="profesional_id" required <?= $firmada ? 'disabled' : '' ?>>
                    <option value="">Selecciona el profesional...</option>
                    <?php foreach ($profesionales as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= (string) $val('profesional_id') === (string) $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============ SIGNOS VITALES ============ -->
    <div class="form-section-title">Signos vitales</div>
    <div class="form-grid vitales-grid">
        <div class="form-group">
            <label class="form-label" for="pa_sis">PA sistolica</label>
            <input class="form-input" type="number" id="pa_sis" name="pa_sis" value="<?= $val('pa_sis') ?>" placeholder="120" <?= $firmada ? 'disabled' : '' ?>>
        </div>
        <div class="form-group">
            <label class="form-label" for="pa_dia">PA diastolica</label>
            <input class="form-input" type="number" id="pa_dia" name="pa_dia" value="<?= $val('pa_dia') ?>" placeholder="80" <?= $firmada ? 'disabled' : '' ?>>
        </div>
        <div class="form-group">
            <label class="form-label" for="fc">FC (lpm)</label>
            <input class="form-input" type="number" id="fc" name="fc" value="<?= $val('fc') ?>" placeholder="72" <?= $firmada ? 'disabled' : '' ?>>
        </div>
        <div class="form-group">
            <label class="form-label" for="fr">FR (rpm)</label>
            <input class="form-input" type="number" id="fr" name="fr" value="<?= $val('fr') ?>" placeholder="16" <?= $firmada ? 'disabled' : '' ?>>
        </div>
        <div class="form-group">
            <label class="form-label" for="temp">Temp (°C)</label>
            <input class="form-input" type="number" step="0.1" id="temp" name="temp" value="<?= $val('temp') ?>" placeholder="36.5" <?= $firmada ? 'disabled' : '' ?>>
        </div>
        <div class="form-group">
            <label class="form-label" for="spo2">SpO2 (%)</label>
            <input class="form-input" type="number" id="spo2" name="spo2" value="<?= $val('spo2') ?>" placeholder="98" <?= $firmada ? 'disabled' : '' ?>>
        </div>
        <div class="form-group">
            <label class="form-label" for="peso">Peso (kg)</label>
            <input class="form-input" type="number" step="0.1" id="peso" name="peso" value="<?= $val('peso') ?>" placeholder="70.5" <?= $firmada ? 'disabled' : '' ?>>
        </div>
        <div class="form-group">
            <label class="form-label" for="talla">Talla (cm)</label>
            <input class="form-input" type="number" id="talla" name="talla" value="<?= $val('talla') ?>" placeholder="170" <?= $firmada ? 'disabled' : '' ?>>
        </div>
    </div>

    <!-- ============ NOTA SOAP ============ -->
    <div class="form-section-title">Nota clinica (SOAP)</div>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label" for="motivo">Motivo de consulta</label>
            <input class="form-input" type="text" id="motivo" name="motivo" value="<?= $val('motivo') ?>" maxlength="500" <?= $firmada ? 'disabled' : '' ?>>
        </div>
        <div class="form-group">
            <label class="form-label" for="subjetivo">Subjetivo</label>
            <textarea class="form-input" id="subjetivo" name="subjetivo" rows="4" placeholder="Lo que el paciente refiere: sintomas, tiempo de evolucion..." <?= $firmada ? 'disabled' : '' ?>><?= $val('subjetivo') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="objetivo">Objetivo</label>
            <textarea class="form-input" id="objetivo" name="objetivo" rows="4" placeholder="Hallazgos objetivos: exploracion, signos..." <?= $firmada ? 'disabled' : '' ?>><?= $val('objetivo') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="evaluacion">Evaluacion</label>
            <textarea class="form-input" id="evaluacion" name="evaluacion" rows="4" placeholder="Impresion diagnostica / analisis..." <?= $firmada ? 'disabled' : '' ?>><?= $val('evaluacion') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="plan">Plan</label>
            <textarea class="form-input" id="plan" name="plan" rows="4" placeholder="Plan de manejo: estudios, tratamientos, proxima cita..." <?= $firmada ? 'disabled' : '' ?>><?= $val('plan') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="exploracion_fisica">Exploracion fisica</label>
            <textarea class="form-input" id="exploracion_fisica" name="exploracion_fisica" rows="4" placeholder="Exploracion por aparatos y sistemas..." <?= $firmada ? 'disabled' : '' ?>><?= $val('exploracion_fisica') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="evolucion">Evolucion</label>
            <textarea class="form-input" id="evolucion" name="evolucion" rows="3" placeholder="Evolucion del cuadro..." <?= $firmada ? 'disabled' : '' ?>><?= $val('evolucion') ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label" for="notas_privadas">Notas privadas</label>
            <textarea class="form-input" id="notas_privadas" name="notas_privadas" rows="3" placeholder="Notas internas del profesional (no visibles para el paciente)" <?= $firmada ? 'disabled' : '' ?>><?= $val('notas_privadas') ?></textarea>
        </div>
    </div>

    <?php if (!$firmada): ?>
        <div class="form-actions">
            <button type="submit" name="accion" value="completada" class="btn btn-primary">Guardar y completar</button>
            <button type="submit" name="accion" value="borrador" class="btn btn-outline">Guardar borrador</button>
            <a href="<?= $vuelta ?>" class="btn btn-outline">Cancelar</a>
        </div>
    <?php endif; ?>
</form>

<?php if ($esEdicion): ?>
    <!-- ============ DIAGNOSTICOS ============ -->
    <div class="dash-section-title">Diagnosticos de esta consulta</div>
    <div class="table-card exp-card">
        <?php if (empty($diagnosticos)): ?>
            <p class="cell-muted">Sin diagnosticos registrados.</p>
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

        <?php if (!$firmada): ?>
            <form method="POST" action="<?= url('/panel/consultas/' . (int) $consulta['id'] . '/diagnosticos') ?>" class="subform">
                <?= Security::csrfField() ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="codigo_diagnostico">Codigo (CIE-10)</label>
                        <input class="form-input" type="text" id="codigo_diagnostico" name="codigo_diagnostico" maxlength="20" placeholder="Ej: E11.9">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="nombre_diagnostico">Nombre del diagnostico *</label>
                        <input class="form-input" type="text" id="nombre_diagnostico" name="nombre_diagnostico" maxlength="255" required placeholder="Ej: Diabetes mellitus tipo 2">
                    </div>
                    <div class="form-group form-full">
                        <label class="form-label" for="descripcion">Descripcion</label>
                        <input class="form-input" type="text" id="descripcion" name="descripcion" maxlength="500">
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="hidden" name="es_principal" value="0">
                            <input type="checkbox" name="es_principal" value="1"> Diagnostico principal
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="hidden" name="es_cronico" value="0">
                            <input type="checkbox" name="es_cronico" value="1"> Enfermedad cronica
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline">Agregar diagnostico</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- ============ TRATAMIENTOS ============ -->
    <div class="dash-section-title">Tratamientos indicados</div>
    <div class="table-card exp-card">
        <?php if (empty($tratamientos)): ?>
            <p class="cell-muted">Sin tratamientos registrados.</p>
        <?php else: ?>
            <?php foreach ($tratamientos as $tx): ?>
                <div class="exp-item">
                    <span class="exp-label"><?= htmlspecialchars($tx['nombre_tratamiento']) ?></span>
                    <span class="exp-value">
                        <?= htmlspecialchars(trim(($tx['dosis'] ?? '') . ' | ' . ($tx['frecuencia'] ?? '') . ' | ' . ($tx['duracion'] ?? ''))) ?>
                        <span class="badge-estado badge-progreso"><?= htmlspecialchars(ucfirst($tx['estado'])) ?></span>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!$firmada): ?>
            <form method="POST" action="<?= url('/panel/consultas/' . (int) $consulta['id'] . '/tratamientos') ?>" class="subform">
                <?= Security::csrfField() ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="nombre_tratamiento">Tratamiento / medicamento *</label>
                        <input class="form-input" type="text" id="nombre_tratamiento" name="nombre_tratamiento" maxlength="255" required placeholder="Ej: Metformina">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="dosis">Dosis</label>
                        <input class="form-input" type="text" id="dosis" name="dosis" maxlength="200" placeholder="Ej: 850 mg">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="frecuencia">Frecuencia</label>
                        <input class="form-input" type="text" id="frecuencia" name="frecuencia" maxlength="200" placeholder="Ej: cada 12 horas">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="duracion">Duracion</label>
                        <input class="form-input" type="text" id="duracion" name="duracion" maxlength="200" placeholder="Ej: 30 dias">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="via_administracion">Via</label>
                        <input class="form-input" type="text" id="via_administracion" name="via_administracion" maxlength="200" placeholder="Ej: oral">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="fecha_inicio">Fecha inicio</label>
                        <input class="form-input" type="date" id="fecha_inicio" name="fecha_inicio">
                    </div>
                    <div class="form-group form-full">
                        <label class="form-label" for="instrucciones">Instrucciones</label>
                        <input class="form-input" type="text" id="instrucciones" name="instrucciones" maxlength="500" placeholder="Ej: tomar con alimentos">
                    </div>
                </div>
                <button type="submit" class="btn btn-outline">Agregar tratamiento</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (!$firmada && $consulta['estado'] === 'completada'): ?>
        <form method="POST" action="<?= url('/panel/consultas/' . (int) $consulta['id'] . '/firmar') ?>" class="table-card form-card"
              onsubmit="return confirm('Firmar la consulta? Despues no podra editarse.');">
            <?= Security::csrfField() ?>
            <div class="form-actions" style="position:static;border:none;margin:0;padding:0">
                <button type="submit" class="btn btn-primary">Firmar consulta</button>
                <span class="cell-muted">Al firmar, la consulta queda bloqueada para edicion.</span>
            </div>
        </form>
    <?php endif; ?>
<?php endif; ?>
