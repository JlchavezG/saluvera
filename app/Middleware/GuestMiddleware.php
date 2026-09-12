<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class GuestMiddleware
{
    public function handle(Request $request, Response $response): bool
    {
        if (Session::isAuthenticated()) {
            $user = Session::user();
            $rolSlug = $user['rol_slug'] ?? '';

            $routes = [
                'superadmin' => '/panel',
                'clinic_admin' => '/panel',
                'professional' => '/panel/mi-panel',
                'receptionist' => '/panel/agenda',
                'patient' => '/portal',
            ];

            $route = $routes[$rolSlug] ?? '/panel';
            $response->redirectTo($route);
            return false;
        }

        return true;
    }
}
