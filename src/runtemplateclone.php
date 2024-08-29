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

use Ease\WebPage;
use MultiFlexi\RunTemplate;

require_once './init.php';
$oPage->onlyForLogged();

$runTemplate = new RunTemplate(WebPage::getRequestValue('id', 'int'));
$cloneName = \Ease\TWB4\WebPage::getRequestValue('clonename');

$runTemplate->unsetDataValue($runTemplate->getKeyColumn());
$runTemplate->setDataValue('name', $cloneName);

try {
    $cloneId = $runTemplate->insertToSQL();
    $oPage->redirect('runtemplate.php?id='.$cloneId);
} catch (Exception $exc) {
    $oPage->addItem(new PageTop(_('Runtemplate Clone')));
    $oPage->addStatusMessage(_('Error creating runtemplate clone'), 'error');
    $oPage->addItem(new PageBottom());
    $oPage->draw();
}
