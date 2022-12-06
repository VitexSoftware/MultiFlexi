<?php

/**
 * Multi Flexi - Command Runner.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2022 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

use AbraFlexi\MultiFlexi\Application;
use Symfony\Component\Process\Process;

require_once './init.php';

$appCompany = new AppToCompany($oPage->getRequestValue('id', 'int'));
$appInfo = $appCompany->getAppInfo();
$cmdparams = array_key_exists('cmdparams', $appInfo) ? $appInfo['cmdparams'] : '';
$appEnvironment = $appCompany->getAppEnvironment();
foreach ($appEnvironment as $envName => $envValue) {
    if ($envName == strtoupper($envName)) {
        if (strtolower(\Ease\Functions::cfg('APP_DEBUG')) == 'true') {
            $appCompany->addStatusMessage(sprintf(_('Setting Environment %s to %s'), $envName, $envValue), 'debug');
        }
        putenv($envName . '=' . $envValue);
    }
    $cmdparams = str_replace('{' . $envName . '}', $envValue, $cmdparams);
}

$exec = $appInfo['executable'];
$appCompany->addStatusMessage('begin' . $exec . ' ' . $cmdparams . '@' . $appInfo['nazev']);

echo new \Ease\Html\H2Tag(str_replace(' ', '&nbsp;', $exec . ' ' . $cmdparams), ['style' => 'color: green']);

$jobber = new Job();
$runId = $jobber->runBegin($appInfo['app_id'], $appInfo['company_id']);
$process = new Process(array_merge([$exec], explode(' ', $cmdparams)), null, $appEnvironment, null, 32767);
$process->run(function ($type, $buffer) {
    $logger = new \Ease\Sand();
    $logger->setObjectName('Runner');
    if (Process::ERR === $type) {
        $outline = (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert($buffer);
        $logger->addStatusMessage($buffer, 'error');
    } else {
        $logger->addStatusMessage($buffer, 'success');
        $outline = (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert($buffer);
    }
    echo new \Ease\Html\DivTag(nl2br($outline));
});
$appCompany->addStatusMessage('end' . $exec . '@' . $appInfo['nazev']);
$jobber->runEnd($runId, $process->getExitCode());

\Ease\WebPage::singleton()->addJavascript("$('body').css('font-family', 'Courier').css('background-color','black');");

$oPage->draw();
