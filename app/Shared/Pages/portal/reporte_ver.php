<?php
if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$reporte = $reporte ?? null;
$estructura = $estructura ?? [];
$contenido = $contenido ?? [];

if (!$reporte) {
    echo '<div class="portal-card"><div class="empty-state"><h3>Reporte no disponible</h3></div></div>';
    return;
}

$edad = '';
if (!empty($reporte['fecha_nacimiento'])) {
    $edad = date_create($reporte['fecha_nacimiento'])->diff(date_create('today'))->y . ' años';
}

$logoUrl = '';
if (!empty($reporte['organizacion_logo'])) {
    $logo = ltrim(trim($reporte['organizacion_logo']), '/');

    if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
        $logoUrl = $logo;
    } else {
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
    return strtr((string) $texto, [
        '{{nombre_paciente}}' => trim($reporte['paciente_nombre'] ?? ''),
        '{{edad}}' => $edad,
        '{{fecha_nacimiento}}' => !empty($reporte['fecha_nacimiento']) ? date('d/m/Y', strtotime($reporte['fecha_nacimiento'])) : '',
        '{{genero}}' => ucfirst($reporte['genero'] ?? ''),
        '{{profesional_nombre}}' => $reporte['profesional_nombre'] ?? '',
        '{{especialidad}}' => $reporte['especialidad_nombre'] ?? '',
        '{{organizacion_nombre}}' => $reporte['organizacion_nombre'] ?? '',
        '{{fecha}}' => date('d/m/Y'),
        '{{fecha_firma}}' => !empty($reporte['firmado_en']) ? date('d/m/Y H:i', strtotime($reporte['firmado_en'])) : '',
    ]);
};
?>

<div class="reporte-toolbar no-print">
    <a href="<?= url('/portal/reportes') ?>" class="btn btn-outline">Volver a mis reportes</a>
    <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir / Guardar PDF</button>
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
            <span class="badge-estado badge-completada">Firmado</span>
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
                    if (trim((string) $valor) === '') continue;
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

    <?php 
    $firmaUrl = '';
    if (!empty($reporte['profesional_firma'])) {
        $firmaFile = basename($reporte['profesional_firma']);
        $firmaPath = __DIR__ . '/../../../../storage/uploads/signatures/' . $firmaFile;
        if (file_exists($firmaPath)) {
            $firmaUrl = url('/storage/uploads/signatures/' . $firmaFile);
        }
    }
    ?>
    <footer class="reporte-pro-footer">
        <?php if ($firmaUrl): ?>
            <img src="<?= htmlspecialchars($firmaUrl) ?>" alt="Firma" class="firma-img-reporte">
        <?php endif; ?>
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
