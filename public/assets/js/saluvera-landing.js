/**
 * SALUVERA - Landing Page Interactions
 *
 * Interacciones de la landing page usando Vanilla JS moderno.
 * Sin dependencias externas para maximo rendimiento.
 *
 * Funcionalidades:
 * 1. Navbar con efecto capsula al hacer scroll
 * 2. Menu movil con focus trap
 * 3. FAQ acordeon accesible
 * 4. Scroll reveal (animaciones de entrada)
 * 5. Smooth scroll con reduced motion
 * 6. Cierre de menu al hacer click en link
 * 7. Theme Manager (Dark Mode hibrido)
 * 8. Boton ir arriba
 * 9. Manejo de resize
 *
 * @author SALUVERA
 * @version 2.2.0
 */

/* ============================================
   IIFE PARA EVITAR CONTAMINACION GLOBAL
   ============================================ */
(function () {
    'use strict';

    /* ============================================
       CONFIGURACION Y CONSTANTES
       ============================================ */
    const CONFIG = Object.freeze({
        SCROLL_THRESHOLD: 50,
        NAVBAR_HEIGHT: 100,
        THEME_STORAGE_KEY: 'saluvera-theme',
        DEBUG_MODE: false,
        REVEAL_ROOT_MARGIN: '0px 0px -50px 0px',
        REVEAL_THRESHOLD: 0.1,
        LUCIDE_RETRY_ATTEMPTS: 10,
        LUCIDE_RETRY_INTERVAL: 100,
        MOBILE_BREAKPOINT: 992,
        SCROLL_TOP_THRESHOLD: 300
    });

    /* ============================================
       SISTEMA DE LOGGING
       ============================================ */
    const Logger = {
        info: (message, data = null) => {
            if (CONFIG.DEBUG_MODE) {
                console.log('[SALUVERA] ' + message, data ?? '');
            }
        },
        warn: (message, data = null) => {
            console.warn('[SALUVERA] ' + message, data ?? '');
        },
        error: (message, data = null) => {
            console.error('[SALUVERA] ' + message, data ?? '');
        },
        branded: () => {
            console.log(
                '%cSALUVERA%c v2.2.0 - Tecnologia para cuidar lo que importa.',
                'color: #123C46; font-size: 16px; font-weight: bold;',
                'color: #4FD1B5; font-size: 12px;'
            );
        }
    };

    /* ============================================
       UTILIDADES
       ============================================ */
    const prefersReducedMotion = () => {
        return window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches ?? false;
    };

    const isLocalStorageAvailable = () => {
        try {
            const testKey = '__saluvera_test__';
            localStorage.setItem(testKey, testKey);
            localStorage.removeItem(testKey);
            return true;
        } catch (e) {
            return false;
        }
    };

    const debounce = (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    const getElement = (selector, all = false) => {
        try {
            if (selector.startsWith('#')) {
                return document.getElementById(selector.substring(1));
            }
            return all
                ? document.querySelectorAll(selector)
                : document.querySelector(selector);
        } catch (error) {
            Logger.warn('Selector invalido: ' + selector);
            return all ? [] : null;
        }
    };

    /* ============================================
       1. NAVBAR - EFECTO CAPSULA AL SCROLL
       ============================================ */
    const initNavbarScroll = () => {
        const navbar = getElement('#mainNavbar');

        if (!navbar) {
            Logger.warn('Navbar no encontrado');
            return;
        }

        let lastKnownScrollY = window.scrollY;
        let ticking = false;

        const updateNavbar = () => {
            if (lastKnownScrollY > CONFIG.SCROLL_THRESHOLD) {
                if (!navbar.classList.contains('scrolled')) {
                    navbar.classList.add('scrolled');
                }
            } else {
                if (navbar.classList.contains('scrolled')) {
                    navbar.classList.remove('scrolled');
                }
            }
            ticking = false;
        };

        const onScroll = () => {
            lastKnownScrollY = window.scrollY;
            if (!ticking) {
                window.requestAnimationFrame(updateNavbar);
                ticking = true;
            }
        };

        updateNavbar();
        window.addEventListener('scroll', onScroll, { passive: true });
    };

    /* ============================================
       2. MENU MOVIL CON FOCUS TRAP
       ============================================ */
    const initMobileMenu = () => {
        const toggleButton = getElement('#mobileMenuToggle');
        const mobileMenu = getElement('#mobileMenu');

        if (!toggleButton || !mobileMenu) {
            Logger.warn('Menu movil no encontrado');
            return;
        }

        let isOpen = false;
        let previouslyFocusedElement = null;

        const getFocusableElements = () => {
            const focusableSelectors = [
                'a[href]',
                'button:not([disabled])',
                'input:not([disabled])',
                'textarea:not([disabled])',
                'select:not([disabled])',
                '[tabindex]:not([tabindex="-1"])'
            ].join(', ');

            return Array.from(mobileMenu.querySelectorAll(focusableSelectors));
        };

        const trapFocus = (event) => {
            if (event.key !== 'Tab') return;

            const focusableElements = getFocusableElements();
            if (focusableElements.length === 0) return;

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (event.shiftKey) {
                if (document.activeElement === firstElement) {
                    event.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    event.preventDefault();
                    firstElement.focus();
                }
            }
        };

        const toggleMenu = (open) => {
            isOpen = open;

            if (open) {
                previouslyFocusedElement = document.activeElement;
            }

            toggleButton.classList.toggle('active', isOpen);
            mobileMenu.classList.toggle('active', isOpen);

            toggleButton.setAttribute('aria-expanded', String(isOpen));
            toggleButton.setAttribute('aria-label', isOpen ? 'Cerrar menu' : 'Abrir menu');
            mobileMenu.setAttribute('aria-hidden', String(!isOpen));

            if (isOpen) {
                const scrollY = window.scrollY;
                document.body.style.position = 'fixed';
                document.body.style.top = '-' + scrollY + 'px';
                document.body.style.width = '100%';
                document.body.dataset.scrollPosition = String(scrollY);
            } else {
                const scrollY = parseInt(document.body.dataset.scrollPosition || '0', 10);
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                window.scrollTo(0, scrollY);
                delete document.body.dataset.scrollPosition;

                if (previouslyFocusedElement && previouslyFocusedElement.focus) {
                    previouslyFocusedElement.focus();
                }
            }

            if (isOpen) {
                document.addEventListener('keydown', trapFocus);
                const focusableElements = getFocusableElements();
                if (focusableElements.length > 0) {
                    setTimeout(() => focusableElements[0].focus(), 100);
                }
            } else {
                document.removeEventListener('keydown', trapFocus);
            }
        };

        toggleButton.addEventListener('click', () => {
            toggleMenu(!isOpen);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && isOpen) {
                toggleMenu(false);
            }
        });

        window.addEventListener('resize', debounce(() => {
            if (window.innerWidth >= CONFIG.MOBILE_BREAKPOINT && isOpen) {
                toggleMenu(false);
            }
        }, 150));
    };

    /* ============================================
       3. CIERRE DE MENU AL HACER CLICK EN LINK
       ============================================ */
    const initMenuLinkClose = () => {
        const mobileMenu = getElement('#mobileMenu');
        const toggleButton = getElement('#mobileMenuToggle');

        if (!mobileMenu || !toggleButton) {
            return;
        }

        const menuLinks = mobileMenu.querySelectorAll('a');

        menuLinks.forEach((link) => {
            link.addEventListener('click', () => {
                if (mobileMenu.classList.contains('active')) {
                    toggleButton.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    toggleButton.setAttribute('aria-expanded', 'false');
                    toggleButton.setAttribute('aria-label', 'Abrir menu');
                    mobileMenu.setAttribute('aria-hidden', 'true');

                    const scrollY = parseInt(document.body.dataset.scrollPosition || '0', 10);
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.width = '';
                    if (scrollY) {
                        window.scrollTo(0, scrollY);
                    }
                    delete document.body.dataset.scrollPosition;
                }
            });
        });
    };

    /* ============================================
       4. FAQ - ACORDEON ACCESIBLE
       ============================================ */
    const initFaqAccordion = () => {
        const faqItems = getElement('.salu-faq-item', true);

        if (!faqItems || faqItems.length === 0) {
            Logger.warn('FAQ items no encontrados');
            return;
        }

        const openItem = (item, question, answer) => {
            item.classList.add('active');
            question.setAttribute('aria-expanded', 'true');
            answer.style.maxHeight = answer.scrollHeight + 'px';
        };

        const closeItem = (item, question, answer) => {
            item.classList.remove('active');
            question.setAttribute('aria-expanded', 'false');
            answer.style.maxHeight = '0';
        };

        faqItems.forEach((item) => {
            const question = item.querySelector('.salu-faq-question');
            const answer = item.querySelector('.salu-faq-answer');

            if (!question || !answer) return;

            if (item.classList.contains('active')) {
                answer.style.maxHeight = answer.scrollHeight + 'px';
                question.setAttribute('aria-expanded', 'true');
            } else {
                answer.style.maxHeight = '0';
                question.setAttribute('aria-expanded', 'false');
            }

            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');

                faqItems.forEach((otherItem) => {
                    if (otherItem !== item) {
                        const otherQuestion = otherItem.querySelector('.salu-faq-question');
                        const otherAnswer = otherItem.querySelector('.salu-faq-answer');
                        if (otherQuestion && otherAnswer && otherItem.classList.contains('active')) {
                            closeItem(otherItem, otherQuestion, otherAnswer);
                        }
                    }
                });

                if (isActive) {
                    closeItem(item, question, answer);
                } else {
                    openItem(item, question, answer);
                }
            });

            question.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    question.click();
                }
            });
        });
    };

    /* ============================================
       5. SCROLL REVEAL - ANIMACIONES DE ENTRADA
       ============================================ */
    const initScrollReveal = () => {
        const revealElements = getElement('.salu-reveal', true);

        if (!revealElements || revealElements.length === 0) {
            return;
        }

        if (prefersReducedMotion()) {
            revealElements.forEach((el) => {
                el.classList.add('visible');
            });
            return;
        }

        if (!('IntersectionObserver' in window)) {
            revealElements.forEach((el) => {
                el.classList.add('visible');
            });
            return;
        }

        const observerOptions = {
            root: null,
            rootMargin: CONFIG.REVEAL_ROOT_MARGIN,
            threshold: CONFIG.REVEAL_THRESHOLD
        };

        const observerCallback = (entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        };

        const observer = new IntersectionObserver(observerCallback, observerOptions);

        revealElements.forEach((element) => {
            observer.observe(element);
        });
    };

    const setupRevealElements = () => {
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
            const elements = getElement(selector, true);
            if (!elements) return;

            elements.forEach((element, index) => {
                element.classList.add('salu-reveal');
                const delayClass = 'salu-reveal-delay-' + ((index % 4) + 1);
                element.classList.add(delayClass);
            });
        });
    };

    /* ============================================
       6. SMOOTH SCROLL PARA ANCHORS
       ============================================ */
    const initSmoothScroll = () => {
        const anchors = getElement('a[href^="#"]', true);

        if (!anchors) return;

        anchors.forEach((anchor) => {
            anchor.addEventListener('click', (event) => {
                const href = anchor.getAttribute('href');

                if (!href || href === '#' || href.length < 2) {
                    event.preventDefault();
                    return;
                }

                const targetId = href.substring(1);
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    event.preventDefault();

                    const targetPosition =
                        targetElement.getBoundingClientRect().top +
                        window.scrollY -
                        CONFIG.NAVBAR_HEIGHT;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: prefersReducedMotion() ? 'auto' : 'smooth'
                    });

                    history.pushState(null, '', href);

                    targetElement.setAttribute('tabindex', '-1');
                    targetElement.focus({ preventScroll: true });
                }
            });
        });
    };

    /* ============================================
       7. INICIALIZACION DE LUCIDE ICONS
       ============================================ */
    const initLucideIcons = () => {
        let attempts = 0;

        const tryInit = () => {
            attempts++;

            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                try {
                    lucide.createIcons();
                    Logger.info('Lucide Icons inicializado (intento ' + attempts + ')');
                } catch (error) {
                    Logger.error('Error al inicializar Lucide Icons', error);
                }
                return;
            }

            if (attempts < CONFIG.LUCIDE_RETRY_ATTEMPTS) {
                setTimeout(tryInit, CONFIG.LUCIDE_RETRY_INTERVAL);
            } else {
                Logger.warn('Lucide Icons no se pudo cargar despues de ' + attempts + ' intentos');
            }
        };

        tryInit();
    };

    /* ============================================
       7.5 THEME MANAGER - DARK MODE HIBRIDO
       ============================================ */
    const initThemeManager = () => {
        const themeToggleDesktop = getElement('#themeToggleDesktop');
        const themeToggleMobile = getElement('#themeToggleMobile');

        const systemThemeQuery = window.matchMedia?.('(prefers-color-scheme: dark)');

        const announcer = document.createElement('div');
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        announcer.classList.add('visually-hidden');
        announcer.id = 'theme-announcer';
        document.body.appendChild(announcer);

        const getCurrentTheme = () => {
            return document.documentElement.getAttribute('data-theme') || 'light';
        };

        const updateToggleStates = (theme) => {
            const isPressed = theme === 'dark';
            [themeToggleDesktop, themeToggleMobile].forEach((button) => {
                if (button) {
                    button.setAttribute('aria-pressed', String(isPressed));
                }
            });
        };

        const applyTheme = (theme, save = true, announce = false) => {
            const validTheme = theme === 'dark' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', validTheme);

            if (save && isLocalStorageAvailable()) {
                try {
                    localStorage.setItem(CONFIG.THEME_STORAGE_KEY, validTheme);
                } catch (error) {
                    Logger.warn('No se pudo guardar la preferencia de tema', error);
                }
            }

            const ariaLabel = validTheme === 'dark'
                ? 'Cambiar a modo claro'
                : 'Cambiar a modo oscuro';

            [themeToggleDesktop, themeToggleMobile].forEach((button) => {
                if (button) {
                    button.setAttribute('aria-label', ariaLabel);
                    button.setAttribute('title', ariaLabel);
                }
            });

            updateToggleStates(validTheme);

            if (announce) {
                const message = validTheme === 'dark'
                    ? 'Modo oscuro activado'
                    : 'Modo claro activado';
                announcer.textContent = message;
            }
        };

        const toggleTheme = () => {
            const currentTheme = getCurrentTheme();
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme, true, true);
            Logger.info('Tema cambiado a: ' + newTheme);
        };

        const initializeTheme = () => {
            let theme = null;

            if (isLocalStorageAvailable()) {
                try {
                    theme = localStorage.getItem(CONFIG.THEME_STORAGE_KEY);
                } catch (error) {
                    Logger.warn('Error al leer preferencia de tema', error);
                }
            }

            if (!theme) {
                theme = systemThemeQuery?.matches ? 'dark' : 'light';
            }

            applyTheme(theme, false, false);
        };

        const handleSystemThemeChange = (event) => {
            let hasSavedPreference = false;

            if (isLocalStorageAvailable()) {
                try {
                    hasSavedPreference =
                        localStorage.getItem(CONFIG.THEME_STORAGE_KEY) !== null;
                } catch (error) {
                    hasSavedPreference = false;
                }
            }

            if (!hasSavedPreference) {
                const newTheme = event.matches ? 'dark' : 'light';
                applyTheme(newTheme, false, false);
                Logger.info('Tema del sistema cambio a: ' + newTheme);
            }
        };

        initializeTheme();

        if (themeToggleDesktop) {
            themeToggleDesktop.addEventListener('click', toggleTheme);
        }

        if (themeToggleMobile) {
            themeToggleMobile.addEventListener('click', toggleTheme);
        }

        if (systemThemeQuery && typeof systemThemeQuery.addEventListener === 'function') {
            systemThemeQuery.addEventListener('change', handleSystemThemeChange);
        } else if (systemThemeQuery && typeof systemThemeQuery.addListener === 'function') {
            systemThemeQuery.addListener(handleSystemThemeChange);
        }

        Logger.info('Theme Manager inicializado');
    };

    /* ============================================
       7.7 BOTON IR ARRIBA
       ============================================ */
    const initScrollTop = () => {
        const scrollTopBtn = getElement('#scrollTopBtn');

        if (!scrollTopBtn) {
            Logger.warn('Boton scroll-top no encontrado');
            return;
        }

        const toggleVisibility = () => {
            if (window.scrollY > CONFIG.SCROLL_TOP_THRESHOLD) {
                if (!scrollTopBtn.classList.contains('visible')) {
                    scrollTopBtn.classList.add('visible');
                }
            } else {
                if (scrollTopBtn.classList.contains('visible')) {
                    scrollTopBtn.classList.remove('visible');
                }
            }
        };

        const scrollToTop = () => {
            window.scrollTo({
                top: 0,
                behavior: prefersReducedMotion() ? 'auto' : 'smooth'
            });

            const mainContent = getElement('#contenido-principal');
            if (mainContent) {
                mainContent.setAttribute('tabindex', '-1');
                mainContent.focus({ preventScroll: true });
            }
        };

        toggleVisibility();
        window.addEventListener('scroll', toggleVisibility, { passive: true });
        scrollTopBtn.addEventListener('click', scrollToTop);

        Logger.info('Boton scroll-top inicializado');
    };

    /* ============================================
       8. MANEJO DE RESIZE
       ============================================ */
    const initResizeHandler = () => {
        const handleResize = debounce(() => {
            const activeFaqAnswers = getElement('.salu-faq-item.active .salu-faq-answer', true);
            if (activeFaqAnswers) {
                activeFaqAnswers.forEach((answer) => {
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                });
            }
        }, 150);

        window.addEventListener('resize', handleResize, { passive: true });
    };

    /* ============================================
       9. INICIALIZACION PRINCIPAL
       ============================================ */
    const initSaluveraLanding = () => {
        try {
            setupRevealElements();

            initThemeManager();
            initNavbarScroll();
            initMobileMenu();
            initMenuLinkClose();
            initFaqAccordion();
            initScrollReveal();
            initSmoothScroll();
            initScrollTop();
            initResizeHandler();

            initLucideIcons();

            Logger.branded();
            Logger.info('Landing page inicializada correctamente');
        } catch (error) {
            Logger.error('Error al inicializar la landing page', error);
        }
    };

    /* ============================================
       10. MANEJO DE ERRORES GLOBALES
       ============================================ */
    window.addEventListener('error', (event) => {
        Logger.error('Error no capturado', {
            message: event.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno
        });
    });

    window.addEventListener('unhandledrejection', (event) => {
        Logger.error('Promise rechazada sin manejar', event.reason);
    });

    /* ============================================
       11. EJECUCION
       ============================================ */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSaluveraLanding);
    } else {
        initSaluveraLanding();
    }
})();