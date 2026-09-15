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
<?php if ($prof !== null): ?>
<div class="table-card form-card firma-manuscrita-seccion">
    <div class="firma-seccion-header">
        <div>
            <h3 class="firma-seccion-titulo">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
                Firma Manuscrita Digital
            </h3>
            <p class="firma-seccion-subtitulo">Se incrustará automáticamente en todos tus reportes clínicos firmados</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="abrirModalFirma()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            <?= !empty($prof['firma']) ? 'Cambiar firma' : 'Dibujar mi firma' ?>
        </button>
    </div>

    <?php if (!empty($prof['firma'])): ?>
        <div class="firma-preview-wrap">
            <div class="firma-preview-label">Firma actual</div>
            <div class="firma-preview-card">
                <img src="<?= url('/storage/uploads/signatures/' . htmlspecialchars($prof['firma'])) ?>" alt="Firma" class="firma-preview-img">
            </div>
            <form method="POST" action="<?= url('/panel/mi-perfil/firma/eliminar') ?>" onsubmit="return confirm('¿Eliminar tu firma manuscrita?')">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-outline btn-danger-text btn-sm">Eliminar firma</button>
            </form>
        </div>
    <?php else: ?>
        <div class="firma-empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
            <p>No tienes firma registrada. Haz clic en "Dibujar mi firma" para capturarla.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de captura de firma -->
<div id="modalFirma" class="firma-modal" style="display:none" role="dialog" aria-modal="true" aria-labelledby="modalFirmaTitulo">
    <div class="firma-modal-overlay" onclick="cerrarModalFirma()"></div>
    <div class="firma-modal-content">
        <div class="firma-modal-header">
            <div>
                <h3 id="modalFirmaTitulo">Captura de firma manuscrita</h3>
                <p>Dibuja tu firma con el mouse o el dedo en el recuadro</p>
            </div>
            <button type="button" class="firma-modal-close" onclick="cerrarModalFirma()" aria-label="Cerrar">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="firma-modal-body">
            <canvas id="firmaCanvas" class="firma-canvas"></canvas>

            <div class="firma-toolbar">
                <div class="firma-toolbar-group">
                    <label>Color</label>
                    <div class="firma-colors">
                        <button type="button" class="firma-color-btn active" data-color="#0B2931" style="background:#0B2931" title="Negro azulado"></button>
                        <button type="button" class="firma-color-btn" data-color="#1C5345" style="background:#1C5345" title="Verde oscuro"></button>
                        <button type="button" class="firma-color-btn" data-color="#000080" style="background:#000080" title="Azul"></button>
                        <button type="button" class="firma-color-btn" data-color="#000000" style="background:#000000" title="Negro"></button>
                    </div>
                </div>

                <div class="firma-toolbar-group">
                    <label>Grosor</label>
                    <div class="firma-sizes">
                        <button type="button" class="firma-size-btn" data-size="1" title="Fino"><span style="width:4px;height:4px"></span></button>
                        <button type="button" class="firma-size-btn active" data-size="2" title="Normal"><span style="width:8px;height:8px"></span></button>
                        <button type="button" class="firma-size-btn" data-size="4" title="Grueso"><span style="width:14px;height:14px"></span></button>
                    </div>
                </div>

                <div class="firma-toolbar-group firma-toolbar-actions">
                    <button type="button" class="btn btn-outline btn-sm" onclick="limpiarFirma()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Limpiar
                    </button>
                </div>
            </div>
        </div>

        <div class="firma-modal-footer">
            <button type="button" class="btn btn-outline" onclick="cerrarModalFirma()">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnGuardarFirma" onclick="guardarFirma()" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Guardar firma
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    var modal = document.getElementById('modalFirma');
    var canvas = document.getElementById('firmaCanvas');
    var ctx = canvas.getContext('2d');
    var btnGuardar = document.getElementById('btnGuardarFirma');

    var dibujando = false;
    var haDibujado = false;
    var color = '#0B2931';
    var grosor = 2;
    var ultimoPunto = null;

    function inicializarCanvas() {
        var contenedor = canvas.parentElement;
        var ancho = contenedor.offsetWidth;
        var ratio = window.devicePixelRatio || 1;
        canvas.width = ancho * ratio;
        canvas.height = 200 * ratio;
        canvas.style.width = ancho + 'px';
        canvas.style.height = '200px';
        ctx.scale(ratio, ratio);
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        limpiarFirma();
    }

    function obtenerPos(e) {
        var rect = canvas.getBoundingClientRect();
        var x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
        var y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
        return { x: x, y: y };
    }

    function iniciar(e) {
        e.preventDefault();
        dibujando = true;
        ultimoPunto = obtenerPos(e);
    }

    function dibujar(e) {
        if (!dibujando) return;
        e.preventDefault();
        var punto = obtenerPos(e);
        ctx.strokeStyle = color;
        ctx.lineWidth = grosor;
        ctx.beginPath();
        ctx.moveTo(ultimoPunto.x, ultimoPunto.y);
        ctx.lineTo(punto.x, punto.y);
        ctx.stroke();
        ultimoPunto = punto;
        if (!haDibujado) {
            haDibujado = true;
            btnGuardar.disabled = false;
        }
    }

    function terminar() {
        dibujando = false;
        ultimoPunto = null;
    }

    // Mouse events
    canvas.addEventListener('mousedown', iniciar);
    canvas.addEventListener('mousemove', dibujar);
    canvas.addEventListener('mouseup', terminar);
    canvas.addEventListener('mouseleave', terminar);

    // Touch events
    canvas.addEventListener('touchstart', iniciar, { passive: false });
    canvas.addEventListener('touchmove', dibujar, { passive: false });
    canvas.addEventListener('touchend', terminar);

    // Botones de color
    document.querySelectorAll('.firma-color-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.firma-color-btn').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            color = btn.getAttribute('data-color');
        });
    });

    // Botones de grosor
    document.querySelectorAll('.firma-size-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.firma-size-btn').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            grosor = parseInt(btn.getAttribute('data-size'), 10);
        });
    });

    window.abrirModalFirma = function() {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        haDibujado = false;
        btnGuardar.disabled = true;
        setTimeout(inicializarCanvas, 50);
    };

    window.cerrarModalFirma = function() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    window.limpiarFirma = function() {
        var ratio = window.devicePixelRatio || 1;
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, canvas.width / ratio, canvas.height / ratio);
        haDibujado = false;
        btnGuardar.disabled = true;
    };

    window.guardarFirma = function() {
        if (!haDibujado) return;

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="sv-spinner"></span>Guardando...';

        var dataUrl = canvas.toDataURL('image/png');

        var body = new URLSearchParams();
        body.append('firma_imagen', dataUrl);
        body.append('csrf_token', '<?= Security::getCsrfToken() ?>');

        fetch('<?= url('/panel/mi-perfil/firma') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: body.toString()
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'desconocido'));
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = 'Guardar firma';
            }
        })
        .catch(function(err) {
            alert('Error de conexion: ' + err.message);
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = 'Guardar firma';
        });
    };

    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            cerrarModalFirma();
        }
    });
})();
</script>
<?php endif; ?>
