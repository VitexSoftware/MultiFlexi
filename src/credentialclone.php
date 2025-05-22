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
use MultiFlexi\Credential;

require_once './init.php';
WebPage::singleton()->onlyForLogged();

$credential = new Credential(WebPage::getRequestValue('id', 'int'));
$originalEnv = $credential->getData();
$cloneName = \Ease\TWB5\WebPage::getRequestValue('clonename');

$credential->unsetDataValue($credential->getKeyColumn());
$credential->setDataValue('name', $cloneName);

try {
    $cloneId = $credential->insertToSQL();

    if ($cloneId !== null) {
        $credential->addStatusMessage(_('Credential Cloned'), 'success');
    } else {
        $credential->addStatusMessage(_('Error saving Credential clone'), 'error');
    }

    WebPage::singleton()->redirect('credential.php?id='.$cloneId);
} catch (Exception $exc) {
    WebPage::singleton()->addItem(new PageTop(_('Credential Clone')));
    WebPage::singleton()->addStatusMessage(_('Error creating credential clone'), 'error');
    WebPage::singleton()->addItem(new PageBottom());
    WebPage::singleton()->draw();
}
