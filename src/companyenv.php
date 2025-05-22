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

namespace MultiFlexi\Ui;

require_once './init.php';
WebPage::singleton()->onlyForLogged();
$result = false;
$name = \Ease\TWB5\WebPage::getRequestValue('name');
$value = \Ease\TWB5\WebPage::getRequestValue('value');
$pk = \Ease\TWB5\WebPage::getRequestValue('pk', 'int');

if (null !== $pk) {
    $companyEnver = new \MultiFlexi\CompanyEnv();
    $companyEnver->setMyKey($pk);

    if ($name === 'keyword' && empty($value)) {
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
