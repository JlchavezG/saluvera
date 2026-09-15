<?php
/**
 * SALUVERA - Vista profesional de reporte clínico imprimible
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$reporte = $reporte ?? null;
$estructura = $estructura ?? [];
$contenido = $contenido ?? [];

if (!$reporte) {
    echo '<div class="table-card"><div class="empty-state"><h3>Reporte no encontrado</h3></div></div>';
    return;
}

$edad = '';
if (!empty($reporte['fecha_nacimiento'])) {
    $edad = date_create($reporte['fecha_nacimiento'])->diff(date_create('today'))->y . ' años';
}

$logoUrl = '';
if (!empty($reporte['organizacion_logo'])) {
    $logo = trim($reporte['organizacion_logo']);

    if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
        $logoUrl = $logo;
    } else {
        $logo = ltrim($logo, '/');

        $candidatos = [
            'storage/uploads/' . $logo,
            'storage/' . $logo,
            $logo,
        ];

        foreach ($candidatos as $candidato) {
            if (file_exists(__DIR__ . '/../../../../public/' . $candidato)) {
                $logoUrl = url('/' . $candidato);
                break;
            }
        }
    }
}

$reemplazarVariables = function ($texto) use ($reporte, $edad) {
    $variables = [
        '{{nombre_paciente}}' => trim($reporte['paciente_nombre'] ?? ''),
        '{{edad}}' => $edad,
        '{{fecha_nacimiento}}' => !empty($reporte['fecha_nacimiento']) ? date('d/m/Y', strtotime($reporte['fecha_nacimiento'])) : '',
        '{{genero}}' => ucfirst($reporte['genero'] ?? ''),
        '{{profesional_nombre}}' => $reporte['profesional_nombre'] ?? '',
        '{{especialidad}}' => $reporte['especialidad_nombre'] ?? '',
        '{{organizacion_nombre}}' => $reporte['organizacion_nombre'] ?? '',
        '{{fecha}}' => date('d/m/Y'),
        '{{fecha_firma}}' => !empty($reporte['firmado_en']) ? date('d/m/Y H:i', strtotime($reporte['firmado_en'])) : '',
    ];

    return strtr((string) $texto, $variables);
};
?>

<div class="reporte-toolbar no-print">
    <a href="<?= url('/panel/reportes-clinicos/mis-reportes') ?>" class="btn btn-outline">Mis reportes</a>

    <?php if ($reporte['estado'] === 'borrador'): ?>
        <a href="<?= url('/panel/reportes-clinicos/editar-reporte/' . $reporte['id']) ?>" class="btn btn-outline">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Editar borrador
        </a>
    <?php endif; ?>

    <button type="button" class="btn btn-primary" onclick="window.print()">
        Imprimir / Guardar PDF
    </button>

    <?php if ($reporte['estado'] === 'borrador'): ?>
        <form method="POST" action="<?= url('/panel/reportes-clinicos/firmar') ?>" style="display:inline" onsubmit="return confirm('Al firmar el reporte ya no podrá editarse. ¿Continuar?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $reporte['id'] ?>">
            <button type="submit" class="btn btn-primary">Firmar reporte</button>
        </form>
    <?php endif; ?>

    <?php if ($reporte['estado'] === 'firmado'): ?>
        <form method="POST" action="<?= url('/panel/reportes-clinicos/compartir') ?>" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $reporte['id'] ?>">
            <input type="hidden" name="compartir" value="<?= (int) $reporte['compartido_portal'] === 1 ? '0' : '1' ?>">
            <button type="submit" class="btn btn-outline">
                <?= (int) $reporte['compartido_portal'] === 1 ? 'Retirar del portal' : 'Compartir en portal del paciente' ?>
            </button>
        </form>
    <?php 
        $msgEliminar = $reporte['estado'] === 'borrador' 
            ? '¿Eliminar este borrador? Esta acción no se puede deshacer.' 
            : '⚠️ Este reporte está FIRMADO. Eliminarlo es permanente. ¿Estás seguro?';
    ?>
    <form method="POST" action="<?= url('/panel/reportes-clinicos/eliminar-reporte') ?>" style="display:inline" onsubmit="return confirm('<?= $msgEliminar ?>')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $reporte['id'] ?>">
            <button type="submit" class="btn btn-outline" style="color:#C53030;border-color:rgba(197,48,48,0.3)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                Eliminar reporte
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="reporte-documento reporte-documento-pro">
    <header class="reporte-pro-header">
        <div class="reporte-pro-logo-wrap">
            <?php if ($logoUrl): ?>
                <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo" class="reporte-pro-logo">
            <?php else: ?>
                <div class="reporte-pro-logo-placeholder">
                    <?= strtoupper(substr($reporte['organizacion_nombre'] ?? 'S', 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="reporte-pro-org">
            <h1><?= htmlspecialchars($reporte['organizacion_nombre'] ?? 'SALUVERA') ?></h1>
            <?php if (!empty($reporte['organizacion_direccion'])): ?>
                <p><?= htmlspecialchars($reporte['organizacion_direccion']) ?></p>
            <?php endif; ?>
            <p>
                <?php if (!empty($reporte['organizacion_telefono'])): ?>
                    Tel. <?= htmlspecialchars($reporte['organizacion_telefono']) ?>
                <?php endif; ?>
                <?php if (!empty($reporte['organizacion_correo'])): ?>
                    <?= !empty($reporte['organizacion_telefono']) ? ' · ' : '' ?>
                    <?= htmlspecialchars($reporte['organizacion_correo']) ?>
                <?php endif; ?>
            </p>
        </div>

        <div class="reporte-pro-status">
            <span class="badge-estado <?= $reporte['estado'] === 'firmado' ? 'badge-completada' : 'badge-pendiente' ?>">
                <?= ucfirst($reporte['estado']) ?>
            </span>
        </div>
    </header>

    <section class="reporte-pro-title">
        <h2><?= htmlspecialchars($reporte['titulo']) ?></h2>
        <p><?= htmlspecialchars($reporte['plantilla_nombre'] ?? 'Reporte clínico') ?></p>
    </section>

    <section class="reporte-pro-paciente">
        <div>
            <span>Paciente</span>
            <strong><?= htmlspecialchars($reporte['paciente_nombre']) ?></strong>
        </div>
        <?php if ($edad): ?>
            <div>
                <span>Edad</span>
                <strong><?= htmlspecialchars($edad) ?></strong>
            </div>
        <?php endif; ?>
        <?php if (!empty($reporte['genero'])): ?>
            <div>
                <span>Género</span>
                <strong><?= ucfirst(htmlspecialchars($reporte['genero'])) ?></strong>
            </div>
        <?php endif; ?>
        <div>
            <span>Fecha</span>
            <strong><?= date('d/m/Y', strtotime($reporte['creado_en'])) ?></strong>
        </div>
    </section>

    <main class="reporte-pro-body">
        <?php if (!empty($estructura['secciones'])): ?>
            <?php foreach ($estructura['secciones'] as $seccion): ?>
                <?php
                    $valor = $contenido[$seccion['id']] ?? '';
                    if (trim((string) $valor) === '') {
                        continue;
                    }
                    $valor = $reemplazarVariables($valor);
                ?>
                <section class="reporte-pro-section">
                    <h3><?= htmlspecialchars($seccion['titulo']) ?></h3>
                    <div><?= nl2br(htmlspecialchars($valor)) ?></div>
                </section>
            <?php endforeach; ?>
        <?php else: ?>
            <?php foreach ($contenido as $valor): ?>
                <?php if (trim((string) $valor) === '') continue; ?>
                <section class="reporte-pro-section">
                    <div><?= nl2br(htmlspecialchars($reemplazarVariables($valor))) ?></div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <footer class="reporte-pro-footer">
        <div class="reporte-pro-firma-line"></div>
        <p class="reporte-pro-firma-nombre"><?= htmlspecialchars($reporte['profesional_nombre'] ?? '') ?></p>
        <p class="reporte-pro-firma-detalle"><?= htmlspecialchars($reporte['especialidad_nombre'] ?? '') ?></p>

        <?php if (!empty($reporte['firmado_en'])): ?>
            <p class="reporte-pro-firma-fecha">
                Firmado digitalmente el <?= date('d/m/Y \a \l\a\s H:i', strtotime($reporte['firmado_en'])) ?>
            </p>
        <?php endif; ?>
    </footer>
</div>
