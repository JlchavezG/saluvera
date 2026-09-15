<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class PortalController
{
    private Paciente $pacientes;
    private Cita $citas;
    private Expediente $expediente;
    private Documento $documentos;
    private Consulta $consultas;

    public function __construct()
    {
        $this->pacientes = new Paciente();
        $this->citas = new Cita();
        $this->expediente = new Expediente();
        $this->documentos = new Documento();
        $this->consultas = new Consulta();
    }

    // ========================================================================
    // SESION DEL PORTAL
    // ========================================================================

    private function portalPaciente(): ?array
    {
        $id = Session::get('portal_paciente_id');

        if (!$id) {
            return null;
        }

        $paciente = $this->pacientes->findById((int) $id);

        if ($paciente === null || (int) $paciente['activo'] !== 1) {
            return null;
        }

        return $paciente;
    }

    private function renderPortal(string $vista, array $datos, string $titulo, Response $response): void
    {
        $datos['pacientePortal'] = $this->portalPaciente();
        $content = View::render($vista, $datos);
        $html = View::render('Layouts.portal', [
            'pageTitle' => $titulo,
            'content' => $content,
            'pacientePortal' => $datos['pacientePortal'],
        ]);
        $response->html($html);
    }

    // ========================================================================
    // ACCESO POR TOKEN
    // ========================================================================

    public function acceso(Request $request, Response $response): void
    {
        $token = trim((string) $request->routeParam('token', ''));

        if ($token === '') {
            $response->redirectTo('/');
        }

        $db = Database::getInstance();
        $pac = $db->fetchOne(
            "SELECT id, activo FROM pacientes
             WHERE portal_token = ? AND portal_token_expira > NOW() LIMIT 1",
            [$token]
        );

        if ($pac === null || (int) $pac['activo'] !== 1) {
            $content = View::render('Pages/portal/error', [
                'mensaje' => 'El enlace de acceso no es valido o ya expiro. Solicita uno nuevo en tu clinica.',
            ]);
            $html = View::render('Layouts.main', [
                'pageTitle' => 'Acceso no valido',
                'content' => $content,
            ]);
            $response->html($html);
            return;
        }

        Session::set('portal_paciente_id', (int) $pac['id']);
        $response->redirectTo('/portal/inicio');
    }

    public function salir(Request $request, Response $response): void
    {
        Session::set('portal_paciente_id', null);
        $response->redirectTo('/');
    }

    // ========================================================================
    // INICIO
    // ========================================================================

    public function inicio(Request $request, Response $response): void
    {
        $paciente = $this->portalPaciente();

        if ($paciente === null) {
            $response->redirectTo('/');
        }

        $pid = (int) $paciente['id'];
        $proximas = $this->citas->proximasDePaciente($pid, 5);
        $docs = $this->documentos->findByPaciente($pid, (int) $paciente['organizacion_id'], 5, 0);

        $this->renderPortal('Pages/portal/inicio', [
            'proximas' => $proximas,
            'documentos' => $docs,
            'diagnosticos' => $this->expediente->diagnosticosActivos($pid),
            'tratamientos' => $this->expediente->tratamientosActivos($pid),
        ], 'Mi Portal', $response);
    }

    // ========================================================================
    // CITAS
    // ========================================================================

    public function citas(Request $request, Response $response): void
    {
        $paciente = $this->portalPaciente();

        if ($paciente === null) {
            $response->redirectTo('/');
        }

        $pid = (int) $paciente['id'];

        $this->renderPortal('Pages/portal/citas', [
            'proximas' => $this->citas->proximasDePaciente($pid, 20),
            'historial' => $this->citas->historialDePaciente($pid, 50),
        ], 'Mis Citas', $response);
    }

    // ========================================================================
    // EXPEDIENTE (sin notas privadas)
    // ========================================================================

    public function expediente(Request $request, Response $response): void
    {
        $paciente = $this->portalPaciente();

        if ($paciente === null) {
            $response->redirectTo('/');
        }

        $pid = (int) $paciente['id'];
        $timeline = $this->expediente->timeline($pid);

        // Enriquecer cada consulta con dx, tx y vitales (sin notas privadas)
        $detalle = [];
        foreach ($timeline as $c) {
            $consulta = $this->consultas->findById((int) $c['id']);

            if ($consulta === null) {
                continue;
            }

            $detalle[] = [
                'consulta' => $consulta,
                'vitales' => Consulta::vitalesArray($consulta['signos_vitales'] ?? null),
                'diagnosticos' => $this->consultas->diagnosticos((int) $consulta['id']),
                'tratamientos' => $this->consultas->tratamientos((int) $consulta['id']),
            ];
        }

        $this->renderPortal('Pages/portal/expediente', [
            'detalle' => $detalle,
            'expediente' => $this->expediente->findByPaciente($pid),
        ], 'Mi Expediente', $response);
    }

    // ========================================================================
    // DOCUMENTOS
    // ========================================================================

    public function reportesClinicos(Request $request, Response $response): void
    {
        $paciente = $this->portalPaciente();

        if ($paciente === null) {
            $response->redirectTo('/portal/salir');
            return;
        }

        $modelo = new ReportePaciente();
        $reportes = $modelo->listarCompartidosPortal((int) $paciente['id']);

        $content = View::render('Pages/portal/reportes', [
            'paciente' => $paciente,
            'reportes' => $reportes,
        ]);

        $html = View::render('Layouts.portal', [
            'pageTitle' => 'Mis Reportes',
            'content' => $content,
            'paciente' => $paciente,
        ]);

        $response->html($html);
    }

    public function verReporteClinico(Request $request, Response $response): void
    {
        $paciente = $this->portalPaciente();

        if ($paciente === null) {
            $response->redirectTo('/portal/salir');
            return;
        }

        $id = (int) $request->routeParam('id', 0);

        $modelo = new ReportePaciente();
        $reporte = $modelo->buscarCompartidoPortal($id, (int) $paciente['id']);

        if ($reporte === null) {
            $response->html('<h1>Acceso denegado</h1>', 403);
            return;
        }

        $estructura = !empty($reporte['estructura_json']) ? (json_decode($reporte['estructura_json'], true) ?: []) : [];
        $contenido = !empty($reporte['contenido_json']) ? (json_decode($reporte['contenido_json'], true) ?: []) : [];

        $content = View::render('Pages/portal/reporte_ver', [
            'paciente' => $paciente,
            'reporte' => $reporte,
            'estructura' => $estructura,
            'contenido' => $contenido,
        ]);

        $html = View::render('Layouts.portal', [
            'pageTitle' => $reporte['titulo'],
            'content' => $content,
            'paciente' => $paciente,
        ]);

        $response->html($html);
    }

    public function documentos(Request $request, Response $response): void
    {
        $paciente = $this->portalPaciente();

        if ($paciente === null) {
            $response->redirectTo('/');
        }

        $pid = (int) $paciente['id'];

        $this->renderPortal('Pages/portal/documentos', [
            'documentos' => $this->documentos->findByPaciente($pid, (int) $paciente['organizacion_id'], 100, 0),
        ], 'Mis Documentos', $response);
    }

    public function verDocumento(Request $request, Response $response): void
    {
        $paciente = $this->portalPaciente();

        if ($paciente === null) {
            $response->redirectTo('/');
        }

        $id = (int) $request->routeParam('id', 0);
        $doc = $this->documentos->findById($id);

        // Alcance estricto: solo documentos del paciente de la sesion
        if ($doc === null || (int) $doc['eliminado'] === 1 || (int) $doc['paciente_id'] !== (int) $paciente['id']) {
            $response->html('<h1>Acceso denegado</h1>', 403);
            return;
        }

        $rutaCompleta = dirname(__DIR__, 2) . '/public' . $doc['ruta_archivo'];

        if (!file_exists($rutaCompleta)) {
            $response->html('<h1>Archivo no encontrado</h1>', 404);
            return;
        }

        header('Content-Type: ' . ($doc['tipo_mime'] ?? 'application/octet-stream'));
        header('Content-Disposition: inline; filename="' . ($doc['nombre_archivo'] ?? 'documento') . '"');
        header('Content-Length: ' . (int) ($doc['tamano_archivo'] ?? filesize($rutaCompleta)));
        header('Cache-Control: private, max-age=0, must-revalidate');

        readfile($rutaCompleta);
        exit;
    }

    // ========================================================================
    // GENERAR ACCESO (lado del panel)
    // ========================================================================

    public function generarAcceso(Request $request, Response $response): void
    {
        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/pacientes');
        }

        $id = (int) $request->routeParam('id', 0);
        $paciente = $this->pacientes->findById($id);

        if ($paciente === null) {
            Session::flashError('Paciente no encontrado.');
            $response->redirectTo('/panel/pacientes');
        }

        // Alcance por rol
        $user = Session::user() ?? [];
        $rol = $user['rol_slug'] ?? '';

        if ($rol !== 'superadmin') {
            $userModel = new User();
            $full = $userModel->findById(Session::getUserId());
            $orgId = (int) ($full['organizacion_id'] ?? 0);

            if ((int) $paciente['organizacion_id'] !== $orgId) {
                Session::flashError('Paciente no encontrado.');
                $response->redirectTo('/panel/pacientes');
            }

            if ($rol === 'professional') {
                $db = Database::getInstance();
                $prof = $db->fetchOne("SELECT id FROM profesionales WHERE usuario_id = ? LIMIT 1", [Session::getUserId()]);
                $profId = $prof !== null ? (int) $prof['id'] : 0;

                if ($profId === 0 || !$this->pacientes->esPacienteDe($id, $profId)) {
                    Session::flashError('Sin permiso sobre este paciente.');
                    $response->redirectTo('/panel/pacientes');
                }
            }
        }

        $token = bin2hex(random_bytes(24));
        $expira = date('Y-m-d H:i:s', strtotime('+30 days'));

        $this->pacientes->setPortalToken($id, $token, $expira);

        $enlace = url('/portal/acceso/' . $token);

        Session::flashSuccess('Enlace del portal generado. Esta visible en el expediente para copiarlo.');
        Session::set('portal_link_' . $id, $enlace);
        $response->redirectTo('/panel/pacientes/' . $id . '/expediente');
    }
}
