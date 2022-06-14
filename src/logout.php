<?php

/**
 * Multi Flexi - Sign off page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

use AbraFlexi\MultiSetup\Ui\PageBottom;
use AbraFlexi\MultiSetup\Ui\PageTop;

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
