<?php
/**
 * SALUVERA - Formulario de Organizacion (superadmin) con mapa
 *
 * @version 2.22.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$org = $org ?? null;
$zonas = $zonas ?? [];
$planes = $planes ?? [];
$estados = $estados ?? [];
$errores = $errores ?? [];
$old = $old ?? [];
$esEdicion = $org !== null;

$flat = [];
foreach ($errores as $field => $msgs) {
    foreach ((array) $msgs as $m) {
        $flat[] = $m;
    }
}

$val = function ($key) use ($old, $org) {
    if (isset($old[$key]) && $old[$key] !== '') {
        return htmlspecialchars($old[$key]);
    }
    if ($org !== null && isset($org[$key]) && $org[$key] !== null) {
        return htmlspecialchars($org[$key]);
    }
    return '';
};

$sel = function ($key, $valor) use ($old, $org) {
    $current = $old[$key] ?? ($org[$key] ?? '');
    return $current === $valor ? 'selected' : '';
};

$accion = $esEdicion
    ? url('/panel/organizaciones/' . (int) $org['id'] . '/editar')
    : url('/panel/organizaciones');

$latActual = (string) ($org['latitud'] ?? ($old['latitud'] ?? ''));
$lngActual = (string) ($org['longitud'] ?? ($old['longitud'] ?? ''));
$tieneCoords = ($latActual !== '' && $lngActual !== '');
$usarOpenCage = defined('OPENCAGE_API_KEY') && OPENCAGE_API_KEY !== '';
$apiGeocoder = $usarOpenCage ? OPENCAGE_API_KEY : '';
?>
<div class="page-header">
    <div>
        <h2 class="page-title"><?= $esEdicion ? 'Editar Organizacion' : 'Nueva Organizacion' ?></h2>
        <p class="page-subtitle">
            <?= $esEdicion ? 'Modificando ' . htmlspecialchars($org['nombre']) : 'Registra un nuevo cliente de la plataforma' ?>
        </p>
    </div>
    <a href="<?= url('/panel/organizaciones') ?>" class="btn btn-outline">Volver</a>
</div>

<?php if (!empty($flat)): ?>
    <div class="form-error-box" role="alert">
        <?= implode('<br>', array_map('htmlspecialchars', $flat)) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= $accion ?>" class="table-card form-card" novalidate>
    <?= Security::csrfField() ?>

    <div class="form-grid">
        <div class="form-group form-full">
            <label class="form-label" for="nombre">Nombre de la organizacion *</label>
            <input class="form-input" type="text" id="nombre" name="nombre" value="<?= $val('nombre') ?>" required maxlength="255" placeholder="Ej: Clinica Santa Fe">
        </div>

        <div class="form-group form-full">
            <label class="form-label" for="geo_direccion">Direccion</label>
            <div class="geo-search-row">
                <input class="form-input" type="text" id="geo_direccion" name="direccion" value="<?= $val('direccion') ?>" maxlength="500" placeholder="Ej: Av. Reforma 123, Col. Centro, Ciudad de Mexico">
                <select class="search-select" id="geo_pais" aria-label="Pais">
                    <option value="">Todos los paises</option>
                    <option value="mx" selected>Mexico</option>
                    <option value="co">Colombia</option>
                    <option value="ar">Argentina</option>
                    <option value="cl">Chile</option>
                    <option value="pe">Peru</option>
                    <option value="gt">Guatemala</option>
                    <option value="ec">Ecuador</option>
                    <option value="ve">Venezuela</option>
                    <option value="bo">Bolivia</option>
                    <option value="uy">Uruguay</option>
                    <option value="py">Paraguay</option>
                    <option value="cr">Costa Rica</option>
                    <option value="pa">Panama</option>
                    <option value="sv">El Salvador</option>
                    <option value="hn">Honduras</option>
                    <option value="ni">Nicaragua</option>
                    <option value="do">Republica Dominicana</option>
                    <option value="us">Estados Unidos</option>
                    <option value="es">Espana</option>
                </select>
                <button type="button" class="btn btn-outline" id="geo_buscar">Buscar en mapa</button>
            </div>
            <div class="geo-suggestions" id="geo_sugerencias"></div>
        </div>

        <div class="form-group">
            <label class="form-label" for="correo">Correo</label>
            <input class="form-input" type="email" id="correo" name="correo" value="<?= $val('correo') ?>" maxlength="255">
        </div>

        <div class="form-group">
            <label class="form-label" for="telefono">Telefono</label>
            <input class="form-input" type="tel" id="telefono" name="telefono" value="<?= $val('telefono') ?>" maxlength="50">
        </div>

        <div class="form-group">
            <label class="form-label" for="zona_horaria">Zona horaria *</label>
            <select class="form-input" id="zona_horaria" name="zona_horaria" required>
                <?php foreach ($zonas as $valor => $nombre): ?>
                    <option value="<?= htmlspecialchars($valor) ?>" <?= $sel('zona_horaria', $valor) ?>><?= htmlspecialchars($nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="plan_suscripcion">Plan *</label>
            <select class="form-input" id="plan_suscripcion" name="plan_suscripcion" required>
                <?php foreach ($planes as $valor => $nombre): ?>
                    <option value="<?= htmlspecialchars($valor) ?>" <?= $sel('plan_suscripcion', $valor) ?>><?= htmlspecialchars($nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="estado_suscripcion">Estado *</label>
            <select class="form-input" id="estado_suscripcion" name="estado_suscripcion" required>
                <?php foreach ($estados as $valor => $nombre): ?>
                    <option value="<?= htmlspecialchars($valor) ?>" <?= $sel('estado_suscripcion', $valor) ?>><?= htmlspecialchars($nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- ============ UBICACION GEOGRAFICA CON MAPA ============ -->
    <div class="geo-section">
        <div class="geo-header">
            <h3 class="geo-title">Ubicacion geografica del negocio</h3>
        </div>

        <div class="geo-map" id="geo_map"></div>

        <div class="geo-actions">
            <button type="button" class="btn-geo" id="geo_gps">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/>
                </svg>
                Usar mi ubicacion
            </button>
            <?php if ($tieneCoords): ?>
                <a class="btn-geo" target="_blank" rel="noopener"
                   href="https://www.google.com/maps?q=<?= htmlspecialchars($latActual) ?>,<?= htmlspecialchars($lngActual) ?>">
                    Ver en Google Maps
                </a>
            <?php endif; ?>
        </div>

        <div class="geo-coords-row">
            <div class="form-group">
                <label class="form-label" for="latitud">Latitud (automatico)</label>
                <input class="form-input" type="number" step="0.000001" id="latitud" name="latitud" value="<?= htmlspecialchars($latActual) ?>" placeholder="Se llena solo">
            </div>
            <div class="form-group">
                <label class="form-label" for="longitud">Longitud (automatico)</label>
                <input class="form-input" type="number" step="0.000001" id="longitud" name="longitud" value="<?= htmlspecialchars($lngActual) ?>" placeholder="Se llena solo">
            </div>
        </div>

        <small class="geo-hint">Escribe la direccion arriba y pulsa "Buscar en mapa", haz clic en el mapa, arrastra el marcador o usa el GPS.</small>
    </div>

    <?php if (!$esEdicion): ?>
        <div class="org-admin-section">
            <h3 class="org-admin-title">Administrador de la clinica (opcional)</h3>
            <p class="cell-muted">Si completas estos campos, se creara automaticamente el usuario administrador de esta organizacion.</p>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="admin_nombre">Nombre del admin</label>
                    <input class="form-input" type="text" id="admin_nombre" name="admin_nombre" maxlength="100" placeholder="Ej: Maria Gonzalez">
                </div>

                <div class="form-group">
                    <label class="form-label" for="admin_correo">Correo del admin</label>
                    <input class="form-input" type="email" id="admin_correo" name="admin_correo" maxlength="255" placeholder="admin@clinica.com">
                </div>

                <div class="form-group">
                    <label class="form-label" for="admin_password">Contrasena</label>
                    <input class="form-input" type="password" id="admin_password" name="admin_password" minlength="8" autocomplete="new-password">
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <?= $esEdicion ? 'Guardar cambios' : 'Crear organizacion' ?>
        </button>
        <a href="<?= url('/panel/organizaciones') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>

<script src="<?= url('app-assets/js/geo-picker.js') ?>"></script>
<script>
    svInitGeoPicker({
        provider: '<?= $usarOpenCage ? 'opencage' : 'osm' ?>',
        apiKey: '<?= htmlspecialchars($apiGeocoder) ?>',
        mapId: 'geo_map',
        latId: 'latitud',
        lngId: 'longitud',
        addrId: 'geo_direccion',
        searchBtnId: 'geo_buscar',
        countrySelId: 'geo_pais',
        suggestionsId: 'geo_sugerencias',
        geoBtnId: 'geo_gps',
        initialLat: '<?= htmlspecialchars($latActual) ?>',
        initialLng: '<?= htmlspecialchars($lngActual) ?>'
    });
</script>
