<?php
/**
 * SALUVERA - Landing Page
 *
 * Pagina principal de presentacion del producto.
 * Incluye Dark Mode hibrido con deteccion de sistema
 * y persistencia en localStorage.
 *
 * @author SALUVERA
 * @version 2.2.0
 */
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SALUVERA - La plataforma integral para profesionales de la salud. Gestiona agenda, expedientes clinicos, consultas y seguimiento de pacientes en un solo lugar.">
    <meta name="keywords" content="software medico, expediente clinico electronico, agenda medica, plataforma salud, SaaS salud, Mexico">
    <meta name="author" content="SALUVERA">
    <meta name="theme-color" content="#123C46">

    <!-- Open Graph -->
    <meta property="og:title" content="SALUVERA - Tecnologia para cuidar lo que importa">
    <meta property="og:description" content="La plataforma integral para profesionales de la salud. Agenda, expedientes, consultas y seguimiento en un solo lugar.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://saluvera.com">
    <meta property="og:locale" content="es_MX">

    <!-- Favicon: Logo SALUVERA en SVG (Deep Teal + Mint) -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' fill='none'%3E%3Cpath d='M16 4C9.373 4 4 9.373 4 16s5.373 12 12 12 12-5.373 12-12S22.627 4 16 4z' fill='%23123C46'/%3E%3Cpath d='M10 16c0-3.314 2.686-6 6-6s6 2.686 6 6' stroke='%234FD1B5' stroke-width='2.5' stroke-linecap='round'/%3E%3Ccircle cx='16' cy='20' r='2' fill='%234FD1B5'/%3E%3C/svg%3E">

    <title>SALUVERA - Tecnologia para cuidar lo que importa</title>

    <!-- ============================================ -->
    <!-- THEME DETECTION - Anti-flash                 -->
    <!-- Debe estar ANTES del CSS para evitar flash   -->
    <!-- ============================================ -->
    <script>
        (function() {
            'use strict';
            try {
                const savedTheme = localStorage.getItem('saluvera-theme');
                let themeToApply = savedTheme;

                if (!themeToApply) {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    themeToApply = prefersDark ? 'dark' : 'light';
                }

                document.documentElement.setAttribute('data-theme', themeToApply);
            } catch (error) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>

    <!-- SALUVERA Landing CSS -->
    <link rel="stylesheet" href="assets/css/saluvera-landing.css">
</head>
<body>

    <!-- Skip link para accesibilidad (navegacion por teclado) -->
    <a href="#contenido-principal" class="salu-skip-link">Saltar al contenido principal</a>

    <!-- ============================================ -->
    <!-- NAVBAR - Capsula flotante con efecto scroll  -->
    <!-- ============================================ -->
    <nav class="salu-navbar" id="mainNavbar" aria-label="Navegacion principal">
        <div class="container">
            <div class="salu-navbar-inner">
                <!-- Logo -->
                <a href="/" class="salu-navbar-brand" aria-label="SALUVERA - Ir al inicio">
                    <div class="salu-logo">
                        <div class="salu-logo-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                <title>Logo SALUVERA</title>
                                <path d="M16 4C9.373 4 4 9.373 4 16s5.373 12 12 12 12-5.373 12-12S22.627 4 16 4z" fill="#123C46"/>
                                <path d="M10 16c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="#4FD1B5" stroke-width="2.5" stroke-linecap="round"/>
                                <circle cx="16" cy="20" r="2" fill="#4FD1B5"/>
                            </svg>
                        </div>
                        <span class="salu-logo-text">SALUVERA</span>
                    </div>
                </a>

                <!-- Menu central -->
                <ul class="salu-navbar-menu" role="menubar">
                    <li role="none"><a href="#caracteristicas" class="salu-navbar-link" role="menuitem">Caracteristicas</a></li>
                    <li role="none"><a href="#especialidades" class="salu-navbar-link" role="menuitem">Especialidades</a></li>
                    <li role="none"><a href="#precios" class="salu-navbar-link" role="menuitem">Precios</a></li>
                    <li role="none"><a href="#faq" class="salu-navbar-link" role="menuitem">Preguntas frecuentes</a></li>
                </ul>

                <!-- Botones desktop -->
                <div class="salu-navbar-actions">
                    <!-- Theme Toggle Button (Desktop) -->
                    <button type="button" class="salu-theme-toggle" id="themeToggleDesktop" aria-label="Cambiar tema" title="Cambiar tema" aria-pressed="false">
                        <span class="salu-theme-toggle-track">
                            <span class="salu-theme-toggle-thumb">
                                <i data-lucide="sun" class="salu-theme-icon-sun" aria-hidden="true"></i>
                                <i data-lucide="moon" class="salu-theme-icon-moon" aria-hidden="true"></i>
                            </span>
                        </span>
                    </button>

                    <a href="#" class="btn btn-ghost">Iniciar sesion</a>
                    <a href="#" class="btn btn-primary-salu">Prueba gratis</a>
                </div>

                <!-- Acciones moviles -->
                <div class="salu-navbar-mobile-actions">
                    <!-- Theme Toggle Button (Mobile) -->
                    <button type="button" class="salu-theme-toggle" id="themeToggleMobile" aria-label="Cambiar tema" title="Cambiar tema" aria-pressed="false">
                        <span class="salu-theme-toggle-track">
                            <span class="salu-theme-toggle-thumb">
                                <i data-lucide="sun" class="salu-theme-icon-sun" aria-hidden="true"></i>
                                <i data-lucide="moon" class="salu-theme-icon-moon" aria-hidden="true"></i>
                            </span>
                        </span>
                    </button>

                    <!-- Boton menu movil -->
                    <button type="button" class="salu-navbar-toggle" id="mobileMenuToggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="mobileMenu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Menu movil (drawer) -->
    <div class="salu-mobile-menu" id="mobileMenu" role="dialog" aria-modal="true" aria-label="Menu de navegacion" aria-hidden="true">
        <div class="salu-mobile-menu-inner">
            <ul class="salu-mobile-menu-links" role="menu">
                <li role="none"><a href="#caracteristicas" role="menuitem">Caracteristicas</a></li>
                <li role="none"><a href="#especialidades" role="menuitem">Especialidades</a></li>
                <li role="none"><a href="#precios" role="menuitem">Precios</a></li>
                <li role="none"><a href="#faq" role="menuitem">Preguntas frecuentes</a></li>
            </ul>
            <div class="salu-mobile-menu-actions">
                <a href="#" class="btn btn-ghost w-100 mb-2">Iniciar sesion</a>
                <a href="#" class="btn btn-primary-salu w-100">Prueba gratis</a>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- CONTENIDO PRINCIPAL                          -->
    <!-- ============================================ -->
    <main id="contenido-principal">

        <!-- ============================================ -->
        <!-- HERO SECTION                                 -->
        <!-- ============================================ -->
        <section class="salu-hero" aria-labelledby="hero-title">
            <div class="salu-hero-bg" aria-hidden="true"></div>
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <div class="salu-hero-content">
                            <span class="salu-badge">
                                <i data-lucide="sparkles" aria-hidden="true"></i>
                                Plataforma integral para profesionales de la salud
                            </span>

                            <h1 class="salu-hero-title" id="hero-title">
                                Gestiona tu consulta con la tranquilidad de quien
                                <span class="salu-text-gradient">cuida lo que importa</span>
                            </h1>

                            <p class="salu-hero-subtitle">
                                La plataforma que unifica agenda, expedientes clinicos, consultas y seguimiento
                                de pacientes en un solo lugar. Disenada especificamente para profesionales de la salud en Mexico.
                            </p>

                            <div class="salu-hero-ctas">
                                <a href="#" class="btn btn-primary-salu btn-lg">
                                    Comenzar prueba gratuita
                                    <i data-lucide="arrow-right" aria-hidden="true"></i>
                                </a>
                                <a href="#" class="btn btn-outline-salu btn-lg">
                                    <i data-lucide="play-circle" aria-hidden="true"></i>
                                    Ver demostracion
                                </a>
                            </div>

                            <div class="salu-hero-social-proof">
                                <div class="salu-avatars" aria-hidden="true">
                                    <div class="salu-avatar" style="background: #4FD1B5;">MG</div>
                                    <div class="salu-avatar" style="background: #1F5964;">CR</div>
                                    <div class="salu-avatar" style="background: #8B70B8;">AM</div>
                                    <div class="salu-avatar" style="background: #36A269;">JL</div>
                                </div>
                                <div class="salu-social-text">
                                    <div class="salu-stars" role="img" aria-label="Calificacion: 5 de 5 estrellas">
                                        <i data-lucide="star" aria-hidden="true"></i>
                                        <i data-lucide="star" aria-hidden="true"></i>
                                        <i data-lucide="star" aria-hidden="true"></i>
                                        <i data-lucide="star" aria-hidden="true"></i>
                                        <i data-lucide="star" aria-hidden="true"></i>
                                    </div>
                                    <span><strong>500+ profesionales</strong> confian en SALUVERA</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="salu-hero-mockup" aria-hidden="true">
                            <div class="salu-mockup-window">
                                <div class="salu-mockup-header">
                                    <div class="salu-mockup-dots">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                    <div class="salu-mockup-title">SALUVERA - Panel de control</div>
                                </div>
                                <div class="salu-mockup-body">
                                    <div class="salu-mockup-sidebar">
                                        <div class="salu-mockup-logo-small">S</div>
                                        <div class="salu-mockup-nav-item active">
                                            <i data-lucide="layout-dashboard" aria-hidden="true"></i>
                                        </div>
                                        <div class="salu-mockup-nav-item">
                                            <i data-lucide="calendar" aria-hidden="true"></i>
                                        </div>
                                        <div class="salu-mockup-nav-item">
                                            <i data-lucide="users" aria-hidden="true"></i>
                                        </div>
                                        <div class="salu-mockup-nav-item">
                                            <i data-lucide="file-text" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                    <div class="salu-mockup-content">
                                        <div class="salu-mockup-kpis">
                                            <div class="salu-mockup-kpi">
                                                <div class="salu-mockup-kpi-label">Citas hoy</div>
                                                <div class="salu-mockup-kpi-value">12</div>
                                            </div>
                                            <div class="salu-mockup-kpi">
                                                <div class="salu-mockup-kpi-label">Pacientes</div>
                                                <div class="salu-mockup-kpi-value">248</div>
                                            </div>
                                            <div class="salu-mockup-kpi">
                                                <div class="salu-mockup-kpi-label">Proxima</div>
                                                <div class="salu-mockup-kpi-value">10:30</div>
                                            </div>
                                        </div>
                                        <div class="salu-mockup-appointments">
                                            <div class="salu-mockup-appointment">
                                                <div class="salu-mockup-appointment-time">09:00</div>
                                                <div class="salu-mockup-appointment-info">
                                                    <div class="salu-mockup-appointment-name">Maria Gonzalez</div>
                                                    <div class="salu-mockup-appointment-type">Consulta psicologica</div>
                                                </div>
                                                <div class="salu-mockup-appointment-status confirmed"></div>
                                            </div>
                                            <div class="salu-mockup-appointment">
                                                <div class="salu-mockup-appointment-time">10:30</div>
                                                <div class="salu-mockup-appointment-info">
                                                    <div class="salu-mockup-appointment-name">Carlos Ramirez</div>
                                                    <div class="salu-mockup-appointment-type">Seguimiento</div>
                                                </div>
                                                <div class="salu-mockup-appointment-status pending"></div>
                                            </div>
                                            <div class="salu-mockup-appointment">
                                                <div class="salu-mockup-appointment-time">12:00</div>
                                                <div class="salu-mockup-appointment-info">
                                                    <div class="salu-mockup-appointment-name">Ana Martinez</div>
                                                    <div class="salu-mockup-appointment-type">Primera vez</div>
                                                </div>
                                                <div class="salu-mockup-appointment-status confirmed"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tarjetas flotantes decorativas -->
                            <div class="salu-floating-card salu-floating-card-1">
                                <i data-lucide="check-circle" aria-hidden="true"></i>
                                <div>
                                    <div class="salu-floating-title">Cita confirmada</div>
                                    <div class="salu-floating-subtitle">Maria G. - 10:30 AM</div>
                                </div>
                            </div>

                            <div class="salu-floating-card salu-floating-card-2">
                                <i data-lucide="trending-up" aria-hidden="true"></i>
                                <div>
                                    <div class="salu-floating-title">+23% este mes</div>
                                    <div class="salu-floating-subtitle">Pacientes activos</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- SOCIAL PROOF - Logos                         -->
        <!-- ============================================ -->
        <section class="salu-logos" aria-label="Clientes que confian en SALUVERA">
            <div class="container">
                <p class="salu-logos-title">Profesionales y clinicas en todo Mexico confian en SALUVERA</p>
                <div class="salu-logos-grid">
                    <div class="salu-logo-item">Clinica Norte</div>
                    <div class="salu-logo-item">Centro Medico Sur</div>
                    <div class="salu-logo-item">Salud Integral</div>
                    <div class="salu-logo-item">Bienestar Total</div>
                    <div class="salu-logo-item">Vita Medica</div>
                    <div class="salu-logo-item">Sanos Hoy</div>
                </div>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- PROBLEMA - Puntos de dolor                   -->
        <!-- ============================================ -->
        <section class="salu-problem" aria-labelledby="problema-title">
            <div class="container">
                <div class="salu-section-header">
                    <span class="salu-badge">
                        <i data-lucide="alert-circle" aria-hidden="true"></i>
                        El problema
                    </span>
                    <h2 class="salu-section-title" id="problema-title">Te suena familiar?</h2>
                    <p class="salu-section-subtitle">
                        Sabemos los desafios que enfrentas dia a dia como profesional de la salud.
                    </p>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="salu-problem-card">
                            <div class="salu-problem-icon">
                                <i data-lucide="calendar-x" aria-hidden="true"></i>
                            </div>
                            <h3>Pierdes tiempo con agendas desorganizadas</h3>
                            <p>Citas duplicadas, horarios que se traslapan, pacientes que no llegan. La gestion manual de tu agenda te quita tiempo valioso.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="salu-problem-card">
                            <div class="salu-problem-icon">
                                <i data-lucide="files" aria-hidden="true"></i>
                            </div>
                            <h3>Expedientes clinicos dispersos</h3>
                            <p>Papel, Excel, WhatsApp, notas en diferentes lugares. No tienes una vision completa del historial de tus pacientes.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="salu-problem-card">
                            <div class="salu-problem-icon">
                                <i data-lucide="eye-off" aria-hidden="true"></i>
                            </div>
                            <h3>Sin visibilidad de la evolucion</h3>
                            <p>No puedes ver facilmente como ha evolucionado cada paciente a lo largo del tiempo. Cada consulta empieza casi desde cero.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="salu-problem-card">
                            <div class="salu-problem-icon">
                                <i data-lucide="clock" aria-hidden="true"></i>
                            </div>
                            <h3>La administracion te quita tiempo de atencion</h3>
                            <p>Pasas mas tiempo gestionando papeleo que atendiendo a tus pacientes. Tu vocacion merece mejores herramientas.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- SOLUCION - 3 Pilares                         -->
        <!-- ============================================ -->
        <section class="salu-solution" aria-labelledby="solucion-title">
            <div class="container">
                <div class="salu-section-header">
                    <span class="salu-badge salu-badge-mint">
                        <i data-lucide="check-circle-2" aria-hidden="true"></i>
                        La solucion
                    </span>
                    <h2 class="salu-section-title" id="solucion-title">Todo lo que necesitas en una sola plataforma</h2>
                    <p class="salu-section-subtitle">
                        SALUVERA unifica tu practica profesional en un sistema integral, seguro y facil de usar.
                    </p>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="salu-pillar-card">
                            <div class="salu-pillar-icon">
                                <i data-lucide="calendar-check" aria-hidden="true"></i>
                            </div>
                            <h3>Agenda inteligente</h3>
                            <p>Gestiona citas sin conflictos, con recordatorios automaticos y vista personalizada por profesional. Nunca mas una cita duplicada.</p>
                            <ul class="salu-pillar-features">
                                <li><i data-lucide="check" aria-hidden="true"></i> Vista dia, semana y mes</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Confirmacion automatica</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Bloqueo de horarios</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="salu-pillar-card featured">
                            <div class="salu-pillar-badge">Mas importante</div>
                            <div class="salu-pillar-icon">
                                <i data-lucide="file-heart" aria-hidden="true"></i>
                            </div>
                            <h3>Expediente clinico completo</h3>
                            <p>Historial completo de cada paciente, con notas, diagnosticos, tratamientos y evolucion en un solo lugar. Configurable por especialidad.</p>
                            <ul class="salu-pillar-features">
                                <li><i data-lucide="check" aria-hidden="true"></i> Linea de tiempo de evolucion</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Busqueda rapida</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Conforme a NOM-004</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="salu-pillar-card">
                            <div class="salu-pillar-icon">
                                <i data-lucide="activity" aria-hidden="true"></i>
                            </div>
                            <h3>Seguimiento continuo</h3>
                            <p>Linea de tiempo visual de la evolucion del paciente, con recordatorios y seguimiento personalizado. Nunca pierdas el hilo del tratamiento.</p>
                            <ul class="salu-pillar-features">
                                <li><i data-lucide="check" aria-hidden="true"></i> Evolucion cronologica</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Recordatorios automaticos</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Indicadores de progreso</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- CARACTERISTICAS - Grid de 6                  -->
        <!-- ============================================ -->
        <section class="salu-features" id="caracteristicas" aria-labelledby="caracteristicas-title">
            <div class="container">
                <div class="salu-section-header">
                    <span class="salu-badge">
                        <i data-lucide="layers" aria-hidden="true"></i>
                        Caracteristicas
                    </span>
                    <h2 class="salu-section-title" id="caracteristicas-title">Disenado para cada aspecto de tu practica</h2>
                    <p class="salu-section-subtitle">
                        Cada modulo fue pensado para resolver un desafio real del profesional de la salud.
                    </p>
                </div>

                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="salu-feature-card">
                            <div class="salu-feature-icon">
                                <i data-lucide="calendar" aria-hidden="true"></i>
                            </div>
                            <h3>Agenda inteligente</h3>
                            <p>Vista dia/semana/mes, confirmacion automatica, bloqueo de horarios y prevencion de conflictos en tiempo real.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="salu-feature-card">
                            <div class="salu-feature-icon">
                                <i data-lucide="file-text" aria-hidden="true"></i>
                            </div>
                            <h3>Expediente clinico electronico</h3>
                            <p>Configurable por especialidad, linea de tiempo de evolucion, busqueda rapida y cumplimiento con NOM-004-SSA3-2012.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="salu-feature-card">
                            <div class="salu-feature-icon">
                                <i data-lucide="stethoscope" aria-hidden="true"></i>
                            </div>
                            <h3>Consultas estructuradas</h3>
                            <p>Patron SOAP, guardado automatico, plantillas personalizables por especialidad y notas clinicas organizadas.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="salu-feature-card">
                            <div class="salu-feature-icon">
                                <i data-lucide="pill" aria-hidden="true"></i>
                            </div>
                            <h3>Indicaciones terapeuticas</h3>
                            <p>Registro de medicamentos, dosis, frecuencia y duracion. Impresion de indicaciones con formato profesional.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="salu-feature-card">
                            <div class="salu-feature-icon">
                                <i data-lucide="folder-open" aria-hidden="true"></i>
                            </div>
                            <h3>Documentos y archivos</h3>
                            <p>Adjunta estudios, resultados de laboratorio e imagenes. Almacenamiento seguro y organizado por paciente.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="salu-feature-card">
                            <div class="salu-feature-icon">
                                <i data-lucide="bar-chart-3" aria-hidden="true"></i>
                            </div>
                            <h3>Reportes y estadisticas</h3>
                            <p>Citas por periodo, pacientes activos, ocupacion de agenda y evolucion. Datos claros para mejores decisiones.</p>
                        </div>
                    </div>
                </div>

                <div class="salu-features-cta">
                    <a href="#" class="btn btn-outline-salu">
                        Ver todas las caracteristicas
                        <i data-lucide="arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- BENEFICIOS                                   -->
        <!-- ============================================ -->
        <section class="salu-benefits" aria-labelledby="beneficios-title">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <div class="salu-benefits-visual">
                            <div class="salu-benefit-graph" role="img" aria-label="Grafico de tiempo ahorrado con SALUVERA: gestion administrativa 85%, busqueda de expedientes 72%, seguimiento de pacientes 65%">
                                <div class="salu-graph-header">
                                    <span>Tiempo ahorrado con SALUVERA</span>
                                    <strong>+40%</strong>
                                </div>
                                <div class="salu-graph-bars">
                                    <div class="salu-graph-bar">
                                        <div class="salu-graph-bar-label">Gestion administrativa</div>
                                        <div class="salu-graph-bar-track">
                                            <div class="salu-graph-bar-fill" style="width: 85%;"></div>
                                        </div>
                                    </div>
                                    <div class="salu-graph-bar">
                                        <div class="salu-graph-bar-label">Busqueda de expedientes</div>
                                        <div class="salu-graph-bar-track">
                                            <div class="salu-graph-bar-fill" style="width: 72%;"></div>
                                        </div>
                                    </div>
                                    <div class="salu-graph-bar">
                                        <div class="salu-graph-bar-label">Seguimiento de pacientes</div>
                                        <div class="salu-graph-bar-track">
                                            <div class="salu-graph-bar-fill" style="width: 65%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="salu-benefits-content">
                            <span class="salu-badge">
                                <i data-lucide="award" aria-hidden="true"></i>
                                Beneficios
                            </span>
                            <h2 class="salu-section-title" id="beneficios-title">Mas tiempo para lo que realmente importa: tus pacientes</h2>

                            <div class="salu-benefits-list">
                                <div class="salu-benefit-item">
                                    <div class="salu-benefit-icon">
                                        <i data-lucide="clock" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h3>Ahorra tiempo</h3>
                                        <p>Reduce hasta un 40% el tiempo en gestion administrativa. Dedica mas tiempo a atender, menos a papelear.</p>
                                    </div>
                                </div>
                                <div class="salu-benefit-item">
                                    <div class="salu-benefit-icon">
                                        <i data-lucide="heart-pulse" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h3>Mejora la atencion</h3>
                                        <p>Accede al historial completo del paciente en segundos. Decisiones mas informadas, mejor atencion.</p>
                                    </div>
                                </div>
                                <div class="salu-benefit-item">
                                    <div class="salu-benefit-icon">
                                        <i data-lucide="users" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h3>Aumenta la retencion</h3>
                                        <p>Seguimiento continuo significa pacientes mas fieles. El seguimiento marca la diferencia.</p>
                                    </div>
                                </div>
                                <div class="salu-benefit-item">
                                    <div class="salu-benefit-icon">
                                        <i data-lucide="shield-check" aria-hidden="true"></i>
                                    </div>
                                    <div>
                                        <h3>Cumple normativas</h3>
                                        <p>Expediente clinico conforme a NOM-004-SSA3-2012 y LFPDPPP. Trabaja con tranquilidad legal.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- ESPECIALIDADES                               -->
        <!-- ============================================ -->
        <section class="salu-specialties" id="especialidades" aria-labelledby="especialidades-title">
            <div class="container">
                <div class="salu-section-header">
                    <span class="salu-badge">
                        <i data-lucide="stethoscope" aria-hidden="true"></i>
                        Especialidades
                    </span>
                    <h2 class="salu-section-title" id="especialidades-title">Disenado para cada especialidad</h2>
                    <p class="salu-section-subtitle">
                        SALUVERA se adapta a tu practica, sin importar tu especialidad.
                    </p>
                </div>

                <div class="row g-3">
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="salu-specialty-card">
                            <div class="salu-specialty-icon">
                                <i data-lucide="brain" aria-hidden="true"></i>
                            </div>
                            <h3>Psicologos</h3>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="salu-specialty-card">
                            <div class="salu-specialty-icon">
                                <i data-lucide="heart" aria-hidden="true"></i>
                            </div>
                            <h3>Medicos generales</h3>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="salu-specialty-card">
                            <div class="salu-specialty-icon">
                                <i data-lucide="activity" aria-hidden="true"></i>
                            </div>
                            <h3>Especialistas</h3>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="salu-specialty-card">
                            <div class="salu-specialty-icon">
                                <i data-lucide="brain-circuit" aria-hidden="true"></i>
                            </div>
                            <h3>Psiquiatras</h3>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="salu-specialty-card">
                            <div class="salu-specialty-icon">
                                <i data-lucide="apple" aria-hidden="true"></i>
                            </div>
                            <h3>Nutriologos</h3>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="salu-specialty-card">
                            <div class="salu-specialty-icon">
                                <i data-lucide="smile" aria-hidden="true"></i>
                            </div>
                            <h3>Odontologos</h3>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="salu-specialty-card">
                            <div class="salu-specialty-icon">
                                <i data-lucide="dumbbell" aria-hidden="true"></i>
                            </div>
                            <h3>Fisioterapeutas</h3>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="salu-specialty-card">
                            <div class="salu-specialty-icon">
                                <i data-lucide="hand-heart" aria-hidden="true"></i>
                            </div>
                            <h3>Terapeutas</h3>
                        </div>
                    </div>
                </div>

                <p class="salu-specialties-note">
                    <i data-lucide="sparkles" aria-hidden="true"></i>
                    Y muchas mas especialidades. SALUVERA se adapta a tu practica.
                </p>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- SEGURIDAD                                    -->
        <!-- ============================================ -->
        <section class="salu-security" aria-labelledby="seguridad-title">
            <div class="container">
                <div class="salu-section-header">
                    <span class="salu-badge">
                        <i data-lucide="shield-check" aria-hidden="true"></i>
                        Seguridad
                    </span>
                    <h2 class="salu-section-title" id="seguridad-title">Seguridad y cumplimiento normativo garantizados</h2>
                    <p class="salu-section-subtitle">
                        Tu informacion y la de tus pacientes esta protegida con los mas altos estandares.
                    </p>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="salu-security-card">
                            <div class="salu-security-icon">
                                <i data-lucide="lock" aria-hidden="true"></i>
                            </div>
                            <h3>Proteccion de datos</h3>
                            <ul>
                                <li>Cifrado AES-256</li>
                                <li>Cumplimiento LFPDPPP</li>
                                <li>Copias de seguridad automaticas diarias</li>
                                <li>Servidores seguros en Mexico</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="salu-security-card">
                            <div class="salu-security-icon">
                                <i data-lucide="file-check" aria-hidden="true"></i>
                            </div>
                            <h3>Expediente clinico legal</h3>
                            <ul>
                                <li>Conforme a NOM-004-SSA3-2012</li>
                                <li>Retencion minima de 5 anos</li>
                                <li>Auditoria completa de cambios</li>
                                <li>Imposibilidad de borrado de notas</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="salu-security-card">
                            <div class="salu-security-icon">
                                <i data-lucide="user-check" aria-hidden="true"></i>
                            </div>
                            <h3>Acceso controlado</h3>
                            <ul>
                                <li>Autenticacion de dos factores</li>
                                <li>Permisos granulares por rol</li>
                                <li>Registro de actividad completo</li>
                                <li>Sesiones seguras con expiracion</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="salu-security-badges">
                    <div class="salu-compliance-badge">
                        <i data-lucide="shield-check" aria-hidden="true"></i>
                        <span>Cumple LFPDPPP</span>
                    </div>
                    <div class="salu-compliance-badge">
                        <i data-lucide="shield-check" aria-hidden="true"></i>
                        <span>Cumple NOM-004</span>
                    </div>
                    <div class="salu-compliance-badge">
                        <i data-lucide="shield-check" aria-hidden="true"></i>
                        <span>Cifrado AES-256</span>
                    </div>
                    <div class="salu-compliance-badge">
                        <i data-lucide="shield-check" aria-hidden="true"></i>
                        <span>99.9% Disponibilidad</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- TESTIMONIOS                                  -->
        <!-- ============================================ -->
        <section class="salu-testimonials" aria-labelledby="testimonios-title">
            <div class="container">
                <div class="salu-section-header">
                    <span class="salu-badge">
                        <i data-lucide="message-circle" aria-hidden="true"></i>
                        Testimonios
                    </span>
                    <h2 class="salu-section-title" id="testimonios-title">Lo que dicen nuestros usuarios</h2>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <figure class="salu-testimonial-card">
                            <div class="salu-testimonial-stars" role="img" aria-label="Calificacion: 5 de 5 estrellas">
                                <i data-lucide="star" aria-hidden="true"></i>
                                <i data-lucide="star" aria-hidden="true"></i>
                                <i data-lucide="star" aria-hidden="true"></i>
                                <i data-lucide="star" aria-hidden="true"></i>
                                <i data-lucide="star" aria-hidden="true"></i>
                            </div>
                            <blockquote class="salu-testimonial-text">
                                "SALUVERA transformo mi practica. Ahora tengo mas tiempo para mis pacientes y menos para papeleo. El expediente clinico es exactamente lo que necesitaba."
                            </blockquote>
                            <figcaption class="salu-testimonial-author">
                                <div class="salu-testimonial-avatar" style="background: #4FD1B5;" aria-hidden="true">MG</div>
                                <div>
                                    <div class="salu-testimonial-name">Dra. Maria Gonzalez</div>
                                    <div class="salu-testimonial-role">Psicologa Clinica</div>
                                </div>
                            </figcaption>
                        </figure>
                    </div>
                    <div class="col-md-4">
                        <figure class="salu-testimonial-card">
                            <div class="salu-testimonial-stars" role="img" aria-label="Calificacion: 5 de 5 estrellas">
                                <i data-lucide="star" aria-hidden="true"></i>
                                <i data-lucide="star" aria-hidden="true"></i>
                                <i data-lucide="star" aria-hidden="true"></i>
                                <i data-lucide="star" aria-hidden="true"></i>
                                <i data-lucide="star" aria-hidden="true"></i>
                            </div>
                            <blockquote class="salu-testimonial-text">
                                "La agenda inteligente me ahorro horas de trabajo. Ya no tengo citas duplicadas y mis pacientes reciben recordatorios automaticos. Increible."
                            </blockquote>
                            <figcaption class="salu-testimonial-author">
                                <div class="salu-testimonial-avatar" style="background: #1F5964;" aria-hidden="true">CR</div>
                                <div>
                                    <div class="salu-testimonial-name">Dr. Carlos Ramirez</div>
                                    <div class="salu-testimonial-role">Medico General</div>
                                </div>
                            </figcaption>
                        </figure>
                    </div>
                    <div class="col-md-4">
                        <figure class="salu-testimonial-card">
                            <div class="salu-testimonial-stars" role="img" aria-label="Calificacion: 5 de 5 estrellas">
                                <i data-lucide="star" aria-hidden="true"></i>
                                <i data-lucide="star" aria-hidden="true"></i>
                                <i data-lucide="star" aria-hidden="true"></i>
                                <i data-lucide="star" aria-hidden="true"></i>
                                <i data-lucide="star" aria-hidden="true"></i>
                            </div>
                            <blockquote class="salu-testimonial-text">
                                "El expediente clinico configurable es perfecto para mi especialidad. Muy intuitivo y profesional. Mis pacientes notan la diferencia en la atencion."
                            </blockquote>
                            <figcaption class="salu-testimonial-author">
                                <div class="salu-testimonial-avatar" style="background: #8B70B8;" aria-hidden="true">AM</div>
                                <div>
                                    <div class="salu-testimonial-name">Lic. Ana Martinez</div>
                                    <div class="salu-testimonial-role">Nutriologa</div>
                                </div>
                            </figcaption>
                        </figure>
                    </div>
                </div>

                <div class="salu-testimonials-rating">
                    <div class="salu-rating-large">
                        <span class="salu-rating-number">4.9</span>
                        <div class="salu-rating-stars" role="img" aria-label="Calificacion: 5 de 5 estrellas">
                            <i data-lucide="star" aria-hidden="true"></i>
                            <i data-lucide="star" aria-hidden="true"></i>
                            <i data-lucide="star" aria-hidden="true"></i>
                            <i data-lucide="star" aria-hidden="true"></i>
                            <i data-lucide="star" aria-hidden="true"></i>
                        </div>
                        <span class="salu-rating-count">Basado en mas de 500 resenas</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- PRECIOS                                      -->
        <!-- ============================================ -->
        <section class="salu-pricing" id="precios" aria-labelledby="precios-title">
            <div class="container">
                <div class="salu-section-header">
                    <span class="salu-badge">
                        <i data-lucide="credit-card" aria-hidden="true"></i>
                        Precios
                    </span>
                    <h2 class="salu-section-title" id="precios-title">Planes que se adaptan a tu practica</h2>
                    <p class="salu-section-subtitle">
                        14 dias de prueba gratuita. Sin tarjeta de credito. Cancela cuando quieras.
                    </p>
                </div>

                <div class="row g-4 justify-content-center">
                    <div class="col-md-6 col-lg-4">
                        <div class="salu-pricing-card">
                            <div class="salu-pricing-header">
                                <h3>Individual</h3>
                                <p>Para profesionales independientes</p>
                            </div>
                            <div class="salu-pricing-price">
                                <span class="salu-pricing-currency">$</span>
                                <span class="salu-pricing-amount">299</span>
                                <span class="salu-pricing-period">/mes</span>
                            </div>
                            <ul class="salu-pricing-features">
                                <li><i data-lucide="check" aria-hidden="true"></i> 1 profesional</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Hasta 100 pacientes</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Todas las caracteristicas</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Soporte por correo electronico</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> 5 GB de almacenamiento</li>
                            </ul>
                            <a href="#" class="btn btn-outline-salu w-100">Comenzar prueba</a>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="salu-pricing-card featured">
                            <div class="salu-pricing-badge">Mas popular</div>
                            <div class="salu-pricing-header">
                                <h3>Profesional</h3>
                                <p>Para consultorios pequenos</p>
                            </div>
                            <div class="salu-pricing-price">
                                <span class="salu-pricing-currency">$</span>
                                <span class="salu-pricing-amount">599</span>
                                <span class="salu-pricing-period">/mes</span>
                            </div>
                            <ul class="salu-pricing-features">
                                <li><i data-lucide="check" aria-hidden="true"></i> Hasta 3 profesionales</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Pacientes ilimitados</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Todas las caracteristicas</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Soporte prioritario</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Reportes avanzados</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> 25 GB de almacenamiento</li>
                            </ul>
                            <a href="#" class="btn btn-primary-salu w-100">Comenzar prueba</a>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="salu-pricing-card">
                            <div class="salu-pricing-header">
                                <h3>Clinica</h3>
                                <p>Para clinicas y centros medicos</p>
                            </div>
                            <div class="salu-pricing-price">
                                <span class="salu-pricing-currency">$</span>
                                <span class="salu-pricing-amount">1,299</span>
                                <span class="salu-pricing-period">/mes</span>
                            </div>
                            <ul class="salu-pricing-features">
                                <li><i data-lucide="check" aria-hidden="true"></i> Profesionales ilimitados</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Pacientes ilimitados</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Todas las caracteristicas</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Soporte 24/7</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Acceso a API</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> Incorporacion personalizada</li>
                                <li><i data-lucide="check" aria-hidden="true"></i> 100 GB de almacenamiento</li>
                            </ul>
                            <a href="#" class="btn btn-outline-salu w-100">Contactar ventas</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- PREGUNTAS FRECUENTES                         -->
        <!-- ============================================ -->
        <section class="salu-faq" id="faq" aria-labelledby="faq-title">
            <div class="container">
                <div class="salu-section-header">
                    <span class="salu-badge">
                        <i data-lucide="help-circle" aria-hidden="true"></i>
                        Preguntas frecuentes
                    </span>
                    <h2 class="salu-section-title" id="faq-title">Preguntas frecuentes</h2>
                </div>

                <div class="salu-faq-list">
                    <div class="salu-faq-item">
                        <button type="button" class="salu-faq-question" aria-expanded="false" aria-controls="faq-answer-1" id="faq-question-1">
                            <span>Necesito instalar algo en mi computadora?</span>
                            <i data-lucide="chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="salu-faq-answer" id="faq-answer-1" role="region" aria-labelledby="faq-question-1">
                            <p>No. SALUVERA es una plataforma 100% en la nube. Solo necesitas un navegador web actualizado (Chrome, Firefox, Safari o Edge) y conexion a internet. Funciona en computadora, tableta y movil.</p>
                        </div>
                    </div>
                    <div class="salu-faq-item">
                        <button type="button" class="salu-faq-question" aria-expanded="false" aria-controls="faq-answer-2" id="faq-question-2">
                            <span>Puedo migrar mis datos actuales al sistema?</span>
                            <i data-lucide="chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="salu-faq-answer" id="faq-answer-2" role="region" aria-labelledby="faq-question-2">
                            <p>Si. Ofrecemos asistencia para migrar tus datos desde Excel, otros sistemas o expedientes en papel. Nuestro equipo te acompana en todo el proceso de incorporacion sin costo adicional en los planes Profesional y Clinica.</p>
                        </div>
                    </div>
                    <div class="salu-faq-item">
                        <button type="button" class="salu-faq-question" aria-expanded="false" aria-controls="faq-answer-3" id="faq-question-3">
                            <span>Es seguro para informacion clinica sensible?</span>
                            <i data-lucide="chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="salu-faq-answer" id="faq-answer-3" role="region" aria-labelledby="faq-question-3">
                            <p>Absolutamente. SALUVERA cumple con LFPDPPP y NOM-004-SSA3-2012. Utilizamos cifrado AES-256, servidores seguros en Mexico, copias de seguridad automaticas y auditoria completa de todos los cambios. Tu informacion y la de tus pacientes esta protegida con los mas altos estandares.</p>
                        </div>
                    </div>
                    <div class="salu-faq-item">
                        <button type="button" class="salu-faq-question" aria-expanded="false" aria-controls="faq-answer-4" id="faq-question-4">
                            <span>Funciona en dispositivos moviles?</span>
                            <i data-lucide="chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="salu-faq-answer" id="faq-answer-4" role="region" aria-labelledby="faq-question-4">
                            <p>Si. SALUVERA esta disenado con enfoque movil primero. Funciona perfectamente en telefonos inteligentes y tabletas. Proximamente lanzaremos aplicaciones nativas para iOS y Android con funcionalidades especificas para consulta movil.</p>
                        </div>
                    </div>
                    <div class="salu-faq-item">
                        <button type="button" class="salu-faq-question" aria-expanded="false" aria-controls="faq-answer-5" id="faq-question-5">
                            <span>Que pasa si cancelo mi suscripcion?</span>
                            <i data-lucide="chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="salu-faq-answer" id="faq-answer-5" role="region" aria-labelledby="faq-question-5">
                            <p>Puedes cancelar cuando quieras, sin penalizaciones. Conservamos tus datos durante 90 dias despues de la cancelacion para que puedas exportarlos. Despues de ese periodo, cumpliendo con la NOM-004, mantenemos los expedientes clinicos en resguardo por el tiempo legal requerido.</p>
                        </div>
                    </div>
                    <div class="salu-faq-item">
                        <button type="button" class="salu-faq-question" aria-expanded="false" aria-controls="faq-answer-6" id="faq-question-6">
                            <span>Ofrecen capacitacion para mi equipo?</span>
                            <i data-lucide="chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="salu-faq-answer" id="faq-answer-6" role="region" aria-labelledby="faq-question-6">
                            <p>Si. Todos los planes incluyen acceso a nuestra base de conocimiento y tutoriales en video. Los planes Profesional y Clinica incluyen sesiones de incorporacion personalizadas con nuestro equipo para ti y tu personal.</p>
                        </div>
                    </div>
                </div>

                <div class="salu-faq-cta">
                    <p>Tienes otra pregunta?</p>
                    <a href="#" class="btn btn-outline-salu">
                        Contactanos
                        <i data-lucide="arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- ============================================ -->
        <!-- CTA FINAL                                    -->
        <!-- ============================================ -->
        <section class="salu-cta-final" aria-labelledby="cta-final-title">
            <div class="container">
                <div class="salu-cta-final-content">
                    <h2 id="cta-final-title">Comienza a gestionar mejor tu practica hoy</h2>
                    <p>Unete a los cientos de profesionales que ya confian en SALUVERA para cuidar lo que realmente importa.</p>
                    <div class="salu-cta-final-buttons">
                        <a href="#" class="btn btn-mint btn-lg">
                            Comenzar prueba gratuita
                            <i data-lucide="arrow-right" aria-hidden="true"></i>
                        </a>
                        <a href="#" class="btn btn-outline-white btn-lg">
                            Agendar demostracion
                        </a>
                    </div>
                    <p class="salu-cta-final-note">14 dias gratis · Sin tarjeta de credito · Cancela cuando quieras</p>
                </div>
            </div>
        </section>

    </main>

    <!-- ============================================ -->
    <!-- FOOTER                                       -->
    <!-- ============================================ -->
    <footer class="salu-footer" role="contentinfo">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="salu-footer-brand">
                        <div class="salu-logo">
                            <div class="salu-logo-icon">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                                    <title>Logo SALUVERA</title>
                                    <path d="M16 4C9.373 4 4 9.373 4 16s5.373 12 12 12 12-5.373 12-12S22.627 4 16 4z" fill="#4FD1B5"/>
                                    <path d="M10 16c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="#123C46" stroke-width="2.5" stroke-linecap="round"/>
                                    <circle cx="16" cy="20" r="2" fill="#123C46"/>
                                </svg>
                            </div>
                            <span class="salu-logo-text">SALUVERA</span>
                        </div>
                        <p class="salu-footer-tagline">Tecnologia para cuidar lo que importa.</p>
                        <div class="salu-footer-social">
                            <a href="#" aria-label="Facebook de SALUVERA" target="_blank" rel="noopener noreferrer">
                                <i data-lucide="facebook" aria-hidden="true"></i>
                            </a>
                            <a href="#" aria-label="Twitter de SALUVERA" target="_blank" rel="noopener noreferrer">
                                <i data-lucide="twitter" aria-hidden="true"></i>
                            </a>
                            <a href="#" aria-label="LinkedIn de SALUVERA" target="_blank" rel="noopener noreferrer">
                                <i data-lucide="linkedin" aria-hidden="true"></i>
                            </a>
                            <a href="#" aria-label="Instagram de SALUVERA" target="_blank" rel="noopener noreferrer">
                                <i data-lucide="instagram" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h3>Producto</h3>
                    <ul>
                        <li><a href="#caracteristicas">Caracteristicas</a></li>
                        <li><a href="#especialidades">Especialidades</a></li>
                        <li><a href="#precios">Precios</a></li>
                        <li><a href="#">Demostracion</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h3>Recursos</h3>
                    <ul>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Centro de ayuda</a></li>
                        <li><a href="#">Documentacion de API</a></li>
                        <li><a href="#">Estado del sistema</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h3>Empresa</h3>
                    <ul>
                        <li><a href="#">Acerca de</a></li>
                        <li><a href="#">Contacto</a></li>
                        <li><a href="#">Trabaja con nosotros</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="#">Aviso de privacidad</a></li>
                        <li><a href="#">Terminos de uso</a></li>
                        <li><a href="#">Politica de cookies</a></li>
                        <li><a href="#">Cumplimiento</a></li>
                    </ul>
                </div>
            </div>
            <div class="salu-footer-bottom">
                <p>&copy; 2026 SALUVERA. Todos los derechos reservados.</p>
                <p class="salu-footer-location">
                    <i data-lucide="map-pin" aria-hidden="true"></i>
                    Hecho en Mexico con
                    <i data-lucide="heart" class="salu-footer-heart" aria-hidden="true"></i>
                    <span class="visually-hidden">amor</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- ============================================ -->
    <!-- BOTON IR ARRIBA                              -->
    <!-- ============================================ -->
    <button type="button" class="salu-scroll-top" id="scrollTopBtn" aria-label="Ir arriba" title="Ir arriba">
        <i data-lucide="arrow-up" aria-hidden="true"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

    <!-- SALUVERA Landing JS -->
    <script src="assets/js/saluvera-landing.js" defer></script>

</body>
</html>