<?php
/**
 * SALUVERA - Formulario de Profesional con selector de org
 *
 * @version 2.15.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$profesional = $profesional ?? null;
$especialidades = $especialidades ?? [];
$organizaciones = $organizaciones ?? [];
$orgDestino = $orgDestino ?? 0;
$esSuperAdmin = $esSuperAdmin ?? false;
$errores = $errores ?? [];
$old = $old ?? [];
$esEdicion = $profesional !== null;

$flat = [];
foreach ($errores as $field => $msgs) {
    foreach ((array) $msgs as $m) {
        $flat[] = $m;
    }
}

$val = function ($key) use ($old, $profesional) {
    if (isset($old[$key]) && $old[$key] !== '') {
        return htmlspecialchars($old[$key]);
    }
    if ($profesional !== null && isset($profesional[$key]) && $profesional[$key] !== null) {
        return htmlspecialchars($profesional[$key]);
    }
    return '';
};

$selEsp = function ($espId) use ($old, $profesional) {
    $current = $old['especialidad_id'] ?? ($profesional['especialidad_id'] ?? null) ?? '';
    return (string) $current === (string) $espId ? 'selected' : '';
};

$selOrg = function ($orgId) use ($old, $orgDestino) {
    $current = $old['organizacion_id'] ?? $orgDestino;
    return (string) $current === (string) $orgId ? 'selected' : '';
};

$accion = $esEdicion
    ? url('/panel/profesionales/' . (int) $profesional['id'] . '/editar')
    : url('/panel/profesionales');
?>
<div class="page-header">
    <div>
        <h2 class="page-title"><?= $esEdicion ? 'Editar Profesional' : 'Nuevo Profesional' ?></h2>
        <p class="page-subtitle">
            <?= $esEdicion
                ? 'Modificando a ' . htmlspecialchars($profesional['nombre'] . ' ' . $profesional['apellidos'])
                : 'El profesional tendra acceso al sistema con su correo y contrasena' ?>
        </p>
    </div>
    <a href="<?= url('/panel/profesionales') ?>" class="btn btn-outline">Volver</a>
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
                <small class="cell-muted">La organizacion a la que pertenecera este profesional</small>
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
            <label class="form-label" for="correo">Correo electronico *</label>
            <?php if ($esEdicion): ?>
                <input class="form-input" type="email" value="<?= $val('correo') ?>" disabled>
                <small class="cell-muted">El correo es la credencial de acceso y no puede cambiarse</small>
            <?php else: ?>
                <input class="form-input" type="email" id="correo" name="correo" value="<?= $val('correo') ?>" required maxlength="255">
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">
                <?= $esEdicion ? 'Nueva contrasena (opcional)' : 'Contrasena de acceso *' ?>
            </label>
            <input class="form-input" type="password" id="password" name="password" <?= $esEdicion ? '' : 'required' ?> minlength="8" autocomplete="new-password">
            <?php if ($esEdicion): ?>
                <small class="cell-muted">Dejala en blanco para conservar la contrasena actual</small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="telefono">Telefono</label>
            <input class="form-input" type="tel" id="telefono" name="telefono" value="<?= $val('telefono') ?>" maxlength="50">
        </div>

        <div class="form-group">
            <label class="form-label" for="especialidad_id">Especialidad</label>
            <select class="form-input" id="especialidad_id" name="especialidad_id">
                <option value="">Sin especialidad</option>
                <?php foreach ($especialidades as $esp): ?>
                    <option value="<?= (int) $esp['id'] ?>" <?= $selEsp($esp['id']) ?>><?= htmlspecialchars($esp['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="numero_cedula">Numero de cedula</label>
            <input class="form-input" type="text" id="numero_cedula" name="numero_cedula" value="<?= $val('numero_cedula') ?>" maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label" for="duracion_consulta">Duracion de consulta (min) *</label>
            <input class="form-input" type="number" id="duracion_consulta" name="duracion_consulta" value="<?= $val('duracion_consulta') !== '' ? $val('duracion_consulta') : '30' ?>" min="5" max="240" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="costo_consulta">Costo de consulta</label>
            <input class="form-input" type="number" id="costo_consulta" name="costo_consulta" value="<?= $val('costo_consulta') ?>" min="0" step="0.01">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <?= $esEdicion ? 'Guardar cambios' : 'Registrar profesional' ?>
        </button>
        <a href="<?= url('/panel/profesionales') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>
