<?php

/**
 * Multi Flexi - Scheduled actions executor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
 */

namespace MultiFlexi;

require_once '../vendor/autoload.php';
\Ease\Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$daemonize = boolval(\Ease\Shared::cfg('MULTIFLEXI_DAEMONIZE', true));
$loggers = ['syslog', '\MultiFlexi\LogToSQL'];
if (\Ease\Shared::cfg('ZABBIX_SERVER') && \Ease\Shared::cfg('ZABBIX_HOST')  && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}
if (\Ease\Shared::cfg('APP_DEBUG') == 'true') {
    $loggers[] = 'console';
}
define('EASE_LOGGER', implode('|', $loggers));
\Ease\Shared::user(new \Ease\Anonym());

$scheduler = new Scheduler();
$scheduler->logBanner('MultiFlexi Daemon started');

do {
    foreach ($scheduler->getCurrentJobs() as $scheduledJob) {
        $job = new Job($scheduledJob['job']);
        $job->performJob();
        $scheduler->deleteFromSQL($scheduledJob['id']);
        $job->cleanUp();
    }
    if ($daemonize) {
        sleep(\Ease\Shared::cfg("MULTIFLEXI_CYCLE_PAUSE", 10));
    }
} while ($daemonize);

$scheduler->logBanner('MultiFlexi Daemon ended');
