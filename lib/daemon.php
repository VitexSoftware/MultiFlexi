<?php

/**
 * Multi Flexi - Scheduled actions executor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

require_once '../vendor/autoload.php';
\Ease\Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
define('EASE_LOGGER', 'syslog|\AbraFlexi\MultiFlexi\LogToSQL');
\Ease\Shared::user(new \Ease\Anonym());
$scheduler = new Scheduler();
while (\Ease\Functions::cfg('DAEMONIZE', true)) {

    foreach ($scheduler->getCurrentJobs() as $scheduledJob) {
        $job = new Job( $scheduledJob['job']);
        $job->performJob();
        $scheduler->deleteFromSQL($scheduledJob['id']);
    }

    sleep(\Ease\Functions::cfg("CYCLE_PAUSE", 10));
}
