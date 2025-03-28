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

use Ease\Html\DivTag;
use Ease\Html\H2Tag;
use Ease\WebPage;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

require_once './init.php';
WebPage::singleton()->onlyForLogged();

$jobber = new Job();
$jobber->prepareJob(WebPage::singleton()->getRequestValue('id', 'int'));
echo new H2Tag(str_replace(' ', '&nbsp;', $jobber->getCmdline()), ['style' => 'color: green']);

$jobber->performJob();

echo new DivTag(nl2br((new AnsiToHtmlConverter())->convert($jobber->getOutput())));

WebPage::singleton()->addJavascript("$('body').css('font-family', 'Courier').css('background-color','black');");
WebPage::singleton()->draw();
