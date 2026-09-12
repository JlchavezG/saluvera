<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class ProfessionalController
{
    private Profesional $model;
    private User $userModel;

    public function __construct()
    {
        $this->model = new Profesional();
        $this->userModel = new User();
    }

    private function miOrgId(): int
    {
        $user = $this->userModel->findById(Session::getUserId());
        return (int) ($user['organizacion_id'] ?? 1);
    }

    private function esSuperAdmin(): bool
    {
        $user = Session::user() ?? [];
        return ($user['rol_slug'] ?? '') === 'superadmin';
    }

    private function orgIdDestino(?int $orgIdDesdeFormulario): int
    {
        if ($this->esSuperAdmin() && $orgIdDesdeFormulario !== null && $orgIdDesdeFormulario > 0) {
            // Validar que la org exista y este activa
            $org = (new Organizacion())->findById($orgIdDesdeFormulario);
            if ($org !== null && (int) $org['activo'] === 1) {
                return $orgIdDesdeFormulario;
            }
        }

        return $this->miOrgId();
    }

    private function rolProfessionalId(): int
    {
        $db = Database::getInstance();
        $rol = $db->fetchOne("SELECT id FROM roles WHERE slug = 'professional' LIMIT 1");
        return (int) ($rol['id'] ?? 0);
    }

    private function buscarEnOrg(int $id): ?array
    {
        $profesional = $this->model->findById($id);

        if ($profesional === null) {
            return null;
        }

        // Superadmin puede ver cualquier organizacion
        if (!$this->esSuperAdmin()) {
            if ((int) $profesional['organizacion_id'] !== $this->miOrgId()) {
                return null;
            }
        }

        return $profesional;
    }

    // ========================================================================
    // READ: LISTADO
    // ========================================================================

    public function index(Request $request, Response $response): void
    {
        $q = trim((string) $request->input('q', ''));
        $orgFiltro = $this->esSuperAdmin() ? (int) $request->input('org_id', 0) : 0;
        $page = max(1, $request->int('page', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        if ($this->esSuperAdmin()) {
            $org = $orgFiltro > 0 ? $orgFiltro : 0; // 0 = todas
            $profesionales = $this->model->searchGlobal($q, $org, $perPage, $offset);
            $total = $this->model->countSearchGlobal($q, $org);
        } else {
            $org = $this->miOrgId();
            $profesionales = $this->model->search($org, $q, $perPage, $offset);
            $total = $this->model->countSearch($org, $q);
        }

        $totalPages = max(1, (int) ceil($total / $perPage));

        $content = View::render('Pages/profesionales/list', [
            'profesionales' => $profesionales,
            'organizaciones' => $this->esSuperAdmin() ? $this->model->getOrganizaciones() : [],
            'orgFiltro' => $orgFiltro,
            'q' => $q,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'esSuperAdmin' => $this->esSuperAdmin(),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Profesionales',
            'content' => $content,
        ]);

        $response->html($html);
    }

    // ========================================================================
    // CREATE
    // ========================================================================

    public function create(Request $request, Response $response): void
    {
        $content = View::render('Pages/profesionales/form', [
            'profesional' => null,
            'especialidades' => $this->model->getEspecialidades(),
            'organizaciones' => $this->esSuperAdmin() ? $this->model->getOrganizaciones() : [],
            'orgDestino' => $this->miOrgId(),
            'esSuperAdmin' => $this->esSuperAdmin(),
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Nuevo Profesional',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function store(Request $request, Response $response): void
    {
        if (!$request->isPost()) {
            $response->redirectTo('/panel/profesionales');
        }

        if (!Security::verifyCsrfToken()) {
            Session::flashError('Sesion expirada. Intenta de nuevo.');
            $response->redirectTo('/panel/profesionales/nuevo');
        }

        $data = $this->datosDelFormulario($request);
        $data['password'] = (string) $request->input('password', '');
        $orgIdDesdeForm = $this->esSuperAdmin() ? (int) $request->input('organizacion_id', 0) : 0;

        $validator = Validator::make($data, [
            'nombre' => 'required|min:2|max:100',
            'apellidos' => 'required|min:2|max:100',
            'correo' => 'required|email',
            'password' => 'required|min:8',
            'numero_cedula' => 'max:100',
            'duracion_consulta' => 'required|numeric',
            'costo_consulta' => 'numeric',
        ]);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', array_merge($data, ['organizacion_id' => $orgIdDesdeForm]));
            $response->redirectTo('/panel/profesionales/nuevo');
        }

        if ($this->userModel->existsByEmail($data['correo'])) {
            Session::flash('form_errors', ['correo' => ['Ese correo ya esta registrado en el sistema']]);
            Session::flash('form_old', array_merge($data, ['organizacion_id' => $orgIdDesdeForm]));
            $response->redirectTo('/panel/profesionales/nuevo');
        }

        $rolId = $this->rolProfessionalId();
        if ($rolId === 0) {
            Session::flashError('No existe el rol professional en el sistema.');
            $response->redirectTo('/panel/profesionales/nuevo');
        }

        $orgDestino = $this->orgIdDestino($orgIdDesdeForm > 0 ? $orgIdDesdeForm : null);

        $userId = $this->userModel->create([
            'organizacion_id' => $orgDestino,
            'correo' => $data['correo'],
            'contrasena_hash' => Security::hashPassword($data['password']),
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'],
            'telefono' => $data['telefono'] ?? null,
            'rol_id' => $rolId,
        ]);

        $this->model->create([
            'usuario_id' => $userId,
            'organizacion_id' => $orgDestino,
            'especialidad_id' => $data['especialidad_id'],
            'numero_cedula' => $data['numero_cedula'],
            'duracion_consulta' => (int) $data['duracion_consulta'],
            'costo_consulta' => $data['costo_consulta'],
        ]);

        Session::flashSuccess('Profesional "' . $data['nombre'] . ' ' . $data['apellidos'] . '" registrado.');
        $response->redirectTo('/panel/profesionales');
    }

    // ========================================================================
    // UPDATE
    // ========================================================================

    public function edit(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $profesional = $this->buscarEnOrg($id);

        if ($profesional === null) {
            Session::flashError('Profesional no encontrado.');
            $response->redirectTo('/panel/profesionales');
        }

        $content = View::render('Pages/profesionales/form', [
            'profesional' => $profesional,
            'especialidades' => $this->model->getEspecialidades(),
            'organizaciones' => $this->esSuperAdmin() ? $this->model->getOrganizaciones() : [],
            'orgDestino' => (int) $profesional['organizacion_id'],
            'esSuperAdmin' => $this->esSuperAdmin(),
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Editar Profesional',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost()) {
            $response->redirectTo('/panel/profesionales');
        }

        if (!Security::verifyCsrfToken()) {
            Session::flashError('Sesion expirada. Intenta de nuevo.');
            $response->redirectTo('/panel/profesionales');
        }

        $profesional = $this->buscarEnOrg($id);

        if ($profesional === null) {
            Session::flashError('Profesional no encontrado.');
            $response->redirectTo('/panel/profesionales');
        }

        $data = $this->datosDelFormulario($request);
        $password = (string) $request->input('password', '');

        $reglas = [
            'nombre' => 'required|min:2|max:100',
            'apellidos' => 'required|min:2|max:100',
            'numero_cedula' => 'max:100',
            'duracion_consulta' => 'required|numeric',
            'costo_consulta' => 'numeric',
        ];

        if ($password !== '') {
            $reglas['password'] = 'min:8';
        }

        $validator = Validator::make(array_merge($data, ['password' => $password]), $reglas);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/profesionales/' . $id . '/editar');
        }

        $this->userModel->update((int) $profesional['usuario_id'], [
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'],
            'telefono' => $data['telefono'],
        ]);

        if ($password !== '') {
            $this->userModel->updatePassword(
                (int) $profesional['usuario_id'],
                Security::hashPassword($password)
            );
        }

        // Solo superadmin puede mover de organizacion (y NO movemos al usuario base)
        $updateData = [
            'especialidad_id' => $data['especialidad_id'],
            'numero_cedula' => $data['numero_cedula'],
            'duracion_consulta' => (int) $data['duracion_consulta'],
            'costo_consulta' => $data['costo_consulta'],
        ];

        if ($this->esSuperAdmin()) {
            $nuevaOrg = (int) $request->input('organizacion_id', 0);
            if ($nuevaOrg > 0 && $nuevaOrg !== (int) $profesional['organizacion_id']) {
                $orgObj = (new Organizacion())->findById($nuevaOrg);
                if ($orgObj !== null && (int) $orgObj['activo'] === 1) {
                    $updateData['organizacion_id'] = $nuevaOrg;
                    // Tambien mover al usuario
                    $this->userModel->update((int) $profesional['usuario_id'], ['organizacion_id' => $nuevaOrg]);
                    $db = Database::getInstance();
                    $db->update('usuario_roles', ['organizacion_id' => $nuevaOrg], 'usuario_id = ?', [$profesional['usuario_id']]);
                }
            }
        }

        $this->model->update($id, $updateData);

        Session::flashSuccess('Profesional actualizado correctamente.');
        $response->redirectTo('/panel/profesionales');
    }

    // ========================================================================
    // DELETE / RESTORE
    // ========================================================================

    public function delete(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/profesionales');
        }

        $profesional = $this->buscarEnOrg($id);

        if ($profesional === null) {
            Session::flashError('Profesional no encontrado.');
            $response->redirectTo('/panel/profesionales');
        }

        $this->model->setActive($id, 0);

        Session::flashSuccess('Profesional dado de baja.');
        $response->redirectTo('/panel/profesionales');
    }

    public function restore(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/profesionales');
        }

        $profesional = $this->buscarEnOrg($id);

        if ($profesional === null) {
            Session::flashError('Profesional no encontrado.');
            $response->redirectTo('/panel/profesionales');
        }

        $this->model->setActive($id, 1);

        Session::flashSuccess('Profesional reactivado correctamente.');
        $response->redirectTo('/panel/profesionales');
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
            'telefono' => $nulo($request->input('telefono', '')),
            'correo' => trim((string) $request->input('correo', '')),
            'especialidad_id' => $nulo($request->input('especialidad_id', '')),
            'numero_cedula' => $nulo($request->input('numero_cedula', '')),
            'duracion_consulta' => $nulo($request->input('duracion_consulta', '')),
            'costo_consulta' => $nulo($request->input('costo_consulta', '')),
        ];
    }
}
