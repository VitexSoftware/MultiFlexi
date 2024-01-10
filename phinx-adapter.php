<?php

/**
 * Multi Flexi - Phinx database adapter.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2021-2024 Vitex Software
 */
if (file_exists('./vendor/autoload.php')) {
    include_once './vendor/autoload.php';
} else {
    include_once '../vendor/autoload.php';
}

$shared = \Ease\Shared::instanced();
if (file_exists('../.env')) {
    $shared->loadConfig('../.env', true);
}

$prefix = file_exists('./db/') ? './db/' : '../db/';

$sqlOptions = [];

if (strstr(\Ease\Shared::cfg('DB_CONNECTION'), 'sqlite')) {
    $sqlOptions['database'] = $prefix . basename(\Ease\Shared::cfg('DB_DATABASE'));
}

$engine = new \Ease\SQL\Engine(null, $sqlOptions);
$cfg = [
    'paths' => [
        'migrations' => [$prefix . 'migrations'],
        'seeds' => [$prefix . 'seeds']
    ],
    'environments' =>
    [
        'default_environment' => 'production',
        'development' => [
            'adapter' => \Ease\Shared::cfg('DB_CONNECTION'),
            'name' => $engine->database,
            'connection' => $engine->getPdo($sqlOptions)
        ],
        'production' => [
            'adapter' => \Ease\Shared::cfg('DB_CONNECTION'),
            'name' => $engine->database,
            'connection' => $engine->getPdo($sqlOptions)
        ],
    ]
];

return $cfg;
