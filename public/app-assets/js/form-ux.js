/**
 * SALUVERA - form-ux v2
 * UX global de formularios + toggle de visibilidad de contrasena
 */
(function () {
    var ICONO_OJO = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    var ICONO_OJO_CERRADO = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

    function init() {
        var forms = document.querySelectorAll('form.form-card, form.auth-form');

        forms.forEach(function (form) {
            var hayErrores = !!document.querySelector('.form-error-box');

            // ---------- 1. Autofocus ----------
            if (!hayErrores) {
                var first = form.querySelector('input:not([type=hidden]):not([disabled]):not([type=password]), select:not([disabled])');
                if (first) {
                    try { first.focus({ preventScroll: true }); } catch (e) { first.focus(); }
                }
            }

            // ---------- 2. Autocomplete ----------
            form.querySelectorAll('input, select, textarea').forEach(function (el) {
                var n = (el.name || el.id || '').toLowerCase();
                if (n === '') return;

                if (n.indexOf('password') !== -1) {
                    el.autocomplete = 'new-password';
                    return;
                }
                if (el.autocomplete && el.autocomplete !== '' && el.autocomplete !== 'off') return;

                var map = {
                    'correo': 'email',
                    'telefono': 'tel',
                    'nombre': 'given-name',
                    'apellidos': 'family-name',
                    'fecha_nacimiento': 'bday',
                    'ciudad': 'address-level2',
                    'direccion': 'street-address',
                    'ocupacion': 'organization-title'
                };

                for (var k in map) {
                    if (n === k) { el.autocomplete = map[k]; break; }
                }
            });

            // ---------- 3. Mascara de telefono ----------
            form.querySelectorAll('input[type=tel]').forEach(function (el) {
                el.addEventListener('input', function () {
                    if (el.value.charAt(0) === '+') return;
                    var d = el.value.replace(/\D/g, '').slice(0, 10);
                    var out = d;
                    if (d.length > 7) {
                        out = d.slice(0, 2) + ' ' + d.slice(2, 6) + ' ' + d.slice(6);
                    } else if (d.length > 2) {
                        out = d.slice(0, 2) + ' ' + d.slice(2);
                    }
                    el.value = out;
                });
            });

            // ---------- 4. Contrasena: ojito + generador ----------
            form.querySelectorAll('input[type=password]').forEach(function (pw) {
                if (pw.dataset.uxpass) return;
                pw.dataset.uxpass = '1';

                // Wrapper relativo para posicionar el ojito dentro del campo
                var wrap = document.createElement('span');
                wrap.className = 'ux-pass-wrap';
                pw.parentNode.insertBefore(wrap, pw);
                wrap.appendChild(pw);

                // Boton ojito
                var ojo = document.createElement('button');
                ojo.type = 'button';
                ojo.className = 'btn-ux-eye';
                ojo.title = 'Mostrar contrasena';
                ojo.setAttribute('aria-label', 'Mostrar u ocultar contrasena');
                ojo.innerHTML = ICONO_OJO;
                ojo.addEventListener('click', function () {
                    var visible = pw.type === 'text';
                    pw.type = visible ? 'password' : 'text';
                    ojo.innerHTML = visible ? ICONO_OJO : ICONO_OJO_CERRADO;
                    ojo.title = visible ? 'Mostrar contrasena' : 'Ocultar contrasena';
                    pw.focus();
                });
                wrap.appendChild(ojo);

                // Generador de contrasena segura
                var gen = document.createElement('button');
                gen.type = 'button';
                gen.className = 'btn-ux-gen';
                gen.textContent = 'Generar contrasena segura';
                gen.addEventListener('click', function () {
                    var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
                    var out = '';
                    var arr = new Uint32Array(12);
                    (window.crypto || window.msCrypto).getRandomValues(arr);
                    for (var i = 0; i < 12; i++) {
                        out += chars[arr[i] % chars.length];
                    }
                    pw.type = 'text';
                    pw.value = out;
                    ojo.innerHTML = ICONO_OJO_CERRADO;
                    ojo.title = 'Ocultar contrasena';
                    pw.focus();
                });
                wrap.insertAdjacentElement('afterend', gen);
            });

            // ---------- 5. Anti doble submit ----------
            form.addEventListener('submit', function () {
                var btns = form.querySelectorAll('button[type=submit]');
                btns.forEach(function (b) {
                    if (!b.dataset.orig) b.dataset.orig = b.textContent;
                    b.disabled = true;
                    b.textContent = 'Guardando...';
                });
                setTimeout(function () {
                    btns.forEach(function (b) {
                        b.disabled = false;
                        b.textContent = b.dataset.orig;
                    });
                }, 6000);
            });

            // ---------- 6. Secciones por modulo ----------
            var action = form.getAttribute('action') || '';
            var sections = null;

            if (action.indexOf('/panel/pacientes') === 0) {
                sections = { nombre: 'Datos personales', correo: 'Contacto', tipo_sangre: 'Datos clinicos', notas: 'Notas internas' };
            } else if (action.indexOf('/panel/profesionales') === 0) {
                sections = { nombre: 'Datos personales', correo: 'Credenciales de acceso', especialidad_id: 'Datos profesionales' };
            } else if (action.indexOf('/panel/agenda') === 0) {
                sections = { paciente_id: 'Datos de la cita', fecha_cita: 'Fecha y horario', motivo: 'Detalle de la consulta' };
            } else if (action.indexOf('/panel/consultorios') === 0) {
                sections = { nombre: 'Datos del consultorio' };
            } else if (action.indexOf('/panel/organizacion') === 0 || action.indexOf('/panel/organizaciones') === 0) {
                sections = { nombre: 'Datos del negocio' };
            }

            if (sections) {
                Object.keys(sections).forEach(function (field) {
                    var el = form.querySelector('[name="' + field + '"]');
                    if (!el) return;
                    var group = el.closest('.form-group');
                    if (!group || group.dataset.uxsec) return;
                    var t = document.createElement('div');
                    t.className = 'form-section-title';
                    t.textContent = sections[field];
                    group.parentNode.insertBefore(t, group);
                    group.dataset.uxsec = '1';
                });
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
