<?php
/**
 * SALUVERA - Landing Page
 * 
 * Página principal de presentación del producto.
 * 
 * @author SALUVERA
 * @version 1.0.0
 */
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SALUVERA - La plataforma integral para profesionales de la salud. Gestiona agenda, expedientes clínicos, consultas y seguimiento de pacientes en un solo lugar.">
    <meta name="keywords" content="software médico, expediente clínico electrónico, agenda médica, plataforma salud, SaaS salud, México">
    <meta name="author" content="SALUVERA">
    
    <!-- Open Graph -->
    <meta property="og:title" content="SALUVERA - Tecnología para cuidar lo que importa">
    <meta property="og:description" content="La plataforma integral para profesionales de la salud. Agenda, expedientes, consultas y seguimiento en un solo lugar.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://saluvera.com">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' fill='none'%3E%3Cpath d='M16 4C9.373 4 4 9.373 4 16s5.373 12 12 12 12-5.373 12-12S22.627 4 16 4z' fill='%23123C46'/%3E%3Cpath d='M10 16c0-3.314 2.686-6 6-6s6 2.686 6 6' stroke='%234FD1B5' stroke-width='2.5' stroke-linecap='round'/%3E%3Ccircle cx='16' cy='20' r='2' fill='%234FD1B5'/%3E%3C/svg%3E"> 
    <title>SALUVERA - Tecnología para cuidar lo que importa</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <!-- SALUVERA Landing CSS -->
    <link rel="stylesheet" href="assets/css/saluvera-landing.css">
</head>
<body>

    <!-- ============================================ -->
    <!-- NAVBAR - Cápsula flotante con efecto scroll  -->
    <!-- ============================================ -->
    <nav class="salu-navbar" id="mainNavbar">
        <div class="container">
            <div class="salu-navbar-inner">
                <!-- Logo -->
                <a href="#" class="salu-navbar-brand">
                    <div class="salu-logo">
                        <div class="salu-logo-icon">
                            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 4C9.373 4 4 9.373 4 16s5.373 12 12 12 12-5.373 12-12S22.627 4 16 4z" fill="#123C46"/>
                                <path d="M10 16c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="#4FD1B5" stroke-width="2.5" stroke-linecap="round"/>
                                <circle cx="16" cy="20" r="2" fill="#4FD1B5"/>
                            </svg>
                        </div>
                        <span class="salu-logo-text">SALUVERA</span>
                    </div>
                </a>
                
                <!-- Menú central -->
                <ul class="salu-navbar-menu">
                    <li><a href="#caracteristicas" class="salu-navbar-link">Características</a></li>
                    <li><a href="#especialidades" class="salu-navbar-link">Especialidades</a></li>
                    <li><a href="#precios" class="salu-navbar-link">Precios</a></li>
                    <li><a href="#faq" class="salu-navbar-link">FAQ</a></li>
                </ul>
                
                <!-- Botones -->
                <div class="salu-navbar-actions">
                    <a href="#" class="btn btn-ghost">Iniciar sesión</a>
                    <a href="#" class="btn btn-primary-salu">Prueba gratis</a>
                </div>
                
                <!-- Mobile menu button -->
                <button class="salu-navbar-toggle" id="mobileMenuToggle" aria-label="Abrir menú">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>
    
    <!-- Mobile menu drawer -->
    <div class="salu-mobile-menu" id="mobileMenu">
        <div class="salu-mobile-menu-inner">
            <ul class="salu-mobile-menu-links">
                <li><a href="#caracteristicas">Características</a></li>
                <li><a href="#especialidades">Especialidades</a></li>
                <li><a href="#precios">Precios</a></li>
                <li><a href="#faq">FAQ</a></li>
            </ul>
            <div class="salu-mobile-menu-actions">
                <a href="#" class="btn btn-ghost w-100 mb-2">Iniciar sesión</a>
                <a href="#" class="btn btn-primary-salu w-100">Prueba gratis</a>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- HERO SECTION                                 -->
    <!-- ============================================ -->
    <section class="salu-hero">
        <div class="salu-hero-bg"></div>
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="salu-hero-content">
                        <span class="salu-badge">
                            <i data-lucide="sparkles"></i>
                            Plataforma integral para profesionales de la salud
                        </span>
                        
                        <h1 class="salu-hero-title">
                            Gestiona tu consulta con la tranquilidad de quien 
                            <span class="salu-text-gradient">cuida lo que importa</span>
                        </h1>
                        
                        <p class="salu-hero-subtitle">
                            La plataforma que unifica agenda, expedientes clínicos, consultas y seguimiento 
                            de pacientes en un solo lugar. Diseñada específicamente para profesionales de la salud en México.
                        </p>
                        
                        <div class="salu-hero-ctas">
                            <a href="#" class="btn btn-primary-salu btn-lg">
                                Comenzar prueba gratuita
                                <i data-lucide="arrow-right"></i>
                            </a>
                            <a href="#" class="btn btn-outline-salu btn-lg">
                                <i data-lucide="play-circle"></i>
                                Ver demostración
                            </a>
                        </div>
                        
                        <div class="salu-hero-social-proof">
                            <div class="salu-avatars">
                                <div class="salu-avatar" style="background: #4FD1B5;">MG</div>
                                <div class="salu-avatar" style="background: #1F5964;">CR</div>
                                <div class="salu-avatar" style="background: #8B70B8;">AM</div>
                                <div class="salu-avatar" style="background: #36A269;">JL</div>
                            </div>
                            <div class="salu-social-text">
                                <div class="salu-stars">
                                    <i data-lucide="star"></i>
                                    <i data-lucide="star"></i>
                                    <i data-lucide="star"></i>
                                    <i data-lucide="star"></i>
                                    <i data-lucide="star"></i>
                                </div>
                                <span><strong>500+ profesionales</strong> confían en SALUVERA</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="salu-hero-mockup">
                        <div class="salu-mockup-window">
                            <div class="salu-mockup-header">
                                <div class="salu-mockup-dots">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                                <div class="salu-mockup-title">SALUVERA — Dashboard</div>
                            </div>
                            <div class="salu-mockup-body">
                                <div class="salu-mockup-sidebar">
                                    <div class="salu-mockup-logo-small">S</div>
                                    <div class="salu-mockup-nav-item active">
                                        <i data-lucide="layout-dashboard"></i>
                                    </div>
                                    <div class="salu-mockup-nav-item">
                                        <i data-lucide="calendar"></i>
                                    </div>
                                    <div class="salu-mockup-nav-item">
                                        <i data-lucide="users"></i>
                                    </div>
                                    <div class="salu-mockup-nav-item">
                                        <i data-lucide="file-text"></i>
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
                                            <div class="salu-mockup-kpi-label">Próxima</div>
                                            <div class="salu-mockup-kpi-value">10:30</div>
                                        </div>
                                    </div>
                                    <div class="salu-mockup-appointments">
                                        <div class="salu-mockup-appointment">
                                            <div class="salu-mockup-appointment-time">09:00</div>
                                            <div class="salu-mockup-appointment-info">
                                                <div class="salu-mockup-appointment-name">María González</div>
                                                <div class="salu-mockup-appointment-type">Consulta psicológica</div>
                                            </div>
                                            <div class="salu-mockup-appointment-status confirmed"></div>
                                        </div>
                                        <div class="salu-mockup-appointment">
                                            <div class="salu-mockup-appointment-time">10:30</div>
                                            <div class="salu-mockup-appointment-info">
                                                <div class="salu-mockup-appointment-name">Carlos Ramírez</div>
                                                <div class="salu-mockup-appointment-type">Seguimiento</div>
                                            </div>
                                            <div class="salu-mockup-appointment-status pending"></div>
                                        </div>
                                        <div class="salu-mockup-appointment">
                                            <div class="salu-mockup-appointment-time">12:00</div>
                                            <div class="salu-mockup-appointment-info">
                                                <div class="salu-mockup-appointment-name">Ana Martínez</div>
                                                <div class="salu-mockup-appointment-type">Primera vez</div>
                                            </div>
                                            <div class="salu-mockup-appointment-status confirmed"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Elementos flotantes -->
                        <div class="salu-floating-card salu-floating-card-1">
                            <i data-lucide="check-circle"></i>
                            <div>
                                <div class="salu-floating-title">Cita confirmada</div>
                                <div class="salu-floating-subtitle">María G. — 10:30 AM</div>
                            </div>
                        </div>
                        
                        <div class="salu-floating-card salu-floating-card-2">
                            <i data-lucide="trending-up"></i>
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
    <section class="salu-logos">
        <div class="container">
            <p class="salu-logos-title">Profesionales y clínicas en todo México confían en SALUVERA</p>
            <div class="salu-logos-grid">
                <div class="salu-logo-item">Clínica Norte</div>
                <div class="salu-logo-item">Centro Médico Sur</div>
                <div class="salu-logo-item">Salud Integral</div>
                <div class="salu-logo-item">Bienestar Total</div>
                <div class="salu-logo-item">Vita Médica</div>
                <div class="salu-logo-item">Sanos Hoy</div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- PROBLEMA - Pain Points                       -->
    <!-- ============================================ -->
    <section class="salu-problem">
        <div class="container">
            <div class="salu-section-header">
                <span class="salu-badge">
                    <i data-lucide="alert-circle"></i>
                    El problema
                </span>
                <h2 class="salu-section-title">¿Te suena familiar?</h2>
                <p class="salu-section-subtitle">
                    Sabemos los desafíos que enfrentas día a día como profesional de la salud.
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="salu-problem-card">
                        <div class="salu-problem-icon">
                            <i data-lucide="calendar-x"></i>
                        </div>
                        <h3>Pierdes tiempo con agendas desorganizadas</h3>
                        <p>Citas duplicadas, horarios que se traslapan, pacientes que no llegan. La gestión manual de tu agenda te quita tiempo valioso.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="salu-problem-card">
                        <div class="salu-problem-icon">
                            <i data-lucide="files"></i>
                        </div>
                        <h3>Expedientes clínicos dispersos</h3>
                        <p>Papel, Excel, WhatsApp, notas en diferentes lugares. No tienes una visión completa del historial de tus pacientes.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="salu-problem-card">
                        <div class="salu-problem-icon">
                            <i data-lucide="eye-off"></i>
                        </div>
                        <h3>Sin visibilidad de la evolución</h3>
                        <p>No puedes ver fácilmente cómo ha evolucionado cada paciente a lo largo del tiempo. Cada consulta empieza casi desde cero.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="salu-problem-card">
                        <div class="salu-problem-icon">
                            <i data-lucide="clock"></i>
                        </div>
                        <h3>La administración te quita tiempo de atención</h3>
                        <p>Pasas más tiempo gestionando papeleo que atendiendo a tus pacientes. Tu vocación merece mejores herramientas.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- SOLUCIÓN - 3 Pilares                         -->
    <!-- ============================================ -->
    <section class="salu-solution">
        <div class="container">
            <div class="salu-section-header">
                <span class="salu-badge salu-badge-mint">
                    <i data-lucide="check-circle-2"></i>
                    La solución
                </span>
                <h2 class="salu-section-title">Todo lo que necesitas en una sola plataforma</h2>
                <p class="salu-section-subtitle">
                    SALUVERA unifica tu práctica profesional en un sistema integral, seguro y fácil de usar.
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="salu-pillar-card">
                        <div class="salu-pillar-icon">
                            <i data-lucide="calendar-check"></i>
                        </div>
                        <h3>Agenda inteligente</h3>
                        <p>Gestiona citas sin conflictos, con recordatorios automáticos y vista personalizada por profesional. Nunca más una cita duplicada.</p>
                        <ul class="salu-pillar-features">
                            <li><i data-lucide="check"></i> Vista día, semana y mes</li>
                            <li><i data-lucide="check"></i> Confirmación automática</li>
                            <li><i data-lucide="check"></i> Bloqueo de horarios</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="salu-pillar-card featured">
                        <div class="salu-pillar-badge">Más importante</div>
                        <div class="salu-pillar-icon">
                            <i data-lucide="file-heart"></i>
                        </div>
                        <h3>Expediente clínico completo</h3>
                        <p>Historial completo de cada paciente, con notas, diagnósticos, tratamientos y evolución en un solo lugar. Configurable por especialidad.</p>
                        <ul class="salu-pillar-features">
                            <li><i data-lucide="check"></i> Timeline de evolución</li>
                            <li><i data-lucide="check"></i> Búsqueda rápida</li>
                            <li><i data-lucide="check"></i> Conforme a NOM-004</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="salu-pillar-card">
                        <div class="salu-pillar-icon">
                            <i data-lucide="activity"></i>
                        </div>
                        <h3>Seguimiento continuo</h3>
                        <p>Timeline visual de la evolución del paciente, con recordatorios y seguimiento personalizado. Nunca pierdas el hilo del tratamiento.</p>
                        <ul class="salu-pillar-features">
                            <li><i data-lucide="check"></i> Evolución cronológica</li>
                            <li><i data-lucide="check"></i> Recordatorios automáticos</li>
                            <li><i data-lucide="check"></i> Indicadores de progreso</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- CARACTERÍSTICAS - Grid de 6                  -->
    <!-- ============================================ -->
    <section class="salu-features" id="caracteristicas">
        <div class="container">
            <div class="salu-section-header">
                <span class="salu-badge">
                    <i data-lucide="layers"></i>
                    Características
                </span>
                <h2 class="salu-section-title">Diseñado para cada aspecto de tu práctica</h2>
                <p class="salu-section-subtitle">
                    Cada módulo fue pensado para resolver un desafío real del profesional de la salud.
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="salu-feature-card">
                        <div class="salu-feature-icon">
                            <i data-lucide="calendar"></i>
                        </div>
                        <h3>Agenda inteligente</h3>
                        <p>Vista día/semana/mes, confirmación automática, bloqueo de horarios y prevención de conflictos en tiempo real.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="salu-feature-card">
                        <div class="salu-feature-icon">
                            <i data-lucide="file-text"></i>
                        </div>
                        <h3>Expediente clínico electrónico</h3>
                        <p>Configurable por especialidad, timeline de evolución, búsqueda rápida y cumplimiento con NOM-004-SSA3-2012.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="salu-feature-card">
                        <div class="salu-feature-icon">
                            <i data-lucide="stethoscope"></i>
                        </div>
                        <h3>Consultas estructuradas</h3>
                        <p>Patrón SOAP, autosave, plantillas personalizables por especialidad y notas clínicas organizadas.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="salu-feature-card">
                        <div class="salu-feature-icon">
                            <i data-lucide="pill"></i>
                        </div>
                        <h3>Indicaciones terapéuticas</h3>
                        <p>Registro de medicamentos, dosis, frecuencia y duración. Impresión de indicaciones con formato profesional.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="salu-feature-card">
                        <div class="salu-feature-icon">
                            <i data-lucide="folder-open"></i>
                        </div>
                        <h3>Documentos y archivos</h3>
                        <p>Adjunta estudios, resultados de laboratorio e imágenes. Almacenamiento seguro y organizado por paciente.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="salu-feature-card">
                        <div class="salu-feature-icon">
                            <i data-lucide="bar-chart-3"></i>
                        </div>
                        <h3>Reportes y estadísticas</h3>
                        <p>Citas por período, pacientes activos, ocupación de agenda y evolución. Datos claros para mejores decisiones.</p>
                    </div>
                </div>
            </div>
            
            <div class="salu-features-cta">
                <a href="#" class="btn btn-outline-salu">
                    Ver todas las características
                    <i data-lucide="arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- BENEFICIOS                                   -->
    <!-- ============================================ -->
    <section class="salu-benefits">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="salu-benefits-visual">
                        <div class="salu-benefit-graph">
                            <div class="salu-graph-header">
                                <span>Tiempo ahorrado con SALUVERA</span>
                                <strong>+40%</strong>
                            </div>
                            <div class="salu-graph-bars">
                                <div class="salu-graph-bar">
                                    <div class="salu-graph-bar-label">Gestión administrativa</div>
                                    <div class="salu-graph-bar-track">
                                        <div class="salu-graph-bar-fill" style="width: 85%;"></div>
                                    </div>
                                </div>
                                <div class="salu-graph-bar">
                                    <div class="salu-graph-bar-label">Búsqueda de expedientes</div>
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
                            <i data-lucide="award"></i>
                            Beneficios
                        </span>
                        <h2 class="salu-section-title">Más tiempo para lo que realmente importa: tus pacientes</h2>
                        
                        <div class="salu-benefits-list">
                            <div class="salu-benefit-item">
                                <div class="salu-benefit-icon">
                                    <i data-lucide="clock"></i>
                                </div>
                                <div>
                                    <h4>Ahorra tiempo</h4>
                                    <p>Reduce hasta un 40% el tiempo en gestión administrativa. Dedica más tiempo a atender, menos a papelear.</p>
                                </div>
                            </div>
                            <div class="salu-benefit-item">
                                <div class="salu-benefit-icon">
                                    <i data-lucide="heart-pulse"></i>
                                </div>
                                <div>
                                    <h4>Mejora la atención</h4>
                                    <p>Accede al historial completo del paciente en segundos. Decisiones más informadas, mejor atención.</p>
                                </div>
                            </div>
                            <div class="salu-benefit-item">
                                <div class="salu-benefit-icon">
                                    <i data-lucide="users"></i>
                                </div>
                                <div>
                                    <h4>Aumenta la retención</h4>
                                    <p>Seguimiento continuo significa pacientes más fieles. El seguimiento marca la diferencia.</p>
                                </div>
                            </div>
                            <div class="salu-benefit-item">
                                <div class="salu-benefit-icon">
                                    <i data-lucide="shield-check"></i>
                                </div>
                                <div>
                                    <h4>Cumple normativas</h4>
                                    <p>Expediente clínico conforme a NOM-004-SSA3-2012 y LFPDPPP. Trabaja con tranquilidad legal.</p>
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
    <section class="salu-specialties" id="especialidades">
        <div class="container">
            <div class="salu-section-header">
                <span class="salu-badge">
                    <i data-lucide="stethoscope"></i>
                    Especialidades
                </span>
                <h2 class="salu-section-title">Diseñado para cada especialidad</h2>
                <p class="salu-section-subtitle">
                    SALUVERA se adapta a tu práctica, sin importar tu especialidad.
                </p>
            </div>
            
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="salu-specialty-card">
                        <div class="salu-specialty-icon">
                            <i data-lucide="brain"></i>
                        </div>
                        <h4>Psicólogos</h4>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="salu-specialty-card">
                        <div class="salu-specialty-icon">
                            <i data-lucide="heart"></i>
                        </div>
                        <h4>Médicos generales</h4>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="salu-specialty-card">
                        <div class="salu-specialty-icon">
                            <i data-lucide="activity"></i>
                        </div>
                        <h4>Especialistas</h4>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="salu-specialty-card">
                        <div class="salu-specialty-icon">
                            <i data-lucide="brain-circuit"></i>
                        </div>
                        <h4>Psiquiatras</h4>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="salu-specialty-card">
                        <div class="salu-specialty-icon">
                            <i data-lucide="apple"></i>
                        </div>
                        <h4>Nutriólogos</h4>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="salu-specialty-card">
                        <div class="salu-specialty-icon">
                            <i data-lucide="smile"></i>
                        </div>
                        <h4>Odontólogos</h4>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="salu-specialty-card">
                        <div class="salu-specialty-icon">
                            <i data-lucide="dumbbell"></i>
                        </div>
                        <h4>Fisioterapeutas</h4>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="salu-specialty-card">
                        <div class="salu-specialty-icon">
                            <i data-lucide="hand-heart"></i>
                        </div>
                        <h4>Terapeutas</h4>
                    </div>
                </div>
            </div>
            
            <p class="salu-specialties-note">
                <i data-lucide="sparkles"></i>
                Y muchas más especialidades. SALUVERA se adapta a tu práctica.
            </p>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- SEGURIDAD                                    -->
    <!-- ============================================ -->
    <section class="salu-security">
        <div class="container">
            <div class="salu-section-header">
                <span class="salu-badge">
                    <i data-lucide="shield-check"></i>
                    Seguridad
                </span>
                <h2 class="salu-section-title">Seguridad y cumplimiento normativo garantizados</h2>
                <p class="salu-section-subtitle">
                    Tu información y la de tus pacientes está protegida con los más altos estándares.
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="salu-security-card">
                        <div class="salu-security-icon">
                            <i data-lucide="lock"></i>
                        </div>
                        <h3>Protección de datos</h3>
                        <ul>
                            <li>Cifrado AES-256</li>
                            <li>Cumplimiento LFPDPPP</li>
                            <li>Backups automáticos diarios</li>
                            <li>Servidores seguros en México</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="salu-security-card">
                        <div class="salu-security-icon">
                            <i data-lucide="file-check"></i>
                        </div>
                        <h3>Expediente clínico legal</h3>
                        <ul>
                            <li>Conforme a NOM-004-SSA3-2012</li>
                            <li>Retención mínima de 5 años</li>
                            <li>Auditoría completa de cambios</li>
                            <li>Imposibilidad de borrado de notas</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="salu-security-card">
                        <div class="salu-security-icon">
                            <i data-lucide="user-check"></i>
                        </div>
                        <h3>Acceso controlado</h3>
                        <ul>
                            <li>Autenticación de dos factores</li>
                            <li>Permisos granulares por rol</li>
                            <li>Registro de actividad completo</li>
                            <li>Sesiones seguras con expiración</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="salu-security-badges">
                <div class="salu-compliance-badge">
                    <i data-lucide="shield-check"></i>
                    <span>Cumple LFPDPPP</span>
                </div>
                <div class="salu-compliance-badge">
                    <i data-lucide="shield-check"></i>
                    <span>Cumple NOM-004</span>
                </div>
                <div class="salu-compliance-badge">
                    <i data-lucide="shield-check"></i>
                    <span>Cifrado AES-256</span>
                </div>
                <div class="salu-compliance-badge">
                    <i data-lucide="shield-check"></i>
                    <span>99.9% Uptime</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- TESTIMONIOS                                  -->
    <!-- ============================================ -->
    <section class="salu-testimonials">
        <div class="container">
            <div class="salu-section-header">
                <span class="salu-badge">
                    <i data-lucide="message-circle"></i>
                    Testimonios
                </span>
                <h2 class="salu-section-title">Lo que dicen nuestros usuarios</h2>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="salu-testimonial-card">
                        <div class="salu-testimonial-stars">
                            <i data-lucide="star"></i>
                            <i data-lucide="star"></i>
                            <i data-lucide="star"></i>
                            <i data-lucide="star"></i>
                            <i data-lucide="star"></i>
                        </div>
                        <p class="salu-testimonial-text">
                            "SALUVERA transformó mi práctica. Ahora tengo más tiempo para mis pacientes y menos para papeleo. El expediente clínico es exactamente lo que necesitaba."
                        </p>
                        <div class="salu-testimonial-author">
                            <div class="salu-testimonial-avatar" style="background: #4FD1B5;">MG</div>
                            <div>
                                <div class="salu-testimonial-name">Dra. María González</div>
                                <div class="salu-testimonial-role">Psicóloga Clínica</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="salu-testimonial-card">
                        <div class="salu-testimonial-stars">
                            <i data-lucide="star"></i>
                            <i data-lucide="star"></i>
                            <i data-lucide="star"></i>
                            <i data-lucide="star"></i>
                            <i data-lucide="star"></i>
                        </div>
                        <p class="salu-testimonial-text">
                            "La agenda inteligente me ahorró horas de trabajo. Ya no tengo citas duplicadas y mis pacientes reciben recordatorios automáticos. Increíble."
                        </p>
                        <div class="salu-testimonial-author">
                            <div class="salu-testimonial-avatar" style="background: #1F5964;">CR</div>
                            <div>
                                <div class="salu-testimonial-name">Dr. Carlos Ramírez</div>
                                <div class="salu-testimonial-role">Médico General</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="salu-testimonial-card">
                        <div class="salu-testimonial-stars">
                            <i data-lucide="star"></i>
                            <i data-lucide="star"></i>
                            <i data-lucide="star"></i>
                            <i data-lucide="star"></i>
                            <i data-lucide="star"></i>
                        </div>
                        <p class="salu-testimonial-text">
                            "El expediente clínico configurable es perfecto para mi especialidad. Muy intuitivo y profesional. Mis pacientes notan la diferencia en la atención."
                        </p>
                        <div class="salu-testimonial-author">
                            <div class="salu-testimonial-avatar" style="background: #8B70B8;">AM</div>
                            <div>
                                <div class="salu-testimonial-name">Lic. Ana Martínez</div>
                                <div class="salu-testimonial-role">Nutrióloga</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="salu-testimonials-rating">
                <div class="salu-rating-large">
                    <span class="salu-rating-number">4.9</span>
                    <div class="salu-rating-stars">
                        <i data-lucide="star"></i>
                        <i data-lucide="star"></i>
                        <i data-lucide="star"></i>
                        <i data-lucide="star"></i>
                        <i data-lucide="star"></i>
                    </div>
                    <span class="salu-rating-count">Basado en 500+ reseñas</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- PRECIOS                                      -->
    <!-- ============================================ -->
    <section class="salu-pricing" id="precios">
        <div class="container">
            <div class="salu-section-header">
                <span class="salu-badge">
                    <i data-lucide="credit-card"></i>
                    Precios
                </span>
                <h2 class="salu-section-title">Planes que se adaptan a tu práctica</h2>
                <p class="salu-section-subtitle">
                    14 días de prueba gratuita. Sin tarjeta de crédito. Cancela cuando quieras.
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
                            <li><i data-lucide="check"></i> 1 profesional</li>
                            <li><i data-lucide="check"></i> Hasta 100 pacientes</li>
                            <li><i data-lucide="check"></i> Todas las características</li>
                            <li><i data-lucide="check"></i> Soporte por email</li>
                            <li><i data-lucide="check"></i> 5 GB de almacenamiento</li>
                        </ul>
                        <a href="#" class="btn btn-outline-salu w-100">Comenzar prueba</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="salu-pricing-card featured">
                        <div class="salu-pricing-badge">Más popular</div>
                        <div class="salu-pricing-header">
                            <h3>Profesional</h3>
                            <p>Para consultorios pequeños</p>
                        </div>
                        <div class="salu-pricing-price">
                            <span class="salu-pricing-currency">$</span>
                            <span class="salu-pricing-amount">599</span>
                            <span class="salu-pricing-period">/mes</span>
                        </div>
                        <ul class="salu-pricing-features">
                            <li><i data-lucide="check"></i> Hasta 3 profesionales</li>
                            <li><i data-lucide="check"></i> Pacientes ilimitados</li>
                            <li><i data-lucide="check"></i> Todas las características</li>
                            <li><i data-lucide="check"></i> Soporte prioritario</li>
                            <li><i data-lucide="check"></i> Reportes avanzados</li>
                            <li><i data-lucide="check"></i> 25 GB de almacenamiento</li>
                        </ul>
                        <a href="#" class="btn btn-primary-salu w-100">Comenzar prueba</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="salu-pricing-card">
                        <div class="salu-pricing-header">
                            <h3>Clínica</h3>
                            <p>Para clínicas y centros médicos</p>
                        </div>
                        <div class="salu-pricing-price">
                            <span class="salu-pricing-currency">$</span>
                            <span class="salu-pricing-amount">1,299</span>
                            <span class="salu-pricing-period">/mes</span>
                        </div>
                        <ul class="salu-pricing-features">
                            <li><i data-lucide="check"></i> Profesionales ilimitados</li>
                            <li><i data-lucide="check"></i> Pacientes ilimitados</li>
                            <li><i data-lucide="check"></i> Todas las características</li>
                            <li><i data-lucide="check"></i> Soporte 24/7</li>
                            <li><i data-lucide="check"></i> API access</li>
                            <li><i data-lucide="check"></i> Onboarding personalizado</li>
                            <li><i data-lucide="check"></i> 100 GB de almacenamiento</li>
                        </ul>
                        <a href="#" class="btn btn-outline-salu w-100">Contactar ventas</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- FAQ                                          -->
    <!-- ============================================ -->
    <section class="salu-faq" id="faq">
        <div class="container">
            <div class="salu-section-header">
                <span class="salu-badge">
                    <i data-lucide="help-circle"></i>
                    FAQ
                </span>
                <h2 class="salu-section-title">Preguntas frecuentes</h2>
            </div>
            
            <div class="salu-faq-list">
                <div class="salu-faq-item">
                    <button class="salu-faq-question" aria-expanded="false">
                        <span>¿Necesito instalar algo en mi computadora?</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="salu-faq-answer">
                        <p>No. SALUVERA es una plataforma 100% en la nube. Solo necesitas un navegador web actualizado (Chrome, Firefox, Safari o Edge) y conexión a internet. Funciona en computadora, tablet y móvil.</p>
                    </div>
                </div>
                <div class="salu-faq-item">
                    <button class="salu-faq-question" aria-expanded="false">
                        <span>¿Puedo migrar mis datos actuales al sistema?</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="salu-faq-answer">
                        <p>Sí. Ofrecemos asistencia para migrar tus datos desde Excel, otros sistemas o expedientes en papel. Nuestro equipo te acompaña en todo el proceso de onboarding sin costo adicional en los planes Profesional y Clínica.</p>
                    </div>
                </div>
                <div class="salu-faq-item">
                    <button class="salu-faq-question" aria-expanded="false">
                        <span>¿Es seguro para información clínica sensible?</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="salu-faq-answer">
                        <p>Absolutamente. SALUVERA cumple con LFPDPPP y NOM-004-SSA3-2012. Utilizamos cifrado AES-256, servidores seguros en México, backups automáticos y auditoría completa de todos los cambios. Tu información y la de tus pacientes está protegida con los más altos estándares.</p>
                    </div>
                </div>
                <div class="salu-faq-item">
                    <button class="salu-faq-question" aria-expanded="false">
                        <span>¿Funciona en dispositivos móviles?</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="salu-faq-answer">
                        <p>Sí. SALUVERA está diseñado con enfoque mobile-first. Funciona perfectamente en smartphones y tablets. Próximamente lanzaremos aplicaciones nativas para iOS y Android con funcionalidades específicas para consulta móvil.</p>
                    </div>
                </div>
                <div class="salu-faq-item">
                    <button class="salu-faq-question" aria-expanded="false">
                        <span>¿Qué pasa si cancelo mi suscripción?</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="salu-faq-answer">
                        <p>Puedes cancelar cuando quieras, sin penalizaciones. Conservamos tus datos durante 90 días después de la cancelación para que puedas exportarlos. Después de ese periodo, cumpliendo con la NOM-004, mantenemos los expedientes clínicos en resguardo por el tiempo legal requerido.</p>
                    </div>
                </div>
                <div class="salu-faq-item">
                    <button class="salu-faq-question" aria-expanded="false">
                        <span>¿Ofrecen capacitación para mi equipo?</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="salu-faq-answer">
                        <p>Sí. Todos los planes incluyen acceso a nuestra base de conocimiento y tutoriales en video. Los planes Profesional y Clínica incluyen sesiones de onboarding personalizadas con nuestro equipo para ti y tu personal.</p>
                    </div>
                </div>
            </div>
            
            <div class="salu-faq-cta">
                <p>¿Tienes otra pregunta?</p>
                <a href="#" class="btn btn-outline-salu">
                    Contáctanos
                    <i data-lucide="arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- CTA FINAL                                    -->
    <!-- ============================================ -->
    <section class="salu-cta-final">
        <div class="container">
            <div class="salu-cta-final-content">
                <h2>Comienza a gestionar mejor tu práctica hoy</h2>
                <p>Únete a los cientos de profesionales que ya confían en SALUVERA para cuidar lo que realmente importa.</p>
                <div class="salu-cta-final-buttons">
                    <a href="#" class="btn btn-mint btn-lg">
                        Comenzar prueba gratuita
                        <i data-lucide="arrow-right"></i>
                    </a>
                    <a href="#" class="btn btn-outline-white btn-lg">
                        Agendar demostración
                    </a>
                </div>
                <p class="salu-cta-final-note">14 días gratis · Sin tarjeta de crédito · Cancela cuando quieras</p>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- FOOTER                                       -->
    <!-- ============================================ -->
    <footer class="salu-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="salu-footer-brand">
                        <div class="salu-logo">
                            <div class="salu-logo-icon">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 4C9.373 4 4 9.373 4 16s5.373 12 12 12 12-5.373 12-12S22.627 4 16 4z" fill="#4FD1B5"/>
                                    <path d="M10 16c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="#123C46" stroke-width="2.5" stroke-linecap="round"/>
                                    <circle cx="16" cy="20" r="2" fill="#123C46"/>
                                </svg>
                            </div>
                            <span class="salu-logo-text">SALUVERA</span>
                        </div>
                        <p class="salu-footer-tagline">Tecnología para cuidar lo que importa.</p>
                        <div class="salu-footer-social">
                            <a href="#" aria-label="Facebook"><i data-lucide="facebook"></i></a>
                            <a href="#" aria-label="Twitter"><i data-lucide="twitter"></i></a>
                            <a href="#" aria-label="LinkedIn"><i data-lucide="linkedin"></i></a>
                            <a href="#" aria-label="Instagram"><i data-lucide="instagram"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h5>Producto</h5>
                    <ul>
                        <li><a href="#caracteristicas">Características</a></li>
                        <li><a href="#especialidades">Especialidades</a></li>
                        <li><a href="#precios">Precios</a></li>
                        <li><a href="#">Demo</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h5>Recursos</h5>
                    <ul>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Centro de ayuda</a></li>
                        <li><a href="#">Documentación API</a></li>
                        <li><a href="#">Estado del sistema</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h5>Empresa</h5>
                    <ul>
                        <li><a href="#">Acerca de</a></li>
                        <li><a href="#">Contacto</a></li>
                        <li><a href="#">Trabaja con nosotros</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h5>Legal</h5>
                    <ul>
                        <li><a href="#">Aviso de privacidad</a></li>
                        <li><a href="#">Términos de uso</a></li>
                        <li><a href="#">Política de cookies</a></li>
                        <li><a href="#">Cumplimiento</a></li>
                    </ul>
                </div>
            </div>
            <div class="salu-footer-bottom">
                <p>&copy; 2026 SALUVERA. Todos los derechos reservados. Komio Creativo and IscjlchavezG</p>
                <p class="salu-footer-location">
                    <i data-lucide="map-pin"></i>
                    Hecho en México con ❤️
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SALUVERA Landing JS -->
    <script src="assets/js/saluvera-landing.js"></script>
    
    <!-- Inicializar Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>