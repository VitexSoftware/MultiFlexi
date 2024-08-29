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

if ((null === $serverId) === false) {
    $companies->setDataValue('server', $serverId);
}

$_SESSION['company'] = $companies->getMyKey();

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
        $oPage->redirect('server.php?id='.$companies->getDataValue('server'));
    } else {
        $companies->addStatusMessage(_('Error deleting Company').' '.$companies->getDataValue('name'), 'error');
    }

    $companies->unsetDataValue('name');
}

$instanceName = $companies->getDataValue('name');

if (\strlen($instanceName)) {
    $instanceLink = new ATag(
        $companies->getApiURL().$companies->getDataValue('company'),
        $companies->getApiURL().$companies->getDataValue('company'),
    );
} else {
    $instanceName = _('New Company');
    $instanceLink = null;
}

$instanceRow = new Row();
$instanceRow->addColumn(4, new DeleteCompanyForm($companies, null, ['action' => 'companydelete.php']));

if (\strlen($companies->getDataValue('logo'))) {
    $rightColumn[] = new \Ease\Html\ImgTag($companies->getDataValue('logo'), 'logo', ['class' => 'img-fluid']);
}

$rightColumn[] = new EnvironmentView($companyEnver->getEnvFields());
$instanceRow->addColumn(8, $rightColumn);
$oPage->container->addItem(new CompanyPanel($companies, $instanceRow));
$oPage->addItem(new PageBottom());
$oPage->draw();
