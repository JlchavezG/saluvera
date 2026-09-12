<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class AdminMiddleware
{
    public function handle(Request $request, Response $response): bool
    {
        // Primero: sesion activa
        $auth = new AuthMiddleware();
        if (!$auth->handle($request, $response)) {
            return false;
        }

        // Segundo: rol administrador
        $user = Session::user() ?? [];
        $rol = $user['rol_slug'] ?? '';

        if (!in_array($rol, ['superadmin', 'clinic_admin'], true)) {
            Session::flashError('No tienes permisos de administrador para esa seccion.');

            $ruta = ($rol === 'professional') ? '/panel/mi-panel' : '/panel/agenda';
            $response->redirectTo($ruta);
            return false;
        }

        return true;
    }
}
