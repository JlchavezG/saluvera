    <?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Request
{
    private array $get;
    private array $post;
    private array $files;
    private array $server;
    private array $cookies;
    private array $headers;
    private ?array $json = null;
    private string $method;
    private string $uri;
    private array $routeParams = [];

    public function __construct()
    {
        $this->get = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->files = $_FILES ?? [];
        $this->server = $_SERVER ?? [];
        $this->cookies = $_COOKIE ?? [];
        $this->headers = $this->parseHeaders();
        $this->method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        $this->uri = $this->parseUri();
        $this->parseJson();

        // Soporte para PUT, PATCH, DELETE con _method
        if ($this->method === 'POST' && isset($this->post['_method'])) {
            $this->method = strtoupper($this->post['_method']);
        }
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = str_replace('_', '-', substr($key, 5));
                $headers[$headerName] = $value;
            }
        }

        if (isset($this->server['CONTENT_TYPE'])) {
            $headers['CONTENT-TYPE'] = $this->server['CONTENT_TYPE'];
        }

        if (isset($this->server['CONTENT_LENGTH'])) {
            $headers['CONTENT-LENGTH'] = $this->server['CONTENT_LENGTH'];
        }

        return $headers;
    }

    private function parseUri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');
        return $uri === '//' ? '/' : $uri;
    }

    private function parseJson(): void
    {
        $contentType = $this->server['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $rawInput = file_get_contents('php://input');
            if (!empty($rawInput)) {
                $decoded = json_decode($rawInput, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->json = $decoded;
                }
            }
        }
    }

    // ========================================================================
    // METODOS HTTP
    // ========================================================================

    public function method(): string
    {
        return $this->method;
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isPut(): bool
    {
        return $this->method === 'PUT';
    }

    public function isPatch(): bool
    {
        return $this->method === 'PATCH';
    }

    public function isDelete(): bool
    {
        return $this->method === 'DELETE';
    }

    public function isAjax(): bool
    {
        return ($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public function isJson(): bool
    {
        return $this->json !== null;
    }

    // ========================================================================
    // URL Y RUTAS
    // ========================================================================

    public function uri(): string
    {
        return $this->uri;
    }

    public function path(): string
    {
        return trim($this->uri, '/');
    }

    public function segments(): array
    {
        return array_values(array_filter(explode('/', $this->path())));
    }

    public function segment(int $index, mixed $default = null): mixed
    {
        return $this->segments()[$index] ?? $default;
    }

    public function fullUrl(): string
    {
        $scheme = $this->isSecure() ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $this->uri;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function routeParam(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    // ========================================================================
    // DATOS DE ENTRADA
    // ========================================================================

    public function input(string $key, mixed $default = null): mixed
    {
        // Prioridad: JSON > POST > GET > Route Params
        if ($this->json !== null && isset($this->json[$key])) {
            return $this->json[$key];
        }

        if (isset($this->post[$key])) {
            return $this->post[$key];
        }

        if (isset($this->get[$key])) {
            return $this->get[$key];
        }

        if (isset($this->routeParams[$key])) {
            return $this->routeParams[$key];
        }

        return $default;
    }

    public function all(): array
    {
        $data = array_merge($this->get, $this->post, $this->json ?? []);
        unset($data['_method']);
        return $data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function json(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->json;
        }
        return $this->json[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return $this->input($key) !== null;
    }

    public function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            if ($this->has($key)) {
                $result[$key] = $this->input($key);
            }
        }
        return $result;
    }

    public function except(array $keys): array
    {
        $all = $this->all();
        foreach ($keys as $key) {
            unset($all[$key]);
        }
        return $all;
    }

    // ========================================================================
    // ARCHIVOS
    // ========================================================================

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        $file = $this->file($key);
        return $file !== null && $file['error'] !== UPLOAD_ERR_NO_FILE;
    }

    public function fileIsValid(string $key): bool
    {
        $file = $this->file($key);
        return $file !== null && $file['error'] === UPLOAD_ERR_OK;
    }

    // ========================================================================
    // HEADERS Y COOKIES
    // ========================================================================

    public function header(string $key, mixed $default = null): mixed
    {
        $key = strtoupper(str_replace('_', '-', $key));
        return $this->headers[$key] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->header('Authorization', '');
        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    // ========================================================================
    // INFORMACION DEL CLIENTE
    // ========================================================================

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function host(): string
    {
        return $this->server['HTTP_HOST'] ?? 'localhost';
    }

    public function isSecure(): bool
    {
        return ($this->server['HTTPS'] ?? '') === 'on'
            || ($this->server['SERVER_PORT'] ?? '') == 443;
    }

    public function referer(): ?string
    {
        return $this->server['HTTP_REFERER'] ?? null;
    }

    // ========================================================================
    // VALIDACION BASICA
    // ========================================================================

    public function sanitized(string $key): string
    {
        $value = $this->input($key, '');
        if (!is_string($value)) {
            return '';
        }
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    public function int(string $key, int $default = 0): int
    {
        return (int) ($this->input($key, $default));
    }

    public function float(string $key, float $default = 0.0): float
    {
        return (float) ($this->input($key, $default));
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->input($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function email(string $key): ?string
    {
        $value = $this->input($key, '');
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }

    // ========================================================================
    // UTILIDADES
    // ========================================================================

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'uri' => $this->uri,
            'get' => $this->get,
            'post' => $this->post,
            'json' => $this->json,
            'files' => array_keys($this->files),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'is_ajax' => $this->isAjax(),
        ];
    }
}   