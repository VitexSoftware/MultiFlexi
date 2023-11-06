<?php
/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use MultiFlexi\Company;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Company')));
$serverId = WebPage::getRequestValue('server', 'int');
if ($serverId) {
    $serverserver = new \MultiFlexi\Servers($serverId);
    $companyConfig = $serverserver->getConnectionDetails();
    $companyConfig['company'] = WebPage::getGetValue('company');
} else {
    $companyConfig = [];
}

$companies = new Company(WebPage::getRequestValue('id', 'int'), $companyConfig);
if (is_null($serverId) === false) {
    $companies->setDataValue('server', $serverId);
}
$_SESSION['company'] = $companies->getMyKey();
$companyEnver = new \MultiFlexi\CompanyEnv($companies->getMyKey());
if ($oPage->isPosted()) {
    if (array_key_exists('env', $_POST)) {
        $companyEnver->addEnv($_POST['env']['newkey'], $_POST['env']['newvalue']);
    } else {
        if ($companies->takeData($_POST)) {
            /* try to save company or cath error */
            try {
                $companies->saveToSQL();
                $companies->addStatusMessage(_('Company Saved'), 'success');
            } catch (\Exception $exc) {
                $companies->addStatusMessage($exc->getMessage(), 'error');
            }

            //        $companies->prepareRemoteCompany(); TODO: Run applications setup on new company
            $oPage->redirect('?id=' . $companies->getMyKey());
        } else {
            $companies->addStatusMessage(_('Error saving Company') . ' ' . $companies->getDataValue('name'), 'error');
        }
    }
} else {
    if (!empty(WebPage::getGetValue('company'))) {
        $companies->setDataValue('company', WebPage::getGetValue('company'));
        $companies->setDataValue('name', WebPage::getGetValue('name'));
        $companies->setDataValue('ic', WebPage::getGetValue('ic'));
        $companies->setDataValue('email', WebPage::getGetValue('email'));
        $companies->serverId = WebPage::getGetValue('server', 'int');
        $companies->loadFromAbraFlexi();
    }
}
$instanceName = $companies->getDataValue('name');
if (empty($instanceName)) {
    $instanceName = _('New Company');
    $instanceLink = null;
} else {
    $instanceLink = new ATag($companies->getLink(), $companies->getRecordName());
}

$instanceRow = new Row();
$instanceRow->addColumn(4, new RegisterCompanyForm($companies, null, ['action' => 'companysetup.php']));
//$instanceRow->addColumn(4, new ui\AbraFlexiInstanceStatus($companies));


if (empty($companies->getDataValue('logo')) === false) {
    $rightColumn[] = new \Ease\Html\ImgTag($companies->getDataValue('logo'), 'logo', ['class' => 'img-fluid']);
}

$rightColumn[] = new EnvironmentEditor($companyEnver->getEnvFields());
$instanceRow->addColumn(8, $rightColumn);
$oPage->container->addItem(new Panel($instanceName, 'light', $instanceRow));
$oPage->addItem(new PageBottom());
$oPage->draw();
