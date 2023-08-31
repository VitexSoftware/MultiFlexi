<?php

/**
 * Multi Flexi - Customer instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use AbraFlexi\MultiFlexi\Application;
use AbraFlexi\MultiFlexi\Ui\PageBottom;
use AbraFlexi\MultiFlexi\Ui\PageTop;
use AbraFlexi\MultiFlexi\Ui\RegisterAppForm;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Application')));
$apps = new Application($oPage->getRequestValue('id', 'int'));
$instanceName = $apps->getDataValue('nazev');
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

$instanceRow = new Row();
$instanceRow->addColumn(8, new RegisterAppForm($apps));
$panel[] = new \Ease\Html\ImgTag(empty($apps->getDataValue('image')) ? 'images/apps.svg' : $apps->getDataValue('image'), 'Logo', ['class' => 'img-fluid']);
$panel[] = new \Ease\Html\HrTag();
if (array_key_exists('company', $_SESSION) && is_object($_SESSION['company']) && $_SESSION['company']->getMyKey()) {
    $panel[] = new \Ease\TWB4\LinkButton('id=' . $apps->getMyKey() . '&company=' . $_SESSION['company']->getMyKey(), sprintf(_('Assign to %s'), $_SESSION['company']->getRecordName()), 'success');
}


$jobber = new \AbraFlexi\MultiFlexi\Job();
$jobs = $jobber->listingQuery()->select(['job.id', 'job.company_id', 'job.begin', 'job.exitcode', 'user.login', 'job.launched_by', 'company.nazev'], true)->leftJoin('company ON company.id = job.company_id')
                ->leftJoin('user ON user.id = job.launched_by')
                ->where('app_id', $apps->getMyKey())->limit(10)->orderBy('job.id DESC')->fetchAll();
$jobList = new \Ease\TWB4\Table();
$jobList->addRowHeaderColumns([_('Job ID'), _('Company'), _('Launch time'), _('Exit Code'), _('Launched by')]);
foreach ($jobs as $job) {
    $job['id'] = new \Ease\Html\ATag('job.php?id=' . $job['id'], $job['id']);
    $job['company_id'] = new \Ease\Html\ATag('company.php?id=' . $job['company_id'], $job['nazev']);
    unset($job['nazev']);
    $job['launched_by'] = new \Ease\Html\ATag('user.php?id' . $job['launched_by'], $job['login']);
    unset($job['login']);
    $job['begin'] = [$job['begin'], '<br>', new \Ease\Html\SmallTag(new \Ease\ui\LiveAge((new \DateTime($job['begin']))->getTimestamp()))];
    $job['exitcode'] = new ExitCode($job['exitcode']);
    $jobList->addRowColumns($job);
}

$panel[] = [new \Ease\Html\H3Tag(_('Last 10 Jobs')), $jobList];
$panel[] = new LinkButton('logs.php?apps_id=' . $apps->getMyKey(), _('Application Log'), 'info');
$panel[] = new LinkButton('joblist.php?app_id=' . $apps->getMyKey(), _('All Application Jobs history'), 'info');
$instanceRow->addColumn(4, $panel);
$oPage->container->addItem(new Panel($instanceName, 'inverse',
                $instanceRow,
                is_null($apps->getMyKey()) ?
                        new LinkButton('', _('Config fields'), 'inverse disabled') :
                        [new LinkButton('conffield.php?app_id=' . $apps->getMyKey(), _('Config fields'), 'warning'),
                    new ConfigFieldsBadges(\AbraFlexi\MultiFlexi\Conffield::getAppConfigs($apps->getMyKey()))
                        ]
));
$oPage->addItem(new PageBottom());
$oPage->draw();
