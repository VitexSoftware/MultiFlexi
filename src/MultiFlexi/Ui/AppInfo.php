<?php

/**
 * Multi Flexi  - New Company registration form
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\Label;
use Ease\TWB4\LinkButton;
use MultiFlexi\Application;
use MultiFlexi\Conffield;

/**
 * Registered AbraFlexi instance editor Form
 *
 * @author
 */
class AppInfo extends \Ease\Html\DivTag
{
    /**
     *
     * @param Application $app
     * @param int                     $companyId
     * @param array                   $properties
     */
    public function __construct($app, $companyId, $properties = [])
    {
        parent::__construct(new \Ease\Html\H2Tag($app->getRecordName()));

        $mainRow = new \Ease\TWB4\Row();
        $mainRow->addColumn(2, [
            new \Ease\Html\DivTag($app->getDataValue('description'), ['style' => 'color: white;']),
            new \Ease\Html\DivTag(new \Ease\Html\ATag($app->getDataValue('homepage'), $app->getDataValue('homepage'))),
            new \Ease\Html\DivTag(new AppLogo($app), ['style' => 'margin: auto;  width: 90%;  padding: 10px;']),
            new Label(($app->getDataValue('enabled') ? 'success' : 'danger'), ($app->getDataValue('enabled') ? _('Enabled') : _('Disabled')), ['style' => 'text-align: center; text-shadow: 1px 1px 2px white;']),
            new \Ease\Html\DivTag($app->getDataValue('enabled') ? $app->getDataValue('executable') : $app->getDataValue('deploy'), ['style' => 'font: 1.3rem Inconsolata, monospace; text-shadow: 0 0 5px #C8C8C8; color: white;'])
            ], 'md', ['style' => 'background-color: black; background-image: radial-gradient( rgba(250, 250, 250, 0.75), black 120% ); padding: 6px;']);
        //        $mainRow->addColumn(4, new AppLaunchForm($app, $companyId));
        $mainRow->addColumn(4, new RuntemplateLaunchForm($app, $companyId));
        $mainRow->addColumn(4, new AppJobsTable($app->getMyKey(), $companyId));

        $mainRow->addColumn(2, [new LinkButton('conffield.php?app_id=' . $app->getMyKey() . '&company_id=' . $companyId, [new \Ease\Html\ImgTag('images/set.svg', _('Set'), ['height' => '30px']) ,_('Config fields')], 'warning btn-sm  btn-block'),
            new \MultiFlexi\Ui\ConfigFieldsBadges(Conffield::getAppConfigs($app->getMyKey()))
        ]);

        $this->addItem($mainRow);
        //        //$this->addItem();
        //
        //        $this->addItem();
        //        $this->addItem();
        $this->addItem(new \Ease\Html\HrTag());
    }

    public function afterAdd()
    {

        //        $this->addInput(new InputTextTag('company'),
        //                _('AbraFlexi company code'));
        //
        //        $this->addInput(new InputTextTag('ic'), _('Organization ID'));
        //
        //        $this->addInput(new InputEmailTag('email'), _('Send notification to'));
        //
        //        $this->addInput(new CustomerSelect('customer'), _('Customer'));
        //        $this->addInput(new \Serverselect('server'), _('AbraFlexi server'));
        //
        //        $this->addInput(new SemaforLight($this->engine->getDataValue('rw')),
        //                _('write permission'));
        //        $this->addItem(new InputHiddenTag('rw', false));
        //
        //        $this->addItem(new InputHiddenTag('setup'), false);
        //
        //        $this->addInput(new SemaforLight($this->engine->getDataValue('webhook')),
        //                _('WebHook established'));
        //        $this->addItem(new InputHiddenTag('webhook'));
        //
        //        $this->addInput(new Toggle('enabled'), _('Enabled'));
    }
}
