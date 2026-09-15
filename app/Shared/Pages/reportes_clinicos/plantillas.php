<?php
/**
 * SALUVERA - Lista de Plantillas de Reportes Clínicos (Cards V2)
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$plantillas = $plantillas ?? [];
$tipos = $tipos ?? [];
$esProfesional = $esProfesional ?? false;
$esAdmin = $esAdmin ?? false;
$especialidades = $especialidades ?? [];

// Iconos SVG por tipo de plantilla
$iconosTipo = [
    'informe' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
    'evolucion' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
    'evaluacion' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
    'certificado' => '<circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>',
    'justificante' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>',
    'consentimiento' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    'receta' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7z"/>',
    'referencia' => '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>',
    'otro' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>',
];
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Reportes Clínicos</h2>
        <p class="page-subtitle">Elige una plantilla para generar reportes profesionales a tus pacientes</p>
    </div>
    <a href="<?= url('/panel/reportes-clinicos/mis-reportes') ?>" class="btn btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Mis reportes
    </a>
</div>

<?php if ($esAdmin && !empty($especialidades)): ?>
<div class="filtro-especialidades">
    <label class="filtro-label">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filtrar por especialidad:
    </label>
    <select id="filtroEspecialidad" class="filtro-select" onchange="filtrarEspecialidad(this.value)">
        <option value="">Todas las especialidades</option>
        <?php foreach ($especialidades as $esp): ?>
            <option value="<?= htmlspecialchars($esp['nombre']) ?>"><?= htmlspecialchars($esp['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<?php endif; ?>

<?php if (empty($plantillas)): ?>
    <div class="table-card">
        <div class="empty-state">
            <h3>Sin plantillas disponibles</h3>
            <p>No hay plantillas para tu especialidad todavía.</p>
        </div>
    </div>
<?php else: ?>
    <div class="plantillas-grid">
        <?php foreach ($plantillas as $p): ?>
            <?php 
                $estructura = json_decode($p['estructura_json'], true) ?: [];
                $tipo = $p['tipo'] ?? 'otro';
            ?>
            <div class="plantilla-card" data-especialidad="<?= htmlspecialchars($p['especialidad_nombre'] ?? 'General') ?>">
                <div class="plantilla-card-top">
                    <div class="plantilla-icono tipo-bg-<?= htmlspecialchars($tipo) ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <?= $iconosTipo[$tipo] ?? $iconosTipo['otro'] ?>
                        </svg>
                    </div>
                    <?php if ((int) $p['es_sistema'] === 1): ?>
                        <span class="plantilla-sistema-badge">Del sistema</span>
                    <?php else: ?>
                        <span class="plantilla-propia-badge">Personalizada</span>
                    <?php endif; ?>
                </div>

                <h3 class="plantilla-nombre"><?= htmlspecialchars($p['nombre']) ?></h3>
                <p class="plantilla-descripcion"><?= htmlspecialchars($p['descripcion'] ?? '') ?></p>

                <?php if ($esAdmin && !empty($p['especialidad_nombre'])): ?><div class="plantilla-esp-label"><?= htmlspecialchars($p['especialidad_nombre']) ?></div><?php endif; ?>
                <div class="plantilla-tags">
                    <span class="plantilla-tag tipo-<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipos[$tipo] ?? ucfirst($tipo)) ?></span>
                    <span class="plantilla-tag tag-neutral"><?= count($estructura['secciones'] ?? []) ?> secciones</span>
                    <?php if ($p['especialidad_nombre']): ?>
                        <span class="plantilla-tag tag-neutral"><?= htmlspecialchars($p['especialidad_nombre']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="plantilla-acciones">
                    <a href="<?= url('/panel/reportes-clinicos/crear?plantilla_id=' . $p['id']) ?>" class="btn btn-primary btn-block">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        Usar plantilla
                    </a>
                    <?php if ((int) $p['es_sistema'] === 1): ?>
                        <form method="POST" action="<?= url('/panel/reportes-clinicos/duplicar') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-outline btn-block">Duplicar y personalizar</button>
                        </form>
                    <?php else: ?>
                        <a href="<?= url('/panel/reportes-clinicos/editar/' . $p['id']) ?>" class="btn btn-outline btn-block">Editar</a>
                        <form method="POST" action="<?= url('/panel/reportes-clinicos/eliminar') ?>" onsubmit="return confirm('¿Eliminar esta plantilla?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-outline btn-block btn-danger-text">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<script>
function filtrarEspecialidad(valor) {
    var cards = document.querySelectorAll('.plantilla-card');
    cards.forEach(function(card) {
        if (valor === '' || card.getAttribute('data-especialidad') === valor) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
