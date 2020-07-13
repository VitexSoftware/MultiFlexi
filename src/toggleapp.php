<?php

/**
 * Multi FlexiBee Setup - Toggle app status.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

require_once './init.php';


$oPage->onlyForLogged();

$app_id = \Ease\TWB4\WebPage::getRequestValue('app');
$company_id = \Ease\TWB4\WebPage::getRequestValue('company');
$interval = \Ease\TWB4\WebPage::getRequestValue('interval');
$state = $interval != 'n';

$result = false;

if (!is_null($app_id) && !is_null($company_id)) {
    $switcher = new \FlexiPeeHP\MultiSetup\AppToCompany();
    $switcher->setData(['app_id' => (int) $app_id, 'company_id' => (int) $company_id, 'interv' => $interval]);
    if ($switcher->setState($state)) {
        $switcher->performInit();
        $result = 201;
    } else {
        $result = 400;
    }
    http_response_code($result);
} else {
    http_response_code(404);
}



