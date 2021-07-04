<?php

/**
 * Multi Flexi - Customer instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use AbraFlexi\MultiSetup\Customer;

require_once './init.php';


$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Customer')));

$customers = new Customer($oPage->getRequestValue('id', 'int'));
$instanceName = $customers->getRecordName();

if ($oPage->isPosted()) {
    if ($customers->takeData($_POST) && !is_null($customers->saveToSQL())) {
        $customers->addStatusMessage(_('Customer Saved'), 'success');
//        $customers->prepareRemoteAbraFlexi();
        $oPage->redirect('?id=' . $customers->getMyKey());
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
$instanceRow->addColumn(2, new \Ease\Html\ImgTag(\Ease\User::getGravatar(strval($customers->getDataValue('email')) , 400, 'mm', 'g'),'Gravatar',['class'=>'img-fluid'])  );
//$instanceRow->addColumn(4, new ui\AbraFlexiInstanceStatus($customers));

$oPage->container->addItem(new Panel($instanceName, 'info',
                $instanceRow, $instanceLink));

$oPage->addItem(new PageBottom());

$oPage->draw();
