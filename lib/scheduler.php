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

date_default_timezone_set('Europe/Prague');

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['syslog', '\MultiFlexi\LogToSQL'];

if (Shared::cfg('ZABBIX_SERVER') && Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

if (strtolower(Shared::cfg('APP_DEBUG', 'False')) === 'true') {
    $loggers[] = 'console';
}

\define('EASE_LOGGER', implode('|', $loggers));
$interval = $argc === 2 ? $argv[1] : null;
\define('APP_NAME', 'MultiFlexi scheduler '.RunTemplate::codeToInterval($interval));
Shared::user(new Anonym());

$jobber = new Job();

if (Shared::cfg('APP_DEBUG')) {
    $jobber->logBanner();
}

if (\MultiFlexi\Runner::isServiceActive('multiflexi') === false) {
    $jobber->addStatusMessage(_('systemd service is not running. Consider `systemctl start multiflexi`'), 'warning');
}

$companer = new Company();
$companies = $companer->listingQuery();
$customConfig = new Configuration();

if ($interval) {
    $runtemplate = new \MultiFlexi\RunTemplate();

    foreach ($companies as $company) {
        LogToSQL::singleton()->setCompany($company['id']);

        $appsForCompany = $runtemplate->getColumnsFromSQL(['id', 'interv', 'delay', 'name', 'executor'], ['company_id' => $company['id'], 'interv' => $interval, 'active' => true]);

        if (empty($appsForCompany) && ($interval !== 'i')) {
            $companer->addStatusMessage(sprintf(_('No applications to run for %s in interval %s'), $company['name'], $interval), 'debug');
        } else {
            if (Shared::cfg('APP_DEBUG') === 'true') {
                $jobber->addStatusMessage(sprintf(_('%s Scheduler interval %s begin'), $company['name'], $interval), 'debug');
            }

            foreach ($appsForCompany as $runtemplateData) {
                if (null !== $interval && ($interval !== $runtemplateData['interv'])) {
                    continue;
                }

                $startTime = new \DateTime();
                $jobber->addStatusMessage('Now Is '.$startTime->format('Y-m-d H:i:s'), 'debug');

                if (empty($runtemplateData['delay']) === false) {
                    $startTime->modify('+'.$runtemplateData['delay'].' seconds');
                    $jobber->addStatusMessage('Adding Startup delay  +'.$runtemplateData['delay'].' seconds to '.$startTime->format('Y-m-d H:i:s'), 'debug');
                }

                $jobber->prepareJob($runtemplateData['id'], new ConfigFields(''), $startTime, $runtemplateData['executor'], RunTemplate::codeToInterval($interval));
                $jobber->scheduleJobRun($startTime);
                $jobber->addStatusMessage('🧩 #'.$jobber->application->getMyKey()."\t".$jobber->application->getRecordName().':'.$runtemplateData['name'].' (runtemplate #'.$runtemplateData['id'].') - '.sprintf(_('Launch %s for 🏣 %s'), $startTime->format(\DATE_RSS), $company['name']));
            }

            if (Shared::cfg('APP_DEBUG') === 'true') {
                $jobber->addStatusMessage(sprintf(_('%s Scheduler interval %s end'), $company['name'], RunTemplate::codeToInterval($interval)), 'debug');
            }
        }
    }
} else {
    echo "interval i/y/m/w/d/h missing\n";

    exit(1);
}
