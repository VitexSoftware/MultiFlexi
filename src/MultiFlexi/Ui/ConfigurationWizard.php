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

/**
 * Description of ConfigurationWizard.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class ConfigurationWizard extends Wizard
{
    public \MultiFlexi\Company $company;
    private \MultiFlexi\Application $application;

    public function __construct(\MultiFlexi\Company $company)
    {
        $this->step = (int) \Ease\WebPage::getRequestValue('step');
        $this->application = new \MultiFlexi\Application(\Ease\WebPage::getRequestValue('app_id', 'int'));
        $this->company = $company;
        $footer = new \Ease\TWB4\ProgressBar($this->getStepPercent(), $this->getStepPercent().'% '.$this->getStepLabel(), 'progress-bar-striped progress-bar-animated bg-info');
        $body = new \Ease\TWB4\Row();
        $body->addColumn(2, new \Ease\Html\DivTag('🧙🏻‍♂️', ['style' => 'font-size: 220px; background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(12,121,9,1) 35%, rgba(0,212,255,1) 100%); height: 100%']));
        $body->addColumn(10, $this->getStepBody());
        parent::__construct(_('Configuration Wizard').': '.$this->getStepLabel(), 'inverse', $body, $footer);
    }

    public function steps()
    {
        return [
            0 => _('Choose Application'),
            1 => _('Configure Application'),
            2 => _('Launch Application'),
        ];
    }

    public function getStepBody(): mixed
    {
        switch ($this->step) {
            case 0:
                $body = $this->appChooser();

                break;
            case 1:
                $body = $this->appConfigurator();

                break;

            default:
                $body = 'n/a';

                break;
        }

        return $body;
    }

    public function appConfigurator()
    {
        $configForm = new \Ease\TWB4\Form();
        $configForm->addInput(new \Ease\Html\InputHiddenTag('app_id', $this->application->getMyKey()));
        $configForm->addInput(new \Ease\Html\InputHiddenTag('company_id', $this->company->getMyKey()));
        $configForm->addInput(new \Ease\Html\InputHiddenTag('step', $this->step));

        $configFields = $this->application->getAppEnvironmentFields();

        \Ease\Functions::loadClassesInNamespace('MultiFlexi\Ui\Form');
        $formsAvailble = \Ease\Functions::classesInNamespace('MultiFlexi\Ui\Form');
        $reqs = explode(',', $this->application->getRequirements());

        $intersection = array_intersect($formsAvailble, $reqs);

        foreach ($intersection as $form) {
            $formClass = 'MultiFlexi\\Ui\\Form\\'.$form;
            $configForm->addItem(new $formClass());
        }
        
        $configForm->addItem( new \Ease\TWB4\SubmitButton(_('Next').' ➡️', 'primary') );
        return $configForm;
    }

    public function appChooser()
    {
        $apps = new \MultiFlexi\Application();

        $allAppData = $apps->listingQuery()->select(['id', 'image', 'name', 'description', 'topics'], true);

        $fbtable = new \Ease\TWB4\Table();
        $fbtable->addRowHeaderColumns([_('Image'), _('Name'), _('Description')]);

        foreach ($allAppData as $appData) {
            $appData['image'] = new \Ease\Html\ImgTag($appData['image'], _('Icon'), ['height' => 40]);
            $appData['name'] = new \Ease\Html\ATag('wizard.php?company_id='.$this->company->getMyKey().'&app_id='.$appData['id'].'&step='.$this->getNextStep(), _($appData['name']));
            $appData['description'] = _($appData['description']).'<br>'.new \Ease\Html\SmallTag($appData['topics']);
            unset($appData['id'], $appData['topics']);

            $fbtable->addRowColumns($appData);
        }

        //        $apper = new \MultiFlexi\CompanyApp($this->company);
        //        $assigned = $apper->getAssigned();
        //        $apps = new \Ease\Html\TableTag();
        //        foreach ($assigned as $appData){
        //            $apps->addRowColumns($appData['name']);
        //        }

        return $fbtable;
    }
}
