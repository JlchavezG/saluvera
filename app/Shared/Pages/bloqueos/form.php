<?php
/**
 * SALUVERA - Formulario de bloqueo de dias
 *
 * @version 2.27.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitted');
}

$bloqueo = $bloqueo ?? null;
$profesionales = $profesionales ?? [];
$esProfessional = $esProfessional ?? false;
$esSuperAdmin = $esSuperAdmin ?? false;
$errores = $errores ?? [];
$old = $old ?? [];
$esEdicion = $bloqueo !== null;

$flat = [];
foreach ($errores as $field => $msgs) {
    foreach ((array) $msgs as $m) {
        $flat[] = $m;
    }
}

$val = function ($key, $default = '') use ($old, $bloqueo) {
    if (isset($old[$key]) && $old[$key] !== '') {
        return htmlspecialchars($old[$key]);
    }
    if ($bloqueo !== null && isset($bloqueo[$key]) && $bloqueo[$key] !== null) {
        return htmlspecialchars($bloqueo[$key]);
    }
    return htmlspecialchars($default);
};

$accion = $esEdicion
    ? url('/panel/bloqueos/' . (int) $bloqueo['id'] . '/editar')
    : url('/panel/bloqueos');
?>
<div class="page-header">
    <div>
        <h2 class="page-title"><?= $esEdicion ? 'Editar Bloqueo' : 'Bloquear Dias' ?></h2>
        <p class="page-subtitle">Durante el periodo bloqueado no se podran agendar citas</p>
    </div>
    <a href="<?= url('/panel/bloqueos') ?>" class="btn btn-outline">Volver</a>
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
                    <option value="">Selecciona el profesional...</option>
                    <?php foreach ($profesionales as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= (string) $val('profesional_id') === (string) $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars(trim($p['nombre'] . ' ' . $p['apellidos'])) ?>
                            <?= !empty($p['org_nombre']) ? ' (' . htmlspecialchars($p['org_nombre']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($esEdicion): ?>
                    <input type="hidden" name="profesional_id" value="<?= (int) $bloqueo['profesional_id'] ?>">
                    <small class="cell-muted">El profesional no puede cambiarse al editar</small>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label" for="fecha_inicio">Desde *</label>
            <input class="form-input" type="date" id="fecha_inicio" name="fecha_inicio" value="<?= $val('fecha_inicio', date('Y-m-d')) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="fecha_fin">Hasta *</label>
            <input class="form-input" type="date" id="fecha_fin" name="fecha_fin" value="<?= $val('fecha_fin', $val('fecha_inicio', date('Y-m-d'))) ?>" required>
            <small class="cell-muted">Para un solo dia, pon la misma fecha</small>
        </div>

        <div class="form-group form-full">
            <label class="form-label" for="motivo">Motivo</label>
            <input class="form-input" type="text" id="motivo" name="motivo" value="<?= $val('motivo') ?>" maxlength="255" placeholder="Ej: Vacaciones, congreso medico, asunto personal...">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Bloquear dias' ?></button>
        <a href="<?= url('/panel/bloqueos') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>

<script>
    // Si cambia "desde" y "hasta" esta vacio o es anterior, se sincroniza
    (function () {
        var ini = document.getElementById('fecha_inicio');
        var fin = document.getElementById('fecha_fin');
        if (!ini || !fin) return;
        ini.addEventListener('change', function () {
            if (fin.value === '' || fin.value < ini.value) {
                fin.value = ini.value;
            }
        });
    })();
</script>
