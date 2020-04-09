<?php

/**
 * Multi FlexiBee Setup - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use FlexiPeeHP\MultiSetup\FlexiBees;

require_once './init.php';

$oPage->addItem(new PageTop(_('FlexiBee instance')));

$flexiBees = new FlexiBees($oPage->getRequestValue('id', 'int'));
$instanceName = $flexiBees->getRecordName();

if ($oPage->isPosted()) {
    if ($flexiBees->takeData($_POST) && !is_null($flexiBees->saveToSQL())) {
        $flexiBees->addStatusMessage(_('FlexiBee instance Saved'), 'success');
        $flexiBees->prepareRemoteFlexiBee();
        $oPage->redirect('flexibees.php');
    } else {
        $flexiBees->addStatusMessage(_('Error saving FlexiBee instance'),
                'error');
    }
}

if (strlen($instanceName)) {
    $instanceLink = new ATag($flexiBees->getLink(),
            $flexiBees->getLink());
} else {
    $instanceName = _('New FlexiBee instance');
    $instanceLink = null;
}

$instanceRow = new Row();
$instanceRow->addColumn(8, new RegisterFlexiBeeForm($flexiBees));
//$instanceRow->addColumn(4, new ui\FlexiBeeInstanceStatus($flexiBees));

$oPage->container->addItem(new Panel($instanceName, 'info',
                $instanceRow, $instanceLink));

$oPage->addItem(new PageBottom());

$oPage->draw();
