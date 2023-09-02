<?php

/**
 * Multi Flexi - Scheduled actions executor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi;

require_once '../vendor/autoload.php';
\Ease\Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');

$loggers = ['syslog', '\AbraFlexi\MultiFlexi\LogToSQL'];
if (\Ease\Functions::cfg('ZABBIX_SERVER')) {
    $loggers[] = '\AbraFlexi\MultiFlexi\LogToZabbix';
}
define('EASE_LOGGER', implode('|', $loggers));


\Ease\Shared::user(new \Ease\Anonym());
$scheduler = new Scheduler();
$scheduler->logBanner('MultiFlexi Daemon started');
while (\Ease\Functions::cfg('DAEMONIZE', true)) {

    foreach ($scheduler->getCurrentJobs() as $scheduledJob) {
        $job = new Job( $scheduledJob['job']);
        $job->performJob();
        $scheduler->deleteFromSQL($scheduledJob['id']);
        $job->cleanUp();
    }

    sleep(\Ease\Functions::cfg("CYCLE_PAUSE", 10));
}
$scheduler->logBanner('MultiFlexi Daemon ended');

