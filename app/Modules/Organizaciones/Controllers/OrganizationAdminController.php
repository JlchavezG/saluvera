<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class OrganizationAdminController
{
    private Organizacion $model;

    public function __construct()
    {
        $this->model = new Organizacion();
    }

    private function esSuperAdmin(): bool
    {
        $user = Session::user() ?? [];
        return ($user['rol_slug'] ?? '') === 'superadmin';
    }

    private function guardarORechazar(Response $response): void
    {
        if (!$this->esSuperAdmin()) {
            Session::flashError('Solo el superadmin puede gestionar organizaciones.');
            $response->redirectTo('/panel');
        }
    }

    public function index(Request $request, Response $response): void
    {
        $this->guardarORechazar($response);

        $page = max(1, $request->int('page', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $orgs = $this->model->getAll($perPage, $offset);
        $total = $this->model->countAll();
        $totalPages = max(1, (int) ceil($total / $perPage));

        $content = View::render('Pages/organizaciones/list', [
            'orgs' => $orgs,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'planes' => Organizacion::planes(),
            'estados' => Organizacion::estados(),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Organizaciones',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function create(Request $request, Response $response): void
    {
        $this->guardarORechazar($response);

        $content = View::render('Pages/organizaciones/form', [
            'org' => null,
            'zonas' => Organizacion::zonasHorarias(),
            'planes' => Organizacion::planes(),
            'estados' => Organizacion::estados(),
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Nueva Organizacion',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function store(Request $request, Response $response): void
    {
        $this->guardarORechazar($response);

        $nulo = function ($v) {
            $v = trim((string) $v);
            return ($v === '') ? null : $v;
        };

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/organizaciones');
        }

        $data = [
            'nombre' => trim((string) $request->input('nombre', '')),
            'correo' => trim((string) $request->input('correo', '')),
            'telefono' => trim((string) $request->input('telefono', '')),
            'direccion' => trim((string) $request->input('direccion', '')),
            'latitud' => $nulo($request->input('latitud', '')),
            'longitud' => $nulo($request->input('longitud', '')),
            'zona_horaria' => trim((string) $request->input('zona_horaria', '')),
            'plan_suscripcion' => trim((string) $request->input('plan_suscripcion', '')),
            'estado_suscripcion' => trim((string) $request->input('estado_suscripcion', '')),
        ];

        $validator = Validator::make($data, [
            'nombre' => 'required|min:2|max:255',
            'correo' => 'email',
            'zona_horaria' => 'required',
            'plan_suscripcion' => 'required',
            'estado_suscripcion' => 'required',
        ]);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/organizaciones/nueva');
        }

        if (!array_key_exists($data['plan_suscripcion'], Organizacion::planes()) ||
            !array_key_exists($data['estado_suscripcion'], Organizacion::estados())) {
            Session::flash('form_errors', ['plan_suscripcion' => ['Valor no valido']]);
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/organizaciones/nueva');
        }

        $orgId = $this->model->create([
            'nombre' => $data['nombre'],
            'correo' => $data['correo'] !== '' ? $data['correo'] : null,
            'telefono' => $data['telefono'] !== '' ? $data['telefono'] : null,
            'direccion' => $data['direccion'] !== '' ? $data['direccion'] : null,
            'latitud' => $data['latitud'] ?? null,
            'longitud' => $data['longitud'] ?? null,
            'zona_horaria' => $data['zona_horaria'],
            'plan_suscripcion' => $data['plan_suscripcion'],
            'estado_suscripcion' => $data['estado_suscripcion'],
        ]);

        // Opcional: crear el primer clinic_admin de esta organizacion
        $adminCorreo = trim((string) $request->input('admin_correo', ''));
        $adminPassword = (string) $request->input('admin_password', '');
        $adminNombre = trim((string) $request->input('admin_nombre', ''));

        if ($adminCorreo !== '' && $adminPassword !== '') {
            $userModel = new User();
            $db = Database::getInstance();
            $rolClinic = $db->fetchOne("SELECT id FROM roles WHERE slug = 'clinic_admin' LIMIT 1");

            if ($rolClinic !== null && !$userModel->existsByEmail($adminCorreo)) {
                $userModel->create([
                    'organizacion_id' => $orgId,
                    'correo' => $adminCorreo,
                    'contrasena_hash' => Security::hashPassword($adminPassword),
                    'nombre' => $adminNombre !== '' ? $adminNombre : 'Admin',
                    'apellidos' => $data['nombre'],
                    'rol_id' => (int) $rolClinic['id'],
                ]);
                Session::flashSuccess("Organizacion '{$data['nombre']}' creada con su admin: {$adminCorreo}");
            } else {
                Session::flashSuccess("Organizacion '{$data['nombre']}' creada (admin no creado: correo duplicado o rol faltante)");
            }
        } else {
            Session::flashSuccess("Organizacion '{$data['nombre']}' creada correctamente.");
        }

        $response->redirectTo('/panel/organizaciones');
    }

    public function edit(Request $request, Response $response): void
    {
        $this->guardarORechazar($response);

        $id = (int) $request->routeParam('id', 0);
        $org = $this->model->findById($id);

        if ($org === null) {
            Session::flashError('Organizacion no encontrada.');
            $response->redirectTo('/panel/organizaciones');
        }

        $content = View::render('Pages/organizaciones/form', [
            'org' => $org,
            'zonas' => Organizacion::zonasHorarias(),
            'planes' => Organizacion::planes(),
            'estados' => Organizacion::estados(),
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Editar Organizacion',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function update(Request $request, Response $response): void
    {
        $this->guardarORechazar($response);

        $nulo = function ($v) {
            $v = trim((string) $v);
            return ($v === '') ? null : $v;
        };

        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/organizaciones');
        }

        $org = $this->model->findById($id);

        if ($org === null) {
            Session::flashError('Organizacion no encontrada.');
            $response->redirectTo('/panel/organizaciones');
        }

        $data = [
            'nombre' => trim((string) $request->input('nombre', '')),
            'correo' => trim((string) $request->input('correo', '')),
            'telefono' => trim((string) $request->input('telefono', '')),
            'direccion' => trim((string) $request->input('direccion', '')),
            'latitud' => $nulo($request->input('latitud', '')),
            'longitud' => $nulo($request->input('longitud', '')),
            'zona_horaria' => trim((string) $request->input('zona_horaria', '')),
            'plan_suscripcion' => trim((string) $request->input('plan_suscripcion', '')),
            'estado_suscripcion' => trim((string) $request->input('estado_suscripcion', '')),
        ];

        $validator = Validator::make($data, [
            'nombre' => 'required|min:2|max:255',
            'correo' => 'email',
            'zona_horaria' => 'required',
            'plan_suscripcion' => 'required',
            'estado_suscripcion' => 'required',
        ]);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/organizaciones/' . $id . '/editar');
        }

        $this->model->update($id, [
            'nombre' => $data['nombre'],
            'correo' => $data['correo'] !== '' ? $data['correo'] : null,
            'telefono' => $data['telefono'] !== '' ? $data['telefono'] : null,
            'direccion' => $data['direccion'] !== '' ? $data['direccion'] : null,
            'latitud' => $data['latitud'] ?? null,
            'longitud' => $data['longitud'] ?? null,
            'zona_horaria' => $data['zona_horaria'],
            'plan_suscripcion' => $data['plan_suscripcion'],
            'estado_suscripcion' => $data['estado_suscripcion'],
        ]);

        Session::flashSuccess("Organizacion '{$data['nombre']}' actualizada.");
        $response->redirectTo('/panel/organizaciones');
    }

    public function suspender(Request $request, Response $response): void
    {
        $this->guardarORechazar($response);

        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/organizaciones');
        }

        $org = $this->model->findById($id);

        if ($org === null) {
            Session::flashError('Organizacion no encontrada.');
            $response->redirectTo('/panel/organizaciones');
        }

        $this->model->setActive($id, 0);

        Session::flashSuccess("Organizacion '{$org['nombre']}' suspendida.");
        $response->redirectTo('/panel/organizaciones');
    }

    public function activar(Request $request, Response $response): void
    {
        $this->guardarORechazar($response);

        $id = (int) $request->routeParam('id', 0);

        if (!$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/organizaciones');
        }

        $org = $this->model->findById($id);

        if ($org === null) {
            Session::flashError('Organizacion no encontrada.');
            $response->redirectTo('/panel/organizaciones');
        }

        $this->model->setActive($id, 1);

        Session::flashSuccess("Organizacion '{$org['nombre']}' activada.");
        $response->redirectTo('/panel/organizaciones');
    }
}
