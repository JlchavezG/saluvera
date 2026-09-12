<?php
/**
 * SALUVERA - Subir documento al expediente
 *
 * @version 2.25.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$paciente = $paciente ?? [];
$consultas = $consultas ?? [];
$tipos = $tipos ?? [];
$errores = $errores ?? [];
$old = $old ?? [];

$nombrePaciente = trim($paciente['nombre'] . ' ' . $paciente['apellidos']);

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
        <h2 class="page-title">Subir documento</h2>
        <p class="page-subtitle">Expediente de <?= htmlspecialchars($nombrePaciente) ?></p>
    </div>
    <a href="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/expediente') ?>" class="btn btn-outline">Volver</a>
</div>

<?php if (!empty($flat)): ?>
    <div class="form-error-box" role="alert">
        <?= implode('<br>', array_map('htmlspecialchars', $flat)) ?>
    </div>
<?php endif; ?>

<form method="POST"
      action="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/documentos/nuevo') ?>"
      enctype="multipart/form-data"
      class="table-card form-card" novalidate>
    <?= Security::csrfField() ?>
    <input type="hidden" name="paciente_id" value="<?= (int) $paciente['id'] ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="10485760">

    <div class="form-section-title">Datos del documento</div>
    <div class="form-grid">
        <div class="form-group form-full">
            <label class="form-label" for="titulo">Titulo *</label>
            <input class="form-input" type="text" id="titulo" name="titulo" value="<?= $val('titulo') ?>" required maxlength="255" placeholder="Ej: Estudios de laboratorio marzo 2026">
        </div>

        <div class="form-group">
            <label class="form-label" for="tipo_documento">Tipo *</label>
            <select class="form-input" id="tipo_documento" name="tipo_documento" required>
                <option value="">Selecciona un tipo...</option>
                <?php foreach ($tipos as $valor => $nombre): ?>
                    <option value="<?= htmlspecialchars($valor) ?>" <?= $sel('tipo_documento', $valor) ?>><?= htmlspecialchars($nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="consulta_id">Vincular a consulta (opcional)</label>
            <select class="form-input" id="consulta_id" name="consulta_id">
                <option value="0">Sin vincular</option>
                <?php foreach ($consultas as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= $sel('consulta_id', $c['id']) ?>>
                        <?= date('d/m/Y', strtotime($c['fecha_consulta'])) ?> - <?= htmlspecialchars($c['motivo'] ?? 'Sin motivo') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group form-full">
            <label class="form-label" for="descripcion">Descripcion</label>
            <textarea class="form-input" id="descripcion" name="descripcion" rows="2" placeholder="Observaciones opcionales del documento"><?= $val('descripcion') ?></textarea>
        </div>
    </div>

    <div class="form-section-title">Archivo</div>
    <div class="form-grid">
        <div class="form-group form-full">
            <label class="form-label" for="archivo">Archivo *</label>
            <input class="form-input" type="file" id="archivo" name="archivo" required
                   accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.txt">
            <small class="cell-muted">Maximo 10 MB. Formatos: PDF, imagenes (JPG/PNG/WebP), Word, Excel, texto.</small>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Subir documento</button>
        <a href="<?= url('/panel/pacientes/' . (int) $paciente['id'] . '/expediente') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>
