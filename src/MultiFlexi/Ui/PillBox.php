<?php

/**
 * Multi Flexi  - PillBox
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright (c) 2017-2019, Vítězslav Dvořák
 */

namespace MultiFlexi\Ui;

/**
 * Description of GroupChooser
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class PillBox extends \Ease\Html\InputTextTag
{
    public function __construct(
        $name,
        $valuesAvailble,
        $valuesShown,
        $properties = array()
    ) {
        parent::__construct(
            $name,
            is_array($valuesShown) ? implode(',', $valuesShown) : $valuesShown,
            $properties
        );
        $this->setTagID($name . 'pillBox');

        $this->addJavaScript("
$('#" . $this->getTagID() . "').selectize({
        plugins: ['remove_button'],
        valueField: 'id',
        maxOptions: 10000,
	labelField: 'name',  
        searchField: 'name',
        persist: true,
        options: " . json_encode($valuesAvailble) . " 
});
");
    }
}
