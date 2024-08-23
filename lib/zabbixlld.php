<?php

/**
 * Multi Flexi - Zabbix Low Level Discovery
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023-2024 Vitex Software
 */

namespace MultiFlexi;

use Ease\Anonym;
use Ease\Functions;
use Ease\Shared;
use MultiFlexi\Application;

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['syslog', '\MultiFlexi\LogToSQL'];
if (\Ease\Shared::cfg('ZABBIX_SERVER') && \Ease\Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}
define('EASE_LOGGER', implode('|', $loggers));
Shared::user(new Anonym());

$mode = array_key_exists(1, $argv) ? $argv[1] : 'n/a';

$lldData = [];
$ap2c = new \MultiFlexi\RunTemplate();
$companer = new Company();
$apper = new Application();
foreach ($companer->listingQuery()->where('enabled', 1) as $companyData) {
    if ($mode == '-a') {
        $companer->setData($companyData);
        $appsForCompany = $ap2c->getPeriodAppsForCompany($companyData['id']);
        foreach ($appsForCompany as $companyAppData) {
            $apper->loadFromSQL($companyAppData['app_id']);
            $appName = $apper->getRecordName();
            $lldData[] = [
                '{#APPNAME}' => $appName,
                '{#INTERVAL}' => Job::codeToInterval($companyAppData['interv']),
                '{#COMPANY_NAME}' => $companyData['name'],
                '{#COMPANY_CODE}' => $companyData['code'],
                '{#COMPANY_SERVER}' => \Ease\Shared::cfg('ZABBIX_HOST')
            ];
        }
    } else {
        $lldData[] = [
            '{#COMPANY_NAME}' => $companyData['name'],
            '{#COMPANY_CODE}' => $companyData['code'],
            '{#COMPANY_SERVER}' => \Ease\Shared::cfg('ZABBIX_HOST')
        ];
    }
}
echo json_encode($lldData, JSON_PRETTY_PRINT);
