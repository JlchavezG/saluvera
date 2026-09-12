<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class ConsultorioController
{
    private Consultorio $model;

    public function __construct()
    {
        $this->model = new Consultorio();
    }

    private function orgId(): int
    {
        $userModel = new User();
        $user = $userModel->findById(Session::getUserId());
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
            $org = (new Organizacion())->findById($orgIdDesdeFormulario);
            if ($org !== null && (int) $org['activo'] === 1) {
                return $orgIdDesdeFormulario;
            }
        }

        return $this->orgId();
    }

    private function buscarEnOrg(int $id): ?array
    {
        $consultorio = $this->model->findById($id);

        if ($consultorio === null) {
            return null;
        }

        if (!$this->esSuperAdmin()) {
            if ((int) $consultorio['organizacion_id'] !== $this->orgId()) {
                return null;
            }
        }

        return $consultorio;
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
            $org = $orgFiltro > 0 ? $orgFiltro : 0;
            $consultorios = $this->model->searchGlobal($q, $org, $perPage, $offset);
            $total = $this->model->countSearchGlobal($q, $org);
        } else {
            $org = $this->orgId();
            $consultorios = $this->model->search($org, $q, $perPage, $offset);
            $total = $this->model->countSearch($org, $q);
        }

        $totalPages = max(1, (int) ceil($total / $perPage));

        $content = View::render('Pages/consultorios/list', [
            'consultorios' => $consultorios,
            'organizaciones' => $this->esSuperAdmin() ? $this->model->getOrganizaciones() : [],
            'orgFiltro' => $orgFiltro,
            'q' => $q,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'esSuperAdmin' => $this->esSuperAdmin(),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Consultorios',
            'content' => $content,
        ]);

        $response->html($html);
    }

    // ========================================================================
    // CREATE
    // ========================================================================

    public function create(Request $request, Response $response): void
    {
        $content = View::render('Pages/consultorios/form', [
            'consultorio' => null,
            'organizaciones' => $this->esSuperAdmin() ? $this->model->getOrganizaciones() : [],
            'orgDestino' => $this->orgId(),
            'esSuperAdmin' => $this->esSuperAdmin(),
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Nuevo Consultorio',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function store(Request $request, Response $response): void
    {
        if (!$request->isPost()) {
            $response->redirectTo('/panel/consultorios');
        }

        if (!Security::verifyCsrfToken()) {
            Session::flashError('Sesion expirada. Intenta de nuevo.');
            $response->redirectTo('/panel/consultorios/nuevo');
        }

        $data = $this->datosDelFormulario($request);
        $orgIdDesdeForm = $this->esSuperAdmin() ? (int) $request->input('organizacion_id', 0) : 0;

        $validator = Validator::make($data, [
            'nombre' => 'required|min:2|max:150',
            'ubicacion' => 'max:255',
            'direccion' => 'max:500',
        ]);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', array_merge($data, ['organizacion_id' => $orgIdDesdeForm]));
            $response->redirectTo('/panel/consultorios/nuevo');
        }

        $orgDestino = $this->orgIdDestino($orgIdDesdeForm > 0 ? $orgIdDesdeForm : null);

        $this->model->create(array_merge($data, [
            'organizacion_id' => $orgDestino,
        ]));

        Session::flashSuccess('Consultorio "' . $data['nombre'] . '" creado correctamente.');
        $response->redirectTo('/panel/consultorios');
    }

    // ========================================================================
    // UPDATE
    // ========================================================================

    public function edit(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $consultorio = $this->buscarEnOrg($id);

        if ($consultorio === null) {
            Session::flashError('Consultorio no encontrado.');
            $response->redirectTo('/panel/consultorios');
        }

        $content = View::render('Pages/consultorios/form', [
            'consultorio' => $consultorio,
            'organizaciones' => $this->esSuperAdmin() ? $this->model->getOrganizaciones() : [],
            'orgDestino' => (int) $consultorio['organizacion_id'],
            'esSuperAdmin' => $this->esSuperAdmin(),
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Editar Consultorio',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost()) {
            $response->redirectTo('/panel/consultorios');
        }

        if (!Security::verifyCsrfToken()) {
            Session::flashError('Sesion expirada. Intenta de nuevo.');
            $response->redirectTo('/panel/consultorios');
        }

        $consultorio = $this->buscarEnOrg($id);

        if ($consultorio === null) {
            Session::flashError('Consultorio no encontrado.');
            $response->redirectTo('/panel/consultorios');
        }

        $data = $this->datosDelFormulario($request);
        $orgIdDesdeForm = $this->esSuperAdmin() ? (int) $request->input('organizacion_id', 0) : 0;

        $validator = Validator::make($data, [
            'nombre' => 'required|min:2|max:150',
            'ubicacion' => 'max:255',
            'direccion' => 'max:500',
        ]);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', array_merge($data, ['organizacion_id' => $orgIdDesdeForm]));
            $response->redirectTo('/panel/consultorios/' . $id . '/editar');
        }

        if ($this->esSuperAdmin() && $orgIdDesdeForm > 0) {
            $org = (new Organizacion())->findById($orgIdDesdeForm);
            if ($org !== null && (int) $org['activo'] === 1 && (int) $org['id'] !== (int) $consultorio['organizacion_id']) {
                $data['organizacion_id'] = (int) $org['id'];
            }
        }

        $this->model->update($id, $data);

        Session::flashSuccess('Consultorio "' . $data['nombre'] . '" actualizado correctamente.');
        $response->redirectTo('/panel/consultorios');
    }

    // ========================================================================
    // DELETE / RESTORE
    // ========================================================================

    public function delete(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/consultorios');
        }

        $consultorio = $this->buscarEnOrg($id);

        if ($consultorio === null) {
            Session::flashError('Consultorio no encontrado.');
            $response->redirectTo('/panel/consultorios');
        }

        $this->model->setActive($id, 0);

        Session::flashSuccess('Consultorio dado de baja.');
        $response->redirectTo('/panel/consultorios');
    }

    public function restore(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/consultorios');
        }

        $consultorio = $this->buscarEnOrg($id);

        if ($consultorio === null) {
            Session::flashError('Consultorio no encontrado.');
            $response->redirectTo('/panel/consultorios');
        }

        $this->model->setActive($id, 1);

        Session::flashSuccess('Consultorio reactivado correctamente.');
        $response->redirectTo('/panel/consultorios');
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
            'ubicacion' => $nulo($request->input('ubicacion', '')),
            'direccion' => $nulo($request->input('direccion', '')),
            'latitud' => $nulo($request->input('latitud', '')),
            'longitud' => $nulo($request->input('longitud', '')),
        ];
    }
}
