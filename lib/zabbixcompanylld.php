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
use MultiFlexi\Ui\ActionsChooser;

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['syslog', '\MultiFlexi\LogToSQL'];

if (\Ease\Shared::cfg('ZABBIX_SERVER') && \Ease\Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

\define('EASE_LOGGER', implode('|', $loggers));
Shared::user(new Anonym());

$venue = \array_key_exists(1, $argv) ? $argv[1] : '';

if ($venue === '-h') {
    echo _('multiflexi-zabbix-lld [SERVER.COMPANY_CODE]');

    exit;
}

$argParts = explode('.', $venue);
$companyCode = $argParts[\count($argParts) - 1];
$server = str_replace('.'.$companyCode, '', $venue);

$lldData = [];
$rumtemplate = new \MultiFlexi\RunTemplate();
$companer = new Company(['slug' => $companyCode], ['autoload' => true]);
$ca = new \MultiFlexi\CompanyApp($companer);
$apper = new Application();

$companyData = $companer->getData();

$appsAssigned = $ca->getAll()->leftJoin('apps ON apps.id = companyapp.app_id')->select(['apps.name', 'apps.description', 'apps.id', 'apps.image, apps.code, apps.uuid'], true)->fetchAll('id');
$runtemplates = $rumtemplate->listingQuery()->where('runtemplate.active', true)->leftJoin('company ON company.id = runtemplate.company_id')->select(['runtemplate.id', 'interv', 'company.slug AS company_code', 'company.name AS company_name']);

$actions = new \MultiFlexi\ActionConfig();
$succesActions = ActionsChooser::toggles('success');
$failActions = ActionsChooser::toggles('fail');

foreach ($runtemplates as $runtemplateData) {
    if (\strlen($companyCode) && $companyCode !== $runtemplateData['company_code']) {
        continue;
    }

    if (\array_key_exists($runtemplateData['app_id'], $appsAssigned)) {
        $lldData[] = [
            '{#APPNAME}' => $appsAssigned[$runtemplateData['app_id']]['name'],
            '{#APPNAME_CODE}' => $appsAssigned[$runtemplateData['app_id']]['code'],
            '{#APPNAME_UUID}' => $appsAssigned[$runtemplateData['app_id']]['uuid'],
            '{#INTERVAL}' => Scheduler::codeToInterval($runtemplateData['interv']),
            '{#INTERVAL_SECONDS}' => Scheduler::codeToSeconds($runtemplateData['interv']),
            '{#RUNTEMPLATE}' => $runtemplateData['id'],
            '{#RUNTEMPLATE_NAME}' => $runtemplateData['name'],
            '{#COMPANY_NAME}' => $runtemplateData['company_name'],
            '{#COMPANY_CODE}' => $runtemplateData['company_code'],
            '{#COMPANY_SERVER}' => \Ease\Shared::cfg('ZABBIX_HOST'),
            '{#DATA_ITEM}' => false, // TODO
        ];
    } else {
        $rumtemplate->addStatusMessage('Application '.$runtemplateData['app_id'].' is not assigned with company ?');
    }
}

echo json_encode($lldData, \JSON_PRETTY_PRINT);
