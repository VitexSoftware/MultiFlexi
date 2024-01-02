<?php

/**
 * Multi Flexi - Executor Modules Listing
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of EnvModulesListing
 *
 * @author vitex
 */
class ActionsChooser extends \Ease\Html\DivTag {

    public function __construct($prefix = '', $toggles = [], $properties = []) {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Action');
        $actions = \Ease\Functions::classesInNamespace('MultiFlexi\\Action');

        parent::__construct(null, $properties);
        foreach ($actions as $action) {
            $actionClass = '\\MultiFlexi\\Action\\' . $action;
            $moduleRow = new \Ease\TWB4\Row();

            $moduleRow->addColumn(1, new ActionImage($action, ['height' => '50px']));
            $moduleRow->addColumn(1, new \Ease\TWB4\Widgets\Toggle($prefix . 'actionSwitch[' . $action . ']', (array_key_exists($action, $toggles) && $toggles[$action])));
            $moduleRow->addColumn(4, [new \Ease\Html\StrongTag($actionClass::name()), new \Ease\Html\PTag(new \Ease\Html\SmallTag($actionClass::description()))]);
            $moduleRow->addColumn(4, $actionClass::inputs($toggles));
            $this->addItem(new \Ease\Html\PTag($moduleRow));
        }
    }

    /**
     * 
     * 
     * @param string $prefix
     * 
     * @return array
     */
    public static function toggles($prefix) {
        $toggles = [];
        $updates = \Ease\WebPage::getRequestValue($prefix . 'actionSwitch') ? \Ease\WebPage::getRequestValue($prefix . 'actionSwitch') : [];
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Action');
        $actions = \Ease\Functions::classesInNamespace('MultiFlexi\\Action');
        foreach ($actions as $action) {
            $toggles[$action] = array_key_exists($action, $updates);
        }
        return $toggles;
    }
}
