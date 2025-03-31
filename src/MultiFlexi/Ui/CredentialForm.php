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

use Ease\Html\ATag;
use Ease\Html\DivTag;
use Ease\Html\InputHiddenTag;
use Ease\Html\InputTextTag;
use Ease\Html\SpanTag;
use Ease\TWB4\Badge;
use Ease\TWB4\Form;
use Ease\TWB4\FormGroup;
use Ease\TWB4\LinkButton;
use Ease\TWB4\Row;
use Ease\TWB4\SubmitButton;
use MultiFlexi\ConfigField;
use MultiFlexi\Credential;
use MultiFlexi\CredentialType;
use MultiFlexi\Job;
use MultiFlexi\RunTplCreds;

/**
 * Description of CredentialForm.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CredentialForm extends Form
{
    public Credential $kredenc;

    public function __construct(Credential $kredenc)
    {
        $this->kredenc = $kredenc;
        $formContents = [];
        $formType = $kredenc->getDataValue('formType');

        if ($kredenc->getDataValue('company_id')) {
            $credentialTypeSelect = new CredentialTypeSelect('credential_type_id', (int) $kredenc->getDataValue('company_id'), (int) $kredenc->getDataValue('credential_type_id'));
            $credentialTypeSelect->finalize();

            if ($credentialTypeSelect->getItemsCount()) {
                $formContents[] = new FormGroup(_('Credential Type'), $credentialTypeSelect);
            } else {
                $formContents[] = new FormGroup(_('Credential Type'), new SpanTag(_('No credential types for company defined yet')));
            }

            $formContents[] = new LinkButton('credentialtype.php?company_id='.$kredenc->getDataValue('company_id').'&class='.$formType, '️➕ 🔐', 'success btn-sm', ['title' => _('New Credential Type')]);
        } else {
            $formContents[] = new FormGroup(_('Choose company first'), new InputHiddenTag('credential_type_id', 0));
        }

        $formContents[] = new FormGroup(_('Company'), new CompanySelect('company_id', (int) $kredenc->getDataValue('company_id')));

        $formContents[] = new FormGroup(_('Credential Name'), new InputTextTag('name', $kredenc->getRecordName()));

        if (null !== $kredenc->getMyKey()) {
            $rtplcr = new RunTplCreds();
            $runtlUsing = $rtplcr->getRuntemplatesForCredential($kredenc->getMyKey())->select(['runtemplate.name', 'company_id', 'app_id'])->leftJoin('runtemplate ON runtemplate.id = runtplcreds.runtemplate_id')->fetchAll();

            if ($runtlUsing) {
                $formContents[] = new DivTag(_('Used by').'('.\count($runtlUsing).')');

                $jobber = new Job();

                $runtemplatesDiv = new DivTag();

                foreach ($runtlUsing as $runtemplateData) {
                    $linkProperties['title'] = $runtemplateData['name'];
                    $lastJobInfo = $jobber->listingQuery()->select(['id', 'exitcode'], true)->where(['company_id' => $runtemplateData['company_id'], 'app_id' => $runtemplateData['app_id']])->order('id DESC')->limit(1)->fetchAll();

                    if ($lastJobInfo) {
                        $companyAppStatus = new ATag('job.php?id='.$lastJobInfo[0]['id'], new ExitCode($lastJobInfo[0]['exitcode'], ['style' => 'font-size: 1.0em; font-family: monospace;']), ['class' => 'btn btn-inverse btn-sm']);
                    } else {
                        $companyAppStatus = new Badge('disabled', '🪤', ['style' => 'font-size: 1.0em; font-family: monospace;']);
                    }

                    $runtemplatesDiv->addItem(new SpanTag([new ATag('runtemplate.php?id='.$runtemplateData['runtemplate_id'], '⚗️#'.$runtemplateData['runtemplate_id'], ['class' => 'btn btn-inverse btn-sm', 'title' => $runtemplateData['name']]), $companyAppStatus], ['class' => 'btn-group', 'role' => 'group']));
                }

                $formContents[] = $runtemplatesDiv;
            }
        }

        if ($kredenc->getDataValue('credential_type_id')) {
            $fieldsSource = new CredentialType($kredenc->getDataValue('credential_type_id'));

            foreach ($fieldsSource->getFields() as $field) {
                $formContents[] = $this->confiField($kredenc, $field);
            }
        }

        $formContents[] = new InputHiddenTag('id', $kredenc->getMyKey());

        $submitRow = new Row();
        $submitRow->addColumn(10, new SubmitButton('🍏 '._('Apply'), 'primary btn-lg btn-block'));

        if (null === $kredenc->getMyKey()) {
            $submitRow->addColumn(2, new SubmitButton('⚰️ '._('Remove').' !', 'disabled btn-lg btn-block', ['disabled' => 'true']));
        } else {
            if (WebPage::getRequestValue('remove') === 'true') {
                $submitRow->addColumn(2, new LinkButton('credential.php?delete='.$kredenc->getMyKey(), '⚰️ '._('Remove').' !', 'danger btn-lg btn-block'));
            } else {
                $submitRow->addColumn(2, new LinkButton('credential.php?id='.$kredenc->getMyKey().'&remove=true', '⚰️ '._('Remove').' ?', 'warning btn-lg btn-block'));
            }
        }

        $formContents[] = $submitRow;

        parent::__construct(['action' => 'credential.php'], ['method' => 'POST'], $formContents);
    }

    #[\Override]
    public function finalize(): void
    {
        $this->fillUp((array) $this->kredenc->getData());
        parent::finalize();
    }

    private function confiField(Credential $credential, ConfigField $field): Row
    {
        $credTypeId = $credential->getMyKey();

        $credTypeFieldRow = new Row();
        $credTypeFieldRow->addColumn(4, [$field->getType(), new \Ease\Html\H2Tag($field->getCode())]);
        $credTypeFieldRow->addColumn(4, new FormGroup($field->getName(), new InputTextTag($field->getCode(), $field->getValue()), $field->getHint(), $field->getDefaultValue()));

        return $credTypeFieldRow;
    }
}
