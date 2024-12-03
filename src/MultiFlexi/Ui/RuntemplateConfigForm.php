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
 * Description of CustomAppConfigForm.
 *
 * @author vitex
 */
class RuntemplateConfigForm extends EngineForm
{
    private array $modulesEnv;

    public function __construct(\MultiFlexi\RunTemplate $engine)
    {
        parent::__construct($engine, null, ['method' => 'post', 'action' => 'runtemplate.php']);
        $defaults = $engine->getAppEnvironment();
        $customized = $engine->getRuntemplateEnvironment();

        $fieldsOf = [];
        $fieldSource = [];
        $credSource = [];

        \Ease\Functions::loadClassesInNamespace('MultiFlexi\Ui\Form');

        foreach (\Ease\Functions::classesInNamespace('MultiFlexi\Ui\Form') as $formAvailble) {
            $formClass = '\\MultiFlexi\\Ui\\Form\\'.$formAvailble;

            $formTypes[$formAvailble] = $formClass::name();
            $fieldsOf[$formAvailble] = $formClass::fields();

            foreach ($fieldsOf[$formAvailble] as $fieldName => $fieldDetails) {
                $fieldSource[$fieldName] = $formAvailble;
            }
        }

        $kredenc = new \MultiFlexi\Credential();
        $companyCredentials = $kredenc->listingQuery()->where('company_id', $engine->getDataValue('company_id'))->fetchAll('id');

        $companyCredentialsByType = [];

        $crdHlpr = new \MultiFlexi\RunTplCreds();
        $usedCreds = $crdHlpr->getCredentialsForRuntemplate($engine->getMyKey())->fetchAll('credentials_id');

        foreach ($companyCredentials as $credInfo) {
            if (\array_key_exists($credInfo['formType'], $companyCredentialsByType) === false) {
                $companyCredentialsByType[$credInfo['formType']][] = ['id' => '', 'name' => _('Do not use')];
            }

            $companyCredentialsByType[$credInfo['formType']][$credInfo['name']] = $credInfo;
        }

        $reqs = $engine->getApplication()->getRequirements();

        $reqsRow = new \Ease\TWB4\Row();

        $credData = [];

        foreach ($reqs as $req) {
            $credentialChosen = '';
            $formClass = '\\MultiFlexi\\Ui\\Form\\'.$req;

            if (\array_key_exists($req, $companyCredentialsByType)) {
                foreach ($companyCredentialsByType[$req] as $candidat) {
                    if (\array_key_exists($candidat['id'], $usedCreds)) {
                        $credentialChosen = (string) $candidat['id'];
                        $kredenc->loadFromSQL($candidat['id']);

                        foreach ($kredenc->getData() as $overrideKey => $overrideValue) {
                            $credData[$overrideKey] = $overrideValue;
                            $credSource[$overrideKey] = $candidat['id'];
                        }
                    }
                }

                $reqsRow->addColumn(2, [
                    new \Ease\Html\ImgTag($formClass::$logo, $req, ['title' => $formClass::name(), 'height' => '30']), new CredentialSelect('credential['.$req.']', $engine->getDataValue('company_id'),$req, $credentialChosen),
                    new \Ease\TWB4\LinkButton('credential.php?company_id='.$engine->getDataValue('company_id').'&formType='.$req, '️➕ 🔐', 'success btn-sm', ['title' => _('New Credential')]),
                ]);
            } else {
                if (\array_key_exists($req, $formTypes) === false) {
                    $reqsRow->addColumn(2, new \Ease\TWB4\Badge('warning', sprintf(_('Form %s not avilble'), new \Ease\Html\StrongTag($req))));
                }
            }
        }

        $this->addItem($reqsRow);

        $appFields = \MultiFlexi\Conffield::getAppConfigs($engine->getDataValue('app_id'));

        $columns = array_keys(array_merge($appFields, $customized));

        asort($columns);

        foreach ($columns as $fieldName) {
            if (\array_key_exists($fieldName, $appFields) && \array_key_exists($fieldName, $defaults)) {
                $fieldInfo = array_merge($defaults[$fieldName], $appFields[$fieldName]);
            } else {
                $fieldInfo = \array_key_exists($fieldName, $appFields) ? $appFields[$fieldName] : ['type' => 'text', 'source' => _('Custom')];
            }

            $value = \array_key_exists('value', $fieldInfo) ? $fieldInfo['value'] : (\array_key_exists('defval', $fieldInfo) ? $fieldInfo['defval'] : '');

            if ($fieldInfo['type'] === 'checkbox') {
                $input = new \Ease\Html\DivTag(new \Ease\TWB4\Widgets\Toggle($fieldName, $value === 'true' ? true : false, 'true', []));
            } else {
                $input = new \Ease\Html\InputTag($fieldName, $value, ['type' => $fieldInfo['type']]);
            }

            if (\array_key_exists($fieldName, $fieldSource)) {
                $formClass = '\\MultiFlexi\\Ui\\Form\\'.$fieldSource[$fieldName];

                if (\array_key_exists($fieldName, $credData)) {
                    $input->setTagProperty('disabled', '1');
                    $input->setValue($credData[$fieldName]);
                }

                $formIcon = new \Ease\Html\ImgTag($formClass::$logo, $formClass::name(), ['height' => 20, 'title' => $formClass::name()]);

                $reqInfo = $companyCredentialsByType[$fieldSource[$fieldName]];

                if (\array_key_exists($fieldName, $credSource)) {
                    $fieldLink = new \Ease\Html\ATag('credential.php?id='.$credSource[$fieldName], $formIcon.'&nbsp;'.$fieldName);
                } else {
                    $fieldLink = $formIcon;
                }

                $formGroup = $this->addInput($input, $fieldLink, \array_key_exists('defval', $fieldInfo) ? $fieldInfo['defval'] : '', \array_key_exists('description', $fieldInfo) ? $fieldInfo['description'] : '');
            } else {
                $formGroup = $this->addInput($input, $fieldName.'&nbsp;('.$fieldInfo['source'].')', \array_key_exists('defval', $fieldInfo) ? $fieldInfo['defval'] : '', \array_key_exists('description', $fieldInfo) ? $fieldInfo['description'] : '');
            }

            if (\array_key_exists('required', $fieldInfo) && $fieldInfo['required']) {
                $formGroup->addTagClass('bg-danger');
            }
        }

        //$this->addItem( new RuntemplateTopicsChooser('topics', $engine)); //TODO
        
        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $engine->getDataValue('app_id')));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $engine->getDataValue('company_id')));

        $saveRow = new \Ease\TWB4\Row();
        $saveRow->addColumn(8, new \Ease\TWB4\SubmitButton(_('Save'), 'success btn-lg btn-block'));
        $saveRow->addColumn(4, new \Ease\TWB4\LinkButton('actions.php?id='.$engine->getMyKey(), '🛠️&nbsp;'._('Actions'), 'secondary btn-lg btn-block'));
        $this->addItem($saveRow);
    }
}
