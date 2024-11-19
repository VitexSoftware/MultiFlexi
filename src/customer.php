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

WebPage::singleton()->onlyForLogged();

$customers = new Customer(WebPage::singleton()->getRequestValue('id', 'int'));
$instanceName = $customers->getRecordName();
$_SESSION['customer'] = $customers->getMyKey();

WebPage::singleton()->addItem(new PageTop(_('Customer')));

if (WebPage::singleton()->isPosted()) {
    if ($customers->takeData($_POST) && null !== $customers->saveToSQL()) {
        $customers->addStatusMessage(_('Customer Saved'), 'success');
        //        $customers->prepareRemoteAbraFlexi();
        WebPage::singleton()->redirect('?id='.$customers->getMyKey());
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

WebPage::singleton()->container->addItem(new Panel(
    $instanceName,
    'info',
    $instanceRow,
    $instanceLink,
));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
