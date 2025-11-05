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
 * Description of EnvModulesListing.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class ActionsChooser extends \Ease\Html\DivTag
{
    public \MultiFlexi\RunTemplate $runtemplate;
    
    public function __construct($prefix, \MultiFlexi\RunTemplate $runTemplate, $toggles = [], $properties = [])
    {
        $this->runtemplate = $runTemplate;
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Action');
        $actions = \Ease\Functions::classesInNamespace('MultiFlexi\\Action');

        parent::__construct(null, $properties);

        foreach ($actions as $action) {
            $actionClass = '\\MultiFlexi\\Action\\'.$action;

            if ($actionClass::usableForApp($runTemplate->getApplication())) {
                $moduleRow = new \Ease\TWB4\Row();

                $moduleRow->addColumn(1, new ActionImage($action, ['height' => '50px']));
                $moduleRow->addColumn(1, new \Ease\TWB4\Widgets\Toggle($prefix.'actionSwitch['.$action.']', \array_key_exists($action, $toggles) && $toggles[$action], '', ['data-size' => 'lg']));
                $moduleRow->addColumn(4, [new \Ease\Html\StrongTag($actionClass::name()), new \Ease\Html\PTag(new \Ease\Html\SmallTag($actionClass::description()))]);
                $moduleRow->addColumn(4, self::getActionInputs($action, $prefix));
                $moduleRow->setTagClass('form-row');
                $this->addItem(new \Ease\Html\PTag($moduleRow));
            }
        }
    }

    /**
     * @return array
     */
    public static function toggles(string $prefix)
    {
        $toggles = [];
        $updates = \Ease\WebPage::getRequestValue($prefix.'actionSwitch') ?: [];
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Action');
        $actions = \Ease\Functions::classesInNamespace('MultiFlexi\\Action');

        foreach ($actions as $action) {
            $toggles[$action] = \array_key_exists($action, $updates);
        }

        return $toggles;
    }

    /**
     * @return array
     */
    public static function formModuleCofig(string $prefix)
    {
        return \Ease\WebPage::getRequestValue($prefix) ? \Ease\WebPage::getRequestValue($prefix) : [];
    }

    /**
     * SQL To Form Data.
     *
     * @param \Envms\FluentPDO\Queries\Select $query
     *
     * @return array
     */
    public static function sqlToForm($query)
    {
        $formData = [];

        foreach ($query as $field) {
            $formData[$field['mode'].'['.$field['module'].']['.$field['keyname'].']'] = $field['value'];
        }

        return $formData;
    }

    /**
     * Get action input fields from UI classes or fallback to core action class.
     *
     * @param string $action Action name
     * @param string $prefix Form field prefix
     *
     * @return mixed Form field(s)
     */
    public function getActionInputs(string $action, string $prefix)
    {
        // First try to use the UI-specific class
        $uiActionClass = '\\MultiFlexi\\Ui\\Action\\'.$action;

        if (class_exists($uiActionClass) && method_exists($uiActionClass, 'inputs')) {
            // Create instance with a dummy RunTemplate since we only need inputs
            $instance = new $uiActionClass($this->runtemplate);
            return $instance->inputs($prefix);
        }

        // If no inputs method exists, return empty badge
        return new \Ease\TWB4\Badge('secondary', _('No configuration required'));
    }
}
