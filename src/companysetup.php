<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use AbraFlexi\MultiFlexi\Company;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Company')));
$companies = new Company(WebPage::getRequestValue('id', 'int'));
$_SESSION['company'] = &$companies;
$companyEnver = new \AbraFlexi\MultiFlexi\CompanyEnv($companies->getMyKey());
if ($oPage->isPosted()) {
    if (array_key_exists('env', $_POST)) {
        $companyEnver->addEnv($_POST['env']['newkey'], $_POST['env']['newvalue']);
    } else {
        if ($companies->takeData($_POST) && !is_null($companies->saveToSQL())) {
            $companies->addStatusMessage(_('Company Saved'), 'success');
//        $companies->prepareRemoteCompany(); TODO: Run applications setup on new company
            $oPage->redirect('?id=' . $companies->getMyKey());
        } else {
            $companies->addStatusMessage(_('Error saving Company'), 'error');
        }
    }
} else {
    if (!empty(WebPage::getGetValue('company'))) {
        $companies->setDataValue('company', WebPage::getGetValue('company'));
        $companies->setDataValue('nazev', WebPage::getGetValue('nazev'));
        $companies->setDataValue('ic', WebPage::getGetValue('ic'));
        $companies->setDataValue('email', WebPage::getGetValue('email'));
        $companies->setDataValue('abraflexi', WebPage::getGetValue('abraflexi', 'int'));
    }
}
$instanceName = $companies->getDataValue('nazev');
if (strlen($instanceName)) {
    $instanceLink = new ATag($companies->getApiURL() . $companies->getDataValue('company'),
            $companies->getApiURL() . $companies->getDataValue('company'));
} else {
    $instanceName = _('New Company');
    $instanceLink = null;
}

$instanceRow = new Row();
$instanceRow->addColumn(4, new RegisterCompanyForm($companies, null, ['action' => 'companysetup.php']));
//$instanceRow->addColumn(4, new ui\AbraFlexiInstanceStatus($companies));


if (strlen($companies->getDataValue('logo'))) {
    $rightColumn[] = new \Ease\Html\ImgTag($companies->getDataValue('logo'), 'logo', ['class' => 'img-fluid']);
}

$rightColumn[] = new EnvironmentEditor($companyEnver->getEnvFields());
$instanceRow->addColumn(8, $rightColumn);
$oPage->container->addItem(new Panel($instanceName, 'light', $instanceRow));
$oPage->addItem(new PageBottom());
$oPage->draw();
