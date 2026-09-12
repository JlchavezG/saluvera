<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class ExpedienteController
{
    private Expediente $model;
    private Paciente $pacientes;
    private Documento $documentos;

    public function __construct()
    {
        $this->model = new Expediente();
        $this->pacientes = new Paciente();
        $this->documentos = new Documento();
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

        if ($this->rol() === 'superadmin') {
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

    // ========================================================================
    // VER EXPEDIENTE (antecedentes + timeline + activos)
    // ========================================================================

    public function show(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $paciente = $this->pacienteEnAlcance($id);

        if ($paciente === null) {
            Session::flashError('Paciente no encontrado o sin permiso.');
            $response->redirectTo('/panel/pacientes');
        }

        $profFiltro = $this->rol() === 'professional' ? $this->miProfesionalId() : null;

        $content = View::render('Pages/expediente/show', [
            'paciente' => $paciente,
            'expediente' => $this->model->findByPaciente($id),
            'timeline' => $this->model->timeline($id, $profFiltro),
            'diagnosticos' => $this->model->diagnosticosActivos($id),
            'tratamientos' => $this->model->tratamientosActivos($id),
            'rol' => $this->rol(),
            'documentos' => $this->documentos->findByPaciente($id, (int) $paciente['organizacion_id']),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Expediente Clinico',
            'content' => $content,
        ]);

        $response->html($html);
    }

    // ========================================================================
    // EDITAR ANTECEDENTES
    // ========================================================================

    public function edit(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $paciente = $this->pacienteEnAlcance($id);

        if ($paciente === null) {
            Session::flashError('Paciente no encontrado o sin permiso.');
            $response->redirectTo('/panel/pacientes');
        }

        $content = View::render('Pages/expediente/form', [
            'paciente' => $paciente,
            'expediente' => $this->model->findByPaciente($id),
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Antecedentes del Paciente',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/pacientes');
        }

        $paciente = $this->pacienteEnAlcance($id);

        if ($paciente === null) {
            Session::flashError('Paciente no encontrado o sin permiso.');
            $response->redirectTo('/panel/pacientes');
        }

        $nulo = function ($v) {
            $v = trim((string) $v);
            return ($v === '') ? null : $v;
        };

        $data = [
            'alergias' => $nulo($request->input('alergias', '')),
            'enfermedades_cronicas' => $nulo($request->input('enfermedades_cronicas', '')),
            'cirugias' => $nulo($request->input('cirugias', '')),
            'antecedentes_familiares' => $nulo($request->input('antecedentes_familiares', '')),
            'habitos' => $nulo($request->input('habitos', '')),
            'medicamentos_actuales' => $nulo($request->input('medicamentos_actuales', '')),
            'notas' => $nulo($request->input('notas', '')),
        ];

        $exp = $this->model->asegurar($id, $this->orgId(), Session::getUserId());

        $this->model->update((int) $exp['id'], $data, Session::getUserId());

        Session::flashSuccess('Antecedentes del paciente actualizados.');
        $response->redirectTo('/panel/pacientes/' . $id . '/expediente');
    }
}
