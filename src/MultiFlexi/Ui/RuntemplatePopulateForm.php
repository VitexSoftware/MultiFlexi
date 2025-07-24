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
 * Description of RuntemplatePopulateForm.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class RuntemplatePopulateForm extends \Ease\TWB4\Form
{
    public function __construct(\MultiFlexi\RunTemplate $runtemplate)
    {
        parent::__construct(['action' => 'runtemplatepopulate.php?id='.(string) $runtemplate->getMyKey(), 'class' => 'form-inline']);
        $this->addInput(new \Ease\Html\InputFileTag('env', '.env'), _('Load .env').'&nbsp;', '');
        $this->addItem(new \Ease\TWB4\SubmitButton('🚚 '._('Populate'), 'success mb-2', ['type' => 'submit']));
        $this->addItem([_('Replace Existing'), new \Ease\TWB4\Widgets\Toggle('replace')]);
        $this->setTagProperty('enctype', 'multipart/form-data');
    }

    /**
     * Move items to element root.
     */
    public function finalize(): void
    {
        $contents = $this->formDiv->pageParts;
        $this->emptyContents();
        $this->pageParts = $contents;
        parent::finalize();
    }
}
