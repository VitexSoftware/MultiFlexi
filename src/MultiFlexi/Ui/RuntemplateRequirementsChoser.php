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
    private array $assignedCredentials = [];

    public function __construct(\MultiFlexi\RunTemplate $runtemplater, $properties = [])
    {
        parent::__construct([], $properties);
        $this->addTagClass('card-group');

        $runtemplater->getAssignedCredentials();
        $this->providers = \MultiFlexi\Requirement::getCredentialProviders();
        $this->credTypes = \MultiFlexi\Requirement::getCredentialTypes($runtemplater->getCompany());
        $this->assigned = \MultiFlexi\Requirement::getCredentials($runtemplater->getCompany());

        $this->assignedCredentials = $runtemplater->getCredentialsAssigned();

        foreach ($runtemplater->getRequirements() as $requirement) {
            $this->addItem($this->requirementPanel($requirement, $runtemplater));
        }
    }

    public function requirementPanel(string $requirement, \MultiFlexi\RunTemplate $runtemplate)
    {
        $state = 'default';
        $companyId = $runtemplate->getDataValue('company_id');
        $adders = new \Ease\TWB4\Row();
        $widget = new \Ease\Html\DivTag();

        if (\array_key_exists($requirement, $this->providers)) {
            if (\array_key_exists($requirement, $this->credTypes)) {
                $state = 'success';
                $widget->addItem(new CredentialSelect('credential['.$requirement.']', $companyId, $requirement, \array_key_exists($requirement, $this->assignedCredentials) ? (string) $this->assignedCredentials[$requirement]['credentials_id'] : ''));

                if(\array_key_exists($requirement, $this->assignedCredentials)){
                    $adders->addColumn(4, new \Ease\TWB4\LinkButton('credential.php?id='.$this->assignedCredentials[$requirement]['credentials_id'], sprintf(_('Edit credential %s'), $requirement) , 'secondary'));
                }
                $helper = new \MultiFlexi\CredentialType();
                $credTypes = $helper->listingQuery()->where('company_id', $companyId)->where('class', $requirement);

                foreach ($credTypes as $myCredType) {
                    $adders->addColumn(4,new \Ease\TWB4\LinkButton('credential.php?company_id='.$companyId.'&credential_type_id='.$myCredType['id'], '️➕ 🔐'.sprintf(_('Create credential based on %s type'), $myCredType['name']), 'info btn-sm btn-block'));
                }

                if (\array_key_exists($requirement, $this->assignedCredentials) === false) {
                    $runtemplate->addStatusMessage(sprintf(_('Choose credential handling %s'), $requirement));
                }
            } else {
                $state = 'warning';
                $adders->addColumn(4,new \Ease\TWB4\LinkButton('credentialtype.php?company_id='.$companyId.'&class='.$requirement, '️➕ 🔐'._('Create Credential type'), 'success btn-sm', ['title' => _('New Credential Type')]));
                $runtemplate->addStatusMessage(sprintf(_('Please, define The Credential type using %s'), $requirement));
            }
        } else {
            $state = 'danger';
            $runtemplate->addStatusMessage(sprintf(_('Install "%s" extension'), '<strong>'.$requirement.'</strong>'));
            $adders->addColumn(4,_('Provider not found'));
        }

        return new \Ease\TWB4\Panel(new \Ease\Html\StrongTag($requirement), $state, $widget, $adders);
    }
}
