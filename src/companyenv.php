<?php

/**
 * Multi Flexi - Update Company Environment
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

require_once './init.php';
$oPage->onlyForLogged();
$result = false;
$name = \Ease\TWB4\WebPage::getRequestValue('name');
$value = \Ease\TWB4\WebPage::getRequestValue('value');
$pk = \Ease\TWB4\WebPage::getRequestValue('pk', 'int');
if (!is_null($pk)) {
    $companyEnver = new \MultiFlexi\CompanyEnv();
    $companyEnver->setMyKey($pk);
    if ($name == 'keyword' && empty($value)) {
        $result = $companyEnver->deleteFromSQL() ? 201 : 500;
    } else {
        $companyEnver->setDataValue($name, $value);
        if ($companyEnver->dbsync()) {
            $result = 201;
        } else {
            $result = 400;
        }
    }

    http_response_code($result);
} else {
    http_response_code(404);
}


