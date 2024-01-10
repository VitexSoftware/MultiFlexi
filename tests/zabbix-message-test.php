<?php

/**
 * Multi Flexi - Logging (to Zabbix) tester
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2024 Vitex Software
 */

namespace MultiFlexi;

use \Ease\Anonym,
    \Ease\Functions,
    \Ease\Shared,
    \MultiFlexi\Job;

define('APP_NAME', 'ZabbixTester');
require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['console', 'syslog', '\MultiFlexi\LogToSQL'];
if (Functions::cfg('ZABBIX_SERVER')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
} 
define('EASE_LOGGER', implode('|', $loggers));
Shared::user(new Anonym());
$jobber = new Job();

$jobber->addStatusMessage(_('Test Info Message'), 'info');
$jobber->addStatusMessage(_('Test Success Message'), 'success');
$jobber->addStatusMessage(_('Test Debug Message'), 'debug');
$jobber->addStatusMessage(_('Test Errir Message'), 'error');

if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
    $jobber->reportToZabbix(['phase' => 'test', 'stdout' => 'stdout test', 'stderr' => 'stderr test', 'exitcode' => 666, 'end' => (new \DateTime())->format('Y-m-d H:i:s')]);
} else {
    $jobber->addStatusMessage(_('ZABBIX_SERVER not configured'), 'warning');
}
