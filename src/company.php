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
$instanceName = $companies->getDataValue('nazev');
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

if (strlen($instanceName)) {
    $instanceLink = new ATag($companies->getApiURL() . $companies->getDataValue('company'),
            $companies->getApiURL() . $companies->getDataValue('company'));
} else {
    $instanceName = _('New Company');
    $instanceLink = null;
}

$instanceRow = new Row();
$jobber = new \AbraFlexi\MultiFlexi\Job();
$jobs = $jobber->listingQuery()->select(['apps.nazev AS appname', 'apps.image AS appimage', 'job.id', 'begin', 'exitcode', 'launched_by', 'login', 'job.app_id AS app_id'], true)->leftJoin('apps ON apps.id = job.app_id')->leftJoin('user ON user.id = job.launched_by')->where('company_id', $companies->getMyKey())->limit(20)->orderBy('job.id DESC')->fetchAll();
$jobList = new \Ease\TWB4\Table();
$jobList->addRowHeaderColumns([_('Application'), _('Job ID'), _('Launch time'), _('Exit Code'), _('Launcher')]);
foreach ($jobs as $job) {
    $job['appimage'] = new ATag('app.php?id=' . $job['app_id'], [ new \Ease\TWB4\Badge('light', [new \Ease\Html\ImgTag($job['appimage'], $job['appname'], ['height' => 30, 'title' => $job['appname']]),'&nbsp;', $job['appname']])]);
    unset($job['appname']);
    unset($job['app_id']);
    $job['id'] = new ATag('job.php?id=' . $job['id'], new \Ease\TWB4\Badge('info', $job['id']));
    $job['begin'] = [$job['begin'], ' ', new \Ease\Html\SmallTag(new \Ease\ui\LiveAge((new \DateTime($job['begin']))->getTimestamp()))];
    $job['exitcode'] = new ExitCode($job['exitcode']);
    $job['launched_by'] = $job['launched_by'] ? new ATag('user.php?id=' . $job['launched_by'], new \Ease\TWB4\Badge('info', $job['login'])) : _('Timer');
    unset($job['login']);
    $jobList->addRowColumns($job);
}



$instanceRow->addColumn(6, [$jobList, new \Ease\TWB4\LinkButton('tasks.php?company_id=' . $companies->getMyKey(), _('Setup tasks'), 'warning')]);
$instanceRow->addColumn(2, new RegisterCompanyForm($companies, null, ['action' => 'company.php']));
//$instanceRow->addColumn(4, new ui\AbraFlexiInstanceStatus($companies));


if (strlen($companies->getDataValue('logo'))) {
    $rightColumn[] = new \Ease\Html\ImgTag($companies->getDataValue('logo'), 'logo', ['class' => 'img-fluid']);
}

$rightColumn['envs'] = new EnvsForm($companyEnver->getData());
$rightColumn['envs']->addItem(new \Ease\Html\InputHiddenTag('id', $companies->getMyKey()));
$instanceRow->addColumn(4, $rightColumn);
$bottomLine = new Row();
$bottomLine->addColumn(8, $instanceLink);
//$delUrl = 'company.php?delete='.$myId = $companies->getMyKey();
//$bottomLine->addColumn(4,
//    new \Ease\TWB4\ButtonDropdown( _('Company operations'), 'warning', 'sm',
//        [$delUrl=> _('Remove company') ] ));
//$bottomLine->addColumn(4, );
$oPage->container->addItem(new Panel($instanceName, 'info',
                $instanceRow, $bottomLine));
$oPage->addItem(new PageBottom());
$oPage->draw();
