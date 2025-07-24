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
class ExecutorSelect extends \Ease\Html\SelectTag
{
    public array $executors = [];

    /**
     *  Executor Select.
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
     * Executors list.
     */
    public function loadItems(): array
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Executor');
        $executors = \Ease\Functions::classesInNamespace('MultiFlexi\\Executor');
        $items = [];

        foreach ($executors as $injector) {
            $executorClass = '\\MultiFlexi\\Executor\\'.$injector;
            $items[$injector] = $executorClass::description();
            $this->executors[$injector] = $executorClass;
        }

        return $items;
    }
}
