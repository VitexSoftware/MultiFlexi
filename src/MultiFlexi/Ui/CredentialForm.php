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
class CredentialForm extends \Ease\TWB4\Form {

    public  \MultiFlexi\Credential $kredenc;
    public function __construct(\MultiFlexi\Credential $kredenc) {
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

        $companys['0'] = _('Please Select Company');

        foreach ($companer->listingQuery() as $company) {
            $companys[(string) $company['id']] = empty($company['name']) ? (string) ($company['id']) : $company['name'];
        }

        $formContents[] = new \Ease\Html\SelectTag('company_id', $companys, (string) $kredenc->getDataValue('company_id'));
        $formContents[] = new \Ease\TWB4\FormGroup(_('Credential Name'), new \Ease\Html\InputTextTag('name', $kredenc->getRecordName()));

        $class = '\\MultiFlexi\\Ui\\Form\\' . $formType;

        if ($formType && class_exists($class)) {
            $formContents[] = new $class();
        }

        $formContents[] = new \Ease\Html\InputHiddenTag('id', $kredenc->getMyKey());

        $submitRow = new \Ease\TWB4\Row();
        $submitRow->addColumn(10, new \Ease\TWB4\SubmitButton('🍏 ' . _('Apply'), 'primary btn-lg btn-block'));
        if (is_null($kredenc->getMyKey())) {
            $submitRow->addColumn(2, new \Ease\TWB4\SubmitButton( '⚰️ ' . _('Remove') . ' !', 'disabled btn-lg btn-block',['disabled'=>'true']));
        } else {
            if (WebPage::getRequestValue('remove') == 'true') {
                $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('credential.php?delete=' . $kredenc->getMyKey(), '⚰️ ' . _('Remove') . ' !', 'danger btn-lg btn-block'));
            } else {
                $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('credential.php?id=' . $kredenc->getMyKey() . '&remove=true', '⚰️ ' . _('Remove') . ' ?', 'warning btn-lg btn-block'));
            }
        }
        $formContents[] = $submitRow;

        parent::__construct(['action' => 'credential.php'], ['method' => 'POST'], $formContents);
    }
    
    #[\Override]
    public function finalize(): void {
        $this->fillUp($this->kredenc->getData());
        parent::finalize();
    }
    
}
