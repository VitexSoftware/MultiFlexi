<?php

/**
 * Multi Flexi - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\LinkButton;
use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->addItem(new PageTop(_('Multi Flexi')));

try {
    if (empty(\Ease\Shared::user()->listingQuery()->count())) {
        \Ease\Shared::user()->addStatusMessage(_('There is no administrators in the database.'), 'warning');
        $oPage->container->addItem(new LinkButton('createaccount.php', _('Create first Administrator Account'), 'success'));
    }
} catch (\PDOException $exc) {
    \Ease\Shared::user()->addStatusMessage($exc->getMessage());
}

$oPage->container->addItem(new \Ease\Html\DivTag(new OpenClipart('images/openclipart', _('Random OpenClipart'), ['class' => 'mx-auto d-block']), ['style' => 'height: 80%']));

$oPage->addItem(new PageBottom());

$oPage->draw();
