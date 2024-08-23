<?php

/**
 * Multi Flexi - Job ID to launch script generator.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
 */

namespace MultiFlexi;

use Ease\Anonym;
use Ease\Shared;
use MultiFlexi\Job;

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['console', 'syslog', '\MultiFlexi\LogToSQL'];
if (\Ease\Shared::cfg('ZABBIX_SERVER') && \Ease\Shared::cfg('ZABBIX_HOST')  && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}
define('EASE_LOGGER', implode('|', $loggers));
Shared::user(new Anonym());
$jobber = new Job($argc == 2 ? intval($argv[1]) : null);
//$jobber->logBanner();
if ($jobber->getMyKey()) {
    echo $jobber->envFile();
} else {
    echo "job ID missing\n";
    exit(1);
}
