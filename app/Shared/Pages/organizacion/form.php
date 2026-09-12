<?php
/**
 * SALUVERA - Mi Organizacion con mapa interactivo
 *
 * @version 2.22.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$org = $org ?? [];
$zonas = $zonas ?? [];
$errores = $errores ?? [];
$old = $old ?? [];

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
    if (isset($org[$key]) && $org[$key] !== null) {
        return htmlspecialchars($org[$key]);
    }
    return '';
};

$sel = function ($zona) use ($old, $org) {
    $current = $old['zona_horaria'] ?? ($org['zona_horaria'] ?? '');
    return $current === $zona ? 'selected' : '';
};

$latActual = (string) ($org['latitud'] ?? ($old['latitud'] ?? ''));
$lngActual = (string) ($org['longitud'] ?? ($old['longitud'] ?? ''));
$tieneCoords = ($latActual !== '' && $lngActual !== '');
$usarOpenCage = defined('OPENCAGE_API_KEY') && OPENCAGE_API_KEY !== '';
$apiGeocoder = $usarOpenCage ? OPENCAGE_API_KEY : '';
?>
<div class="page-header">
    <div>
        <h2 class="page-title">Editar Organizacion</h2>
        <p class="page-subtitle">Actualiza los datos de tu negocio</p>
    </div>
    <a href="<?= url('/panel/organizacion') ?>" class="btn btn-outline">Volver</a>
</div>

<?php if (!empty($flat)): ?>
    <div class="form-error-box" role="alert">
        <?= implode('<br>', array_map('htmlspecialchars', $flat)) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= url('/panel/organizacion/editar') ?>" class="table-card form-card" novalidate>
    <?= Security::csrfField() ?>

    <div class="form-grid">
        <div class="form-group form-full">
            <label class="form-label" for="nombre">Nombre del negocio *</label>
            <input class="form-input" type="text" id="nombre" name="nombre" value="<?= $val('nombre') ?>" required maxlength="255" placeholder="Ej: Clinica Santa Fe, Consultorio Dr. Ramirez">
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
            <label class="form-label" for="correo">Correo de contacto</label>
            <input class="form-input" type="email" id="correo" name="correo" value="<?= $val('correo') ?>" maxlength="255" placeholder="contacto@tunegocio.com">
        </div>

        <div class="form-group">
            <label class="form-label" for="telefono">Telefono de contacto</label>
            <input class="form-input" type="tel" id="telefono" name="telefono" value="<?= $val('telefono') ?>" maxlength="50">
        </div>

        <div class="form-group">
            <label class="form-label" for="zona_horaria">Zona horaria *</label>
            <select class="form-input" id="zona_horaria" name="zona_horaria" required>
                <?php foreach ($zonas as $valor => $nombre): ?>
                    <option value="<?= htmlspecialchars($valor) ?>" <?= $sel($valor) ?>><?= htmlspecialchars($nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- ============ UBICACION GEOGRAFICA CON MAPA ============ -->
    <div class="geo-section">
        <div class="geo-header">
            <h3 class="geo-title">Ubicacion geografica (para que los pacientes lleguen)</h3>
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

        <small class="geo-hint">Escribe la direccion arriba y pulsa "Buscar en mapa", haz clic en el mapa, arrastra el marcador o usa el GPS. Todo se llena solo.</small>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="<?= url('/panel/organizacion') ?>" class="btn btn-outline">Cancelar</a>
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
