<?php

/**
 * Multi Flexi - About page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

require_once './init.php';
$oPage->onlyForLogged();

$app = new \AbraFlexi\MultiFlexi\Application(WebPage::getRequestValue('app_id'));

$oPage->addItem(new PageTop(_('About')));
if (is_null($app->getMyKey())) {
    $oPage->container->addItem(new \Ease\TWB4\Alert('error', _('app_id not specified')));
    $app->addStatusMessage(_('app_id not specified'), 'error');
} else {
    $oPage->addItem( new JobScheduleForm() );
}

$oPage->addItem(new PageBottom());
$oPage->draw();
