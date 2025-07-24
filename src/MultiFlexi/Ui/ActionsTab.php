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

use MultiFlexi\Application;
use MultiFlexi\RunTemplate;

/**
 * @no-named-arguments
 */
class ActionsTab extends \Ease\TWB4\Form
{
    public function __construct(RunTemplate $runTemplater)
    {
        $periodcalTaskInfo = $runTemplater->getData();
        $app = new Application($periodcalTaskInfo['app_id']);
        $actions = new \MultiFlexi\ActionConfig();
        $failActions = $runTemplater->getDataValue('fail') ? unserialize($runTemplater->getDataValue('fail')) : [];
        $succesActions = $runTemplater->getDataValue('success') ? unserialize($runTemplater->getDataValue('success')) : [];

        $actionsRow = new \Ease\TWB4\Tabs();
        $actionsRow->addTab(_('Success Actions'), new ActionsChooser('success', $app, $succesActions), (bool) $periodcalTaskInfo['success']);
        $actionsRow->addTab(_('Fail Actions'), new ActionsChooser('fail', $app, $failActions), (bool) $periodcalTaskInfo['fail']);

        parent::__construct();
        $this->addItem(new \Ease\Html\InputHiddenTag('app', $periodcalTaskInfo['app_id']));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $periodcalTaskInfo['company_id']));
        $this->addItem(new \Ease\Html\InputHiddenTag('interval', $periodcalTaskInfo['interv']));
        $this->addItem($actionsRow);
        $this->addItem(new \Ease\TWB4\SubmitButton('🍏 '._('Apply'), 'primary btn-lg btn-block'));

        $actionsData = [];
        $configRows = $actions->getRuntemplateConfig($runTemplater->getMyKey());

        if (\is_object($configRows) && method_exists($configRows, 'fetchAll')) {
            $actionsData = ActionsChooser::sqlToForm($configRows->fetchAll());
        } elseif (\is_array($configRows)) {
            foreach ($configRows as $field) {
                $actionsData[$field['mode'].'['.$field['module'].']['.$field['keyname'].']'] = $field['value'];
            }
        }

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

        $this->fillUp($actionsData);
    }
}
