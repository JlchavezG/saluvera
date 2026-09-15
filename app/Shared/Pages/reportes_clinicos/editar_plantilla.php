<?php
if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$plantilla = $plantilla ?? null;
$estructura = $estructura ?? [];
$tipos = $tipos ?? [];

if (!$plantilla) {
    echo '<div class="table-card"><div class="empty-state"><h3>Plantilla no encontrada</h3></div></div>';
    return;
}

$secciones = $estructura['secciones'] ?? [];

$tipoLabels = [
    'texto_largo' => 'Texto largo',
    'texto_corto' => 'Texto corto',
    'fecha' => 'Fecha',
];
?>

<div class="page-header">
    <div>
        <h2 class="page-title">Editar Plantilla</h2>
        <p class="page-subtitle"><?= htmlspecialchars($plantilla['nombre']) ?></p>
    </div>
    <a href="<?= url('/panel/reportes-clinicos') ?>" class="btn btn-outline">Volver</a>
</div>

<form method="POST" action="<?= url('/panel/reportes-clinicos/actualizar') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $plantilla['id'] ?>">

    <div class="table-card">
        <div class="form-group">
            <label class="form-label">Nombre de la plantilla *</label>
            <input type="text" name="nombre" class="form-input" required value="<?= htmlspecialchars($plantilla['nombre']) ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-input">
                    <?php foreach ($tipos as $slug => $nombre): ?>
                        <option value="<?= htmlspecialchars($slug) ?>" <?= ($plantilla['tipo'] ?? '') === $slug ? 'selected' : '' ?>>
                            <?= htmlspecialchars($nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-input" value="<?= htmlspecialchars($plantilla['descripcion'] ?? '') ?>" placeholder="Descripción breve">
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="editor-secciones-header">
            <div>
                <span class="dash-section-title" style="margin:0">Secciones</span>
                <span class="secciones-count" id="seccionesCount"><?= count($secciones) ?></span>
            </div>
            <button type="button" class="btn btn-outline btn-sm" onclick="agregarSeccion()">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Agregar
            </button>
        </div>

        <p class="editor-hint">Arrastra con <span class="drag-hint">⋮⋮</span> para reordenar · Click en una sección para editarla</p>

        <div id="secciones-container" class="secciones-acordeon">
            <?php if (empty($secciones)): ?>
                <div class="secciones-empty" id="seccionesEmpty">
                    <p>Aún no hay secciones. Agrega la primera para comenzar.</p>
                </div>
            <?php else: ?>
                <?php foreach ($secciones as $i => $sec): ?>
                    <div class="seccion-item" data-index="<?= $i ?>">
                        <div class="seccion-header">
                            <div class="seccion-drag-handle" title="Arrastra para reordenar">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                            </div>
                            <span class="seccion-numero"><?= $i + 1 ?></span>
                            <span class="seccion-titulo-preview"><?= htmlspecialchars($sec['titulo'] ?? 'Sin título') ?></span>
                            <span class="seccion-tipo-badge"><?= htmlspecialchars($tipoLabels[$sec['tipo'] ?? 'texto_largo'] ?? 'Texto largo') ?></span>
                            <?php if (!empty($sec['requerido'])): ?><span class="seccion-req-dot" title="Requerido">*</span><?php endif; ?>
                            <div class="seccion-acciones">
                                <button type="button" class="btn-icon" title="Duplicar" onclick="duplicarSeccion(this); event.stopPropagation();">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                </button>
                                <button type="button" class="btn-icon btn-eliminar" title="Eliminar" onclick="eliminarSeccion(this); event.stopPropagation();">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                            <span class="seccion-chevron">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </span>
                        </div>
                        <div class="seccion-body">
                            <div class="form-group">
                                <label class="form-label">Título</label>
                                <input type="text" name="seccion_titulo[]" class="form-input" value="<?= htmlspecialchars($sec['titulo'] ?? '') ?>" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Tipo de campo</label>
                                    <select name="seccion_tipo[]" class="form-input">
                                        <option value="texto_largo" <?= ($sec['tipo'] ?? '') === 'texto_largo' ? 'selected' : '' ?>>Texto largo</option>
                                        <option value="texto_corto" <?= ($sec['tipo'] ?? '') === 'texto_corto' ? 'selected' : '' ?>>Texto corto</option>
                                        <option value="fecha" <?= ($sec['tipo'] ?? '') === 'fecha' ? 'selected' : '' ?>>Fecha</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Placeholder</label>
                                    <input type="text" name="seccion_placeholder[]" class="form-input" value="<?= htmlspecialchars($sec['placeholder'] ?? '') ?>" placeholder="Texto de ayuda">
                                </div>
                            </div>
                            <label class="checkbox-label">
                                <input type="hidden" name="seccion_requerido_idx[<?= $i ?>]" value="<?= $i ?>" <?= !empty($sec['requerido']) ? '' : 'disabled' ?>>
                                <input type="checkbox" name="seccion_requerido_chk[<?= $i ?>]" value="1" <?= !empty($sec['requerido']) ? 'checked' : '' ?> onchange="toggleRequerido(this, <?= $i ?>)">
                                <span>Campo requerido</span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-card form-actions-bar">
        <a href="<?= url('/panel/reportes-clinicos') ?>" class="btn btn-outline">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar plantilla</button>
    </div>
</form>

<script>
(function() {
    'use strict';

    var container = document.getElementById('secciones-container');
    var draggedItem = null;
    var placeholder = null;
    var allowDrag = false;

    var tipoLabels = {
        'texto_largo': 'Texto largo',
        'texto_corto': 'Texto corto',
        'fecha': 'Fecha'
    };

    // ===== SOLO PERMITIR DRAG DESDE EL HANDLE =====
    document.addEventListener('mousedown', function(e) {
        if (e.target.closest('.seccion-drag-handle')) {
            allowDrag = true;
        }
    });
    document.addEventListener('mouseup', function() {
        allowDrag = false;
    });

    // ===== TOGGLE EXPANDIR/COLAPSAR =====
    container.addEventListener('click', function(e) {
        var header = e.target.closest('.seccion-header');
        if (!header) return;
        // No toggle si click en handle o botones
        if (e.target.closest('.seccion-drag-handle') || e.target.closest('.btn-icon')) return;
        
        var item = header.closest('.seccion-item');
        item.classList.toggle('expandida');
    });

    // ===== DRAG & DROP =====
    container.addEventListener('dragstart', function(e) {
        var item = e.target.closest('.seccion-item');
        if (!item || !allowDrag) {
            e.preventDefault();
            return;
        }
        draggedItem = item;
        item.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', '');
        
        placeholder = document.createElement('div');
        placeholder.className = 'seccion-placeholder';
        placeholder.style.height = item.offsetHeight + 'px';
    });

    container.addEventListener('dragend', function() {
        if (draggedItem) draggedItem.classList.remove('dragging');
        if (placeholder && placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
        draggedItem = null;
        placeholder = null;
        allowDrag = false;
        reindexar();
    });

    container.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (!draggedItem) return;
        var afterElement = getDragAfterElement(container, e.clientY);
        if (afterElement == null) {
            container.appendChild(placeholder);
        } else {
            container.insertBefore(placeholder, afterElement);
        }
    });

    container.addEventListener('drop', function(e) {
        e.preventDefault();
        if (!draggedItem || !placeholder) return;
        container.insertBefore(draggedItem, placeholder);
        if (placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
        draggedItem.classList.remove('dragging');
        reindexar();
    });

    function getDragAfterElement(cont, y) {
        var items = Array.from(cont.querySelectorAll('.seccion-item:not(.dragging)'));
        return items.reduce(function(closest, child) {
            var box = child.getBoundingClientRect();
            var offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            }
            return closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    // ===== REINDEXAR =====
    function reindexar() {
        var items = container.querySelectorAll('.seccion-item');
        items.forEach(function(item, index) {
            item.setAttribute('data-index', index);
            var num = item.querySelector('.seccion-numero');
            if (num) num.textContent = index + 1;
            
            var hidden = item.querySelector('input[name^="seccion_requerido_idx"]');
            var chk = item.querySelector('input[name^="seccion_requerido_chk"]');
            if (hidden) { hidden.name = 'seccion_requerido_idx[' + index + ']'; hidden.value = index; }
            if (chk) { chk.name = 'seccion_requerido_chk[' + index + ']'; chk.setAttribute('onchange', 'toggleRequerido(this, ' + index + ')'); }
        });
        actualizarContador();
        actualizarEmpty();
    }

    function actualizarContador() {
        var el = document.getElementById('seccionesCount');
        if (el) el.textContent = container.querySelectorAll('.seccion-item').length;
    }

    function actualizarEmpty() {
        var empty = document.getElementById('seccionesEmpty');
        var items = container.querySelectorAll('.seccion-item');
        if (empty) empty.style.display = items.length === 0 ? 'block' : 'none';
    }

    // ===== ACTUALIZAR PREVIEW EN TIEMPO REAL =====
    container.addEventListener('input', function(e) {
        if (e.target.name === 'seccion_titulo[]') {
            var item = e.target.closest('.seccion-item');
            var preview = item.querySelector('.seccion-titulo-preview');
            if (preview) preview.textContent = e.target.value || 'Sin título';
        }
    });

    container.addEventListener('change', function(e) {
        if (e.target.name === 'seccion_tipo[]') {
            var item = e.target.closest('.seccion-item');
            var badge = item.querySelector('.seccion-tipo-badge');
            if (badge) badge.textContent = tipoLabels[e.target.value] || e.target.value;
        }
    });

    // ===== AGREGAR SECCION =====
    window.agregarSeccion = function() {
        var empty = document.getElementById('seccionesEmpty');
        if (empty && empty.parentNode) empty.parentNode.removeChild(empty);
        
        var index = container.querySelectorAll('.seccion-item').length;
        var div = document.createElement('div');
        div.className = 'seccion-item expandida';
        div.setAttribute('draggable', 'true');
        div.setAttribute('data-index', index);
        div.innerHTML = getSeccionHTML(index, '', 'texto_largo', '', false);
        container.appendChild(div);
        reindexar();
        
        var input = div.querySelector('input[name="seccion_titulo[]"]');
        if (input) input.focus();
    };

    // ===== DUPLICAR =====
    window.duplicarSeccion = function(btn) {
        var item = btn.closest('.seccion-item');
        if (!item) return;
        var titulo = item.querySelector('input[name="seccion_titulo[]"]').value;
        var tipo = item.querySelector('select[name="seccion_tipo[]"]').value;
        var ph = item.querySelector('input[name="seccion_placeholder[]"]').value;
        var chk = item.querySelector('input[name^="seccion_requerido_chk"]');
        var req = chk ? chk.checked : false;
        
        var index = container.querySelectorAll('.seccion-item').length;
        var div = document.createElement('div');
        div.className = 'seccion-item';
        div.setAttribute('draggable', 'true');
        div.setAttribute('data-index', index);
        div.innerHTML = getSeccionHTML(index, titulo + ' (copia)', tipo, ph, req);
        item.parentNode.insertBefore(div, item.nextSibling);
        reindexar();
    };

    // ===== ELIMINAR =====
    window.eliminarSeccion = function(btn) {
        var item = btn.closest('.seccion-item');
        if (!item) return;
        item.classList.add('seccion-eliminando');
        setTimeout(function() { item.remove(); reindexar(); }, 220);
    };

    // ===== TOGGLE REQUERIDO =====
    window.toggleRequerido = function(chk, index) {
        var hidden = document.querySelector('input[name="seccion_requerido_idx[' + index + ']"]');
        if (hidden) hidden.disabled = !chk.checked;
        // Actualizar el punto de requerido en el header
        var item = chk.closest('.seccion-item');
        var dot = item.querySelector('.seccion-req-dot');
        if (chk.checked && !dot) {
            dot = document.createElement('span');
            dot.className = 'seccion-req-dot';
            dot.title = 'Requerido';
            dot.textContent = '*';
            var badge = item.querySelector('.seccion-tipo-badge');
            badge.parentNode.insertBefore(dot, badge.nextSibling);
        } else if (!chk.checked && dot) {
            dot.remove();
        }
    };

    // ===== HTML DE SECCION =====
    function getSeccionHTML(index, titulo, tipo, ph, req) {
        return '<div class="seccion-header">' +
            '<div class="seccion-drag-handle" title="Arrastra para reordenar">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>' +
            '</div>' +
            '<span class="seccion-numero">' + (index + 1) + '</span>' +
            '<span class="seccion-titulo-preview">' + escapeHtml(titulo || 'Sin título') + '</span>' +
            '<span class="seccion-tipo-badge">' + (tipoLabels[tipo] || 'Texto largo') + '</span>' +
            (req ? '<span class="seccion-req-dot" title="Requerido">*</span>' : '') +
            '<div class="seccion-acciones">' +
            '<button type="button" class="btn-icon" title="Duplicar" onclick="duplicarSeccion(this); event.stopPropagation();">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>' +
            '</button>' +
            '<button type="button" class="btn-icon btn-eliminar" title="Eliminar" onclick="eliminarSeccion(this); event.stopPropagation();">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>' +
            '</button>' +
            '</div>' +
            '<span class="seccion-chevron"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>' +
            '</div>' +
            '<div class="seccion-body">' +
            '<div class="form-group"><label class="form-label">Título</label>' +
            '<input type="text" name="seccion_titulo[]" class="form-input" value="' + escapeHtml(titulo) + '" required></div>' +
            '<div class="form-row">' +
            '<div class="form-group"><label class="form-label">Tipo de campo</label>' +
            '<select name="seccion_tipo[]" class="form-input">' +
            '<option value="texto_largo"' + (tipo === 'texto_largo' ? ' selected' : '') + '>Texto largo</option>' +
            '<option value="texto_corto"' + (tipo === 'texto_corto' ? ' selected' : '') + '>Texto corto</option>' +
            '<option value="fecha"' + (tipo === 'fecha' ? ' selected' : '') + '>Fecha</option>' +
            '</select></div>' +
            '<div class="form-group"><label class="form-label">Placeholder</label>' +
            '<input type="text" name="seccion_placeholder[]" class="form-input" value="' + escapeHtml(ph) + '" placeholder="Texto de ayuda"></div>' +
            '</div>' +
            '<label class="checkbox-label">' +
            '<input type="hidden" name="seccion_requerido_idx[' + index + ']" value="' + index + '"' + (req ? '' : ' disabled') + '>' +
            '<input type="checkbox" name="seccion_requerido_chk[' + index + ']" value="1"' + (req ? ' checked' : '') + ' onchange="toggleRequerido(this, ' + index + ')">' +
            '<span>Campo requerido</span></label>' +
            '</div>';
    }

    function escapeHtml(t) {
        var d = document.createElement('div');
        d.textContent = t || '';
        return d.innerHTML;
    }

    actualizarEmpty();
})();
</script>
