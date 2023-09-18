<?php

/**
 * Multi Flexi  - New Company registration form
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Html\ImgTag;
use Ease\Html\InputEmailTag;
use Ease\Html\InputHiddenTag;
use Ease\Html\InputTextTag;
use Ease\TWB4\SubmitButton;
use Ease\TWB4\Widgets\SemaforLight;
use Ease\TWB4\Widgets\Toggle;

/**
 * Registered AbraFlexi instance editor Form
 *
 * @author 
 */
class AppInfo extends \Ease\Html\TableTag
{

    /**
     * 
     * @param \MultiFlexi\Application $apps
     * @param int                     $companyID
     * @param array                   $properties
     */
    public function __construct($apps, $companyID, $properties = [])
    {
        parent::__construct(new \Ease\Html\LabelTag('semafor', new SemaforLight($apps->getDataValue('setup'), ['id' => 'semafor'])), $properties);
        $this->addItem(new \Ease\TWB4\Label(($apps->getDataValue('enabled') ? 'success' : 'danger'), ($apps->getDataValue('enabled') ? _('Enabled') : _('Disabled'))));
        $this->addItem([new \Ease\TWB4\LinkButton('conffield.php?app_id=' . $apps->getMyKey().'&company_id='.$companyID, _('Config fields'), 'warning'),
            new ConfigFieldsBadges(\MultiFlexi\Conffield::getAppConfigs($apps->getMyKey()))
        ]);
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
//        $this->addInput(new \AbraFlexiSelect('abraflexi'), _('AbraFlexi server'));
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