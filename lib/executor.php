<?php

/**
 * Multi FlexiBee Setup - Scheduled actions executor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup;

use Dotenv\Dotenv;
use FlexiPeeHP\MultiSetup\Application;
use FlexiPeeHP\MultiSetup\Company;
use FlexiPeeHP\MultiSetup\Configuration;

require_once '../vendor/autoload.php';

//$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv = Dotenv::create(dirname(__DIR__));
$dotenv->load();

define('EASE_LOGGER', 'syslog|\FlexiPeeHP\MultiSetup\LogToSQL');
//Sdefine('EASE_LOGGER', '\FlexiPeeHP\MultiSetup\LogToSQL');

$companer = new Company();
$companys = $companer->listingQuery()->select('flexibees.*')->select('company.id AS company_id')->leftJoin('flexibees ON flexibees.id = company.flexibee');
$customConfig = new Configuration();

$interval = $argc == 2 ? $argv[1] : null;

if ($interval) {
    $ap2c = new AppToCompany();
    foreach ($companys as $company) {
        LogToSQL::singleton()->setCompany($company['company_id']);
        $appsForCompany = $ap2c->getColumnsFromSQL(['*'], ['company_id' => $company['company_id'], 'interval' => $interval]);

        if (empty($appsForCompany)) {
            $companer->addStatusMessage(sprintf(_('No applications enabled for %s'), $company['nazev']), 'warning');
        } else {

            $envNames = [
                'FLEXIBEE_URL' => $company['url'],
                'FLEXIBEE_LOGIN' => $company['user'],
                'FLEXIBEE_PASSWORD' => $company['password'],
                'FLEXIBEE_COMPANY' => $company['company'],
                'EASE_MAILTO' => $company['email'],
                'EASE_LOGGER' => empty($company['email']) ? 'syslog' : 'syslog|email',
            ];

            foreach ($envNames as $envName => $sqlValue) {
                $companer->addStatusMessage(sprintf(_('Setting Environment %s to %s'), $envName, $sqlValue), 'debug');
                putenv($envName . '=' . $sqlValue);
            }

            foreach ($appsForCompany as $servData) {
                if (!is_null($interval) && ($interval != $servData['interval'])) {
                    continue;
                }

                $app = new Application(intval($servData['app_id']));
                LogToSQL::singleton()->setApplication($app->getMyKey());

                foreach ($customConfig->getColumnsFromSQL(['key', 'value'], ['company_id' => $company['company_id'], 'app_id' => $app->getMyKey()]) as $cfgRaw) {
                    $companer->addStatusMessage(sprintf(_('Setting Environment %s to %s'), $cfgRaw['key'], $cfgRaw['value']), 'debug');
                    putenv($cfgRaw['key'] . '=' . $cfgRaw['value']);
                }

                $exec = $app->getDataValue('executable');
                $companer->addStatusMessage('begin' . $exec . '@' . $company['nazev']);

                foreach (explode("\n", shell_exec($exec)) as $row) {
                    $companer->addStatusMessage($row, 'debug');
                }

                $companer->addStatusMessage('end' . $exec . '@' . $company['nazev']);
            }
        }
    }
} else {
    echo "interval y/m/w/d/h missing\n";
    exit(1);
}
