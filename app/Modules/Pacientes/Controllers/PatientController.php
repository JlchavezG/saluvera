<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class PatientController
{
    private Paciente $model;

    public function __construct()
    {
        $this->model = new Paciente();
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

    private function orgIdDestino(?int $orgIdDesdeFormulario): int
    {
        if ($this->esSuperAdmin() && $orgIdDesdeFormulario !== null && $orgIdDesdeFormulario > 0) {
            $org = (new Organizacion())->findById($orgIdDesdeFormulario);
            if ($org !== null && (int) $org['activo'] === 1) {
                return $orgIdDesdeFormulario;
            }
        }

        return $this->orgId();
    }

    private function buscarEnOrg(int $id): ?array
    {
        $paciente = $this->model->findById($id);

        if ($paciente === null) {
            return null;
        }

        // Superadmin puede acceder a cualquier paciente
        if ($this->esSuperAdmin()) {
            return $paciente;
        }

        // Capa 1: organizacion
        if ((int) $paciente['organizacion_id'] !== $this->orgId()) {
            return null;
        }

        // Capa 2: profesional solo ve los suyos
        $profId = $this->miProfesionalId();
        if ($profId !== null && !$this->model->esPacienteDe($id, $profId)) {
            return null;
        }

        return $paciente;
    }

    // ========================================================================
    // READ: LISTADO CON ALCANCE
    // ========================================================================

    public function index(Request $request, Response $response): void
    {
        $q = trim((string) $request->input('q', ''));
        $orgFiltro = $this->esSuperAdmin() ? (int) $request->input('org_id', 0) : 0;
        $page = max(1, $request->int('page', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $profId = $this->miProfesionalId();

        if ($this->esSuperAdmin()) {
            $org = $orgFiltro > 0 ? $orgFiltro : 0;
            $pacientes = $this->model->searchGlobal($q, $org, $perPage, $offset);
            $total = $this->model->countSearchGlobal($q, $org);
        } elseif ($profId !== null) {
            $pacientes = $this->model->search($this->orgId(), $q, $perPage, $offset, $profId);
            $total = $this->model->countSearch($this->orgId(), $q, $profId);
        } else {
            $pacientes = $this->model->search($this->orgId(), $q, $perPage, $offset);
            $total = $this->model->countSearch($this->orgId(), $q);
        }

        $totalPages = max(1, (int) ceil($total / $perPage));

        $content = View::render('Pages/pacientes/list', [
            'pacientes' => $pacientes,
            'organizaciones' => $this->esSuperAdmin() ? $this->model->getOrganizaciones() : [],
            'orgFiltro' => $orgFiltro,
            'q' => $q,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'soloMios' => $profId !== null,
            'esSuperAdmin' => $this->esSuperAdmin(),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => $profId !== null ? 'Mis Pacientes' : 'Pacientes',
            'content' => $content,
        ]);

        $response->html($html);
    }

    // ========================================================================
    // CREATE
    // ========================================================================

    public function create(Request $request, Response $response): void
    {
        $content = View::render('Pages/pacientes/form', [
            'paciente' => null,
            'organizaciones' => $this->esSuperAdmin() ? $this->model->getOrganizaciones() : [],
            'orgDestino' => $this->orgId(),
            'esSuperAdmin' => $this->esSuperAdmin(),
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Nuevo Paciente',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function store(Request $request, Response $response): void
    {
        if (!$request->isPost()) {
            $response->redirectTo('/panel/pacientes');
        }

        if (!Security::verifyCsrfToken()) {
            Session::flashError('Sesion expirada. Intenta de nuevo.');
            $response->redirectTo('/panel/pacientes/nuevo');
        }

        $data = $this->datosDelFormulario($request);
        $orgIdDesdeForm = $this->esSuperAdmin() ? (int) $request->input('organizacion_id', 0) : 0;

        $validator = Validator::make($data, $this->reglas());

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', array_merge($data, ['organizacion_id' => $orgIdDesdeForm]));
            $response->redirectTo('/panel/pacientes/nuevo');
        }

        $orgDestino = $this->orgIdDestino($orgIdDesdeForm > 0 ? $orgIdDesdeForm : null);
        $data['organizacion_id'] = $orgDestino;

        // Si un profesional crea un paciente, queda asignado a el
        $profId = $this->miProfesionalId();
        if ($profId !== null) {
            $data['profesional_id'] = $profId;
        }

        $this->model->create($data);

        Session::flashSuccess('Paciente "' . $data['nombre'] . ' ' . $data['apellidos'] . '" creado correctamente.');
        $response->redirectTo('/panel/pacientes');
    }

    // ========================================================================
    // UPDATE
    // ========================================================================

    public function edit(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $paciente = $this->buscarEnOrg($id);

        if ($paciente === null) {
            Session::flashError('Paciente no encontrado o sin permiso para accederlo.');
            $response->redirectTo('/panel/pacientes');
        }

        $content = View::render('Pages/pacientes/form', [
            'paciente' => $paciente,
            'organizaciones' => $this->esSuperAdmin() ? $this->model->getOrganizaciones() : [],
            'orgDestino' => (int) $paciente['organizacion_id'],
            'esSuperAdmin' => $this->esSuperAdmin(),
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Editar Paciente',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost()) {
            $response->redirectTo('/panel/pacientes');
        }

        if (!Security::verifyCsrfToken()) {
            Session::flashError('Sesion expirada. Intenta de nuevo.');
            $response->redirectTo('/panel/pacientes');
        }

        $paciente = $this->buscarEnOrg($id);

        if ($paciente === null) {
            Session::flashError('Paciente no encontrado o sin permiso para accederlo.');
            $response->redirectTo('/panel/pacientes');
        }

        $data = $this->datosDelFormulario($request);
        $orgIdDesdeForm = $this->esSuperAdmin() ? (int) $request->input('organizacion_id', 0) : 0;

        $validator = Validator::make($data, $this->reglas());

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', array_merge($data, ['organizacion_id' => $orgIdDesdeForm]));
            $response->redirectTo('/panel/pacientes/' . $id . '/editar');
        }

        unset($data['correo']);

        // Superadmin puede mover de organizacion
        if ($this->esSuperAdmin() && $orgIdDesdeForm > 0) {
            $org = (new Organizacion())->findById($orgIdDesdeForm);
            if ($org !== null && (int) $org['activo'] === 1 && (int) $org['id'] !== (int) $paciente['organizacion_id']) {
                $data['organizacion_id'] = (int) $org['id'];
            }
        }

        $this->model->update($id, $data);

        Session::flashSuccess('Paciente "' . $data['nombre'] . ' ' . $data['apellidos'] . '" actualizado correctamente.');
        $response->redirectTo('/panel/pacientes');
    }

    // ========================================================================
    // DELETE / RESTORE
    // ========================================================================

    public function delete(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/pacientes');
        }

        $paciente = $this->buscarEnOrg($id);

        if ($paciente === null) {
            Session::flashError('Paciente no encontrado o sin permiso.');
            $response->redirectTo('/panel/pacientes');
        }

        $this->model->setActive($id, 0);

        Session::flashSuccess('Paciente dado de baja.');
        $response->redirectTo('/panel/pacientes');
    }

    public function restore(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/pacientes');
        }

        $paciente = $this->buscarEnOrg($id);

        if ($paciente === null) {
            Session::flashError('Paciente no encontrado o sin permiso.');
            $response->redirectTo('/panel/pacientes');
        }

        $this->model->setActive($id, 1);

        Session::flashSuccess('Paciente reactivado correctamente.');
        $response->redirectTo('/panel/pacientes');
    }

    // ========================================================================
    // HELPERS
    // ========================================================================

    private function datosDelFormulario(Request $request): array
    {
        $nulo = function ($v) {
            $v = trim((string) $v);
            return ($v === '') ? null : $v;
        };

        return [
            'nombre' => trim((string) $request->input('nombre', '')),
            'apellidos' => trim((string) $request->input('apellidos', '')),
            'correo' => $nulo($request->input('correo', '')),
            'telefono' => $nulo($request->input('telefono', '')),
            'fecha_nacimiento' => $nulo($request->input('fecha_nacimiento', '')),
            'genero' => $nulo($request->input('genero', '')),
            'tipo_sangre' => $nulo($request->input('tipo_sangre', '')),
            'ciudad' => $nulo($request->input('ciudad', '')),
            'ocupacion' => $nulo($request->input('ocupacion', '')),
            'notas' => $nulo($request->input('notas', '')),
        ];
    }

    private function reglas(): array
    {
        return [
            'nombre' => 'required|min:2|max:100',
            'apellidos' => 'required|min:2|max:100',
            'correo' => 'email',
            'telefono' => 'phone',
            'fecha_nacimiento' => 'date',
            'genero' => 'in:masculino,femenino,otro,prefiero_no_decir',
            'tipo_sangre' => 'in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        ];
    }
}
