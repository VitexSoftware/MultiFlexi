<?php

/**
 * Multi Flexi - RunTemplate clone Form
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2024 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of RuntemplateCloneForm
 *
 * @author vitex
 */
class RuntemplateCloneForm extends \Ease\TWB4\Form
{

    /**
     * 
     * @param \MultiFlexi\RunTemplate $runtemplate
     * @param array $formDivProperties
     * @param array $formContents
     */
    public function __construct(\MultiFlexi\RunTemplate $runtemplate)
    {
        $clonename = $runtemplate->getDataValue('name') ? $runtemplate->getDataValue('name') : $runtemplate->getAppInfo()['app_name'] . ' ' . _('Clone');
        parent::__construct(['action' => 'runtemplateclone.php?id=' . strval($runtemplate->getMyKey()), 'class' => 'form-inline']);
        $this->addInput(new \Ease\Html\InputTextTag('clonename', $clonename), _('Save as copy') . '&nbsp;', $clonename);
        $this->addItem(new \Ease\TWB4\SubmitButton('💕 ' . _('Clone'), 'success mb-2', ['type' => 'submit']));
    }

    /**
     * Move items to elemnt root
     */
    public function finalize()
    {
        $contents = $this->formDiv->pageParts;
        $this->emptyContents();
        $this->pageParts = $contents;
        parent::finalize();
    }
}
