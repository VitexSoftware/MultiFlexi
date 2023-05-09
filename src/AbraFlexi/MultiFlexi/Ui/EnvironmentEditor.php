<?php

/**
 * Multi Flexi - Environment editor
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

/**
 * Description of EnvironmentView
 *
 * @author vitex
 */
class EnvironmentEditor extends \Ease\Html\TableTag
{

    public $fields;

    /**
     * 
     * @param array $fields
     * @param array $properties
     */
    public function __construct($fields, $properties = [])
    {
        $properties['class'] = 'table';
        parent::__construct(null, $properties);
        $this->addRowHeaderColumns([_('Keyword'), _('Value')]);
        $this->fields = $fields;
        foreach ($fields as $fieldProperties) {
//            if (stristr($fieldProperties['keyword'], 'pass')) {
//                $value = preg_replace('(.)', '*', $value);
//            }
            $tableRow = $this->tBody->addItem(new \Ease\Html\TrTag());
            $keyInput = new \Ease\Html\ATag('#', $fieldProperties['keyword'], ['class' => 'editable', 'id' => 'keyword', 'data-pk' => $fieldProperties['id'], 'data-url' => 'companyenv.php', 'data-title' => _('Update Keyname')]);
            $tableRow->addItem(new \Ease\Html\TdTag($keyInput, ['style' => 'width: 20%']));
            $valueInput = new \Ease\Html\ATag('#', $fieldProperties['value'], ['class' => 'editable', 'id' => 'value', 'data-pk' => $fieldProperties['id'], 'data-url' => 'companyenv.php', 'data-title' => _('Update value')]);
            $tableRow->addItem(new \Ease\Html\TdTag($valueInput, ['style' => 'width: 80%']));
        }

        $newItemForm = new \Ease\TWB4\Form();
        $newItemForm->addInput(new \Ease\Html\InputTextTag('env[newkey]'), _('New Config field'), _('Keyword'), _('Create New field here'));
        $newItemForm->addInput(new \Ease\Html\InputTextTag('env[newvalue]'), _('New Config value'), _('Value'), _('Enter New field value here'));
        $newItemForm->addItem(new \Ease\TWB4\SubmitButton(_('Add new field'), 'success'));
        
        $this->addRowFooterColumns([new \Ease\Html\DivTag($newItemForm, ['class' => 'form-row']), sprintf(_('%s items'), count($this->fields))]);
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
//    public function &addRowColumns($columns = null, $properties = [])
//    {
//        $key = [new InplaceInput($columns[0]), ' 🖉'];
//        $value = [new InplaceInput($columns[1]), ' 🖉'];
//        return parent::addRowColumns([$key, $value], $properties);
//    }
}
