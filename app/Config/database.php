<?php
/**
 * SALUVERA - Configuracion de Base de Datos
 *
 * Este archivo devuelve un array con la configuracion de conexion
 * a la base de datos MySQL. Lee las credenciales del archivo .env
 *
 * @author SALUVERA
 * @version 1.0.0
 */

declare(strict_types=1);

// Devolver la configuracion de base de datos
return [
    // --------------------------------------------------------------------
    // CONEXION POR DEFECTO
    // --------------------------------------------------------------------
    'default' => env('DB_CONNECTION', 'mysql'),

    // --------------------------------------------------------------------
    // CONEXIONES DISPONIBLES
    // --------------------------------------------------------------------
    'connections' => [
        // MySQL (conexion principal)
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'saluvera'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'strict' => true,
            'engine' => 'InnoDB',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::MYSQL_ATTR_FOUND_ROWS => true,
            ],
        ],

        // SQLite (para testing, opcional)
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', ':memory:'),
            'prefix' => '',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ],
    ],

    // --------------------------------------------------------------------
    // CONFIGURACION DE TRANSACCIONES
    // --------------------------------------------------------------------
    'transactions' => [
        'isolation_level' => 'READ COMMITTED',
        'retry_attempts' => 3,
        'retry_delay' => 100,
    ],

    // --------------------------------------------------------------------
    // CONFIGURACION DE MIGRACIONES
    // --------------------------------------------------------------------
    'migrations' => [
        'table' => 'migrations',
        'path' => dirname(__DIR__, 2) . '/database/migrations',
    ],

    // --------------------------------------------------------------------
    // CONFIGURACION DE SEEDS
    // --------------------------------------------------------------------
    'seeds' => [
        'path' => dirname(__DIR__, 2) . '/database/seeds',
    ],
];