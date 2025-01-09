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
use Ease\Html\SmallTag;
use Ease\TWB4\LinkButton;
use Ease\TWB4\Row;
use Ease\TWB4\Table;
use Ease\TWB4\Tabs;
use MultiFlexi\Application;
use MultiFlexi\Conffield;
use MultiFlexi\Job;

require_once './init.php';
WebPage::singleton()->onlyForLogged();
$action = \Ease\WebPage::getRequestValue('action');
$apps = new Application(WebPage::getRequestValue('id', 'int') + WebPage::getRequestValue('app', 'int'));
$instanceName = _($apps->getDataValue('name') ?: _('n/a'));

switch ($action) {
    case 'delete':
        $configurator = new \MultiFlexi\Configuration();
        $configurator->deleteFromSQL(['app_id' => $apps->getMyKey()]);

        $apps->deleteFromSQL();
        $apps->addStatusMessage(sprintf(_('Application %s removal'), $apps->getRecordName()), 'success');
        WebPage::singleton()->redirect('apps.php');

        break;

    default:
        if (WebPage::singleton()->isPosted()) {
            if ($apps->takeData($_POST) && null !== $apps->saveToSQL()) {
                $apps->addStatusMessage(_('Application Saved'), 'success');
                //        $apps->prepareRemoteAbraFlexi();
                WebPage::singleton()->redirect('?id='.$apps->getMyKey());
            } else {
                $apps->addStatusMessage(_('Error saving Application'), 'error');
            }
        }

        break;
}

if (empty($instanceName) === false) {
    $instanceLink = '';
} else {
    $instanceName = _('New Application');
    $instanceLink = null;
}

$_SESSION['application'] = $apps->getMyKey();
WebPage::singleton()->addItem(new PageTop('🧩 '.$apps->getRecordName() ? trim(_('Application').' '.$apps->getRecordName()) : $instanceName));
$instanceRow = new Row();
$instanceRow->addColumn(4, new AppEditorForm($apps));
// if (array_key_exists('company', $_SESSION) && is_null($_SESSION['company']) === false) {
//    $company = new Company($_SESSION['company']);
//    $panel[] = new LinkButton('id=' . $apps->getMyKey() . '&company=' . $_SESSION['company'], sprintf(_('Assign to %s'), $company->getRecordName()), 'success');
// }

$jobber = new Job();
$jobs = $jobber->listingQuery()->select(['job.id', 'job.company_id', 'job.begin', 'job.exitcode', 'user.login', 'job.launched_by', 'company.name'], true)->leftJoin('company ON company.id = job.company_id')
    ->leftJoin('user ON user.id = job.launched_by')
    ->where('app_id', $apps->getMyKey())->limit(10)->orderBy('job.id DESC')->fetchAll();
$jobList = new Table();
$jobList->addRowHeaderColumns([_('Job ID'), _('Company'), _('Launch time'), _('Exit Code'), _('Launched by')]);

foreach ($jobs as $job) {
    $job['id'] = new ATag('job.php?id='.$job['id'], '🏁 '.$job['id']);
    $job['company_id'] = new ATag('company.php?id='.$job['company_id'], $job['name']);
    unset($job['name']);
    $job['launched_by'] = new ATag('user.php?id'.$job['launched_by'], $job['login']);
    unset($job['login']);

    if (empty($job['begin'])) {
        $job['begin'] = '⏳'._('Not yet');
    } else {
        $job['begin'] = [$job['begin'], '<br>', new SmallTag(new \Ease\Html\Widgets\LiveAge(new \DateTime($job['begin'])))];
    }

    $job['exitcode'] = new ExitCode($job['exitcode']);
    $jobList->addRowColumns($job);
}

$instanceRow->addColumn(4, null === $apps->getMyKey() ?
                new LinkButton('', _('Config fields'), 'inverse disabled  btn-block') :
                [
                    new ConfigFieldsView(Conffield::getAppConfigs($apps->getMyKey())),
                    new LinkButton('conffield.php?app_id='.$apps->getMyKey(), _('Config fields editor'), 'secondary  btn-block'),
                ]);

$instanceRow->addColumn(4, new AppLogo($apps));

$appOverview = new ApplicationInfo($apps);

$appTabs = new Tabs();
$appTabs->addTab(_('Overview'), $appOverview);
$appTabs->addTab(_('Configuration'), $instanceRow);
$appTabs->addTab(_('Jobs'), [
    $jobList,
    new LinkButton('logs.php?apps_id='.$apps->getMyKey(), _('Application Log'), 'info'),
    new LinkButton('joblist.php?app_id='.$apps->getMyKey(), _('All Application Jobs history'), 'info'),
]);
$appTabs->addTab(_('Export'), new AppJson($apps));

WebPage::singleton()->container->addItem(new ApplicationPanel(
    $apps,
    $appTabs,
    '',
));

WebPage::singleton()->addItem(new PageBottom('app/'.$apps->getMyKey()));
WebPage::singleton()->draw();
