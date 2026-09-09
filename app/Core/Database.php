<?php

if (!defined('SALUVERA_APP')) {
    define('SALUVERA_APP', true);
}

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config;
    private int $queryCount = 0;

    private function __construct()
    {
        $this->loadConfig();
        $this->connect();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig(): void
    {
        $configFile = dirname(__DIR__) . '/Config/database.php';

        if (!file_exists($configFile)) {
            throw new RuntimeException("Config BD no encontrado: {$configFile}");
        }

        $config = require $configFile;
        $default = $config['default'] ?? 'mysql';

        if (!isset($config['connections'][$default])) {
            throw new RuntimeException("Conexion '{$default}' no definida");
        }

        $this->config = $config['connections'][$default];
    }

    private function connect(): void
    {
        try {
            $driver = $this->config['driver'];

            if ($driver === 'mysql') {
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['database'],
                    $this->config['charset']
                );
            } elseif ($driver === 'sqlite') {
                $dsn = "sqlite:" . $this->config['database'];
            } else {
                throw new RuntimeException("Driver no soportado: {$driver}");
            }

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            throw new RuntimeException("Error al conectar: " . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            $this->queryCount++;
            return $stmt;
        } catch (PDOException $e) {
            throw new RuntimeException("Error en consulta: " . $e->getMessage());
        }
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result !== false ? $result : null;
    }

    public function fetchColumn(string $sql, array $params = []): mixed
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        return (int) $this->getConnection()->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setClauses = [];
        $params = [];

        foreach ($data as $column => $value) {
            $setClauses[] = "{$column} = ?";
            $params[] = $value;
        }

        $setString = implode(', ', $setClauses);
        $params = array_merge($params, $whereParams);
        $sql = "UPDATE {$table} SET {$setString} WHERE {$where}";
        return $this->execute($sql, $params);
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->execute($sql, $params);
    }

    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    public function rollback(): bool
    {
        return $this->getConnection()->rollBack();
    }

    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function tableExists(string $table): bool
    {
        try {
            $result = $this->getConnection()->query("SHOW TABLES LIKE '{$table}'")->fetch();
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function count(string $table): int
    {
        $result = $this->query("SELECT COUNT(*) as total FROM {$table}")->fetch();
        return (int) ($result['total'] ?? 0);
    }

    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    public function disconnect(): void
    {
        $this->connection = null;
        self::$instance = null;
    }
}   