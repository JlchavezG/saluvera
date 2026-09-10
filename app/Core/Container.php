<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Container
{
    private static ?Container $instance = null;
    private array $bindings = [];
    private array $instances = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function bind(string $abstract, mixed $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, mixed $concrete): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => true,
        ];
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $binding = $this->bindings[$abstract] ?? null;

        if ($binding === null) {
            return $this->build($abstract, $parameters);
        }

        if (is_array($binding) && isset($binding['singleton'])) {
            if (!isset($this->instances[$abstract])) {
                $this->instances[$abstract] = $this->build($binding['concrete'], $parameters);
            }
            return $this->instances[$abstract];
        }

        return $this->build($binding, $parameters);
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    public function get(string $abstract): mixed
    {
        return $this->make($abstract);
    }

    private function build(mixed $concrete, array $parameters = []): mixed
    {
        if (is_callable($concrete)) {
            return call_user_func($concrete, $this, $parameters);
        }

        if (is_object($concrete)) {
            return $concrete;
        }

        if (!class_exists($concrete)) {
            throw new RuntimeException("Clase no encontrada: {$concrete}");
        }

        $reflection = new ReflectionClass($concrete);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("La clase {$concrete} no es instanciable");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $concrete();
        }

        $dependencies = $this->resolveDependencies($constructor, $parameters);

        return $reflection->newInstanceArgs($dependencies);
    }

    private function resolveDependencies(ReflectionMethod $constructor, array $parameters): array
    {
        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (isset($parameters[$name])) {
                $dependencies[] = $parameters[$name];
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $className = $type->getName();
                $dependencies[] = $this->make($className);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new RuntimeException("No se puede resolver el parametro: {$name}");
            }
        }

        return $dependencies;
    }

    public function call(callable $callback, array $parameters = []): mixed
    {
        if (is_array($callback)) {
            [$class, $method] = $callback;
            if (is_string($class)) {
                $class = $this->make($class);
            }
            $reflection = new ReflectionMethod($class, $method);
            $dependencies = $this->resolveMethodDependencies($reflection, $parameters);
            return $reflection->invokeArgs($class, $dependencies);
        }

        if (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback);
            $instance = $this->make($class);
            $reflection = new ReflectionMethod($instance, $method);
            $dependencies = $this->resolveMethodDependencies($reflection, $parameters);
            return $reflection->invokeArgs($instance, $dependencies);
        }

        return call_user_func($callback, $this);
    }

    private function resolveMethodDependencies(ReflectionMethod $method, array $parameters): array
    {
        $dependencies = [];

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (isset($parameters[$name])) {
                $dependencies[] = $parameters[$name];
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $className = $type->getName();
                $dependencies[] = $this->make($className);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                $dependencies[] = null;
            }
        }

        return $dependencies;
    }

    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }
}