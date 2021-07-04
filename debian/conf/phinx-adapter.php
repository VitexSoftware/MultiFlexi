<?php

/**
 * Multi AbraFlexi Setup - Phinx database adapter.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2021 Vitex Software
 */


include_once '/usr/share/php/EaseCore/Atom.php';
include_once '/usr/share/php/EaseCore/Molecule.php';
include_once '/usr/share/php/EaseCore/Sand.php';
include_once '/usr/share/php/EaseCore/Brick.php';
include_once '/usr/share/php/EaseFluentPDO/Orm.php';
include_once '/usr/share/php/EaseFluentPDO/Engine.php';


/**
 * Get configuration from constant or environment
 * 
 * @param string $constant
 * 
 * @return string
 */
function cfg($constant) {
    $cfg = null;
    if (!empty($constant) && defined($constant)) {
        $cfg = constant($constant);
    } elseif (isset($_ENV) && array_key_exists($constant, $_ENV)) {
        $cfg = $_ENV[$constant];
    } elseif (($env = getenv($constant)) && !empty($env)) {
        $cfg = getenv($constant);
    }
    return $cfg;
}

/**
 * Load Configuration values from json file $this->configFile and define UPPERCASE keys
 *
 * @param string  $configFile      Path to file with configuration
 *
 * @return array full configuration array
 */
function loadConfig($configFile, $defineConstants) {
    foreach (file($configFile) as $cfgRow) {
        if (strchr($cfgRow, '=')) {
            list($key, $value) = explode('=', $cfgRow);
            $configuration[$key] = trim($value, " \t\n\r\0\x0B'\"");
            define($key, $configuration[$key]);
        }
    }
}

if (file_exists('/etc/multiflexi/.env')) {
    loadConfig('/etc/multiflexi/.env', true);
}

$prefix = "/usr/lib/multiflexi/db/";

$sqlOptions = [];

if (strstr(cfg('DB_CONNECTION'), 'sqlite')) {
    $sqlOptions["database"] = "/var/lib/dbconfig-common/sqlite3/multiflexi/" . basename(cfg("DB_DATABASE"));
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
            'adapter' => cfg('DB_CONNECTION'),
            'name' => $engine->database,
            'connection' => $engine->getPdo($sqlOptions)
        ],
        'default_database' => 'production',
        'production' => [
            'adapter' => cfg('DB_CONNECTION'),
            'name' => $engine->database,
            'connection' => $engine->getPdo($sqlOptions)
        ],
    ]
];

return $cfg;
