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
 * Description of CredentialForm.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CredentialForm extends \Ease\TWB4\Form
{
    public \MultiFlexi\Credential $kredenc;

    public function __construct(\MultiFlexi\Credential $kredenc)
    {
        $this->kredenc = $kredenc;
        $formContents = [];
        $formType = $kredenc->getDataValue('formType');

        $forms[''] = _('Please Select Credential type form');
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\Ui\Form');

        foreach (\Ease\Functions::classesInNamespace('MultiFlexi\Ui\Form') as $formAvailble) {
            $forms[$formAvailble] = $formAvailble;
        }

        $formContents[] = new \Ease\Html\SelectTag('formType', $forms, (string) $formType);

        $companer = new \MultiFlexi\Company();

        $companies['0'] = _('Please Select Company');

        foreach ($companer->listingQuery() as $company) {
            $companies[(string) $company['id']] = empty($company['name']) ? (string) ($company['id']) : $company['name'];
        }

        $formContents[] = new \Ease\Html\SelectTag('company_id', $companies, (string) $kredenc->getDataValue('company_id'));
        $formContents[] = new \Ease\TWB4\FormGroup(_('Credential Name'), new \Ease\Html\InputTextTag('name', $kredenc->getRecordName()));

        if (null !== $kredenc->getMyKey()) {
            $rtplcr = new \MultiFlexi\RunTplCreds();
            $runtlUsing = $rtplcr->getRuntemplatesForCredential($kredenc->getMyKey())->select(['runtemplate.name', 'company_id', 'app_id'])->leftJoin('runtemplate ON runtemplate.id = runtplcreds.runtemplate_id')->fetchAll();

            if ($runtlUsing) {
                $formContents[] = new \Ease\Html\DivTag(_('Used by').'('.\count($runtlUsing).')');

                $jobber = new \MultiFlexi\Job();

                $runtemplatesDiv = new \Ease\Html\DivTag();

                foreach ($runtlUsing as $runtemplateData) {
                    $linkProperties['title'] = $runtemplateData['name'];
                    $lastJobInfo = $jobber->listingQuery()->select(['id', 'exitcode'], true)->where(['company_id' => $runtemplateData['company_id'], 'app_id' => $runtemplateData['app_id']])->order('id DESC')->limit(1)->fetchAll();

                    if ($lastJobInfo) {
                        $companyAppStatus = new \Ease\Html\ATag('job.php?id='.$lastJobInfo[0]['id'], new ExitCode($lastJobInfo[0]['exitcode'], ['style' => 'font-size: 1.0em; font-family: monospace;']), ['class' => 'btn btn-inverse btn-sm']);
                    } else {
                        $companyAppStatus = new \Ease\TWB4\Badge('disabled', '🪤', ['style' => 'font-size: 1.0em; font-family: monospace;']);
                    }

                    $runtemplatesDiv->addItem(new \Ease\Html\SpanTag([new \Ease\Html\ATag('runtemplate.php?id='.$runtemplateData['runtemplate_id'], '⚗️#'.$runtemplateData['runtemplate_id'], ['class' => 'btn btn-inverse btn-sm', 'title' => $runtemplateData['name']]), $companyAppStatus], ['class' => 'btn-group', 'role' => 'group']));
                }

                $formContents[] = $runtemplatesDiv;
            }
        }

        $class = '\\MultiFlexi\\Ui\\Form\\'.$formType;

        if ($formType && class_exists($class)) {
            $formContents[] = new $class();
        }

        $formContents[] = new \Ease\Html\InputHiddenTag('id', $kredenc->getMyKey());
        $formContents[] = new \Ease\Html\InputHiddenTag('credential_type_id', 0); // TODO Add Credential Type Chooser

        $submitRow = new \Ease\TWB4\Row();
        $submitRow->addColumn(10, new \Ease\TWB4\SubmitButton('🍏 '._('Apply'), 'primary btn-lg btn-block'));

        if (null === $kredenc->getMyKey()) {
            $submitRow->addColumn(2, new \Ease\TWB4\SubmitButton('⚰️ '._('Remove').' !', 'disabled btn-lg btn-block', ['disabled' => 'true']));
        } else {
            if (WebPage::getRequestValue('remove') === 'true') {
                $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('credential.php?delete='.$kredenc->getMyKey(), '⚰️ '._('Remove').' !', 'danger btn-lg btn-block'));
            } else {
                $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('credential.php?id='.$kredenc->getMyKey().'&remove=true', '⚰️ '._('Remove').' ?', 'warning btn-lg btn-block'));
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
}
