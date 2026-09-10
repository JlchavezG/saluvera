<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class View
{
    private static string $basePath = '';
    private static array $shared = [];

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/');
    }

    public static function getBasePath(): string
    {
        if (self::$basePath === '') {
            self::$basePath = defined('VIEWS_PATH') ? VIEWS_PATH : dirname(__DIR__) . '/Shared';
        }
        return self::$basePath;
    }

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function render(string $template, array $data = []): string
    {
        $file = self::resolvePath($template);

        if (!file_exists($file)) {
            throw new RuntimeException("Vista no encontrada: {$template} (ruta: {$file})");
        }

        $data = array_merge(self::$shared, $data);

        extract($data, EXTR_SKIP);

        ob_start();
        include $file;
        return ob_get_clean();
    }

    public static function display(string $template, array $data = []): void
    {
        echo self::render($template, $data);
    }

    public static function renderPartial(string $partial, array $data = []): string
    {
        return self::render('Partials.' . $partial, $data);
    }

    public static function displayPartial(string $partial, array $data = []): void
    {
        echo self::renderPartial($partial, $data);
    }

    public static function renderComponent(string $component, array $data = []): string
    {
        return self::render('Components.' . $component, $data);
    }

    public static function displayComponent(string $component, array $data = []): void
    {
        echo self::renderComponent($component, $data);
    }

    public static function renderWithLayout(string $layout, string $template, array $data = []): string
    {
        $content = self::render($template, $data);
        $data['content'] = $content;

        return self::render('Layouts.' . $layout, $data);
    }

    public static function displayWithLayout(string $layout, string $template, array $data = []): void
    {
        echo self::renderWithLayout($layout, $template, $data);
    }

    public static function exists(string $template): bool
    {
        $file = self::resolvePath($template);
        return file_exists($file);
    }

    private static function resolvePath(string $template): string
    {
        $template = str_replace('.', '/', $template);
        return self::getBasePath() . '/' . $template . '.php';
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    public static function escape(?string $value): string
    {
        return self::e($value);
    }

    public static function include(string $template, array $data = []): void
    {
        self::display($template, $data);
    }

    public static function section(string $name, string $content): void
    {
        self::$shared['_sections'][$name] = $content;
    }

    public static function yield(string $name, string $default = ''): string
    {
        return self::$shared['_sections'][$name] ?? $default;
    }
}