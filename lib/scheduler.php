<?php

/**
 * Multi Flexi - Use Cron to schedule periodical actions.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
 */

namespace MultiFlexi;

use MultiFlexi\Company;
use MultiFlexi\Configuration;
use Ease\Anonym;
use Ease\Shared;
use GO\Scheduler;

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['syslog', '\MultiFlexi\LogToSQL'];
if (\Ease\Shared::cfg('ZABBIX_SERVER') && \Ease\Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}
if (\Ease\Shared::cfg('APP_DEBUG') == 'true') {
    $loggers[] = 'console';
}
define('EASE_LOGGER', implode('|', $loggers));
$interval = $argc == 2 ? $argv[1] : null;
define('APP_NAME', 'MultiFlexi scheduler ' . Job::codeToInterval($interval));
Shared::user(new Anonym());

$jobber = new Job();
if (\Ease\Shared::cfg('APP_DEBUG')) {
    $jobber->logBanner();
}

if (\MultiFlexi\Runner::isServiceActive('multiflexi') === false) {
    $jobber->addStatusMessage(_('systemd service is not running. Consider `systemctl start multiflexi`'), 'warning');
}

$companer = new Company();
$companys = $companer->listingQuery();
$customConfig = new Configuration();
if ($interval) {

    if ($interval == 'i') {
        $scheduler = new Scheduler();
        #TODO: #2
        $scheduler->run();
    }

    $ap2c = new \MultiFlexi\RunTemplate();
    foreach ($companys as $company) {
        LogToSQL::singleton()->setCompany($company['id']);

        $appsForCompany = $ap2c->getColumnsFromSQL(['id', 'interv'], ['company_id' => $company['id'], 'interv' => $interval]);
        if (empty($appsForCompany) && ($interval != 'i')) {
            $companer->addStatusMessage(sprintf(_('No applications to run for %s in interval %s'), $company['name'], $interval), 'debug');
        } else {
            if (\Ease\Shared::cfg('APP_DEBUG') == 'true') {
                $jobber->addStatusMessage(sprintf(_('%s Scheduler interval %s begin'), $company['name'], $interval), 'debug');
            }
            foreach ($appsForCompany as $servData) {
                if (!is_null($interval) && ($interval != $servData['interv'])) {
                    continue;
                }
                $jobber->prepareJob($servData['id'], [], Job::codeToInterval($interval));
                $jobber->scheduleJobRun(new \DateTime());
                $jobber->addStatusMessage('🧩 #' . $jobber->application->getMyKey() . "\t" . $jobber->application->getRecordName() . ' - ' . sprintf(_('Launch now for 🏣 %s'), $company['name']));
            }
            if (\Ease\Shared::cfg('APP_DEBUG') == 'true') {
                $jobber->addStatusMessage(sprintf(_('%s Scheduler interval %s end'), $company['name'], Job::codeToInterval($interval)), 'debug');
            }
        }
    }
} else {
    echo "interval i/y/m/w/d/h missing\n";
    exit(1);
}
