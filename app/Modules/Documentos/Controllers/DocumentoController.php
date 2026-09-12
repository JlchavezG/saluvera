<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class DocumentoController
{
    private Documento $model;
    private Paciente $pacientes;

    public function __construct()
    {
        $this->model = new Documento();
        $this->pacientes = new Paciente();
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
        $prof = $db->fetchOne(
            "SELECT id FROM profesionales WHERE usuario_id = ? LIMIT 1",
            [Session::getUserId()]
        );
        return $prof !== null ? (int) $prof['id'] : null;
    }

    private function pacienteEnAlcance(int $id): ?array
    {
        $paciente = $this->pacientes->findById($id);

        if ($paciente === null) {
            return null;
        }

        if ($this->esSuperAdmin()) {
            return $paciente;
        }

        if ((int) $paciente['organizacion_id'] !== $this->orgId()) {
            return null;
        }

        if ($this->rol() === 'professional') {
            $profId = $this->miProfesionalId();
            if ($profId === null || !$this->pacientes->esPacienteDe($id, $profId)) {
                return null;
            }
        }

        return $paciente;
    }

    private function documentoEnAlcance(int $id): ?array
    {
        $doc = $this->model->findById($id);

        if ($doc === null || (int) $doc['eliminado'] === 1) {
            return null;
        }

        if ($this->esSuperAdmin()) {
            return $doc;
        }

        if ((int) $doc['organizacion_id'] !== $this->orgId()) {
            return null;
        }

        if ($this->rol() === 'professional') {
            $profId = $this->miProfesionalId();
            if ($profId === null || !$this->pacientes->esPacienteDe((int) $doc['paciente_id'], $profId)) {
                return null;
            }
        }

        return $doc;
    }

    // ========================================================================
    // SUBIR DOCUMENTO
    // ========================================================================

    public function create(Request $request, Response $response): void
    {
        $pacienteId = (int) $request->routeParam('id', 0);
        $paciente = $this->pacienteEnAlcance($pacienteId);

        if ($paciente === null) {
            Session::flashError('Paciente no encontrado o sin permiso.');
            $response->redirectTo('/panel/pacientes');
        }

        $content = View::render('Pages/documentos/form', [
            'paciente' => $paciente,
            'consultas' => $this->model->consultasDelPaciente($pacienteId, (int) $paciente['organizacion_id']),
            'tipos' => Documento::tipos(),
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Subir Documento',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function store(Request $request, Response $response): void
    {
        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/pacientes');
        }

        $pacienteId = (int) $request->input('paciente_id', 0);
        $paciente = $this->pacienteEnAlcance($pacienteId);

        if ($paciente === null) {
            Session::flashError('Paciente no encontrado o sin permiso.');
            $response->redirectTo('/panel/pacientes');
        }

        $titulo = trim((string) $request->input('titulo', ''));
        $tipo = trim((string) $request->input('tipo_documento', ''));
        $descripcion = trim((string) $request->input('descripcion', ''));
        $consultaId = (int) $request->input('consulta_id', 0);
        $old = [
            'paciente_id' => $pacienteId,
            'titulo' => $titulo,
            'tipo_documento' => $tipo,
            'descripcion' => $descripcion,
            'consulta_id' => $consultaId,
        ];

        // Validacion basica
        if ($titulo === '') {
            Session::flash('form_errors', ['titulo' => ['El titulo es obligatorio']]);
            Session::flash('form_old', $old);
            $response->redirectTo('/panel/pacientes/' . $pacienteId . '/documentos/nuevo');
        }

        if (!array_key_exists($tipo, Documento::tipos())) {
            Session::flash('form_errors', ['tipo_documento' => ['Tipo no valido']]);
            Session::flash('form_old', $old);
            $response->redirectTo('/panel/pacientes/' . $pacienteId . '/documentos/nuevo');
        }

        if (!isset($_FILES['archivo'])) {
            Session::flash('form_errors', ['archivo' => ['Debes seleccionar un archivo']]);
            Session::flash('form_old', $old);
            $response->redirectTo('/panel/pacientes/' . $pacienteId . '/documentos/nuevo');
        }

        $errorValidacion = Documento::puedeSubir($_FILES['archivo']);
        if ($errorValidacion !== null) {
            Session::flash('form_errors', ['archivo' => [$errorValidacion]]);
            Session::flash('form_old', $old);
            $response->redirectTo('/panel/pacientes/' . $pacienteId . '/documentos/nuevo');
        }

        $ruta = $this->model->guardarArchivo($_FILES['archivo'], (int) $paciente['organizacion_id'], $pacienteId);

        if ($ruta === null) {
            Session::flashError('No se pudo guardar el archivo en el servidor.');
            Session::flash('form_old', $old);
            $response->redirectTo('/panel/pacientes/' . $pacienteId . '/documentos/nuevo');
        }

        $this->model->crear([
            'organizacion_id' => (int) $paciente['organizacion_id'],
            'paciente_id' => $pacienteId,
            'consulta_id' => $consultaId > 0 ? $consultaId : null,
            'tipo_documento' => $tipo,
            'titulo' => $titulo,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'nombre_archivo' => $_FILES['archivo']['name'],
            'ruta_archivo' => $ruta,
            'tamano_archivo' => (int) $_FILES['archivo']['size'],
            'tipo_mime' => $_FILES['archivo']['type'],
            'subido_por' => Session::getUserId(),
        ]);

        // Notificacion automatica al paciente (WhatsApp + portal)
        $notModel = new Notificacion();
        $notModel->crear([
            'organizacion_id' => (int) $paciente['organizacion_id'],
            'tipo_destinatario' => 'paciente',
            'destinatario_id' => $pacienteId,
            'tipo_notificacion' => 'nuevo_documento',
            'canal' => 'whatsapp',
            'titulo' => 'Nuevo documento en tu expediente',
            'cuerpo' => 'Se ha subido un documento a tu expediente: ' . $titulo . '. Puedes verlo en tu portal de paciente.',
        ]);

        Session::flashSuccess('Documento "' . $titulo . '" subido correctamente.');
        $response->redirectTo('/panel/pacientes/' . $pacienteId . '/expediente');
    }

    // ========================================================================
    // DESCARGA / VISTA
    // ========================================================================

    public function descargar(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $doc = $this->documentoEnAlcance($id);

        if ($doc === null) {
            $response->html('<h1>Acceso denegado</h1>', 403);
            return;
        }

        $rutaCompleta = dirname(__DIR__, 2) . '/public' . $doc['ruta_archivo'];

        if (!file_exists($rutaCompleta)) {
            $response->html('<h1>Archivo no encontrado</h1>', 404);
            return;
        }

        $mime = $doc['tipo_mime'] ?? 'application/octet-stream';
        $nombre = $doc['nombre_archivo'] ?? basename($rutaCompleta);
        $tamano = (int) ($doc['tamano_archivo'] ?? filesize($rutaCompleta));

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $nombre . '"');
        header('Content-Length: ' . $tamano);
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        readfile($rutaCompleta);
        exit;
    }

    // ========================================================================
    // ELIMINAR (logico)
    // ========================================================================

    public function delete(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/pacientes');
        }

        $doc = $this->documentoEnAlcance($id);

        if ($doc === null) {
            Session::flashError('Documento no encontrado o sin permiso.');
            $response->redirectTo('/panel/pacientes');
        }

        $this->model->eliminarLogico($id, Session::getUserId());

        Session::flashSuccess('Documento eliminado del expediente.');
        $response->redirectTo('/panel/pacientes/' . (int) $doc['paciente_id'] . '/expediente');
    }
}
