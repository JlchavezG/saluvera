<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = '';

    public function __construct()
    {
        $this->headers = [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Powered-By' => 'SALUVERA',
        ];
    }

    // ========================================================================
    // STATUS CODE
    // ========================================================================

    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function ok(): self
    {
        return $this->status(200);
    }

    public function created(): self
    {
        return $this->status(201);
    }

    public function noContent(): self
    {
        return $this->status(204);
    }

    public function badRequest(): self
    {
        return $this->status(400);
    }

    public function unauthorized(): self
    {
        return $this->status(401);
    }

    public function forbidden(): self
    {
        return $this->status(403);
    }

    public function notFound(): self
    {
        return $this->status(404);
    }

    public function serverError(): self
    {
        return $this->status(500);
    }

    // ========================================================================
    // HEADERS
    // ========================================================================

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function headers(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->headers[$name] = $value;
        }
        return $this;
    }

    public function contentType(string $type): self
    {
        return $this->header('Content-Type', $type);
    }

    public function noCache(): self
    {
        $this->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->header('Pragma', 'no-cache');
        $this->header('Expires', '0');
        return $this;
    }

    // ========================================================================
    // CONTENIDO
    // ========================================================================

    public function body(string $content): self
    {
        $this->body = $content;
        return $this;
    }

    public function html(string $content): void
    {
        $this->contentType('text/html; charset=UTF-8');
        $this->body = $content;
        $this->send();
    }

    public function json(mixed $data, int $status = 200): void
    {
        $this->statusCode = $status;
        $this->contentType('application/json; charset=UTF-8');
        $this->body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $this->send();
    }

    public function jsonSuccess(mixed $data, string $message = 'Operacion exitosa'): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], 200);
    }

    public function jsonError(string $message, int $status = 400, mixed $errors = null): void
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        $this->json($response, $status);
    }

    // ========================================================================
    // REDIRECCIONES
    // ========================================================================

    public function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }

    public function redirectTo(string $path): never
    {
        $url = url($path);
        $this->redirect($url);
    }

    public function back(): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        $this->redirect($referer);
    }

    // ========================================================================
    // DESCARGAS
    // ========================================================================

    public function download(string $filePath, ?string $fileName = null): never
    {
        if (!file_exists($filePath)) {
            $this->notFound()->html('Archivo no encontrado');
            exit;
        }

        $fileName = $fileName ?? basename($filePath);
        $fileSize = filesize($filePath);

        $this->header('Content-Type', 'application/octet-stream');
        $this->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $this->header('Content-Length', (string) $fileSize);
        $this->header('Cache-Control', 'no-cache, must-revalidate');
        $this->header('Pragma', 'no-cache');

        $this->sendHeaders();

        readfile($filePath);
        exit;
    }

    public function image(string $filePath): void
    {
        if (!file_exists($filePath)) {
            $this->notFound()->html('Imagen no encontrada');
            return;
        }

        $mimeType = mime_content_type($filePath);
        $this->contentType($mimeType);
        $this->body = file_get_contents($filePath);
        $this->send();
    }

    // ========================================================================
    // ERRORES
    // ========================================================================

    public function error404(): void
    {
        $this->notFound()->html($this->renderErrorPage(404, 'Pagina no encontrada', 'La pagina que buscas no existe o fue movida.'));
    }

    public function error403(): void
    {
        $this->forbidden()->html($this->renderErrorPage(403, 'Acceso denegado', 'No tienes permisos para acceder a esta pagina.'));
    }

    public function error401(): void
    {
        $this->unauthorized()->html($this->renderErrorPage(401, 'No autorizado', 'Debes iniciar sesion para acceder a esta pagina.'));
    }

    public function error500(?string $details = null): void
    {
        $message = 'Ocurrio un error interno del servidor.';
        if (defined('APP_DEBUG') && APP_DEBUG && $details !== null) {
            $message .= '<br><br><strong>Detalles:</strong><br>' . htmlspecialchars($details);
        }
        $this->serverError()->html($this->renderErrorPage(500, 'Error interno', $message));
    }

    private function renderErrorPage(int $code, string $title, string $message): string
    {
        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $code . ' - ' . htmlspecialchars($title) . '</title>
    <style>
        body { font-family: Inter, -apple-system, Arial, sans-serif; background: #F7F8F6; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; color: #17252A; }
        .container { text-align: center; padding: 40px; }
        .code { font-size: 96px; font-weight: 700; color: #123C46; margin: 0; }
        .title { font-size: 28px; font-weight: 600; color: #123C46; margin: 16px 0; }
        .message { font-size: 16px; color: #66777C; max-width: 500px; margin: 0 auto 32px; line-height: 1.6; }
        .btn { display: inline-block; background: #123C46; color: #fff; padding: 12px 32px; border-radius: 10px; text-decoration: none; font-weight: 600; transition: background 0.3s; }
        .btn:hover { background: #0B2931; }
    </style>
</head>
<body>
    <div class="container">
        <p class="code">' . $code . '</p>
        <h1 class="title">' . htmlspecialchars($title) . '</h1>
        <p class="message">' . $message . '</p>
        <a href="' . url('/') . '" class="btn">Volver al inicio</a>
    </div>
</body>
</html>';
    }

    // ========================================================================
    // ENVIO
    // ========================================================================

    private function sendHeaders(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value);
            }
        }
    }

    public function send(): void
    {
        $this->sendHeaders();
        echo $this->body;
        exit;
    }

    public function __toString(): string
    {
        return $this->body;
    }
}   