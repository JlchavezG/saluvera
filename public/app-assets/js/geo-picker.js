/**
 * SALUVERA - geo-picker v5
 * Leaflet + OpenCage (geocoder principal) / Nominatim (respaldo)
 *
 * Funcionalidades:
 * - Escribir direccion -> sugerencias -> marcador
 * - Clic en el mapa -> marcador + reverse geocoding
 * - Arrastrar marcador -> actualiza direccion
 * - GPS
 */
function svInitGeoPicker(cfg) {
    var mapEl = document.getElementById(cfg.mapId);
    if (!mapEl || typeof L === 'undefined') { return; }

    var c = svGeoCommon(cfg);
    var map = L.map(cfg.mapId, { scrollWheelZoom: false });
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var marker = null;

    function reverse(lat, lng) {
        if (!c.addrInput) return;
        // Intento 1: OpenCage
        if (cfg.provider === 'opencage' && cfg.apiKey) {
            fetch('https://api.opencagedata.com/geocode/v1/json?q=' + lat + '%2C' + lng + '&key=' + cfg.apiKey + '&language=es&pretty=1&limit=1')
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.results && d.results[0]) {
                        c.addrInput.value = d.results[0].formatted;
                    }
                })
                .catch(function() { reverseNominatim(lat, lng); });
            return;
        }
        reverseNominatim(lat, lng);
    }

    function reverseNominatim(lat, lng) {
        if (!c.addrInput) return;
        fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=es&lat=' + lat + '&lon=' + lng)
            .then(function(r) { return r.json(); })
            .then(function(d) { if (d && d.display_name) c.addrInput.value = d.display_name; })
            .catch(function() {});
    }

    function place(lat, lng, zoom) {
        if (marker === null) {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function () {
                var p = marker.getLatLng();
                c.fill(p.lat, p.lng);
                reverse(p.lat, p.lng);
            });
        } else {
            marker.setLatLng([lat, lng]);
        }
        map.setView([lat, lng], zoom || 16);
        c.fill(lat, lng);
    }

    // === NUEVO: Clic en el mapa para colocar marcador ===
    map.on('click', function(e) {
        place(e.latlng.lat, e.latlng.lng, 16);
        reverse(e.latlng.lat, e.latlng.lng);
    });

    function doSearch(q) {
        var code = c.countrySel ? c.countrySel.value : '';

        if (cfg.provider === 'opencage' && cfg.apiKey) {
            var url = 'https://api.opencagedata.com/geocode/v1/json?q=' + encodeURIComponent(q) +
                      '&key=' + cfg.apiKey + '&language=es&pretty=1&limit=5';
            if (code !== '') {
                var countryNames = { mx:'Mexico', co:'Colombia', ar:'Argentina', cl:'Chile', pe:'Peru',
                    gt:'Guatemala', ec:'Ecuador', ve:'Venezuela', bo:'Bolivia', uy:'Uruguay',
                    py:'Paraguay', cr:'Costa Rica', pa:'Panama', sv:'El Salvador',
                    hn:'Honduras', ni:'Nicaragua', do:'Republica Dominicana',
                    us:'United States', es:'Spain' };
                var cn = countryNames[code];
                if (cn) url += '&countrycode=' + code + '&bounds=&proximity=';
            }

            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.status.code !== 200) {
                        searchNominatim(q, code);
                        return;
                    }
                    if (!data.results || data.results.length === 0) {
                        searchNominatim(q, code);
                        return;
                    }
                    if (data.results.length === 1) {
                        var r = data.results[0];
                        place(r.geometry.lat, r.geometry.lng, 17);
                        if (c.addrInput) c.addrInput.value = r.formatted;
                        c.hideSug();
                    } else {
                        c.showSug(data.results.slice(0, 5).map(function(r) {
                            return {
                                label: r.formatted,
                                onPick: function() {
                                    place(r.geometry.lat, r.geometry.lng, 17);
                                    if (c.addrInput) c.addrInput.value = r.formatted;
                                }
                            };
                        }));
                    }
                })
                .catch(function() { searchNominatim(q, code); });
        } else {
            searchNominatim(q, code);
        }
    }

    function searchNominatim(q, code) {
        var url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=5&accept-language=es&q=' + encodeURIComponent(q);
        if (code !== '') { url += '&countrycodes=' + code; }
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data || data.length === 0) {
                    c.hideSug();
                    alert('No se encontro la direccion.\n\nConsejos:\n- Incluye calle, colonia, ciudad y pais\n- Ejemplo: "Av. Reforma 123, Col. Centro, Ciudad de Mexico"');
                    return;
                }
                if (data.length === 1) {
                    place(parseFloat(data[0].lat), parseFloat(data[0].lon), 17);
                    if (c.addrInput) c.addrInput.value = data[0].display_name;
                    c.hideSug();
                } else {
                    c.showSug(data.map(function(r) {
                        return {
                            label: r.display_name,
                            onPick: function() {
                                place(parseFloat(r.lat), parseFloat(r.lon), 17);
                                if (c.addrInput) c.addrInput.value = r.display_name;
                            }
                        };
                    }));
                }
            })
            .catch(function() { alert('Error al buscar la direccion.'); });
    }

    if (c.searchBtn && c.addrInput) {
        c.searchBtn.addEventListener('click', function () {
            var q = c.addrInput.value.trim();
            if (q === '') { alert('Escribe una direccion para buscar.'); return; }
            c.hideSug();
            doSearch(q);
        });

        c.addrInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); c.searchBtn.click(); }
        });
    }

    c.wireGps(function (lat, lng) {
        place(lat, lng, 17);
        reverse(lat, lng);
    });

    var il = parseFloat(cfg.initialLat), ig = parseFloat(cfg.initialLng);
    if (!isNaN(il) && !isNaN(ig)) { place(il, ig, 16); } else { map.setView([19.432608, -99.133209], 5); }
}

/* ================= HELPERS COMUNES ================= */
function svGeoCommon(cfg) {
    var latInput = document.getElementById(cfg.latId);
    var lngInput = document.getElementById(cfg.lngId);
    var addrInput = cfg.addrId ? document.getElementById(cfg.addrId) : null;
    var countrySel = cfg.countrySelId ? document.getElementById(cfg.countrySelId) : null;
    var sugBox = cfg.suggestionsId ? document.getElementById(cfg.suggestionsId) : null;
    var searchBtn = cfg.searchBtnId ? document.getElementById(cfg.searchBtnId) : null;
    var geoBtn = cfg.geoBtnId ? document.getElementById(cfg.geoBtnId) : null;

    return {
        latInput: latInput, lngInput: lngInput, addrInput: addrInput,
        countrySel: countrySel, sugBox: sugBox, searchBtn: searchBtn, geoBtn: geoBtn,
        fill: function (lat, lng) {
            if (latInput) latInput.value = Number(lat).toFixed(6);
            if (lngInput) lngInput.value = Number(lng).toFixed(6);
        },
        hideSug: function () { if (sugBox) sugBox.innerHTML = ''; },
        showSug: function (items) {
            if (!sugBox) return;
            sugBox.innerHTML = '';
            var t = document.createElement('div');
            t.className = 'geo-suggestions-title';
            t.textContent = items.length + ' resultado(s) — elige el correcto:';
            sugBox.appendChild(t);
            items.forEach(function (it) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'geo-suggestion';
                b.textContent = it.label;
                b.addEventListener('click', function () {
                    it.onPick();
                    sugBox.innerHTML = '';
                });
                sugBox.appendChild(b);
            });
        },
        wireGps: function (onLocate) {
            if (!geoBtn) return;
            geoBtn.addEventListener('click', function () {
                if (!navigator.geolocation) { alert('Tu navegador no soporta geolocalizacion.'); return; }
                geoBtn.disabled = true;
                navigator.geolocation.getCurrentPosition(function (pos) {
                    geoBtn.disabled = false;
                    onLocate(pos.coords.latitude, pos.coords.longitude);
                }, function () {
                    geoBtn.disabled = false;
                    alert('No se pudo obtener tu ubicacion.');
                });
            });
        }
    };
}
