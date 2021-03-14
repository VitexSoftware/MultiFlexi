<?php

namespace AbraFlexi\MultiSetup\Ui;

use Ease\TWB4\LinkButton;
use AbraFlexi\MultiSetup\Ui\PageBottom;
use AbraFlexi\MultiSetup\Ui\PageTop;

/**
 * Multi FlexiBee Setup - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2021 Vitex Software
 */
require_once './init.php';

$oPage->addItem(new PageTop(_('Multi FlexiBee Setup')));

if (empty($oUser->listingQuery()->count())) {
    $oUser->addStatusMessage(_('There is no administrators in the database.'), 'warning');
    $oPage->container->addItem(new LinkButton('createaccount.php', _('Create first Administrator Account'), 'success'));
}



$oPage->addItem(new PageBottom());

$oPage->draw();
