<?php

/**
 * Multi Flexi - Periodical Tasks behaviour
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\WebPage;
use MultiFlexi\AbraFlexi\Company;
use MultiFlexi\Application;
use MultiFlexi\RunTemplate;
use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->onlyForLogged();

$runTemplater = new RunTemplate();
$runTemplater->loadFromSQL($runTemplater->runTemplateID(WebPage::getRequestValue('app'), $_SESSION['company']));

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

$oPage->addItem(new PageTop(_('Periodical Tasks')));

$periodcalTaskInfo = $runTemplater->getData();

$app = new Application($periodcalTaskInfo['app_id']);

$appPanel = new ApplicationPanel($app);

$appPanel->addItem(new \Ease\Html\DivTag(_(\MultiFlexi\Job::codeToInterval($periodcalTaskInfo['interv'])) . ' ' . _('interval')));

$actionsRow = new \Ease\TWB4\Row();
$actionsRow->addColumn(6, new \Ease\TWB4\Panel(_('Success Actions'), 'success', new ActionsChooser('success', $app, $succesActions), $periodcalTaskInfo['success']));
$actionsRow->addColumn(6, new \Ease\TWB4\Panel(_('Fail Actions'), 'danger', new ActionsChooser('fail', $app, $failActions), $periodcalTaskInfo['fail']));

$appPanel->addItem($actionsRow);
$jobtempform = new \Ease\TWB4\Form();
$jobtempform->addItem(new \Ease\Html\InputHiddenTag('app', $periodcalTaskInfo['app_id']));
$jobtempform->addItem(new \Ease\Html\InputHiddenTag('company_id', $periodcalTaskInfo['company_id']));
$jobtempform->addItem(new \Ease\Html\InputHiddenTag('interval', $periodcalTaskInfo['interv']));
$jobtempform->addItem($appPanel);
$jobtempform->addItem(new \Ease\TWB4\SubmitButton(_('Update'), 'primary btn-lg btn-block'));

$oPage->container->addItem(new CompanyPanel(new Company($periodcalTaskInfo['company_id']), $jobtempform));

$oPage->addItem(new PageBottom());

$oPage->finalizeRegistred();
$jobtempform->fillUp(ActionsChooser::sqlToForm($actions->getRuntemplateConfig($runTemplater->getMyKey())));
$oPage->draw();
