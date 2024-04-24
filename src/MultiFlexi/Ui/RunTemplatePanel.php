<?php

/**
 * Multi Flexi - RunTemplate Panel
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2024 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of RunTemplatePanel
 *
 * @author vitex
 */
class RunTemplatePanel extends \Ease\TWB4\Panel {

    /**
     * Run Template Configuration panel
     * 
     * @param \MultiFlexi\RunTemplate $runtemplate
     */
    public function __construct(\MultiFlexi\RunTemplate $runtemplate) {
        $nameInput = new \Ease\Html\ATag('#', $runtemplate->getRecordName(), ['class' => 'editable', 'id' => 'name', 'data-pk' => $runtemplate->getMyKey(), 'data-url' => 'runtemplatesave.php', 'data-title' => _('Update RunTemplate name')]);
        parent::__construct([new \Ease\Html\ATag('companyapp.php?app_id=' . $runtemplate->getDataValue('app_id') . '&company_id=' . $runtemplate->getDataValue('company_id'), '⚗️ '), $nameInput], 'inverse', new RuntemplateConfigForm($runtemplate), new RuntemplateCloneForm($runtemplate));
        $this->includeJavaScript('js/bootstrap-editable.js');
        $this->includeCss('css/bootstrap-editable.css');
        $this->addJavaScript("$.fn.editable.defaults.mode = 'inline';");
        //        $this->addJavaScript("$.fn.editable.options.savenochange = true;");
        $this->addJavaScript("      $.fn.editableform.buttons =
        '<button type=\"submit\" class=\"btn btn-primary btn-sm editable-submit\">' +
        '<i class=\"fa fa-fw fa-check\"></i>' +
        '</button>' +
        '<button type=\"button\" class=\"btn btn-inverse btn-sm editable-cancel\">' +
        '<i class=\"fa fa-fw fa-times\"></i>' +
        '</button>' 
        ");
        $this->addJavaScript("$('.editable').editable();", null, true);
    }
}
