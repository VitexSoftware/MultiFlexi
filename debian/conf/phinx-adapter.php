<?php

/**
 * Multi AbraFlexi Setup - Phinx database adapter.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2021 Vitex Software
 */

include_once '/var/lib/composer/multi-abraflexi-setup/autoload.php';

$shared = \Ease\Shared::instanced();
if (file_exists('/etc/multi-abraflexi-setup/.env')) {
    $shared->loadConfig('/etc/multi-abraflexi-setup/.env', true);
}

$prefix = "/usr/lib/multi-abraflexi-setup/db/";

$sqlOptions = [];

if (strstr(\Ease\Functions::cfg('DB_CONNECTION'), 'sqlite')) {
 $sqlOptions["database"] = "/var/lib/dbconfig-common/sqlite3/multi-abraflexi-setup/".basename(\Ease\Functions::cfg("DB_DATABASE"));
}
$engine = new \Ease\SQL\Engine(null, $sqlOptions);
$cfg = [
    'paths' => [
        'migrations' => [$prefix . 'migrations'],
        'seeds' => [$prefix . 'seeds']
    ],
    'environments' =>
    [
        'default_database' => 'development',
        'development' => [
            'adapter' => \Ease\Functions::cfg('DB_CONNECTION'),
            'name' => $engine->database,
            'connection' => $engine->getPdo($sqlOptions)
        ],
        'default_database' => 'production',
        'production' => [
            'adapter' => \Ease\Functions::cfg('DB_CONNECTION'),
            'name' => $engine->database,
            'connection' => $engine->getPdo($sqlOptions)
        ],
    ]
];

return $cfg;
