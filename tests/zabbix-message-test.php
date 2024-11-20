<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi;

use Ease\Anonym;
use Ease\Shared;

\define('APP_NAME', 'ZabbixTester');

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['console', 'syslog', '\MultiFlexi\LogToSQL'];

if (Shared::cfg('ZABBIX_SERVER')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

\define('EASE_LOGGER', implode('|', $loggers));
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
