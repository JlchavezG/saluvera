<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    // ========================================================================
    // MOSTRAR FORMULARIO DE LOGIN
    // ========================================================================

    public function showLogin(Request $request, Response $response): void
    {
        if (Session::isAuthenticated()) {
            $response->redirectTo('/panel');
        }

        $content = View::render('Auth.login', [
            'error' => Session::getFlash('login_error'),
            'oldEmail' => Session::getFlash('old_email', ''),
        ]);

        $html = View::render('Layouts.auth', [
            'pageTitle' => 'Iniciar Sesion',
            'content' => $content,
        ]);

        $response->html($html);
    }

    // ========================================================================
    // PROCESAR LOGIN
    // ========================================================================

    public function login(Request $request, Response $response): void
    {
        if (!$request->isPost()) {
            $response->redirectTo('/login');
        }

        if (!Security::verifyCsrfToken()) {
            Session::flashError('Sesion expirada. Intenta de nuevo.');
            $response->redirectTo('/login');
        }

        if (!Security::checkRateLimit('login_' . $request->ip(), 5, 15)) {
            Session::flashError('Demasiados intentos. Espera 15 minutos.');
            $response->redirectTo('/login');
        }

        $email = trim($request->input('email', ''));
        $password = $request->input('password', '');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]
        );

        if ($validator->fails()) {
            Session::flash('login_error', $validator->firstError());
            Session::flash('old_email', $email);
            $response->redirectTo('/login');
        }

        $result = $this->userModel->attemptLogin($email, $password);

        if (!$result['success']) {
            Session::flash('login_error', $result['error']);
            Session::flash('old_email', $email);
            $response->redirectTo('/login');
        }

        $user = $result['user'];

        // Registrar IP de acceso
        $this->userModel->updateLastLogin((int) $user['id'], $request->ip());

        // Regenerar ID de sesion (previene session fixation)
        Session::regenerate(true);

        Session::setUserId((int) $user['id']);
        Session::set('user', [
            'id' => (int) $user['id'],
            'nombre' => $user['nombre'],
            'apellidos' => $user['apellidos'],
            'correo' => $user['correo'],
            'rol_id' => (int) $user['rol_id'],
            'rol_nombre' => $user['rol_nombre'],
            'rol_slug' => $user['rol_slug'],
        ]);

        $permissions = $this->userModel->getPermissions((int) $user['rol_id']);
        Session::set('permissions', $permissions);

        Session::set('fingerprint', Security::fingerprint());

        Security::clearRateLimit('login_' . $request->ip());

        Session::flashSuccess('Bienvenido, ' . $user['nombre'] . '!');

        $this->redirectByRole($user['rol_slug'], $response);
    }

    // ========================================================================
    // PROCESAR LOGOUT
    // ========================================================================

    public function logout(Request $request, Response $response): void
    {
        Security::clearRateLimit('login_' . $request->ip());

        Session::destroy();

        Session::start();
        Session::flashSuccess('Sesion cerrada correctamente.');

        $response->redirectTo('/login');
    }

    // ========================================================================
    // REDIRECCION SEGUN ROL
    // ========================================================================

    private function redirectByRole(string $rolSlug, Response $response): void
    {
        $routes = [
            'superadmin' => '/panel',
            'clinic_admin' => '/panel',
            'professional' => '/panel/mi-panel',
            'receptionist' => '/panel/agenda',
            'patient' => '/portal',
        ];

        $route = $routes[$rolSlug] ?? '/panel';
        $response->redirectTo($route);
    }

    // ========================================================================
    // VERIFICAR SESION ACTIVA (para AJAX)
    // ========================================================================

    public function check(Request $request, Response $response): void
    {
        if (!Session::isAuthenticated()) {
            $response->jsonError('No autenticado', 401);
        }

        $response->jsonSuccess([
            'user' => Session::user(),
            'permissions' => Session::get('permissions', []),
        ], 'Sesion activa');
    }
}