<?php

/**
 * Multi Flexi - Cron Scheduled actions executor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi;

use MultiFlexi\Company;
use Ease\Anonym;
use Ease\Shared;

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['syslog', '\MultiFlexi\LogToSQL', 'console'];
if (\Ease\Shared::cfg('ZABBIX_SERVER') && \Ease\Shared::cfg('ZABBIX_HOST')  && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}
if (\Ease\Shared::cfg('APP_DEBUG') == 'true') {
    $loggers[] = 'console';
}
define('EASE_LOGGER', implode('|', $loggers));
define('APP_NAME', 'MultiFlexi trigger');
Shared::user(new Anonym());

$shortopts = "";
$shortopts .= "a:";  // Required value Application ID
$shortopts .= "c:";  // Required value Company Code
$shortopts .= "s::"; // Shedule time
$shortopts .= "e::"; // Optional value Foreground
$shortopts .= "v"; // Optional value Verbose
$shortopts .= "f"; // These options do not accept values

$longopts = [
    "app:", // Required value
    "company:", // Required value
    "environment::", // Optional value
    "schedule::", // Optional value
    "foreground", // Optional value
    "verbose", // No value
];
$options = getopt($shortopts, $longopts);

$jobber = new Job();
if (\Ease\Shared::cfg('APP_DEBUG', array_key_exists('v', $options))) {
    $jobber->logBanner();
}

$companer = new Company();
$companer->setKeyColumn('code');
$companer->loadFromSQL((array_key_exists('company', $options) ? $options['company'] : '') . (array_key_exists('c', $options) ? $options['c'] : ''));
$companer->setKeyColumn('id');

$apper = new Application(intval((array_key_exists('app', $options) ? $options['app'] : '') . (array_key_exists('a', $options) ? $options['a'] : '')));

$companyId = $companer->getMyKey();
$appId = $apper->getMyKey();

if ($companyId && $appId) {
    $runTemplate = new \MultiFlexi\RunTemplate();
    if ($companyId && $appId) {
        $runTemplateId = $runTemplate->runTemplateID($appId, $companyId);
        if ($runTemplateId == 0) {
            $runTemplate->dbsync(['app_id' => $appId, 'company_id' => $companyId, 'interv' => 'n']);
        } else {
            $runTemplate->loadFromSQL($runTemplateId);
        }
    }

    LogToSQL::singleton()->setCompany($companyId);

    $forcedEnv = (array_key_exists('environment', $options) ? $options['environment'] : '') . (array_key_exists('e', $options) ? $options['e'] : '');

    $jobber->prepareJob($runTemplate->getMyKey(), empty($forcedEnv) ? [] : json_decode($forcedEnv), 'trigger');
    if (array_key_exists('f', $options) || array_key_exists('foreground', $options)) {
        $jobber->performJob();
    } else {
        $jobber->scheduleJobRun(new \DateTime((array_key_exists('schedule', $options) ? $options['schedule'] : '') . (array_key_exists('s', $options) ? $options['s'] : '')));
    }
} else {
    echo "Arguments: \n" .
    "--app APP_ID - required\n" .
    "--company COMPANY_CODE - required\n" .
    "--environment {json}\n" .
    "--schedule - A date/time string for \DateTime()  \n" .
    "--foreground - do not schdule for background run\n" .
    "--verbose - more debug messages \n";
    exit(1);
}
