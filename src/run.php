<?php

/**
 * Multi Flexi - Command Runner.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2022 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use AbraFlexi\MultiFlexi\Application;
use AbraFlexi\MultiFlexi\Ui\PageBottom;
use AbraFlexi\MultiFlexi\Ui\PageTop;
use AbraFlexi\MultiFlexi\Ui\RegisterAppForm;
use Symfony\Component\Process\Process;

require_once './init.php';

$apps = new Application($oPage->getRequestValue('id', 'int'));

$customConfig = new Configuration();

$company = $apps->getCompany();

$envNames = [
    'ABRAFLEXI_URL' => $company['url'],
    'ABRAFLEXI_LOGIN' => $company['user'],
    'ABRAFLEXI_PASSWORD' => $company['password'],
    'ABRAFLEXI_COMPANY' => $company['company'],
    'EASE_MAILTO' => $company['email'],
    'EASE_LOGGER' => empty($company['email']) ? 'syslog' : 'syslog|email',
];

foreach ($envNames as $envName => $sqlValue) {
    $companer->addStatusMessage(sprintf(_('Setting Environment %s to %s'), $envName, $sqlValue), 'debug');
    putenv($envName . '=' . $sqlValue);
}

foreach ($appsForCompany as $servData) {
    if (!is_null($interval) && ($interval != $servData['interv'])) {
        continue;
    }

    $app = new Application(intval($servData['app_id']));
    LogToSQL::singleton()->setApplication($app->getMyKey());

    $cmdparams = $app->getDataValue('cmdparams');
    foreach ($customConfig->getColumnsFromSQL(['name', 'value'], ['company_id' => $company['company_id'], 'app_id' => $app->getMyKey()]) as $cfgRaw) {
        $companer->addStatusMessage(sprintf(_('Setting Environment %s to %s'), $cfgRaw['name'], $cfgRaw['value']), 'debug');
        putenv($cfgRaw['name'] . '=' . $cfgRaw['value']);
        $cmdparams = str_replace('{' . $cfgRaw['name'] . '}', $cfgRaw['value'], $cmdparams);
    }

    $exec = $app->getDataValue('executable');
    $companer->addStatusMessage('begin' . $exec . ' ' . $cmdparams . '@' . $company['nazev']);

    foreach (explode("\n", shell_exec($exec . ' ' . $cmdparams)) as $row) {
        $companer->addStatusMessage($row, 'debug');
    }

    $companer->addStatusMessage('end' . $exec . '@' . $company['nazev']);
}







$process = new Process(['ls', '-lsa']);
$process->run(function ($type, $buffer) {
    if (Process::ERR === $type) {
        echo nl2br($buffer);
    } else {
        echo nl2br($buffer);
    }
});

WebPage::singleton()->addJavascript("$('body').css('font-family', 'Courier');");

$oPage->draw();
