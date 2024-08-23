<?php

/**
 * Multi Flexi - Run Template Clone page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2024 Vitex Software
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
    $oPage->redirect('runtemplate.php?id=' . $cloneId);
} catch (Exception $exc) {
    $oPage->addItem(new PageTop(_('Runtemplate Clone')));
    $oPage->addStatusMessage(_('Error creating runtemplate clone'), 'error');
    $oPage->addItem(new PageBottom());
    $oPage->draw();
}
