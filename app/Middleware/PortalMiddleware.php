<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class PortalMiddleware
{
    public function handle(Request $request, Response $response): bool
    {
        $pacienteId = Session::get('portal_paciente_id');

        if (!$pacienteId) {
            $response->redirectTo('/');
            return false;
        }

        return true;
    }
}
