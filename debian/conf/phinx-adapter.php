<?php

/**
 * Multi AbraFlexi Setup - Phinx database adapter.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2021 Vitex Software
 */


include_once '/usr/share/php/EaseCore/Atom.php';
include_once '/usr/share/php/EaseCore/Shared.php';
include_once '/usr/share/php/EaseCore/Molecule.php';
include_once '/usr/share/php/EaseCore/Logger/Logging.php';
include_once '/usr/share/php/EaseCore/Sand.php';
include_once '/usr/share/php/EaseCore/Functions.php';
include_once '/usr/share/php/EaseCore/Logger/Message.php';
include_once '/usr/share/php/EaseCore/Logger/Loggingable.php';
include_once '/usr/share/php/EaseCore/Logger/Loggingable.php';
include_once '/usr/share/php/EaseCore/Logger/ToMemory.php';
include_once '/usr/share/php/EaseCore/RecordKey.php';
include_once '/usr/share/php/EaseCore/Brick.php';
include_once '/usr/share/php/EaseCore/Person.php';
include_once '/usr/share/php/EaseCore/Anonym.php';
include_once '/usr/share/php/EaseCore/User.php';
include_once '/usr/share/php/EaseCore/Logger/ToStd.php';
include_once '/usr/share/php/EaseCore/Logger/ToSyslog.php';
include_once '/usr/share/php/EaseCore/Logger/ToConsole.php';
include_once '/usr/share/php/EaseCore/Logger/Regent.php';
include_once '/usr/share/php/EaseCore/Logger/ToMemory.php';
include_once '/usr/share/php/EaseCore/Exception.php';
include_once '/usr/share/php/EaseFluentPDO/Orm.php';
include_once '/usr/share/php/EaseFluentPDO/Engine.php';


if (file_exists('/etc/multiflexi/multiflexi.env')) {
    \Ease\Shared::instanced()->loadConfig('/etc/multiflexi/multiflexi.env', true);
}

$prefix = "/usr/lib/multiflexi/db/";

$sqlOptions = [];

if (strstr(\Ease\Functions::cfg('DB_CONNECTION'), 'sqlite')) {
    $sqlOptions["database"] = "/var/lib/dbconfig-common/sqlite3/multiflexi/" . basename(\Ease\Functions::cfg("DB_DATABASE"));
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
