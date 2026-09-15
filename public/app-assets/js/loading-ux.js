/**
 * SALUVERA - Loading UX
 * Barra de progreso superior + spinner en boton
 */
(function() {
    'use strict';

    // Crear barra de progreso superior
    var bar = document.createElement('div');
    bar.id = 'sv-progress';
    bar.innerHTML = '<div class="sv-progress-bar"></div>';
    document.body.appendChild(bar);

    var enviando = false;

    function iniciarProgreso() {
        bar.classList.add('active');
    }

    // Funcion global para submits que usan form.submit() directo
    window.svSubmit = function(form) {
        if (!form) return;
        if (form.requestSubmit) {
            form.requestSubmit();
        } else {
            iniciarProgreso();
            form.submit();
        }
    };

    // Interceptar todos los submits de formularios
    document.addEventListener('submit', function(e) {
        if (e.defaultPrevented) return;

        if (enviando) {
            e.preventDefault();
            return;
        }
        enviando = true;

        iniciarProgreso();

        var btn = e.target.querySelector('button[type="submit"]');
        if (btn && !btn.classList.contains('btn-loading')) {
            btn.classList.add('btn-loading');
            btn.setAttribute('data-original', btn.innerHTML);
            btn.innerHTML = '<span class="sv-spinner"></span>Procesando...';
        }
    });

    // Animacion al navegar por enlaces del panel
    document.addEventListener('click', function(e) {
        var link = e.target.closest('a[href]');
        if (!link) return;

        var href = link.getAttribute('href');
        // Solo para enlaces internos que no sean # ni javascript
        if (href && href.indexOf('#') !== 0 && href.indexOf('javascript') !== 0 
            && !link.hasAttribute('download') && link.getAttribute('target') !== '_blank') {
            iniciarProgreso();
        }
    });
})();
