<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles = [])
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(Request $request, Response $response): bool
    {
        // Primero verificar autenticacion
        $auth = new AuthMiddleware();
        if (!$auth->handle($request, $response)) {
            return false;
        }

        // Si no hay roles especificados, permitir cualquier rol autenticado
        if (empty($this->allowedRoles)) {
            return true;
        }

        // Verificar rol del usuario
        $user = Session::user();
        $rolSlug = $user['rol_slug'] ?? '';

        if (!in_array($rolSlug, $this->allowedRoles, true)) {
            $response->error403();
            return false;
        }

        return true;
    }
}