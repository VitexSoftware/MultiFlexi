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
 * Class CredentialTypeForm.
 *
 * Handles the form for editing CredentialType.
 */
class CredentialTypeForm extends \Ease\TWB4\Form
{
    #[\Override]
    public function __construct(\MultiFlexi\CredentialType $credtype, $formProperties = [])
    {
        $formContents[] = new \Ease\Html\InputHiddenTag('id', $credtype->getMyKey());
        $formContents[] = new \Ease\TWB4\FormGroup(_('Credential Name'), new \Ease\Html\InputTextTag('name', $credtype->getRecordName()));

        $submitRow = new \Ease\TWB4\Row();
        $submitRow->addColumn(10, new \Ease\TWB4\SubmitButton('🍏 '._('Apply'), 'primary btn-lg btn-block'));

        if (null === $credtype->getMyKey()) {
            $submitRow->addColumn(2, new \Ease\TWB4\SubmitButton('⚰️ '._('Remove').' !', 'disabled btn-lg btn-block', ['disabled' => 'true']));
        } else {
            if (WebPage::getRequestValue('remove') === 'true') {
                $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('credentialtype.php?delete='.$credtype->getMyKey(), '⚰️ '._('Remove').' !', 'danger btn-lg btn-block'));
            } else {
                $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('credentialtype.php?id='.$credtype->getMyKey().'&remove=true', '⚰️ '._('Remove').' ?', 'warning btn-lg btn-block'));
            }
        }

        $formContents[] = $submitRow;

        parent::__construct(['action' => 'credentialtype.php'], ['method' => 'POST'], $formContents);
    }
}
