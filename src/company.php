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
use FlexiPeeHP\MultiSetup\Company;


require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Company')));

$companies    = new Company($oPage->getRequestValue('id', 'int'));
$instanceName = $companies->getRecordIdent();

if ($oPage->isPosted()) {
    if ($companies->takeData($_POST) && !is_null($companies->saveToSQL())) {
        $companies->addStatusMessage(_('Company Saved'), 'success');
        $companies->prepareRemoteCompany();
        $oPage->redirect('?id='.$companies->getMyKey());
    } else {
        $companies->addStatusMessage(_('Error saving Company'), 'error');
    }
}

if (strlen($instanceName)) {
    $instanceLink = new ATag($companies->getApiURL().$companies->getDataValue('company'),
        $companies->getApiURL().$companies->getDataValue('company'));
} else {
    $instanceName = _('New Company');
    $instanceLink = null;
}

$instanceRow = new Row();
$instanceRow->addColumn(8, new RegisterCompanyForm($companies));
//$instanceRow->addColumn(4, new ui\FlexiBeeInstanceStatus($companies));

$bottomLine = new Row();
$bottomLine->addColumn(8, $instanceLink);
//$delUrl = 'company.php?delete='.$myId = $companies->getMyKey();
//$bottomLine->addColumn(4,
//    new \Ease\TWB4\ButtonDropdown( _('Company operations'), 'warning', 'sm',
//        [$delUrl=> _('Remove company') ] ));

$oPage->container->addItem(new Panel($instanceName, 'info',
        $instanceRow, $bottomLine));

//$oPage->addItem(new ui\PageBottom());

$oPage->draw();
