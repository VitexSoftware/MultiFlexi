<?php

/**
 * Multi FlexiBee Setup - Phinx database adapter.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */
if (file_exists('./vendor/autoload.php')) {
    include_once './vendor/autoload.php';
} else {
    include_once '../vendor/autoload.php';
}

//$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv = \Dotenv\Dotenv::create(__DIR__);
$dotenv->load();


if (strstr(getenv('DB_CONNECTION'), 'sqlite')) {
    $engine = new \Ease\SQL\Engine(null, ['database' => '../db/' . basename(getenv('DB_DATABASE'))]);
} else {
    $engine = new \Ease\SQL\Engine(null);
}

$cfg = [
    'paths' => [
        'migrations' => ['../db/migrations'],
        'seeds' => ['../db/seeds']
    ],
    'environments' =>
    [
        'default_database' => 'development',
        'development' => [
            'adapter' => getenv('DB_CONNECTION'),
            'name' => $engine->database,
            'connection' => $engine->getPdo()
        ],
        'default_database' => 'production',
        'production' => [
            'adapter' => getenv('DB_CONNECTION'),
            'name' => $engine->database,
            'connection' => $engine->getPdo()
        ],
    ]
];

return $cfg;
