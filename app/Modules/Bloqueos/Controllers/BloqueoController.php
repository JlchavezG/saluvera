<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class BloqueoController
{
    private BloqueoAgenda $model;

    public function __construct()
    {
        $this->model = new BloqueoAgenda();
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

    private function bloqueoEnAlcance(int $id): ?array
    {
        $bloqueo = $this->model->findById($id);

        if ($bloqueo === null) {
            return null;
        }

        if ($this->esSuperAdmin()) {
            return $bloqueo;
        }

        if ((int) $bloqueo['organizacion_id'] !== $this->orgId()) {
            return null;
        }

        if ($this->rol() === 'professional') {
            $profId = $this->miProfesionalId();
            if ($profId === null || (int) $bloqueo['profesional_id'] !== $profId) {
                return null;
            }
        }

        return $bloqueo;
    }

    // ========================================================================
    // LISTADO
    // ========================================================================

    public function index(Request $request, Response $response): void
    {
        $rol = $this->rol();

        if ($rol === 'professional') {
            $profId = $this->miProfesionalId() ?? 0;
            $bloqueos = $this->model->searchByProf($profId);
        } elseif ($this->esSuperAdmin()) {
            $bloqueos = $this->model->searchGlobal();
        } else {
            $bloqueos = $this->model->searchByOrg($this->orgId());
        }

        $content = View::render('Pages/bloqueos/list', [
            'bloqueos' => $bloqueos,
            'rol' => $rol,
            'esSuperAdmin' => $this->esSuperAdmin(),
            'esProfessional' => $rol === 'professional',
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => $rol === 'professional' ? 'Mis Bloqueos de Agenda' : 'Bloqueos de Agenda',
            'content' => $content,
        ]);

        $response->html($html);
    }

    // ========================================================================
    // CREAR
    // ========================================================================

    public function create(Request $request, Response $response): void
    {
        $rol = $this->rol();

        $profesionales = [];
        if ($rol !== 'professional') {
            $orgId = $this->esSuperAdmin()
                ? (int) $request->input('org_id', 0)
                : $this->orgId();
            if ($orgId > 0) {
                $profesionales = $this->model->profesionalesDeOrg($orgId);
            } elseif ($this->esSuperAdmin()) {
                // Superadmin sin org elegida: lista todas con nombre de org
                $db = Database::getInstance();
                $profesionales = $db->fetchAll(
                    "SELECT p.id, u.nombre, u.apellidos, o.nombre as org_nombre
                     FROM profesionales p
                     INNER JOIN usuarios u ON p.usuario_id = u.id
                     INNER JOIN organizaciones o ON p.organizacion_id = o.id
                     WHERE p.activo = 1 ORDER BY o.nombre, u.nombre"
                );
            }
        }

        $content = View::render('Pages/bloqueos/form', [
            'bloqueo' => null,
            'profesionales' => $profesionales,
            'esProfessional' => $rol === 'professional',
            'esSuperAdmin' => $this->esSuperAdmin(),
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Bloquear Dias',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function store(Request $request, Response $response): void
    {
        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/bloqueos');
        }

        $data = [
            'profesional_id' => (int) $request->input('profesional_id', 0),
            'fecha_inicio' => trim((string) $request->input('fecha_inicio', '')),
            'fecha_fin' => trim((string) $request->input('fecha_fin', '')),
            'motivo' => trim((string) $request->input('motivo', '')),
        ];

        $validator = Validator::make($data, [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'motivo' => 'max:255',
        ]);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/bloqueos/nuevo');
        }

        if ($data['fecha_fin'] < $data['fecha_inicio']) {
            Session::flash('form_errors', ['fecha_fin' => ['La fecha fin no puede ser anterior a la fecha inicio']]);
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/bloqueos/nuevo');
        }

        // Determinar profesional y organizacion segun rol
        if ($this->rol() === 'professional') {
            $profId = $this->miProfesionalId() ?? 0;
            if ($profId === 0) {
                Session::flashError('No tienes un perfil de profesional asignado.');
                $response->redirectTo('/panel/bloqueos');
            }
            $data['profesional_id'] = $profId;
            $orgId = $this->orgId();
        } else {
            if ($data['profesional_id'] <= 0) {
                Session::flash('form_errors', ['profesional_id' => ['Selecciona el profesional']]);
                Session::flash('form_old', $data);
                $response->redirectTo('/panel/bloqueos/nuevo');
            }

            $profModel = new Profesional();
            $prof = $profModel->findById($data['profesional_id']);

            if ($prof === null) {
                Session::flash('form_errors', ['profesional_id' => ['Profesional no valido']]);
                Session::flash('form_old', $data);
                $response->redirectTo('/panel/bloqueos/nuevo');
            }

            if (!$this->esSuperAdmin() && (int) $prof['organizacion_id'] !== $this->orgId()) {
                Session::flashError('Profesional fuera de tu organizacion.');
                $response->redirectTo('/panel/bloqueos/nuevo');
            }

            $orgId = (int) $prof['organizacion_id'];
        }

        $this->model->create([
            'organizacion_id' => $orgId,
            'profesional_id' => $data['profesional_id'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'motivo' => $data['motivo'] !== '' ? $data['motivo'] : null,
        ]);

        Session::flashSuccess('Dias bloqueados correctamente. Nadie podra agendar citas en ese periodo.');
        $response->redirectTo('/panel/bloqueos');
    }

    // ========================================================================
    // EDITAR / ACTUALIZAR
    // ========================================================================

    public function edit(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $bloqueo = $this->bloqueoEnAlcance($id);

        if ($bloqueo === null) {
            Session::flashError('Bloqueo no encontrado o sin permiso.');
            $response->redirectTo('/panel/bloqueos');
        }

        $profesionales = [];
        if ($this->rol() !== 'professional') {
            $profesionales = $this->model->profesionalesDeOrg((int) $bloqueo['organizacion_id']);
        }

        $content = View::render('Pages/bloqueos/form', [
            'bloqueo' => $bloqueo,
            'profesionales' => $profesionales,
            'esProfessional' => $this->rol() === 'professional',
            'esSuperAdmin' => $this->esSuperAdmin(),
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Editar Bloqueo',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/bloqueos');
        }

        $bloqueo = $this->bloqueoEnAlcance($id);

        if ($bloqueo === null) {
            Session::flashError('Bloqueo no encontrado o sin permiso.');
            $response->redirectTo('/panel/bloqueos');
        }

        $data = [
            'fecha_inicio' => trim((string) $request->input('fecha_inicio', '')),
            'fecha_fin' => trim((string) $request->input('fecha_fin', '')),
            'motivo' => trim((string) $request->input('motivo', '')),
        ];

        $validator = Validator::make($data, [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'motivo' => 'max:255',
        ]);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/bloqueos/' . $id . '/editar');
        }

        if ($data['fecha_fin'] < $data['fecha_inicio']) {
            Session::flash('form_errors', ['fecha_fin' => ['La fecha fin no puede ser anterior a la fecha inicio']]);
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/bloqueos/' . $id . '/editar');
        }

        $this->model->update($id, [
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'motivo' => $data['motivo'] !== '' ? $data['motivo'] : null,
        ]);

        Session::flashSuccess('Bloqueo actualizado correctamente.');
        $response->redirectTo('/panel/bloqueos');
    }

    // ========================================================================
    // ELIMINAR
    // ========================================================================

    public function delete(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/bloqueos');
        }

        $bloqueo = $this->bloqueoEnAlcance($id);

        if ($bloqueo === null) {
            Session::flashError('Bloqueo no encontrado o sin permiso.');
            $response->redirectTo('/panel/bloqueos');
        }

        $this->model->delete($id);

        Session::flashSuccess('Bloqueo eliminado. El periodo vuelve a estar disponible para citas.');
        $response->redirectTo('/panel/bloqueos');
    }
}
