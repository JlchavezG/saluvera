<?php
/**
 * SALUVERA - Configuracion de mapas y geolocalizacion
 *
 * Proveedor: OpenCage (gratuito, sin billing, excelente en LatAm)
 * Registrate en https://opencagedata.com y pega aqui tu API key
 *
 * Limite gratis: 2500 peticiones / dia
 */

if (!defined('SALUVERA_APP')) {
    exit('Acceso directo no permitido');
}

define('MAPS_PROVIDER', 'opencage');
define('OPENCAGE_API_KEY', '2993cacd4efa4fe39b499a5789aa1e36'); // <-- PEGA AQUI TU API KEY de OpenCage
