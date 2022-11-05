<?php

/**
 * Multi Flexi - Scheduled actions executor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

use AbraFlexi\MultiFlexi\Application;
use AbraFlexi\MultiFlexi\Company;
use AbraFlexi\MultiFlexi\Configuration;

require_once '../vendor/autoload.php';

$shared = \Ease\Shared::instanced();
if (file_exists('../.env')) {
    $shared->loadConfig('../.env', true);
}

define('EASE_LOGGER', 'syslog|\AbraFlexi\MultiFlexi\LogToSQL');
//Sdefine('EASE_LOGGER', '\AbraFlexi\MultiFlexi\LogToSQL');

$companer = new Company();
$companys = $companer->listingQuery()->select('abraflexis.*')->select('company.id AS company_id')->leftJoin('abraflexis ON abraflexis.id = company.abraflexi');
$customConfig = new Configuration();

$interval = $argc == 2 ? $argv[1] : null;

if ($interval) {
    $ap2c = new AppToCompany();
    foreach ($companys as $company) {
        LogToSQL::singleton()->setCompany($company['company_id']);
        $appsForCompany = $ap2c->getColumnsFromSQL(['*'], ['company_id' => $company['company_id'], 'interv' => $interval]);

        if (empty($appsForCompany)) {
            $companer->addStatusMessage(sprintf(_('No applications enabled for %s'), $company['nazev']), 'warning');
        } else {

            $envNames = [
                'ABRAFLEXI_URL' => $company['url'],
                'ABRAFLEXI_LOGIN' => $company['user'],
                'ABRAFLEXI_PASSWORD' => $company['password'],
                'ABRAFLEXI_COMPANY' => $company['company'],
                'EASE_EMAILTO' => $company['email'],
                'EASE_LOGGER' => empty($company['email']) ? 'syslog' : 'syslog|email',
            ];

            foreach ($envNames as $envName => $sqlValue) {
                $companer->addStatusMessage(sprintf(_('Setting Environment: export %s=%s'), $envName, $sqlValue), 'debug');
                putenv($envName . '=' . $sqlValue);
            }

            foreach ($appsForCompany as $servData) {
                if (!is_null($interval) && ($interval != $servData['interv'])) {
                    continue;
                }

                $app = new Application(intval($servData['app_id']));
                LogToSQL::singleton()->setApplication($app->getMyKey());

                $cmdparams = $app->getDataValue('cmdparams');
                foreach ($customConfig->getColumnsFromSQL(['name', 'value'], ['company_id' => $company['company_id'], 'app_id' => $app->getMyKey()]) as $cfgRaw) {
                    $companer->addStatusMessage(sprintf(_('Setting custom Environment: export %s=%s'), $cfgRaw['name'], $cfgRaw['value']), 'debug');
                    putenv($cfgRaw['name'] . '=' . $cfgRaw['value']);
                    $cmdparams = str_replace('{' . $cfgRaw['name'] . '}', $cfgRaw['value'], $cmdparams);
                }

                $exec = $app->getDataValue('executable');
                $companer->addStatusMessage('command begin: ' . $exec . ' ' . $cmdparams . '@' . $company['nazev']);

                foreach (explode("\n", shell_exec($exec . ' ' . $cmdparams)) as $row) {
                    $companer->addStatusMessage($row, 'debug');
                }

                $companer->addStatusMessage('command end: ' . $exec . '@' . $company['nazev']);
            }
        }
    }
} else {
    echo "interval y/m/w/d/h missing\n";
    exit(1);
}
