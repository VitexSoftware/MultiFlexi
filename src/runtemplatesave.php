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

// $runTemplate = new RunTemplate(WebPage::getRequestValue('id', 'int'));
//
// if ($oPage->isPosted()) {
//    if ($runTemplate->takeData($_POST) && !is_null($runTemplate->saveToSQL())) {
//        $runTemplate->addStatusMessage(_('Config fields Saved'), 'success');
//    } else {
//        $runTemplate->addStatusMessage(_('Error saving Config fields'), 'error');
//    }
// }

require_once './init.php';
$oPage->onlyForLogged();
$result = false;
$name = \Ease\TWB4\WebPage::getRequestValue('name');
$value = \Ease\TWB4\WebPage::getRequestValue('value');
$pk = \Ease\TWB4\WebPage::getRequestValue('pk', 'int');

if (null !== $pk) {
    $runtemplater = new \MultiFlexi\RunTemplate();
    $runtemplater->setMyKey($pk);

    if ($name === 'keyword' && empty($value)) {
        $result = $runtemplater->deleteFromSQL() ? 201 : 500;
    } else {
        $runtemplater->setDataValue($name, $value);

        if ($runtemplater->dbsync()) {
            $result = 201;
        } else {
            $result = 400;
        }
    }

    http_response_code($result);
} else {
    http_response_code(404);
}
