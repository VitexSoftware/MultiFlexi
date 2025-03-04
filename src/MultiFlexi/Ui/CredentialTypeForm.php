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

use Ease\TWB4\SubmitButton;

/**
 * Class CredentialTypeForm.
 *
 * Handles the form for editing CredentialType.
 */
class CredentialTypeForm extends \Ease\TWB4\Form
{
    public function __construct(\MultiFlexi\CredentialType $credtype, $formProperties = [])
    {
        $credTypeRow1 = new \Ease\TWB4\Row();
        $credTypeRow1->addColumn(2, new CredentialTypeLogo($credtype, ['style' => 'height: 200px']));
        $credTypeRow1->addColumn(8, [
            new \Ease\TWB4\FormGroup(_('Credential Name'), new \Ease\Html\InputTextTag('name', $credtype->getRecordName())),
            new \Ease\TWB4\FormGroup(_('Credential UUID'), new \Ease\Html\InputTextTag('uuid', $credtype->getDataValue('uuid'), ['disabled'])),
        ]);

        $formContents[] = $credTypeRow1;

        $formContents[] = new \Ease\Html\DivTag($this->credTypeField('new'), ['class' => 'border border-primary rounded']);

        $fielder = new \MultiFlexi\CrTypeField();

        $fields = $fielder->listingQuery()->where(['credential_type_id' => $credtype->getMyKey()]);

        foreach ($fields as $crTypeField) {
            $formContents[] = $this->credTypeField((string) $crTypeField['id'], $crTypeField);
        }

        parent::__construct(['action' => 'credentialtype.php'], ['method' => 'POST'], $formContents);
        // $this->setTagProperty('enctype', 'multipart/form-data');

        $this->addItem(new SubmitButton(_('Save'), 'success btn-lg btn-block'));

        $submitRow = new \Ease\TWB4\Row();
        $submitRow->addColumn(10, new \Ease\TWB4\SubmitButton('🍏 '._('Apply'), 'primary btn-lg btn-block'));

        if (null === $credtype->getMyKey()) {
            $submitRow->addColumn(2, new \Ease\TWB4\SubmitButton('⚰️ '._('Remove').' !', 'disabled btn-lg btn-block', ['disabled' => 'true']));
        } else {
            $this->addItem(new \Ease\Html\InputHiddenTag('id', $credtype->getMyKey()));

            if (WebPage::getRequestValue('remove') === 'true') {
                $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('credentialtype.php?delete='.$credtype->getMyKey(), '⚰️ '._('Remove').' !', 'danger btn-lg btn-block'));
            } else {
                $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('credentialtype.php?id='.$credtype->getMyKey().'&remove=true', '⚰️ '._('Remove').' ?', 'warning btn-lg btn-block'));
            }
        }
    }

    public function afterAdd(): void
    {
    }

    private function credTypeField(string $credTypeUuid = 'new', array $fieldData = ['keyname' => '', 'type' => '', 'hint' => '', 'defval' => '']): \Ease\TWB4\Row
    {
        $credTypeFieldRow = new \Ease\TWB4\Row();
        $credTypeFieldRow->addColumn(3, new \Ease\TWB4\FormGroup(_('Field Name'), new \Ease\Html\InputTextTag($credTypeUuid.'[keyname]', $fieldData['keyname'])));
        $credTypeFieldRow->addColumn(2, new \Ease\TWB4\FormGroup(_('Field Type'), new CfgFieldTypeSelect($credTypeUuid.'[type]', $fieldData['type'])));
        $credTypeFieldRow->addColumn(3, new \Ease\TWB4\FormGroup(_('Field Hint'), new \Ease\Html\InputTextTag($credTypeUuid.'[hint]', $fieldData['hint'])));
        $credTypeFieldRow->addColumn(3, new \Ease\TWB4\FormGroup(_('Field Default Value'), new \Ease\Html\InputTextTag($credTypeUuid.'[defval]', $fieldData['defval'])));

        return $credTypeFieldRow;
    }
}
