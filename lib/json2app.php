<?php

/**
 * Multi Flexi - Json to application importer
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi;

require_once '../vendor/autoload.php';

\Ease\Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['console', 'syslog', '\MultiFlexi\LogToSQL'];
if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}
define('EASE_LOGGER', implode('|', $loggers));
define('APP_NAME', 'MultiFlexi json2app');

\Ease\Shared::user(new \Ease\Anonym());
if (array_key_exists(1, $argv) && file_exists($argv[1])) {
    $apper = new Application($argc == 3 ? intval($argv[3]) : null);
    if (\Ease\Shared::cfg('APP_DEBUG')) {
        $apper->logBanner();
    }
    if (empty($apper->importAppJson($argv[1]))) {
        $apper->addStatusMessage(_('Error importing application json'), 'error');
        exit(1);
    }
} else {
    echo "usage: app.template.json [app id]";
}
