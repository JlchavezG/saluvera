<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class GuestMiddleware
{
    public function handle(Request $request, Response $response): bool
    {
        // Si ya hay sesion activa, redirigir al panel
        if (Session::isAuthenticated()) {
            $user = Session::user();
            $rolSlug = $user['rol_slug'] ?? 'patient';

            $route = ($rolSlug === 'patient') ? '/portal' : '/panel';
            $response->redirectTo($route);
            return false;
        }

        return true;
    }
}