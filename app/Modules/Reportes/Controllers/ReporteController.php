<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class ReporteController
{
    private Reporte $model;
    private ReportePlataforma $plataformaModel;

    public function __construct()
    {
        $this->model = new Reporte();
        $this->plataformaModel = new ReportePlataforma();
    }

    private function orgId(): int
    {
        $userModel = new User();
        $user = $userModel->findById(Session::getUserId());
        return (int) ($user['organizacion_id'] ?? 1);
    }

    private function rol(): string
    {
        $user = Session::user() ?? [];
        return $user['rol_slug'] ?? '';
    }

    private function esSuperAdmin(): bool
    {
        return $this->rol() === 'superadmin';
    }

    private function miProfesionalId(): ?int
    {
        if ($this->rol() !== 'professional') {
            return null;
        }
        $db = Database::getInstance();
        $prof = $db->fetchOne("SELECT id FROM profesionales WHERE usuario_id = ? LIMIT 1", [Session::getUserId()]);
        return $prof !== null ? (int) $prof['id'] : null;
    }

    public function exportar(Request $request, Response $response): void
    {
        $rol = $this->rol();

        if ($rol === 'professional') {
            $this->exportarProfesional($request);
        } elseif ($this->esSuperAdmin()) {
            $this->exportarGlobal($request);
        } else {
            $this->exportarOrg($request);
        }
    }

    private function exportarProfesional(Request $request): void
    {
        $profId = $this->miProfesionalId() ?? 0;
        $orgId = $this->orgId();

        $periodo = $request->input('periodo', 'mes');
        [$desde, $hasta] = Reporte::rango($periodo, $request->input('desde'), $request->input('hasta'));

        $user = Session::user() ?? [];
        $nombre = trim(($user['nombre'] ?? '') . ' ' . ($user['apellidos'] ?? ''));

        $consultas = $this->model->consultasCompletadas($orgId, $profId, $desde, $hasta);
        $ingresos = $this->model->ingresos($orgId, $profId, $desde, $hasta);
        $pacientes = $this->model->pacientesAtendidos($orgId, $profId, $desde, $hasta);
        $canceladas = $this->model->citasCanceladas($orgId, $profId, $desde, $hasta);
        $pasadas = $this->model->citasPasadas($orgId, $profId, $desde, $hasta);
        $noAsistidas = $this->model->citasNoAsistidas($orgId, $profId, $desde, $hasta);
        $minutos = $this->model->minutosTrabajados($orgId, $profId, $desde, $hasta);
        $diagnosticos = $this->model->diagnosticosFrecuentes($orgId, $profId, $desde, $hasta);

        $tasaNoShow = $pasadas > 0 ? round(($noAsistidas / $pasadas) * 100, 1) : 0;
        $horasTxt = intdiv($minutos, 60) . 'h ' . ($minutos % 60) . 'm';

        $xl = new ExcelExport();
        $xl->setSheetName('Mis Reportes');
        $xl->setColumnWidths([220, 120]);
        $xl->titulo('Mis Reportes - ' . $nombre);
        $xl->subtitulo(Reporte::nombrePeriodo($periodo) . ' · ' . $desde . ' a ' . $hasta);
        $xl->filaVacia();

        $xl->seccion('Resumen Economico');
        $xl->header(['Metrica', 'Valor']);
        $xl->row([['Ingresos', 's'], [$ingresos, 'm']]);
        $xl->row([['Consultas completadas', 's'], [$consultas, 'n']]);
        $xl->row([['Horas trabajadas', 's'], [$horasTxt, 's']]);
        $xl->filaVacia();

        $xl->seccion('Actividad Clinica');
        $xl->header(['Metrica', 'Valor']);
        $xl->row([['Pacientes atendidos', 's'], [$pacientes, 'n']]);
        $xl->row([['Consultas terminadas', 's'], [$consultas, 'n']]);
        $xl->row([['Citas canceladas', 's'], [$canceladas, 'n']]);
        $xl->row([['No asistieron (%)', 's'], [$tasaNoShow, 'n']]);
        $xl->filaVacia();

        if (!empty($diagnosticos)) {
            $xl->seccion('Diagnosticos Mas Frecuentes');
            $xl->header(['Diagnostico', 'Veces']);
            foreach ($diagnosticos as $d) {
                $xl->row([[$d['nombre_diagnostico'], 's'], [$d['veces'], 'n']]);
            }
        }

        $xl->download('mis_reportes_' . date('Ymd_His') . '.xls');
    }

    private function exportarOrg(Request $request): void
    {
        $orgId = $this->orgId();
        $periodo = $request->input('periodo', 'mes');
        [$desde, $hasta] = Reporte::rango($periodo, $request->input('desde'), $request->input('hasta'));

        $ingresos = $this->model->ingresos($orgId, 0, $desde, $hasta);
        $consultas = $this->model->consultasCompletadas($orgId, 0, $desde, $hasta);
        $pacientes = $this->model->totalPacientes($orgId);
        $porProfesional = $this->model->ingresosPorProfesional($orgId, $desde, $hasta);

        $xl = new ExcelExport();
        $xl->setSheetName('Reportes Clinica');
        $xl->setColumnWidths([220, 120, 120]);
        $xl->titulo('Reportes de la Clinica');
        $xl->subtitulo(Reporte::nombrePeriodo($periodo) . ' · ' . $desde . ' a ' . $hasta);
        $xl->filaVacia();

        $xl->seccion('Resumen');
        $xl->header(['Metrica', 'Valor']);
        $xl->row([['Ingresos totales', 's'], [$ingresos, 'm']]);
        $xl->row([['Consultas completadas', 's'], [$consultas, 'n']]);
        $xl->row([['Pacientes registrados', 's'], [$pacientes, 'n']]);
        $xl->filaVacia();

        $xl->seccion('Ingresos por Profesional');
        $xl->header(['Profesional', 'Consultas', 'Total']);
        foreach ($porProfesional as $p) {
            $xl->row([
                [trim($p['nombre'] . ' ' . $p['apellidos']), 's'],
                [$p['consultas'], 'n'],
                [$p['total'], 'm'],
            ]);
        }

        $xl->download('reportes_clinica_' . date('Ymd_His') . '.xls');
    }

    private function exportarGlobal(Request $request): void
    {
        $periodo = $request->input('periodo', 'mes');
        [$desde, $hasta] = Reporte::rango($periodo, $request->input('desde'), $request->input('hasta'));

        $totalUsuarios = $this->model->totalUsuarios();
        $totalProfesionales = $this->model->totalProfesionalesGlobal();
        $totalOrganizaciones = $this->model->totalOrganizacionesGlobal();
        $totalPacientes = $this->model->totalPacientes(0);
        $totalCitas = $this->model->totalCitasGlobal($desde, $hasta);
        $totalConsultas = $this->model->totalConsultasGlobal($desde, $hasta);
        $pacientesPorOrg = $this->model->pacientesPorOrganizacion();

        $xl = new ExcelExport();
        $xl->setSheetName('Reportes de Plataforma');
        $xl->setColumnWidths([220, 120, 120]);
        $xl->titulo('Reportes de Plataforma');
        $xl->subtitulo(Reporte::nombrePeriodo($periodo) . ' · ' . $desde . ' a ' . $hasta);
        $xl->filaVacia();

        $xl->seccion('Plataforma');
        $xl->header(['Metrica', 'Valor']);
        $xl->row([['Organizaciones', 's'], [$totalOrganizaciones, 'n']]);
        $xl->row([['Usuarios', 's'], [$totalUsuarios, 'n']]);
        $xl->row([['Profesionales', 's'], [$totalProfesionales, 'n']]);
        $xl->row([['Pacientes', 's'], [$totalPacientes, 'n']]);
        $xl->filaVacia();

        $xl->seccion('Actividad del Periodo');
        $xl->header(['Metrica', 'Valor']);
        $xl->row([['Citas totales', 's'], [$totalCitas, 'n']]);
        $xl->row([['Consultas realizadas', 's'], [$totalConsultas, 'n']]);
        $xl->filaVacia();

        $xl->seccion('Pacientes por Organizacion');
        $xl->header(['Organizacion', 'Estado', 'Pacientes']);
        foreach ($pacientesPorOrg as $o) {
            $xl->row([
                [$o['nombre'], 's'],
                [(int) $o['activo'] === 1 ? 'Activa' : 'Inactiva', 's'],
                [$o['pacientes'], 'n'],
            ]);
        }

        $xl->download('reportes_plataforma_' . date('Ymd_His') . '.xls');
    }

    public function index(Request $request, Response $response): void
    {
        $rol = $this->rol();

        if ($rol === 'professional') {
            $this->reporteProfesional($request, $response);
        } elseif ($this->esSuperAdmin()) {
            $this->reportePlataforma($request, $response);
        } else {
            $this->reporteOrg($request, $response);
        }
    }

    // ========================================================================
    // REPORTE DEL PROFESIONAL (mis metricas)
    // ========================================================================

    private function reporteProfesional(Request $request, Response $response): void
    {
        $profId = $this->miProfesionalId() ?? 0;
        $orgId = $this->orgId();

        $periodo = $request->input('periodo', 'mes');
        [$desde, $hasta] = Reporte::rango($periodo, $request->input('desde'), $request->input('hasta'));

        $consultas = $this->model->consultasCompletadas($orgId, $profId, $desde, $hasta);
        $ingresos = $this->model->ingresos($orgId, $profId, $desde, $hasta);
        $pasadas = $this->model->citasPasadas($orgId, $profId, $desde, $hasta);
        $noAsistidas = $this->model->citasNoAsistidas($orgId, $profId, $desde, $hasta);
        $pacientes = $this->model->pacientesAtendidos($orgId, $profId, $desde, $hasta);
        $diagnosticos = $this->model->diagnosticosFrecuentes($orgId, $profId, $desde, $hasta);
        $canceladas = $this->model->citasCanceladas($orgId, $profId, $desde, $hasta);
        $minutosTrabajados = $this->model->minutosTrabajados($orgId, $profId, $desde, $hasta);
        $porMes = $this->model->ingresosPorMes($orgId, $profId, $desde, $hasta);

        $tasaNoShow = $pasadas > 0 ? round(($noAsistidas / $pasadas) * 100, 1) : 0;

        $content = View::render('Pages/reportes/profesional', [
            'periodo' => $periodo,
            'desde' => $desde,
            'hasta' => $hasta,
            'nombrePeriodo' => Reporte::nombrePeriodo($periodo),
            'consultas' => $consultas,
            'ingresos' => $ingresos,
            'tasaNoShow' => $tasaNoShow,
            'pacientes' => $pacientes,
            'diagnosticos' => $diagnosticos,
            'porMes' => $porMes,
            'canceladas' => $canceladas,
            'minutosTrabajados' => $minutosTrabajados,
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => 'Mis Reportes', 'content' => $content]);
        $response->html($html);
    }

    // ========================================================================
    // REPORTE DE ORGANIZACION (clinic_admin)
    // ========================================================================

    private function reporteOrg(Request $request, Response $response): void
    {
        $orgId = $this->orgId();

        $periodo = $request->input('periodo', 'mes');
        [$desde, $hasta] = Reporte::rango($periodo, $request->input('desde'), $request->input('hasta'));

        $content = View::render('Pages/reportes/org', [
            'periodo' => $periodo,
            'desde' => $desde,
            'hasta' => $hasta,
            'nombrePeriodo' => Reporte::nombrePeriodo($periodo),
            'consultas' => $this->model->consultasCompletadas($orgId, 0, $desde, $hasta),
            'ingresos' => $this->model->ingresos($orgId, 0, $desde, $hasta),
            'pacientes' => $this->model->totalPacientes($orgId),
            'atendidos' => $this->model->pacientesAtendidos($orgId, 0, $desde, $hasta),
            'porProfesional' => $this->model->ingresosPorProfesional($orgId, $desde, $hasta),
            'diagnosticos' => $this->model->diagnosticosFrecuentes($orgId, 0, $desde, $hasta),
            'porMes' => $this->model->ingresosPorMes($orgId, 0, $desde, $hasta),
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => 'Reportes de la Clinica', 'content' => $content]);
        $response->html($html);
    }

    // ========================================================================
    // REPORTE GLOBAL (superadmin)
    // ========================================================================

    private function reportePlataforma(Request $request, Response $response): void
    {
        $kpis = $this->plataformaModel->kpisResumen();
        $crecimiento = $this->plataformaModel->crecimientoOrganizaciones();
        $citasPorMes = $this->plataformaModel->citasPorMes();
        
        $usuariosPorRol = $this->plataformaModel->usuariosPorRol();
        $profesionalesPorEspecialidad = $this->plataformaModel->profesionalesPorEspecialidad();
        $pacientesPorGenero = $this->plataformaModel->pacientesPorGenero();
        $pacientesPorEdad = $this->plataformaModel->pacientesPorRangoEdad();
        $pacientesPorCiudad = $this->plataformaModel->pacientesPorCiudad();
        $usuariosActivos = $this->plataformaModel->usuariosActivos();
        $ultimoAcceso = $this->plataformaModel->ultimoAccesoPorUsuario();
        
        $benchmark = $this->plataformaModel->benchmarkOrganizaciones();
        $ratios = $this->plataformaModel->ratioPacientesPorProfesional();
        
        $topProcesos = $this->plataformaModel->topProcesos();
        $noShowPorEspecialidad = $this->plataformaModel->tasaNoShowPorEspecialidad();
        $consultasPorEstado = $this->plataformaModel->consultasPorEstado();
        $documentosPorTipo = $this->plataformaModel->documentosPorTipo();

        // FASE 2: Engagement y Geografia
        $organizacionesEnRiesgo = $this->plataformaModel->organizacionesEnRiesgo();
        $usuariosPorSegmento = $this->plataformaModel->usuariosPorSegmentoAcceso();
        $alertas = $this->plataformaModel->alertasPlataforma();
        $pacientesPorEstado = $this->plataformaModel->pacientesPorEstado();
        $organizacionesUbicacion = $this->plataformaModel->organizacionesConUbicacion();
        $usoFeatures = $this->plataformaModel->usoFeaturesPorOrganizacion();
        $cohortesOrgs = $this->plataformaModel->cohortesOrganizaciones();
        $usuariosPorMes = $this->plataformaModel->usuariosRegistradosPorMes();

        $content = View::render('Pages/reportes/plataforma', [
            'kpis' => $kpis,
            'crecimiento' => $crecimiento,
            'citasPorMes' => $citasPorMes,
            'usuariosPorRol' => $usuariosPorRol,
            'profesionalesPorEspecialidad' => $profesionalesPorEspecialidad,
            'pacientesPorGenero' => $pacientesPorGenero,
            'pacientesPorEdad' => $pacientesPorEdad,
            'pacientesPorCiudad' => $pacientesPorCiudad,
            'usuariosActivos' => $usuariosActivos,
            'ultimoAcceso' => $ultimoAcceso,
            'benchmark' => $benchmark,
            'ratios' => $ratios,
            'topProcesos' => $topProcesos,
            'noShowPorEspecialidad' => $noShowPorEspecialidad,
            'consultasPorEstado' => $consultasPorEstado,
            'documentosPorTipo' => $documentosPorTipo,
            'organizacionesEnRiesgo' => $organizacionesEnRiesgo,
            'usuariosPorSegmento' => $usuariosPorSegmento,
            'alertas' => $alertas,
            'pacientesPorEstado' => $pacientesPorEstado,
            'organizacionesUbicacion' => $organizacionesUbicacion,
            'usoFeatures' => $usoFeatures,
            'cohortesOrgs' => $cohortesOrgs,
            'usuariosPorMes' => $usuariosPorMes,
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => 'Reportes de Plataforma', 'content' => $content]);
        $response->html($html);
    }

    private function reporteGlobal(Request $request, Response $response): void
    {
        $periodo = $request->input('periodo', 'mes');
        [$desde, $hasta] = Reporte::rango($periodo, $request->input('desde'), $request->input('hasta'));

        $content = View::render('Pages/reportes/global', [
            'periodo' => $periodo,
            'desde' => $desde,
            'hasta' => $hasta,
            'nombrePeriodo' => Reporte::nombrePeriodo($periodo),
            'totalUsuarios' => $this->model->totalUsuarios(),
            'usuariosActivos' => $this->model->usuariosActivos(),
            'totalProfesionales' => $this->model->totalProfesionalesGlobal(),
            'totalOrganizaciones' => $this->model->totalOrganizacionesGlobal(),
            'organizacionesActivas' => $this->model->organizacionesActivas(),
            'totalPacientes' => $this->model->totalPacientes(0),
            'totalCitas' => $this->model->totalCitasGlobal($desde, $hasta),
            'citasPorEstado' => $this->model->citasPorEstadoGlobal($desde, $hasta),
            'totalConsultas' => $this->model->totalConsultasGlobal($desde, $hasta),
            'pacientesPorOrganizacion' => $this->model->pacientesPorOrganizacion(),
            'actividadReciente' => $this->model->actividadReciente(),
        ]);

        $html = View::render('Layouts.panel', ['pageTitle' => 'Reportes de Plataforma', 'content' => $content]);
        $response->html($html);
    }
}
