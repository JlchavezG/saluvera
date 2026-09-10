    <?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class AuthMiddleware
{
    public function handle(Request $request, Response $response): bool
    {
        // Verificar sesion activa
        if (!Session::isAuthenticated()) {
            Session::flash('login_error', 'Debes iniciar sesion para acceder.');
            $response->redirectTo('/login');
            return false;
        }

        // Verificar fingerprint de seguridad
        $storedFingerprint = Session::get('fingerprint');
        if ($storedFingerprint !== null && !Security::verifyFingerprint($storedFingerprint)) {
            Session::destroy();
            Session::start();
            Session::flashError('Sesion invalidada por cambio de dispositivo.');
            $response->redirectTo('/login');
            return false;
        }

        // Verificar que el usuario siga activo en la BD
        $userModel = new User();
        $user = $userModel->findById(Session::getUserId());

        if ($user === null || (int) $user['activo'] !== 1) {
            Session::destroy();
            Session::start();
            Session::flashError('Tu cuenta ya no esta activa.');
            $response->redirectTo('/login');
            return false;
        }

        return true;
    }
}