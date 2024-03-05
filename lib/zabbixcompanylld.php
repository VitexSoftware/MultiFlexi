<?php

/**
 * Multi Flexi - Zabbix Low Level Discovery for SERVER.COMPANY
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2024 Vitex Software
 */

namespace MultiFlexi;

use \Ease\Anonym,
    \Ease\Functions,
    \Ease\Shared,
    \MultiFlexi\Application;

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['syslog', '\MultiFlexi\LogToSQL'];
if (Functions::cfg('ZABBIX_SERVER')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}
define('EASE_LOGGER', implode('|', $loggers));
Shared::user(new Anonym());

$venue = array_key_exists(1, $argv) ? $argv[1] : '';

if ($venue) {
    $argParts = explode('.', $venue);
    $companyCode = $argParts[count($argParts) - 1];
    $server = str_replace('.' . $companyCode, '', $venue);

    $lldData = [];
    $ap2c = new \MultiFlexi\RunTemplate();
    $companer = new Company(['code' => $companyCode], ['autoload' => true]);
    $ca = new \MultiFlexi\CompanyApp($companer);
    $apper = new Application();

    $companyData = $companer->getData();
    $appsAssigned = $ca->getAssigned()->leftJoin('apps ON apps.id = companyapp.app_id')->select(['apps.name', 'apps.description', 'apps.id', 'apps.image, apps.code, apps.uuid'], true)->fetchAll('id');
    $runtemplates = $ap2c->getPeriodAppsForCompany($companer->getMyKey());

    foreach ($runtemplates as $runtemplateData) {
//        $apper->setData($app);
//        $app['config'] = $apper->getAppEnvironmentFields();
        $lldData[] = [
            '{#APPNAME}' => $appsAssigned[$runtemplateData['app_id']]['name'],
            '{#APPNAME_CODE}' => $appsAssigned[$runtemplateData['app_id']]['code'],
            '{#APPNAME_UUID}' => $appsAssigned[$runtemplateData['app_id']]['uuid'],
            '{#INTERVAL}' => Job::codeToInterval($runtemplateData['interv']),
            '{#RUNTEMPLATE}' => $runtemplateData['id'],
            '{#COMPANY_NAME}' => $companyData['name'],
            '{#COMPANY_CODE}' => $companyData['code'],
            '{#COMPANY_SERVER}' => \Ease\Shared::cfg('ZABBIX_HOST')
        ];
    }
    echo json_encode($lldData, JSON_PRETTY_PRINT);
} else {
    die(_('SERVER.COMPANY_CODE parameter is requied'));
}
