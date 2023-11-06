<?php
/**
 * Multi Flexi - Customer instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Company,
    \DateTime,
    \Ease\Html\ATag,
    \Ease\Html\H3Tag,
    \Ease\Html\HrTag,
    \Ease\Html\ImgTag,
    \Ease\Html\SmallTag,
    \Ease\TWB4\LinkButton,
    \Ease\TWB4\Panel,
    \Ease\TWB4\Row,
    \Ease\TWB4\Table,
    \Ease\ui\LiveAge,
    \MultiFlexi\Application,
    \MultiFlexi\Conffield,
    \MultiFlexi\Job,
    \MultiFlexi\Ui\PageBottom,
    \MultiFlexi\Ui\PageTop,
    \MultiFlexi\Ui\RegisterAppForm;

require_once './init.php';
$oPage->onlyForLogged();
$apps = new Application($oPage->getRequestValue('id', 'int'));
$instanceName = $apps->getDataValue('name');
if ($oPage->isPosted()) {
    if ($apps->takeData($_POST) && !is_null($apps->saveToSQL())) {
        $apps->addStatusMessage(_('Application Saved'), 'success');
        //        $apps->prepareRemoteAbraFlexi();
        $oPage->redirect('?id=' . $apps->getMyKey());
    } else {
        $apps->addStatusMessage(_('Error saving Application'), 'error');
    }
}

if (empty($instanceName) === false) {
    $instanceLink = '';
} else {
    $instanceName = _('New Application');
    $instanceLink = null;
}

$_SESSION['application'] = $apps->getMyKey();
$oPage->addItem(new PageTop($apps->getRecordName() ? trim(_('Application') . ' ' . $apps->getRecordName()) : $instanceName ));
$instanceRow = new Row();
$instanceRow->addColumn(8, new RegisterAppForm($apps));
$panel[] = new ImgTag(empty($apps->getDataValue('image')) ? 'images/apps.svg' : $apps->getDataValue('image'), 'Logo', ['class' => 'img-fluid']);
$panel[] = new HrTag();
if (array_key_exists('company', $_SESSION) && is_null($_SESSION['company']) === false) {
    $company = new Company($_SESSION['company']);
    $panel[] = new LinkButton('id=' . $apps->getMyKey() . '&company=' . $_SESSION['company'], sprintf(_('Assign to %s'), $company->getRecordName()), 'success');
}

$jobber = new Job();
$jobs = $jobber->listingQuery()->select(['job.id', 'job.company_id', 'job.begin', 'job.exitcode', 'user.login', 'job.launched_by', 'company.name'], true)->leftJoin('company ON company.id = job.company_id')
                ->leftJoin('user ON user.id = job.launched_by')
                ->where('app_id', $apps->getMyKey())->limit(10)->orderBy('job.id DESC')->fetchAll();
$jobList = new Table();
$jobList->addRowHeaderColumns([_('Job ID'), _('Company'), _('Launch time'), _('Exit Code'), _('Launched by')]);
foreach ($jobs as $job) {
    $job['id'] = new ATag('job.php?id=' . $job['id'], $job['id']);
    $job['company_id'] = new ATag('company.php?id=' . $job['company_id'], $job['name']);
    unset($job['name']);
    $job['launched_by'] = new ATag('user.php?id' . $job['launched_by'], $job['login']);
    unset($job['login']);
    if (empty($job['begin'])) {
        $job['begin'] = '⏳' . _('Not yet');
    } else {
        $job['begin'] = [$job['begin'], '<br>', new SmallTag(new LiveAge((new DateTime($job['begin']))->getTimestamp()))];
    }
    $job['exitcode'] = new \MultiFlexi\Ui\ExitCode($job['exitcode']);
    $jobList->addRowColumns($job);
}

$panel[] = [new H3Tag(_('Last 10 Jobs')), $jobList];
$panel[] = new LinkButton('logs.php?apps_id=' . $apps->getMyKey(), _('Application Log'), 'info');
$panel[] = new LinkButton('joblist.php?app_id=' . $apps->getMyKey(), _('All Application Jobs history'), 'info');
$instanceRow->addColumn(4, $panel);
$oPage->container->addItem(new Panel(
                $instanceName,
                'inverse',
                $instanceRow,
                is_null($apps->getMyKey()) ?
                        new LinkButton('', _('Config fields'), 'inverse disabled') :
                        [new LinkButton('conffield.php?app_id=' . $apps->getMyKey(), _('Config fields'), 'warning'),
                    new \MultiFlexi\Ui\ConfigFieldsBadges(Conffield::getAppConfigs($apps->getMyKey()))
                        ]
));

$oPage->addItem( new AppJson($apps) );

$oPage->addItem(new PageBottom());
$oPage->draw();
