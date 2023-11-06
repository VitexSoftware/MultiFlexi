<?php

/**
 * Multi Flexi - Json to application importer
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['console', 'syslog', '\MultiFlexi\LogToSQL'];
if (Functions::cfg('ZABBIX_SERVER')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}
define('EASE_LOGGER', implode('|', $loggers));
Shared::user(new Anonym());
$apper = new Application($argc == 2 ? intval($argv[1]) : null);
if(\Ease\Shared::cfg('APP_DEBUG')){
    $apper->logBanner();
}
if ($apper->getMyKey()) {
    echo $apper->importAppJson(2);
} else {
    echo "app ID missing\n";
    exit(1);
}


