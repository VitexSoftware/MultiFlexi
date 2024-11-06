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

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['syslog', '\MultiFlexi\LogToSQL'];

if (\Ease\Shared::cfg('ZABBIX_SERVER') && \Ease\Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

\define('EASE_LOGGER', implode('|', $loggers));
Shared::user(new Anonym());

$mode = \array_key_exists(1, $argv) ? $argv[1] : 'n/a';

$lldData = [];
$runTemplater = new \MultiFlexi\RunTemplate();

$enabled = $runTemplater->listingQuery()->disableSmartJoin()->where("success LIKE '%\"Zabbix\";b:1;%'")->whereOr("fail LIKE '%\"Zabbix\";b:1;%'");

$configer = new ActionConfig();

foreach ($enabled as $runTemplateData) {
    $succesActions = unserialize($runTemplateData['success']);
    $failActions = unserialize($runTemplateData['fail']);
    $lldData[] = [
        '{#RUN_TEMPLATE_ID}' => $runTemplateData['id'],
        '{#ZABBIX_KEY_SUCCESS}' => $succesActions['Zabbix'] ? $configer->listingQuery()->select('value', true)->where('module', 'Zabbix')->where('keyname', 'key')->where('runtemplate_id', $runTemplateData['id'])->where('mode', 'success')->fetchColumn() : '',
        '{#ZABBIX_KEY_FAIL}' => $failActions['Zabbix'] ? $configer->listingQuery()->select('value', true)->where('module', 'Zabbix')->where('keyname', 'key')->where('runtemplate_id', $runTemplateData['id'])->where('mode', 'fail')->fetchColumn() : '',
    ];
}

echo json_encode($lldData, \JSON_PRETTY_PRINT);
