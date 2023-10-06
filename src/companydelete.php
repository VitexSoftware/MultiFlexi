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
$abraFlexiId = WebPage::getRequestValue('abraflexi', 'int');
if ($abraFlexiId) {
    $abraFlexiServer = new \MultiFlexi\AbraFlexis($abraFlexiId);
    $companyConfig = $abraFlexiServer->getConnectionDetails();
    $companyConfig['company'] = WebPage::getGetValue('company');
} else {
    $companyConfig = [];
}

$companies = new Company(WebPage::getRequestValue('id', 'int'), $companyConfig);
if (is_null($abraFlexiId) === false) {
    $companies->setDataValue('abraflexi', $abraFlexiId);
}
$_SESSION['company'] = &$companies;
$companyEnver = new \MultiFlexi\CompanyEnv($companies->getMyKey());
if ($oPage->isPosted()) {
    $companyEnver->deleteFromSQL(['company_id' => $companies->getMyKey()]);
    $appToCompany = new \MultiFlexi\RunTemplate();
    $appToCompany->deleteFromSQL(['company_id' => $companies->getMyKey()]);
    $logger = new \MultiFlexi\Logger();
    $logger->deleteFromSQL(['company_id' => $companies->getMyKey()]);
    $jobber = new \MultiFlexi\Job();
    $jobber->deleteFromSQL(['company_id' => $companies->getMyKey()]);
    $confer = new \MultiFlexi\Configuration();
    $confer->deleteFromSQL(['company_id' => $companies->getMyKey()]);

    if ($companies->deleteFromSQL(['id' => $companies->getMyKey()])) {
        $companies->addStatusMessage(_('Company Deleted'), 'success');
        $oPage->redirect('abraflexi.php?id=' . $companies->getDataValue('abraflexi'));
    } else {
        $companies->addStatusMessage(_('Error deleting Company') . ' ' . $companies->getDataValue('nazev'), 'error');
    }
}

$instanceName = $companies->getDataValue('nazev');
if (strlen($instanceName)) {
    $instanceLink = new ATag(
        $companies->getApiURL() . $companies->getDataValue('company'),
        $companies->getApiURL() . $companies->getDataValue('company')
    );
} else {
    $instanceName = _('New Company');
    $instanceLink = null;
}

$instanceRow = new Row();
$instanceRow->addColumn(4, new DeleteCompanyForm($companies, null, ['action' => 'companydelete.php']));
if (strlen($companies->getDataValue('logo'))) {
    $rightColumn[] = new \Ease\Html\ImgTag($companies->getDataValue('logo'), 'logo', ['class' => 'img-fluid']);
}

$rightColumn[] = new EnvironmentView($companyEnver->getEnvFields());
$instanceRow->addColumn(8, $rightColumn);
$oPage->container->addItem(new Panel($instanceName, 'light', $instanceRow));
$oPage->addItem(new PageBottom());
$oPage->draw();
