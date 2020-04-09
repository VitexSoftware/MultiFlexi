<?php
/**
 * Multi FlexiBee Setup - Customer instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use FlexiPeeHP\MultiSetup\Customer;

require_once './init.php';


$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Customer')));

$customers    = new Customer($oPage->getRequestValue('id', 'int'));
$instanceName = $customers->getRecordName();

if ($oPage->isPosted()) {
    if ($customers->takeData($_POST) && !is_null($customers->saveToSQL())) {
        $customers->addStatusMessage(_('Customer Saved'), 'success');
//        $customers->prepareRemoteFlexiBee();
        $oPage->redirect('?id='.$customers->getMyKey());
    } else {
        $customers->addStatusMessage(_('Error saving Customer'), 'error');
    }
}

if (strlen($instanceName)) {
    $instanceLink = new ATag($customers->getLink(),
        $customers->getLink());
} else {
    $instanceName = _('New Customer');
    $instanceLink = null;
}

$instanceRow = new Row();
$instanceRow->addColumn(8, new RegisterCustomerForm($customers));
//$instanceRow->addColumn(4, new ui\FlexiBeeInstanceStatus($customers));

$oPage->container->addItem(new Panel($instanceName, 'info',
        $instanceRow, $instanceLink));

$oPage->addItem(new PageBottom());

$oPage->draw();
