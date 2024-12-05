<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of Requirement
 *
 * @author Vitex <info@vitexsoftware.cz> 
 */
class Requirement {

    public static function formsAvailable(): array {
        $forms = [];
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\Ui\Form');
        foreach (\Ease\Functions::classesInNamespace('MultiFlexi\Ui\Form') as $form){
            $forms[$form] = '\MultiFlexi\Ui\Form\\'.$form;
        }
        return $forms;
    }
}
