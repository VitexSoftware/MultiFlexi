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
 * Description of RequirementsChoser.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class RuntemplateRequirementsChoser extends \Ease\Html\DivTag
{
    private array $providers;
    private array $credTypes;
    private array $assigned;

    public function __construct(\MultiFlexi\RunTemplate $runtemplater, $properties = [])
    {
        parent::__construct([], $properties);
        $this->addTagClass('card-group');

        $runtemplater->getAssignedCredentials();
        $this->providers = \MultiFlexi\Requirement::getCredentialProviders();
        $this->credTypes = \MultiFlexi\Requirement::getCredentialTypes($runtemplater->getCompany());
        $this->assigned = \MultiFlexi\Requirement::getCredentials($runtemplater->getCompany());

        foreach ($runtemplater->getRequirements() as $requirement) {
            $this->addItem($this->requirementPanel($requirement, $runtemplater));
        }
    }

    public function requirementPanel(string $requirement, \MultiFlexi\RunTemplate  $runtemplate)
    {
        $state = 'default';
        $companyId = $runtemplate->getDataValue('company_id');
        $adders = new \Ease\Html\DivTag();
        $widget = new \Ease\Html\DivTag();
        if (\array_key_exists($requirement, $this->providers)) {
            if (\array_key_exists($requirement, $this->credTypes)) {
                $state = 'success';
                $widget->addItem(new CredentialSelect('credential['.$requirement.']', $companyId, $requirement) );
                $adders->addItem(new \Ease\TWB4\LinkButton('credential.php?company_id='.$companyId, '️➕ 🔐'._('Create credential'), 'info btn-sm btn-block'));
                $runtemplate->addStatusMessage(sprintf(_('Choose credential handling %s'), $requirement));
            } else {
                $state = 'warning';
                $adders->addItem(new \Ease\TWB4\LinkButton('credentialtype.php?company_id='.$companyId.'&class='.$requirement, '️➕ 🔐'._('Create Credential type'), 'success btn-sm', ['title' => _('New Credential Type')]));
                $runtemplate->addStatusMessage(sprintf(_('Define credential type using %s'), $requirement));
            }
        } else {
            $state = 'danger';
            $runtemplate->addStatusMessage(sprintf( _('Install "%s" extension'), '<strong>'.$requirement.'</strong>' ));
            $adders->addItem(_('Provider not found'));
        }

        return new \Ease\TWB4\Panel( new \Ease\Html\StrongTag($requirement), $state, $widget, $adders);
    }
}
