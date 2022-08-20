<?php

namespace AbraFlexi\MultiFlexi\Ui;

use Ease\TWB4\LinkButton;
use AbraFlexi\MultiFlexi\Ui\PageBottom;
use AbraFlexi\MultiFlexi\Ui\PageTop;

/**
 * Multi Flexi - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2022 Vitex Software
 */
require_once './init.php';

$oPage->addItem(new PageTop(_('Multi Flexi')));

try {
    if (empty($oUser->listingQuery()->count())) {
        $oUser->addStatusMessage(_('There is no administrators in the database.'), 'warning');
        $oPage->container->addItem(new LinkButton('createaccount.php', _('Create first Administrator Account'), 'success'));
    }
} catch (\PDOException $exc) {
    $oUser->addStatusMessage($exc->getMessage());
}

$oPage->addItem(new PageBottom());

$oPage->draw();
