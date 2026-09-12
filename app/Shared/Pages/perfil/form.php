<?php
/**
 * SALUVERA - Mi Perfil (personal + profesional + seguridad)
 *
 * @version 2.32.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$user = $user ?? [];
$prof = $prof ?? null;
$especialidades = $especialidades ?? [];
$misEspecialidades = $misEspecialidades ?? [];
$errores = $errores ?? [];
$old = $old ?? [];

$flat = [];
foreach ($errores as $field => $msgs) {
    foreach ((array) $msgs as $m) {
        $flat[] = $m;
    }
}

$val = function ($key, $default = '') use ($old, $user) {
    if (isset($old[$key]) && $old[$key] !== '') {
        return htmlspecialchars($old[$key]);
    }
    if (isset($user[$key]) && $user[$key] !== null) {
        return htmlspecialchars($user[$key]);
    }
    return htmlspecialchars($default);
};

$pval = function ($key, $default = '') use ($old, $prof) {
    if (isset($old[$key]) && $old[$key] !== '') {
        return htmlspecialchars($old[$key]);
    }
    if ($prof !== null && isset($prof[$key]) && $prof[$key] !== null) {
        return htmlspecialchars($prof[$key]);
    }
    return htmlspecialchars($default);
};
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Mi Perfil</h2>
        <p class="page-subtitle">Tus datos personales<?= $prof !== null ? ' y profesionales' : '' ?></p>
    </div>
</div>

<?php if (!empty($flat)): ?>
    <div class="form-error-box" role="alert">
        <?= implode('<br>', array_map('htmlspecialchars', $flat)) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= url('/panel/mi-perfil') ?>" class="table-card form-card" novalidate>
    <?= Security::csrfField() ?>

    <div class="form-section-title">Datos personales</div>
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
            <label class="form-label" for="telefono">Telefono</label>
            <input class="form-input" type="tel" id="telefono" name="telefono" value="<?= $val('telefono') ?>" maxlength="50">
        </div>
        <div class="form-group">
            <label class="form-label" for="correo">Correo (no editable)</label>
            <input class="form-input" type="email" value="<?= htmlspecialchars($user['correo'] ?? '') ?>" disabled>
            <small class="cell-muted">El correo es tu usuario de acceso</small>
        </div>
    </div>

    <?php if ($prof !== null): ?>
        <div class="form-section-title">Datos profesionales</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="numero_cedula">Numero de cedula</label>
                <input class="form-input" type="text" id="numero_cedula" name="numero_cedula" value="<?= $pval('numero_cedula') ?>" maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label" for="cedula_expira">Cedula expira</label>
                <input class="form-input" type="date" id="cedula_expira" name="cedula_expira" value="<?= $pval('cedula_expira') ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="especialidad_id">Especialidad principal</label>
                <select class="form-input" id="especialidad_id" name="especialidad_id">
                    <option value="">Sin especialidad principal</option>
                    <?php foreach ($especialidades as $e): ?>
                        <option value="<?= (int) $e['id'] ?>" <?= (string) $pval('especialidad_id') === (string) $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-full">
                <label class="form-label">Otras especialidades</label>
                <div class="checks-grid">
                    <?php foreach ($especialidades as $e): ?>
                        <label class="form-check check-inline">
                            <input type="checkbox" name="especialidades[]" value="<?= (int) $e['id'] ?>"
                                   <?= in_array((int) $e['id'], $misEspecialidades, true) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($e['nombre']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group form-full">
                <label class="form-label" for="biografia">Biografia</label>
                <textarea class="form-input" id="biografia" name="biografia" rows="3" placeholder="Presentacion profesional visible para la clinica"><?= $pval('biografia') ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Duracion de consulta</label>
                <input class="form-input" type="text" value="<?= (int) ($prof['duracion_consulta'] ?? 0) ?> min" disabled>
                <small class="cell-muted">Lo define el administrador</small>
            </div>
            <div class="form-group">
                <label class="form-label">Costo de consulta</label>
                <input class="form-input" type="text" value="$<?= number_format((float) ($prof['costo_consulta'] ?? 0), 2) ?>" disabled>
                <small class="cell-muted">Lo define el administrador</small>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-section-title">Seguridad</div>
    <div class="form-grid">
        <div class="form-group">
            <label class="form-label" for="password">Nueva contrasena (opcional)</label>
            <input class="form-input" type="password" id="password" name="password" minlength="8" autocomplete="new-password" placeholder="Deja vacio para no cambiarla">
        </div>
        <div class="form-group">
            <label class="form-label" for="password_confirmar">Confirmar contrasena</label>
            <input class="form-input" type="password" id="password_confirmar" name="password_confirmar" minlength="8" autocomplete="new-password" placeholder="Repite la nueva contrasena">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="<?= url('/panel') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>
