<?php
/**
 * SALUVERA - Formulario de Consultorio con mapa interactivo
 *
 * @version 2.20.0
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

$consultorio = $consultorio ?? null;
$organizaciones = $organizaciones ?? [];
$orgDestino = $orgDestino ?? 0;
$esSuperAdmin = $esSuperAdmin ?? false;
$errores = $errores ?? [];
$old = $old ?? [];
$esEdicion = $consultorio !== null;

$flat = [];
foreach ($errores as $field => $msgs) {
    foreach ((array) $msgs as $m) {
        $flat[] = $m;
    }
}

$val = function ($key) use ($old, $consultorio) {
    if (isset($old[$key]) && $old[$key] !== '') {
        return htmlspecialchars($old[$key]);
    }
    if ($consultorio !== null && isset($consultorio[$key]) && $consultorio[$key] !== null) {
        return htmlspecialchars($consultorio[$key]);
    }
    return '';
};

$selOrg = function ($orgId) use ($old, $orgDestino) {
    $current = $old['organizacion_id'] ?? $orgDestino;
    return (string) $current === (string) $orgId ? 'selected' : '';
};

$accion = $esEdicion
    ? url('/panel/consultorios/' . (int) $consultorio['id'] . '/editar')
    : url('/panel/consultorios');

$latActual = (string) ($consultorio['latitud'] ?? ($old['latitud'] ?? ''));
$lngActual = (string) ($consultorio['longitud'] ?? ($old['longitud'] ?? ''));
$tieneCoords = ($latActual !== '' && $lngActual !== '');
?>
<div class="page-header">
    <div>
        <h2 class="page-title"><?= $esEdicion ? 'Editar Consultorio' : 'Nuevo Consultorio' ?></h2>
        <p class="page-subtitle">
            <?= $esEdicion
                ? 'Modificando ' . htmlspecialchars($consultorio['nombre'])
                : 'Los consultorios se usan al agendar citas' ?>
        </p>
    </div>
    <a href="<?= url('/panel/consultorios') ?>" class="btn btn-outline">Volver</a>
</div>

<?php if (!empty($flat)): ?>
    <div class="form-error-box" role="alert">
        <?= implode('<br>', array_map('htmlspecialchars', $flat)) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= $accion ?>" class="table-card form-card" novalidate>
    <?= Security::csrfField() ?>

    <?php if ($esSuperAdmin): ?>
        <div class="form-grid">
            <div class="form-group form-full">
                <label class="form-label" for="organizacion_id">Organizacion *</label>
                <select class="form-input" id="organizacion_id" name="organizacion_id" required>
                    <option value="">Selecciona la organizacion...</option>
                    <?php foreach ($organizaciones as $o): ?>
                        <option value="<?= (int) $o['id'] ?>" <?= $selOrg($o['id']) ?>><?= htmlspecialchars($o['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <hr class="form-divider">
    <?php endif; ?>

    <div class="form-grid">
        <div class="form-group">
            <label class="form-label" for="nombre">Nombre *</label>
            <input class="form-input" type="text" id="nombre" name="nombre" value="<?= $val('nombre') ?>" required maxlength="150" placeholder="Ej: Consultorio 1">
        </div>

        <div class="form-group">
            <label class="form-label" for="ubicacion">Ubicacion interna</label>
            <input class="form-input" type="text" id="ubicacion" name="ubicacion" value="<?= $val('ubicacion') ?>" maxlength="255" placeholder="Ej: Planta baja, junto a recepcion">
        </div>


    </div>

    <!-- ============ UBICACION GEOGRAFICA CON MAPA ============ -->
    <div class="geo-section">
        <div class="geo-header">
            <h3 class="geo-title">Ubicacion geografica (para que los pacientes lleguen)</h3>
        </div>

        <div class="geo-search-row">
            <input class="form-input" type="text" id="geo_direccion" name="direccion"
                   value="<?= $val('direccion') ?>" maxlength="500"
                   placeholder="Ej: Av. Reforma 123, Col. Centro, Ciudad de Mexico">
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

        <small class="geo-hint">Consejo: Escribe la direccion, usa el GPS, haz clic en el mapa o arrastra el marcador. Las coordenadas y direccion se llenan automaticamente.</small>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            <?= $esEdicion ? 'Guardar cambios' : 'Crear consultorio' ?>
        </button>
        <a href="<?= url('/panel/consultorios') ?>" class="btn btn-outline">Cancelar</a>
    </div>
</form>

<?php
$usarOpenCage = defined('OPENCAGE_API_KEY') && OPENCAGE_API_KEY !== '';
$apiGeocoder = $usarOpenCage ? OPENCAGE_API_KEY : '';
?>
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
