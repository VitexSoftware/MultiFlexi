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
use MultiFlexi\Application;
use MultiFlexi\RunTemplate;

require_once './init.php';

WebPage::singleton()->onlyForLogged();

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

WebPage::singleton()->addItem(new PageTop('🛠 '.$runTemplater->getRecordName()));

$periodcalTaskInfo = $runTemplater->getData();

$app = new Application($periodcalTaskInfo['app_id']);

$interval = new \Ease\Html\DivTag(_(\MultiFlexi\RunTemplate::codeToInterval($periodcalTaskInfo['interv'])).' '._('interval'));
$appPanel = new ApplicationPanel($app, $interval);

$appPanel->headRow->addItem(new RuntemplateButton($runTemplater));

$actionsRow = new \Ease\TWB4\Tabs();
$actionsRow->addTab(_('Success Actions'), new ActionsChooser('success', $app, $succesActions), (bool) $periodcalTaskInfo['success']);
$actionsRow->addTab(_('Fail Actions'), new ActionsChooser('fail', $app, $failActions), (bool) $periodcalTaskInfo['fail']);

$appPanel->addItem($actionsRow);
$jobtempform = new \Ease\TWB4\Form();
$jobtempform->addItem(new \Ease\Html\InputHiddenTag('app', $periodcalTaskInfo['app_id']));
$jobtempform->addItem(new \Ease\Html\InputHiddenTag('company_id', $periodcalTaskInfo['company_id']));
$jobtempform->addItem(new \Ease\Html\InputHiddenTag('interval', $periodcalTaskInfo['interv']));
$jobtempform->addItem($appPanel);
$jobtempform->addItem(new \Ease\TWB4\SubmitButton('🍏 '._('Apply'), 'primary btn-lg btn-block'));

WebPage::singleton()->container->addItem(new CompanyPanel(new \MultiFlexi\Company($periodcalTaskInfo['company_id']), $jobtempform));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->finalizeRegistred();

$actionsData = ActionsChooser::sqlToForm($actions->getRuntemplateConfig($runTemplater->getMyKey()));

\Ease\Functions::loadClassesInNamespace('MultiFlexi\\Action');
$actionModules = \Ease\Functions::classesInNamespace('MultiFlexi\\Action');

$initials = [];

foreach ($actionModules as $action) {
    $actionClass = '\\MultiFlexi\\Action\\'.$action;

    foreach (['success', 'fail'] as $mode) {
        if ($actionClass::usableForApp($app)) {
            $initials[$action][$mode] = (new $actionClass($runTemplater))->initialData('success');

            foreach ($initials[$action][$mode] as $key => $value) {
                if (\array_key_exists($mode.'['.$action.']['.$key.']', $actionsData) === false) {
                    $actionsData[$mode.'['.$action.']['.$key.']'] = $value;
                }
            }
        }
    }
}

// array(10) (
//  [success[CustomCommand][command]] => (string)
//  [success[Zabbix][key]] => (string) ZABBIX_KEY
//  [success[Zabbix][metricsfile]] => (string) metrics.json
//  [success[LaunchJob][jobid]] => (string) 2
//  [success[WebHook][uri]] => (string)
//  [fail[CustomCommand][command]] => (string)
//  [fail[Zabbix][key]] => (string)
//  [fail[Zabbix][metricsfile]] => (string)
//  [fail[LaunchJob][jobid]] => (string) 2
//  [fail[WebHook][uri]] => (string)
// )

$jobtempform->fillUp($actionsData);
WebPage::singleton()->draw();
