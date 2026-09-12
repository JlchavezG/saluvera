<?php
/**
 * SALUVERA - Formulario de horario
 *
 * @version 2.31.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$horario = $horario ?? null;
$profesionales = $profesionales ?? [];
$consultorios = $consultorios ?? [];
$dias = $dias ?? [];
$esProfessional = $esProfessional ?? false;
$errores = $errores ?? [];
$old = $old ?? [];
$esEdicion = $horario !== null;

$flat = [];
foreach ($errores as $field => $msgs) {
    foreach ((array) $msgs as $m) {
        $flat[] = $m;
    }
}

$val = function ($key, $default = '') use ($old, $horario) {
    if (isset($old[$key]) && $old[$key] !== '') {
        return htmlspecialchars($old[$key]);
    }
    if ($horario !== null && isset($horario[$key]) && $horario[$key] !== null) {
        return htmlspecialchars($horario[$key]);
    }
    return htmlspecialchars($default);
};

$accion = $esEdicion ? url('/panel/mis-horarios/' . (int) $horario['id'] . '/editar') : url('/panel/mis-horarios');
$recurrente = $esEdicion ? (int) $horario['es_recurrente'] === 1 : true;
?>
<div class="page-header">
    <div>
        <h2 class="page-title"><?= $esEdicion ? 'Editar Horario' : 'Nuevo Horario' ?></h2>
        <p class="page-subtitle">Define la franja horaria de atencion</p>
    </div>
    <a href="<?= url('/panel/mis-horarios') ?>" class="btn btn-outline">Volver</a>
</div>

<?php if (!empty($flat)): ?>
    <div class="form-error-box" role="alert">
        <?= implode('<br>', array_map('htmlspecialchars', $flat)) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= $accion ?>" class="table-card form-card" novalidate>
    <?= Security::csrfField() ?>

    <div class="form-grid">
        <?php if (!$esProfessional): ?>
            <div class="form-group form-full">
                <label class="form-label" for="profesional_id">Profesional *</label>
                <select class="form-input" id="profesional_id" name="profesional_id" required <?= $esEdicion ? 'disabled' : '' ?>>
                    <option value="">Selecciona...</option>
                    <?php foreach ($profesionales as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= (string) $val('profesional_id') === (string) $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars(trim($p['nombre'] . ' ' . $p['apellidos'])) ?>
                            <?= !empty($p['org_nombre']) ? ' (' . htmlspecialchars($p['org_nombre']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($esEdicion): ?>
                    <input type="hidden" name="profesional_id" value="<?= (int) $horario['profesional_id'] ?>">
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label" for="dia_semana">Dia de la semana *</label>
            <select class="form-input" id="dia_semana" name="dia_semana" required>
                <option value="">Selecciona...</option>
                <?php foreach ($dias as $num => $nombre): ?>
                    <option value="<?= $num ?>" <?= (string) $val('dia_semana') === (string) $num ? 'selected' : '' ?>><?= htmlspecialchars($nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="consultorio_id">Consultorio (opcional)</label>
            <select class="form-input" id="consultorio_id" name="consultorio_id">
                <option value="">Sin consultorio fijo</option>
                <?php foreach ($consultorios as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (string) $val('consultorio_id') === (string) $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="hora_inicio">Hora inicio *</label>
            <input class="form-input" type="time" id="hora_inicio" name="hora_inicio" value="<?= $val('hora_inicio') ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="hora_fin">Hora fin *</label>
            <input class="form-input" type="time" id="hora_fin" name="hora_fin" value="<?= $val('hora_fin') ?>" required>
        </div>

        <div class="form-group">
            <label class="form-check">
                <input type="hidden" name="es_recurrente" value="0">
                <input type="checkbox" name="es_recurrente" value="1" <?= $recurrente ? 'checked' : '' ?>>
                Horario recurrente (todas las semanas)
            </label>
        </div>

        <div class="form-group">
            <label class="form-label" for="valido_desde">Valido desde (si no es recurrente)</label>
            <input class="form-input" type="date" id="valido_desde" name="valido_desde" value="<?= $val('valido_desde') ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="valido_hasta">Valido hasta (si no es recurrente)</label>
            <input class="form-input" type="date" id="valido_hasta" name="valido_hasta" value="<?= $val('valido_hasta') ?>">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Agregar horario' ?></button>
        <a href="<?= url('/panel/mis-horarios') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>
