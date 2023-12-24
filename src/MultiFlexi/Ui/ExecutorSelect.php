<?php

/**
 * Multi Flexi - Executor Modules Selector
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
class ExecutorSelect extends \Ease\Html\SelectTag
{
    /**
     *
     * @var array
     */
    public $executors = [];

    /**
     *  Executor Select
     *
     * @param string $name
     * @param array  $items
     * @param string $defaultValue
     * @param array  $properties
     */
    public function __construct($name, $items = null, $defaultValue = 'Native', $properties = [])
    {
        parent::__construct($name, $items, $defaultValue, $properties);
    }

    /**
     * Executors list
     *
     * @return array
     */
    public function loadItems(): array
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Executor');
        $executors = \Ease\Functions::classesInNamespace('MultiFlexi\\Executor');
        $items = [];
        foreach ($executors as $injector) {
            $executorClass = '\\MultiFlexi\\Executor\\' . $injector;
            $items[$injector] = $executorClass::description();
            $this->executors[$injector] = $executorClass;
        }
        return $items;
    }
}
