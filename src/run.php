<?php

/**
 * Multi Flexi - Command Runner.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2023 Vitex Software
 */

namespace MultiFlexi;

use Ease\Html\DivTag;
use Ease\Html\H2Tag;
use Ease\WebPage;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

require_once './init.php';
$oPage->onlyForLogged();

$jobber = new Job();
$jobber->prepareJob($oPage->getRequestValue('id', 'int'));
echo new H2Tag(str_replace(' ', '&nbsp;', $jobber->getCmdline()), ['style' => 'color: green']);

$jobber->performJob();

echo new DivTag(nl2br((new AnsiToHtmlConverter())->convert($jobber->getOutput())));

WebPage::singleton()->addJavascript("$('body').css('font-family', 'Courier').css('background-color','black');");
$oPage->draw();
