<?php
/**
 * SALUVERA - Antecedentes del expediente
 *
 * @version 2.23.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$paciente = $paciente ?? [];
$expediente = $expediente ?? null;
$errores = $errores ?? [];
$old = $old ?? [];

$nombre = trim($paciente['nombre'] . ' ' . $paciente['apellidos']);

$flat = [];
foreach ($errores as $field => $msgs) {
    foreach ((array) $msgs as $m) {
        $flat[] = $m;
    }
}

$val = function ($key) use ($old, $expediente) {
    if (isset($old[$key]) && $old[$key] !== '') {
        return htmlspecialchars($old[$key]);
    }
    if ($expediente !== null && isset($expediente[$key]) && $expediente[$key] !== null) {
        return htmlspecialchars($expediente[$key]);
    }
    return '';
};

$campos = [
    'alergias' => ['Alergias', 'Ej: Penicilina, mariscos, polen...'],
    'enfermedades_cronicas' => ['Enfermedades cronicas', 'Ej: Diabetes tipo 2, hipertension arterial...'],
    'cirugias' => ['Cirugias previas', 'Ej: Apendicectomia 2015, cesarea 2020...'],
    'antecedentes_familiares' => ['Antecedentes familiares', 'Ej: Padre con infarto, madre con diabetes...'],
    'habitos' => ['Habitos', 'Ej: Tabaquismo, alcohol, ejercicio, alimentacion...'],
    'medicamentos_actuales' => ['Medicamentos actuales', 'Ej: Metformina 850mg cada 12h...'],
];
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Antecedentes de <?= htmlspecialchars($nombre) ?></h2>
        <p class="page-subtitle">Informacion base del expediente clinico</p>
    </div>
    <a href="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/expediente') ?>" class="btn btn-outline">Volver</a>
</div>

<?php if (!empty($flat)): ?>
    <div class="form-error-box" role="alert">
        <?= implode('<br>', array_map('htmlspecialchars', $flat)) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/expediente/editar') ?>" class="table-card form-card" novalidate>
    <?= Security::csrfField() ?>

    <div class="form-grid">
        <?php foreach ($campos as $key => $cfg): ?>
            <div class="form-group">
                <label class="form-label" for="<?= $key ?>"><?= htmlspecialchars($cfg[0]) ?></label>
                <textarea class="form-input" id="<?= $key ?>" name="<?= $key ?>" rows="3" placeholder="<?= htmlspecialchars($cfg[1]) ?>"><?= $val($key) ?></textarea>
            </div>
        <?php endforeach; ?>

        <div class="form-group form-full">
            <label class="form-label" for="notas">Notas del expediente</label>
            <textarea class="form-input" id="notas" name="notas" rows="3" placeholder="Observaciones generales del expediente"><?= $val('notas') ?></textarea>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar antecedentes</button>
        <a href="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/expediente') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>
