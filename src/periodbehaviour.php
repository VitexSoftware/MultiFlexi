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

use Ease\TWB4\WebPage;
use MultiFlexi\AbraFlexi\Company;
use MultiFlexi\Application;
use MultiFlexi\RunTemplate;

require_once './init.php';

$oPage->onlyForLogged();

$runTemplater = new RunTemplate(WebPage::getRequestValue('id', 'int'));
$_SESSION['company'] = $runTemplater->getDataValue('company_id');
$actions = new \MultiFlexi\ActionConfig();

if (\Ease\WebPage::isPosted()) {
    $succesActions = ActionsChooser::toggles('success');
    $failActions = ActionsChooser::toggles('fail');
    $runTemplater->setDataValue('fail', serialize($failActions));
    $runTemplater->setDataValue('success', serialize($succesActions));
    $runTemplater->saveToSQL();

    $actions->saveModeConfigs('success', ActionsChooser::formModuleCofig('success'), $runTemplater->getMyKey());
    $actions->saveModeConfigs('fail', ActionsChooser::formModuleCofig('fail'), $runTemplater->getMyKey());
} else {
    $failActions = $runTemplater->getDataValue('fail') ? unserialize($runTemplater->getDataValue('fail')) : [];
    $succesActions = $runTemplater->getDataValue('success') ? unserialize($runTemplater->getDataValue('success')) : [];
}

$oPage->addItem(new PageTop('🛠 '.$runTemplater->getRecordName()));

$periodcalTaskInfo = $runTemplater->getData();

$app = new Application($periodcalTaskInfo['app_id']);

$interval = new \Ease\Html\DivTag(_(\MultiFlexi\Job::codeToInterval($periodcalTaskInfo['interv'])).' '._('interval'));
$appPanel = new ApplicationPanel($app, $interval);

$runTemplateButton = new \Ease\TWB4\LinkButton('runtemplate.php?id='.$runTemplater->getMyKey(), '⚗️&nbsp;'._('Run Template'), 'dark btn-lg btn-block');
$appPanel->headRow->addColumn(2, $runTemplateButton);

$actionsRow = new \Ease\TWB4\Tabs();
$actionsRow->addTab(_('Success Actions'), new ActionsChooser('success', $app, $succesActions), $periodcalTaskInfo['success']);
$actionsRow->addTab(_('Fail Actions'), new ActionsChooser('fail', $app, $failActions), $periodcalTaskInfo['fail']);

$appPanel->addItem($actionsRow);
$jobtempform = new \Ease\TWB4\Form();
$jobtempform->addItem(new \Ease\Html\InputHiddenTag('app', $periodcalTaskInfo['app_id']));
$jobtempform->addItem(new \Ease\Html\InputHiddenTag('company_id', $periodcalTaskInfo['company_id']));
$jobtempform->addItem(new \Ease\Html\InputHiddenTag('interval', $periodcalTaskInfo['interv']));
$jobtempform->addItem($appPanel);
$jobtempform->addItem(new \Ease\TWB4\SubmitButton('🍏 '._('Apply'), 'primary btn-lg btn-block'));

$oPage->container->addItem(new CompanyPanel(new Company($periodcalTaskInfo['company_id']), $jobtempform));

$oPage->addItem(new PageBottom());

$oPage->finalizeRegistred();
$jobtempform->fillUp(ActionsChooser::sqlToForm($actions->getRuntemplateConfig($runTemplater->getMyKey())));
$oPage->draw();
