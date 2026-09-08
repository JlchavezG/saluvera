/**
 * SALUVERA - Landing Page Interactions
 * 
 * Interacciones de la landing page usando Vanilla JS moderno.
 * Sin dependencias externas para máximo rendimiento.
 * 
 * Funcionalidades:
 * 1. Navbar con efecto cápsula al hacer scroll
 * 2. Mobile menu toggle
 * 3. FAQ acordeón
 * 4. Scroll reveal (animaciones de entrada)
 * 5. Smooth scroll para anchors
 * 6. Cierre de menú al hacer click en link
 * 
 * @author SALUVERA
 * @version 1.0.0
 */

'use strict';

/* ============================================
   1. NAVBAR - EFECTO CÁPSULA AL SCROLL
   ============================================ */

/**
 * Agrega o quita la clase 'scrolled' al navbar
 * cuando el usuario hace scroll más de 50px.
 * 
 * Esto transforma el navbar de transparente
 * a una cápsula con glassmorphism.
 */
function initNavbarScroll() {
    const navbar = document.getElementById('mainNavbar');
    
    // Verificar que el navbar exista antes de continuar
    if (!navbar) {
        console.warn('SALUVERA: Navbar no encontrado');
        return;
    }
    
    const SCROLL_THRESHOLD = 50; // Píxeles antes de activar el efecto
    
    /**
     * Verifica la posición del scroll y actualiza el navbar
     */
    const handleScroll = () => {
        if (window.scrollY > SCROLL_THRESHOLD) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    };
    
    // Ejecutar al cargar la página (por si el usuario ya hizo scroll)
    handleScroll();
    
    // Usar 'passive: true' para mejor rendimiento en scroll
    window.addEventListener('scroll', handleScroll, { passive: true });
}

/* ============================================
   2. MOBILE MENU - TOGGLE
   ============================================ */

/**
 * Maneja la apertura y cierre del menú móvil.
 * Incluye:
 * - Toggle del botón hamburguesa
 * - Toggle del drawer del menú
 * - Bloqueo del scroll del body cuando está abierto
 * - Cierre con tecla Escape (accesibilidad)
 */
function initMobileMenu() {
    const toggleButton = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    
    // Verificar que los elementos existan
    if (!toggleButton || !mobileMenu) {
        console.warn('SALUVERA: Mobile menu no encontrado');
        return;
    }
    
    let isOpen = false;
    
    /**
     * Abre o cierra el menú móvil
     * @param {boolean} open - true para abrir, false para cerrar
     */
    const toggleMenu = (open) => {
        isOpen = open;
        
        // Toggle de clases
        toggleButton.classList.toggle('active', isOpen);
        mobileMenu.classList.toggle('active', isOpen);
        
        // Actualizar atributos ARIA para accesibilidad
        toggleButton.setAttribute('aria-expanded', isOpen.toString());
        toggleButton.setAttribute('aria-label', isOpen ? 'Cerrar menú' : 'Abrir menú');
        
        // Bloquear scroll del body cuando el menú está abierto
        document.body.style.overflow = isOpen ? 'hidden' : '';
    };
    
    // Toggle al hacer click en el botón
    toggleButton.addEventListener('click', () => {
        toggleMenu(!isOpen);
    });
    
    // Cerrar con tecla Escape (accesibilidad)
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen) {
            toggleMenu(false);
            toggleButton.focus(); // Devolver el focus al botón
        }
    });
}

/* ============================================
   3. CIERRE DE MENÚ AL HACER CLICK EN LINK
   ============================================ */

/**
 * Cierra el menú móvil automáticamente cuando
 * el usuario hace click en un enlace de navegación.
 * 
 * Esto mejora la UX en mobile: el usuario selecciona
 * una sección y el menú se cierra solo.
 */
function initMenuLinkClose() {
    const mobileMenu = document.getElementById('mobileMenu');
    const toggleButton = document.getElementById('mobileMenuToggle');
    
    if (!mobileMenu || !toggleButton) {
        return;
    }
    
    // Seleccionar todos los links dentro del menú móvil
    const menuLinks = mobileMenu.querySelectorAll('a');
    
    menuLinks.forEach((link) => {
        link.addEventListener('click', () => {
            // Cerrar el menú
            toggleButton.classList.remove('active');
            mobileMenu.classList.remove('active');
            toggleButton.setAttribute('aria-expanded', 'false');
            toggleButton.setAttribute('aria-label', 'Abrir menú');
            document.body.style.overflow = '';
        });
    });
}

/* ============================================
   4. FAQ - ACORDEÓN
   ============================================ */

/**
 * Maneja el comportamiento del acordeón de FAQ.
 * 
 * Características:
 * - Solo un item abierto a la vez
 * - Animación suave de apertura/cierre
 * - Actualización de atributos ARIA
 * - Accesible por teclado
 */
function initFaqAccordion() {
    const faqItems = document.querySelectorAll('.salu-faq-item');
    
    if (faqItems.length === 0) {
        console.warn('SALUVERA: FAQ items no encontrados');
        return;
    }
    
    faqItems.forEach((item) => {
        const question = item.querySelector('.salu-faq-question');
        
        if (!question) return;
        
        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');
            
            // Cerrar todos los items
            faqItems.forEach((otherItem) => {
                otherItem.classList.remove('active');
                const otherQuestion = otherItem.querySelector('.salu-faq-question');
                if (otherQuestion) {
                    otherQuestion.setAttribute('aria-expanded', 'false');
                }
            });
            
            // Abrir el item clickeado (si estaba cerrado)
            if (!isActive) {
                item.classList.add('active');
                question.setAttribute('aria-expanded', 'true');
            }
        });
    });
}

/* ============================================
   5. SCROLL REVEAL - ANIMACIONES DE ENTRADA
   ============================================ */

/**
 * Agrega animaciones de entrada a los elementos
 * cuando entran en el viewport.
 * 
 * Usa Intersection Observer para máximo rendimiento
 * (no usa scroll events, es mucho más eficiente).
 * 
 * Los elementos deben tener la clase 'salu-reveal'
 * para ser animados.
 */
function initScrollReveal() {
    // Seleccionar todos los elementos que deben animarse
    const revealElements = document.querySelectorAll('.salu-reveal');
    
    if (revealElements.length === 0) {
        // Si no hay elementos con la clase, no hacer nada
        return;
    }
    
    // Verificar soporte de Intersection Observer
    if (!('IntersectionObserver' in window)) {
        // Fallback: mostrar todos los elementos inmediatamente
        revealElements.forEach((el) => {
            el.classList.add('visible');
        });
        return;
    }
    
    // Configuración del observer
    const observerOptions = {
        root: null, // viewport
        rootMargin: '0px 0px -50px 0px', // activar 50px antes de entrar
        threshold: 0.1 // 10% del elemento visible
    };
    
    /**
     * Callback ejecutado cuando un elemento entra/sale del viewport
     */
    const observerCallback = (entries, observer) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                // Agregar clase visible para animar
                entry.target.classList.add('visible');
                
                // Dejar de observar este elemento (solo se anima una vez)
                observer.unobserve(entry.target);
            }
        });
    };
    
    // Crear el observer
    const observer = new IntersectionObserver(observerCallback, observerOptions);
    
    // Observar cada elemento
    revealElements.forEach((element) => {
        observer.observe(element);
    });
}

/**
 * Agrega la clase 'salu-reveal' automáticamente a elementos
 * que deben animarse, basándose en su posición en el DOM.
 * 
 * Esto evita tener que agregar la clase manualmente en el HTML
 * para elementos comunes como cards.
 */
function setupRevealElements() {
    // Selectores de elementos que deben animarse
    const selectors = [
        '.salu-problem-card',
        '.salu-pillar-card',
        '.salu-feature-card',
        '.salu-benefit-item',
        '.salu-specialty-card',
        '.salu-security-card',
        '.salu-testimonial-card',
        '.salu-pricing-card',
        '.salu-faq-item',
        '.salu-section-header'
    ];
    
    selectors.forEach((selector) => {
        const elements = document.querySelectorAll(selector);
        elements.forEach((element, index) => {
            element.classList.add('salu-reveal');
            
            // Agregar delay escalonado para grids (máximo 4 niveles)
            const delayClass = `salu-reveal-delay-${(index % 4) + 1}`;
            element.classList.add(delayClass);
        });
    });
}

/* ============================================
   6. SMOOTH SCROLL PARA ANCHORS
   ============================================ */

/**
 * Habilita scroll suave para todos los enlaces
 * que apuntan a secciones de la página (anchors).
 * 
 * Compensa la altura del navbar fijo para que
 * el contenido no quede oculto debajo de él.
 */
function initSmoothScroll() {
    const anchors = document.querySelectorAll('a[href^="#"]');
    
    anchors.forEach((anchor) => {
        anchor.addEventListener('click', (event) => {
            const href = anchor.getAttribute('href');
            
            // Ignorar links vacíos o "#"
            if (!href || href === '#') {
                event.preventDefault();
                return;
            }
            
            const targetId = href.substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                event.preventDefault();
                
                // Calcular posición compensando el navbar fijo
                const navbarHeight = 80;
                const targetPosition = targetElement.getBoundingClientRect().top + window.scrollY - navbarHeight;
                
                // Scroll suave
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Actualizar la URL sin recargar la página
                history.pushState(null, null, href);
            }
        });
    });
}

/* ============================================
   7. INICIALIZACIÓN DE LUCIDE ICONS
   ============================================ */

/**
 * Inicializa los iconos de Lucide.
 * 
 * Nota: Lucide se inicializa en el HTML con lucide.createIcons(),
 * pero si se agregan iconos dinámicamente, se puede llamar
 * esta función para renderizarlos.
 */
function initLucideIcons() {
    // Verificar que Lucide esté disponible
    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        lucide.createIcons();
    } else {
        console.warn('SALUVERA: Lucide Icons no está cargado');
    }
}

/* ============================================
   8. INICIALIZACIÓN PRINCIPAL
   ============================================ */

/**
 * Función principal que inicializa todas las
 * interacciones de la landing page.
 * 
 * Se ejecuta cuando el DOM está completamente cargado.
 */
function initSaluveraLanding() {
    // 1. Configurar elementos que deben animarse
    setupRevealElements();
    
    // 2. Inicializar todas las funcionalidades
    initNavbarScroll();
    initMobileMenu();
    initMenuLinkClose();
    initFaqAccordion();
    initScrollReveal();
    initSmoothScroll();
    
    // 3. Inicializar iconos (por si acaso)
    initLucideIcons();
    
    // Log de confirmación en desarrollo
    console.log('%c🏥 SALUVERA', 'color: #123C46; font-size: 16px; font-weight: bold;');
    console.log('%cTecnología para cuidar lo que importa.', 'color: #4FD1B5; font-size: 12px;');
    console.log('%cLanding page inicializada correctamente ✓', 'color: #66777C; font-size: 11px;');
}

/* ============================================
   9. EJECUCIÓN
   ============================================ */

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
    // El DOM aún está cargando
    document.addEventListener('DOMContentLoaded', initSaluveraLanding);
} else {
    // El DOM ya está cargado (script al final del body)
    initSaluveraLanding();
}