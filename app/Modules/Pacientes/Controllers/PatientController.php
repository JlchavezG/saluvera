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

    // ========================================================================
    // LISTADO CON BUSQUEDA Y PAGINACION
    // ========================================================================

    public function index(Request $request, Response $response): void
    {
        $q = trim((string) $request->input('q', ''));
        $page = max(1, $request->int('page', 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $org = $this->orgId();

        $pacientes = $this->model->search($org, $q, $perPage, $offset);
        $total = $this->model->countSearch($org, $q);
        $totalPages = max(1, (int) ceil($total / $perPage));

        $content = View::render('Pages/pacientes/list', [
            'pacientes' => $pacientes,
            'q' => $q,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Pacientes',
            'content' => $content,
        ]);

        $response->html($html);
    }

    // ========================================================================
    // FORMULARIO DE ALTA
    // ========================================================================

    public function create(Request $request, Response $response): void
    {
        $content = View::render('Pages/pacientes/form', [
            'errores' => Session::getFlash('form_errors', []),
            'old' => Session::getFlash('form_old', []),
        ]);

        $html = View::render('Layouts.panel', [
            'pageTitle' => 'Nuevo Paciente',
            'content' => $content,
        ]);

        $response->html($html);
    }

    // ========================================================================
    // GUARDAR PACIENTE NUEVO
    // ========================================================================

    public function store(Request $request, Response $response): void
    {
        if (!$request->isPost()) {
            $response->redirectTo('/panel/pacientes');
        }

        if (!Security::verifyCsrfToken()) {
            Session::flashError('Sesion expirada. Intenta de nuevo.');
            $response->redirectTo('/panel/pacientes/nuevo');
        }

        $data = [
            'nombre' => trim((string) $request->input('nombre', '')),
            'apellidos' => trim((string) $request->input('apellidos', '')),
            'correo' => trim((string) $request->input('correo', '')),
            'telefono' => trim((string) $request->input('telefono', '')),
            'fecha_nacimiento' => trim((string) $request->input('fecha_nacimiento', '')),
            'genero' => (string) $request->input('genero', ''),
            'tipo_sangre' => (string) $request->input('tipo_sangre', ''),
            'ciudad' => trim((string) $request->input('ciudad', '')),
            'ocupacion' => trim((string) $request->input('ocupacion', '')),
            'notas' => trim((string) $request->input('notas', '')),
        ];

        $validator = Validator::make($data, [
            'nombre' => 'required|min:2|max:100',
            'apellidos' => 'required|min:2|max:100',
            'correo' => 'email',
            'telefono' => 'phone',
            'fecha_nacimiento' => 'date',
            'genero' => 'in:masculino,femenino,otro,prefiero_no_decir',
            'tipo_sangre' => 'in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        ]);

        if ($validator->fails()) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $data);
            $response->redirectTo('/panel/pacientes/nuevo');
        }

        $nulo = function ($v) {
            return ($v === '') ? null : $v;
        };

        $id = $this->model->create([
            'organizacion_id' => $this->orgId(),
            'nombre' => $data['nombre'],
            'apellidos' => $data['apellidos'],
            'correo' => $nulo($data['correo']),
            'telefono' => $nulo($data['telefono']),
            'fecha_nacimiento' => $nulo($data['fecha_nacimiento']),
            'genero' => $nulo($data['genero']),
            'tipo_sangre' => $nulo($data['tipo_sangre']),
            'ciudad' => $nulo($data['ciudad']),
            'ocupacion' => $nulo($data['ocupacion']),
            'notas' => $nulo($data['notas']),
        ]);

        Session::flashSuccess('Paciente "' . $data['nombre'] . ' ' . $data['apellidos'] . '" creado correctamente.');
        $response->redirectTo('/panel/pacientes');
    }
}
