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

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use MultiFlexi\Customer;

require_once './init.php';

$oPage->onlyForLogged();

$customers = new Customer($oPage->getRequestValue('id', 'int'));
$instanceName = $customers->getRecordName();
$_SESSION['customer'] = $customers->getMyKey();

$oPage->addItem(new PageTop(_('Customer')));

if ($oPage->isPosted()) {
    if ($customers->takeData($_POST) && null !== $customers->saveToSQL()) {
        $customers->addStatusMessage(_('Customer Saved'), 'success');
        //        $customers->prepareRemoteAbraFlexi();
        $oPage->redirect('?id='.$customers->getMyKey());
    } else {
        $customers->addStatusMessage(_('Error saving Customer'), 'error');
    }
}

if (empty($instanceName)) {
    $instanceName = _('New Customer');
    $instanceLink = null;
} else {
    $instanceLink = new ATag($customers->getLink(), $customers->getUserName());
}

$instanceRow = new Row();
$instanceRow->addColumn(8, new RegisterCustomerForm($customers));
$instanceRow->addColumn(2, new \Ease\Html\ImgTag(\Ease\User::getGravatar((string) $customers->getDataValue('email'), 400, 'mm', 'g'), 'Gravatar', ['class' => 'img-fluid']));
// $instanceRow->addColumn(4, new ui\AbraFlexiInstanceStatus($customers));

$oPage->container->addItem(new Panel(
    $instanceName,
    'info',
    $instanceRow,
    $instanceLink,
));

$oPage->addItem(new PageBottom());

$oPage->draw();
