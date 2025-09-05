<?php

return [
    'paths' => [
        'migrations' => 'database/migrations', // Carpeta para migraciones
        'seeds'      => 'database/seeds',      // Carpeta para seeds
    ],

    'environments' => [
        'default_migration_table' => 'phinxlog', // Tabla interna de control
        'default_environment'     => 'development',

        'development' => [
            'adapter' => 'mysql',         // Tipo de base de datos (no "think")
            'host'    => '127.0.0.1',     // Mismo que en database.php
            'name'    => 'thinkphp8',     // Mismo que en database.php
            'user'    => 'root',          // Mismo que en database.php
            'pass'    => 'H@num3n1',      // Mismo que en database.php
            'port'    => '3306',          // Mismo que en database.php
            'charset' => 'utf8mb4',
        ],
    ],
];
