<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class OrganizacionController
{
    private Organizacion $model;

    public function __construct()
    {
        $this->model = new Organizacion();
    }

    private function miOrg(): ?array
    {
        $userModel = new User();
        $user = $userModel->findById(Session::getUserId());
        $orgId = (int) ($user['organizacion_id'] ?? 0);

        if ($orgId === 0) {
            return null;
        }

        return $this->model->findById($orgId);
    }

    public function show(Request $request, Response $response): void
    {
        $org = $this->miOrg();

        if ($org === null) {
            Session::flashError('No se encontro tu organizacion.');
            $response->redirectTo('/panel');
        }

        $content = View::render('Pages/organizacion/show', [
            'org' => $org,
            'deps' => $this->model->countDependencias((int) $org['id']),
            'planes' => Organizacion::planes(),
            'estados' => Organizacion::estados(),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Mi Organizacion',
            'content' => $content,
        ]);

        $response->html($html);
    }

    public function edit(Request $request, Response $response): void
    {
        $org = $this->miOrg();

        if ($org === null) {
            Session::flashError('No se encontro tu organizacion.');
            $response->redirectTo('/panel');
        }

        $content = View::render('Pages/organizacion/form', [
            'org' => $org,
            'zonas' => Organizacion::zonasHorarias(),
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
        $org = $this->miOrg();

        if ($org === null || !$request->isPost() || !Security::verifyCsrfToken()) {
            $response->redirectTo('/panel/organizacion');
        }

        $nulo = function ($v) {
            $v = trim((string) $v);
            return ($v === '') ? null : $v;
        };

        $data = [
            'nombre' => trim((string) $request->input('nombre', '')),
            'correo' => $nulo($request->input('correo', '')),
            'telefono' => $nulo($request->input('telefono', '')),
            'direccion' => $nulo($request->input('direccion', '')),
            'latitud' => $nulo($request->input('latitud', '')),
            'longitud' => $nulo($request->input('longitud', '')),
            'zona_horaria' => trim((string) $request->input('zona_horaria', '')),
        ];

        $validator = Validator::make($data, [
            'nombre' => 'required|min:2|max:255',
            'correo' => 'email',
            'telefono' => 'max:50',
            'zona_horaria' => 'required|max:100',
        ]);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/organizacion/editar');
        }

        // Validar zona horaria contra el catalogo
        if (!array_key_exists($data['zona_horaria'], Organizacion::zonasHorarias())) {
            Session::flash('form_errors', ['zona_horaria' => ['Zona horaria no valida']]);
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/organizacion/editar');
        }

        $this->model->update((int) $org['id'], $data);

        Session::flashSuccess('Datos de la organizacion actualizados correctamente.');
        $response->redirectTo('/panel/organizacion');
    }
}
