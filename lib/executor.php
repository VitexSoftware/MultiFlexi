<?php
/**
 * Multi Flexi - Cron Scheduled actions executor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi;

use \MultiFlexi\Company,
    \MultiFlexi\Configuration,
    \Ease\Anonym,
    \Ease\Shared;

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['syslog', '\MultiFlexi\LogToSQL', 'console'];
if (\Ease\Functions::cfg('ZABBIX_SERVER') && \Ease\Functions::cfg('ZABBIX_HOST')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}
if (\Ease\Functions::cfg('APP_DEBUG') == 'true') {
    $loggers[] = 'console';
}
define('EASE_LOGGER', implode('|', $loggers));
$interval = $argc == 2 ? $argv[1] : null;
define('APP_NAME', 'MultiFlexi executor ' . Job::codeToInterval($interval));
Shared::user(new Anonym());

$jobber = new Job();
if (\Ease\Shared::cfg('APP_DEBUG')) {
    $jobber->logBanner(\Ease\Shared::appName() . ' Interval: ' . Job::codeToInterval($interval));
}

if(\MultiFlexi\Runner::isServiceActive('multiflexi') === false){
    $jobber->addStatusMessage(_('systemd service is not running. Consider `systemctl start multiflexi`'),'warning');
}

$companer = new Company();
$companys = $companer->listingQuery()->select('servers.*')->select('company.id AS company_id')->leftJoin('servers ON servers.id = company.server');
$customConfig = new Configuration();
if ($interval) {
    $ap2c = new \MultiFlexi\RunTemplate();
    foreach ($companys as $company) {
        LogToSQL::singleton()->setCompany($company['company_id']);
        $appsForCompany = $ap2c->getColumnsFromSQL(['id', 'interv'], ['company_id' => $company['company_id'], 'interv' => $interval]);
        if (empty($appsForCompany) && ($interval != 'i')) {
            $companer->addStatusMessage(sprintf(_('No applications to run for %s in interval %s'), $company['name'], $interval), 'debug');
        } else {

            foreach ($appsForCompany as $servData) {
                if (!is_null($interval) && ($interval != $servData['interv'])) {
                    continue;
                }
                $jobber->prepareJob($servData['id'],[], Job::codeToInterval($interval));
                $jobber->performJob();
            }
        }
    }
} else {
    echo "interval y/m/w/d/h missing\n";
    exit(1);
}
