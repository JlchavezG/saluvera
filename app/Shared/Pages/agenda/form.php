<?php
/**
 * SALUVERA - Formulario de Nueva Cita (version limpia y balanceada)
 *
 * @version 2.28.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$pacientes = $pacientes ?? [];
$profesionales = $profesionales ?? [];
$profesionalUnico = $profesionalUnico ?? null;
$consultorios = $consultorios ?? [];
$organizaciones = $organizaciones ?? [];
$orgSeleccionada = $orgSeleccionada ?? ($orgDestino ?? 0);
$requiereOrg = $requiereOrg ?? false;
$esSuperAdmin = $esSuperAdmin ?? false;
$esProfessional = $esProfessional ?? false;
$bloqueosMapa = $bloqueosMapa ?? [];
$bloqueosProfUnico = $bloqueosProfUnico ?? [];
$errores = $errores ?? [];
$old = $old ?? [];

$flat = [];
foreach ($errores as $field => $msgs) {
    foreach ((array) $msgs as $m) {
        $flat[] = $m;
    }
}

$val = function ($key, $default = '') use ($old) {
    if (isset($old[$key]) && $old[$key] !== '') {
        return htmlspecialchars($old[$key]);
    }
    return htmlspecialchars($default);
};

$sel = function ($key, $valor) use ($old) {
    $current = $old[$key] ?? '';
    return (string) $current === (string) $valor ? 'selected' : '';
};
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Nueva Cita</h2>
        <p class="page-subtitle">La hora de fin se calcula automaticamente segun la duracion del profesional</p>
    </div>
    <a href="<?= url('/panel/agenda') ?>" class="btn btn-outline">Volver</a>
</div>

<?php if (!empty($flat)): ?>
    <div class="form-error-box" role="alert">
        <?= implode('<br>', array_map('htmlspecialchars', $flat)) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= url('/panel/agenda') ?>" class="table-card form-card" novalidate>
    <?= Security::csrfField() ?>

    <?php if ($esSuperAdmin): ?>
        <div class="form-grid">
            <div class="form-group form-full">
                <label class="form-label" for="organizacion_id">Organizacion *</label>
                <select class="form-input" id="organizacion_id" name="organizacion_id" required
                        onchange="window.location='<?= url('/panel/agenda/nueva') ?>?organizacion_id='+this.value">
                    <option value="">Selecciona la organizacion...</option>
                    <?php foreach ($organizaciones as $o): ?>
                        <option value="<?= (int) $o['id'] ?>" <?= (int) $orgSeleccionada === (int) $o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ($requiereOrg): ?>
                    <small class="form-hint-warning">Primero selecciona la organizacion para cargar sus pacientes, profesionales y consultorios</small>
                <?php endif; ?>
            </div>
        </div>
        <hr class="form-divider">
    <?php endif; ?>

    <div class="form-grid">
        <div class="form-group">
            <label class="form-label" for="paciente_id">Paciente *</label>
            <select class="form-input" id="paciente_id" name="paciente_id" required <?= $requiereOrg ? 'disabled' : '' ?>>
                <option value=""><?= $requiereOrg ? 'Selecciona primero la organizacion...' : 'Selecciona el paciente...' ?></option>
                <?php foreach ($pacientes as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= $sel('paciente_id', $p['id']) ?>>
                        <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!$requiereOrg && empty($pacientes)): ?>
                <small class="cell-muted">Esta organizacion no tiene pacientes registrados</small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="profesional_id">Profesional *</label>
            <?php if ($esProfessional && $profesionalUnico !== null): ?>
                <input class="form-input" type="text" value="<?= htmlspecialchars($profesionalUnico['nombre'] . ' ' . $profesionalUnico['apellidos']) ?> (tu)" disabled>
                <input type="hidden" name="profesional_id" value="<?= (int) $profesionalUnico['id'] ?>">
            <?php else: ?>
                <select class="form-input" id="profesional_id" name="profesional_id" required <?= $requiereOrg ? 'disabled' : '' ?>>
                    <option value=""><?= $requiereOrg ? 'Selecciona primero la organizacion...' : 'Selecciona el profesional...' ?></option>
                    <?php foreach ($profesionales as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= $sel('profesional_id', $p['id']) ?>>
                            <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$requiereOrg && empty($profesionales)): ?>
                    <small class="cell-muted">Esta organizacion no tiene profesionales registrados</small>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($esProfessional && !empty($bloqueosProfUnico)): ?>
            <div class="form-group form-full">
                <div class="bloqueos-banner">
                    <div class="bloqueos-banner-title">Tus dias bloqueados proximos</div>
                    <div class="bloqueos-chips">
                        <?php foreach ($bloqueosProfUnico as $b): ?>
                            <span class="bloqueo-chip"><?= date('d/m', strtotime($b['inicio'])) ?><?= $b['fin'] !== $b['inicio'] ? ' al ' . date('d/m', strtotime($b['fin'])) : '' ?><?= !empty($b['motivo']) ? ' · ' . htmlspecialchars($b['motivo']) : '' ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php elseif (!$esProfessional): ?>
            <div class="form-group form-full" id="wrapBloqueosProf" style="display:none">
                <div class="bloqueos-banner">
                    <div class="bloqueos-banner-title">Dias bloqueados del profesional seleccionado</div>
                    <div class="bloqueos-chips" id="chipsBloqueosProf"></div>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label" for="consultorio_id">Consultorio</label>
            <select class="form-input" id="consultorio_id" name="consultorio_id" <?= $requiereOrg ? 'disabled' : '' ?>>
                <option value=""><?= $requiereOrg ? 'Selecciona primero la organizacion...' : 'Sin consultorio' ?></option>
                <?php foreach ($consultorios as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= $sel('consultorio_id', $c['id']) ?>>
                        <?= htmlspecialchars($c['nombre']) ?><?= !empty($c['ubicacion']) ? ' (' . htmlspecialchars($c['ubicacion']) . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="fecha_cita">Fecha *</label>
            <input class="form-input" type="date" id="fecha_cita" name="fecha_cita" value="<?= $val('fecha_cita', date('Y-m-d')) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="hora_inicio">Hora de inicio *</label>
            <input class="form-input" type="time" id="hora_inicio" name="hora_inicio" value="<?= $val('hora_inicio') ?>" required step="300">
        </div>

        <div class="form-group form-full">
            <label class="form-label" for="motivo">Motivo de la consulta</label>
            <input class="form-input" type="text" id="motivo" name="motivo" value="<?= $val('motivo') ?>" maxlength="500" placeholder="Ej: Consulta general, revision de resultados...">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" <?= $requiereOrg ? 'disabled' : '' ?>>Crear cita</button>
        <a href="<?= url('/panel/agenda') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>

<?php if (!$esProfessional): ?>
<script>
    var BLOQUEOS_PROF = <?= json_encode($bloqueosMapa) ?>;
    (function () {
        var sel = document.getElementById('profesional_id');
        var wrap = document.getElementById('wrapBloqueosProf');
        var chips = document.getElementById('chipsBloqueosProf');
        if (!sel || !wrap || !chips) return;

        function fmt(d) { var p = d.split('-'); return p[2] + '/' + p[1]; }

        function render() {
            var lista = BLOQUEOS_PROF[sel.value] || [];
            chips.innerHTML = '';
            if (lista.length === 0) { wrap.style.display = 'none'; return; }
            wrap.style.display = '';
            lista.forEach(function (b) {
                var span = document.createElement('span');
                span.className = 'bloqueo-chip';
                var txt = fmt(b.inicio) + (b.fin !== b.inicio ? ' al ' + fmt(b.fin) : '');
                if (b.motivo) txt += ' · ' + b.motivo;
                span.textContent = txt;
                span.title = b.motivo || 'Dia bloqueado';
                chips.appendChild(span);
            });
        }

        sel.addEventListener('change', render);
        render();
    })();
</script>
<?php endif; ?>
