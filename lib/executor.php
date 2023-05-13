<?php

/**
 * Multi Flexi - Scheduled actions executor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
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

\Ease\Shared::user(new \Ease\Anonym());

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
            $companer->addStatusMessage(sprintf(_('No applications to run for %s in interval %s'), $company['nazev'],$interval), 'debug');
        } else {

            $companyEnver = new \AbraFlexi\MultiFlexi\CompanyEnv($company['company_id']);
            
            $appEnvironment = array_merge([
                'ABRAFLEXI_URL' => $company['url'],
                'ABRAFLEXI_LOGIN' => $company['user'],
                'ABRAFLEXI_PASSWORD' => $company['password'],
                'ABRAFLEXI_COMPANY' => $company['company'],
                'LC_ALL' => 'cs_CZ', //TODO: Configure somehow
                'EASE_EMAILTO' => $company['email'],
                'EASE_LOGGER' => empty($company['email']) ? 'console|syslog' : 'console|syslog|email',
            ], $companyEnver->getData());

            
            foreach ($appEnvironment as $envName => $sqlValue) {
                $companer->addStatusMessage(sprintf(_('Setting Environment: export %s=%s'), $envName, $sqlValue), 'debug');
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
                    $cmdparams = str_replace('{' . $cfgRaw['name'] . '}', $cfgRaw['value'], $cmdparams);
                    $appEnvironment[$cfgRaw['name']] = $cfgRaw['value'];
                }

                $exec = $app->getDataValue('executable');
                $companer->addStatusMessage('command begin: ' . $exec . ' ' . $cmdparams . '@' . $company['nazev']);

                $jobber = new Job();
                $runId = $jobber->runBegin($app->getMyKey(), $company['company_id'], $appEnvironment);
                $process = new \Symfony\Component\Process\Process(array_merge([$exec], explode(' ', $cmdparams)), null, $appEnvironment, null, 32767);
                $process->run(function ($type, $buffer) {
                    $logger = new \Ease\Sand();
                    $logger->setObjectName('Runner');
                    if (\Symfony\Component\Process\Process::ERR === $type) {
                        $logger->addStatusMessage($buffer, 'error');
                    } else {
                        $logger->addStatusMessage($buffer, 'success');
                    }
                });
                $app->addStatusMessage('end' . $exec . '@' . $app->getDataValue('name'));
                $jobber->runEnd($runId, $process->getExitCode(), $process->getOutput(), $process->getErrorOutput());
                $companer->addStatusMessage('command end: ' . $exec . '@' . $company['nazev']);
            }
        }
    }
} else {
    echo "interval y/m/w/d/h missing\n";
    exit(1);
}
