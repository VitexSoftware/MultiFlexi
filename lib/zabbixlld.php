<?php

/**
 * Multi Flexi - Zabbix Low Level Discovery
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi;

use \Ease\Anonym,
    \Ease\Functions,
    \Ease\Shared,
    \MultiFlexi\Application;

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['console', 'syslog', '\MultiFlexi\LogToSQL'];
if (Functions::cfg('ZABBIX_SERVER')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}
define('EASE_LOGGER', implode('|', $loggers));
Shared::user(new Anonym());
$lldData = [];
$ap2c = new \MultiFlexi\RunTemplate();
$companer = new Company();
$apper = new Application();
foreach ($companer->listingQuery()->where('enabled', 1) as $companyData) {
    $companer->setData($companyData);
    $appsForCompany = $ap2c->getAppsForCompany($companyData['id']);
    foreach ($appsForCompany as $companyAppData) {
        $apper->loadFromSQL($companyAppData['app_id']);
        $appName = $apper->getRecordName();
        $lldData[] = [
            '{#APPNAME}' => $appName,
            '{#INTERVAL}' => Job::codeToInterval($companyAppData['interv']),
            '{#COMPANY}' => $companyData['name']
        ];
    }
}
echo json_encode($lldData, JSON_PRETTY_PRINT);
