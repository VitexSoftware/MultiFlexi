<?php

/**
 * Multi Flexi - Command Runner.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2023 Vitex Software
 */

namespace MultiFlexi;

use MultiFlexi\Application;
use Symfony\Component\Process\Process;

require_once './init.php';
$oPage->onlyForLogged();

$jobber = new Job();
$jobber->prepareJob($oPage->getRequestValue('id', 'int'));  
echo new \Ease\Html\H2Tag(str_replace(' ', '&nbsp;', $jobber->getCmdline()), ['style' => 'color: green']);

$jobber->performJob();

echo new \Ease\Html\DivTag(nl2br((new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert($jobber->getOutputCachePlaintext())));

\Ease\WebPage::singleton()->addJavascript("$('body').css('font-family', 'Courier').css('background-color','black');");
$oPage->draw();
