<?php
/**
 * SALUVERA - Formulario de Paciente con selector de org
 *
 * @version 2.15.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$paciente = $paciente ?? null;
$organizaciones = $organizaciones ?? [];
$orgDestino = $orgDestino ?? 0;
$esSuperAdmin = $esSuperAdmin ?? false;
$errores = $errores ?? [];
$old = $old ?? [];
$esEdicion = $paciente !== null;

$flat = [];
foreach ($errores as $field => $msgs) {
    foreach ((array) $msgs as $m) {
        $flat[] = $m;
    }
}

$val = function ($key) use ($old, $paciente) {
    if (isset($old[$key]) && $old[$key] !== '') {
        return htmlspecialchars($old[$key]);
    }
    if ($paciente !== null && isset($paciente[$key]) && $paciente[$key] !== null) {
        return htmlspecialchars($paciente[$key]);
    }
    return '';
};

$sel = function ($key, $value) use ($old, $paciente) {
    $current = $old[$key] ?? ($paciente[$key] ?? null) ?? '';
    return $current === $value ? 'selected' : '';
};

$selOrg = function ($orgId) use ($old, $orgDestino) {
    $current = $old['organizacion_id'] ?? $orgDestino;
    return (string) $current === (string) $orgId ? 'selected' : '';
};

$accion = $esEdicion
    ? url('/panel/pacientes/' . (int) $paciente['id'] . '/editar')
    : url('/panel/pacientes');
?>
<div class="page-header">
    <div>
        <h2 class="page-title"><?= $esEdicion ? 'Editar Paciente' : 'Nuevo Paciente' ?></h2>
        <p class="page-subtitle">
            <?= $esEdicion
                ? 'Modificando a ' . htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellidos'])
                : 'Registra los datos del paciente' ?>
        </p>
    </div>
    <a href="<?= url('/panel/pacientes') ?>" class="btn btn-outline">Volver</a>
</div>

<?php if (!empty($flat)): ?>
    <div class="form-error-box" role="alert">
        <?= implode('<br>', array_map('htmlspecialchars', $flat)) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= $accion ?>" class="table-card form-card" novalidate>
    <?= Security::csrfField() ?>

    <?php if ($esSuperAdmin): ?>
        <div class="form-grid">
            <div class="form-group form-full">
                <label class="form-label" for="organizacion_id">Organizacion *</label>
                <select class="form-input" id="organizacion_id" name="organizacion_id" required>
                    <option value="">Selecciona la organizacion...</option>
                    <?php foreach ($organizaciones as $o): ?>
                        <option value="<?= (int) $o['id'] ?>" <?= $selOrg($o['id']) ?>><?= htmlspecialchars($o['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="cell-muted">La organizacion a la que pertenecera este paciente</small>
            </div>
        </div>
        <hr class="form-divider">
    <?php endif; ?>

    <div class="form-grid">
        <div class="form-group">
            <label class="form-label" for="nombre">Nombre *</label>
            <input class="form-input" type="text" id="nombre" name="nombre" value="<?= $val('nombre') ?>" required maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="apellidos">Apellidos *</label>
            <input class="form-input" type="text" id="apellidos" name="apellidos" value="<?= $val('apellidos') ?>" required maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="fecha_nacimiento">Fecha de nacimiento</label>
            <input class="form-input" type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= $val('fecha_nacimiento') ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="genero">Genero</label>
            <select class="form-input" id="genero" name="genero">
                <option value="">Selecciona...</option>
                <option value="masculino" <?= $sel('genero', 'masculino') ?>>Masculino</option>
                <option value="femenino" <?= $sel('genero', 'femenino') ?>>Femenino</option>
                <option value="otro" <?= $sel('genero', 'otro') ?>>Otro</option>
                <option value="prefiero_no_decir" <?= $sel('genero', 'prefiero_no_decir') ?>>Prefiero no decir</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="correo">Correo electronico</label>
            <input class="form-input" type="email" id="correo" name="correo" value="<?= $val('correo') ?>" maxlength="255">
        </div>

        <div class="form-group">
            <label class="form-label" for="telefono">Telefono</label>
            <input class="form-input" type="tel" id="telefono" name="telefono" value="<?= $val('telefono') ?>" placeholder="10 digitos" maxlength="50">
        </div>

        <div class="form-group">
            <label class="form-label" for="tipo_sangre">Tipo de sangre</label>
            <select class="form-input" id="tipo_sangre" name="tipo_sangre">
                <option value="">Selecciona...</option>
                <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $ts): ?>
                    <option value="<?= $ts ?>" <?= $sel('tipo_sangre', $ts) ?>><?= $ts ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="ciudad">Ciudad</label>
            <input class="form-input" type="text" id="ciudad" name="ciudad" value="<?= $val('ciudad') ?>" maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="ocupacion">Ocupacion</label>
            <input class="form-input" type="text" id="ocupacion" name="ocupacion" value="<?= $val('ocupacion') ?>" maxlength="200">
        </div>

        <div class="form-group form-full">
            <label class="form-label" for="notas">Notas</label>
            <textarea class="form-input" id="notas" name="notas" rows="3"><?= $val('notas') ?></textarea>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <?= $esEdicion ? 'Guardar cambios' : 'Guardar paciente' ?>
        </button>
        <a href="<?= url('/panel/pacientes') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>
