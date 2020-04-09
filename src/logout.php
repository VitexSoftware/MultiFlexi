<?php

use Ease\Anonym;
use Ease\Html\ATag;
use Ease\Html\H1Tag;
use Ease\Shared;
use Ease\TWB4\Row;
use FlexiPeeHP\MultiSetup\Ui\PageBottom;
use FlexiPeeHP\MultiSetup\Ui\PageTop;

/**
 * Multi FlexiBee Setup - Sign off page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

require_once './init.php';



if (!is_null($oUser->getUserID())) {
    $oUser->logout();
}

$oPage->addItem(new PageTop(_('Sign Off')));

$byerow = new \Ease\TWB4\Row();
$byerow->addColumn(6);
$byeInfo = $byerow->addColumn(6, new \Ease\Html\H1Tag(_('Good bye')));


$byeInfo->addItem('<br/><br/><br/><br/>');
$byeInfo->addItem(new \Ease\Html\DivTag(new \Ease\Html\ATag('login.php',
                        _('Thank you for your patronage and look forward to another visit'),
                        ['class' => 'jumbotron'])));
$byeInfo->addItem('<br/><br/><br/><br/>');

$oPage->container->addItem($byerow);

$oPage->addItem(new PageBottom());

$oPage->draw();
