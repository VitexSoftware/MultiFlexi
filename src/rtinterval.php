<?php

/**
 * Multi Flexi - Runtemplate interval chooser.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

require_once './init.php';

$oPage->onlyForLogged();

$runtemplate_id = \Ease\TWB4\WebPage::getRequestValue('runtemplate', 'int');
$interval = \Ease\TWB4\WebPage::getRequestValue('interval');
$state = $interval != 'n';

$result = false;

if (!is_null($runtemplate_id)) {
    $switcher = new \MultiFlexi\RunTemplate();
    $switcher->setData(['id' => $runtemplate_id, 'interv' => $interval]);
    http_response_code($switcher->setState($state) ? 201 : 400);
} else {
    http_response_code(404);
}
