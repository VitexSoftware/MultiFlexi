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
    use \Ease\TWB4\Widgets\Selectizer;
    public array $executors = [];
    protected array $executorData = [];

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
        // Load executors if items not provided or empty
        if ($items === null || empty($items)) {
            $items = $this->loadItems();
        }

        parent::__construct($name, $items, $defaultValue, $properties);

        // Setup selectize with executor logos immediately in constructor
        $selectizeProps = [
            'valueField' => 'value',
            'labelField' => 'text',
            'searchField' => ['text', 'name'],
        ];

        // Render function for selected item (shows logo and name)
        $selectizeProps['render']['item'] = 'function (item, escape) { '
            .'return "<div><img src=\"" + escape(item.logo) + "\" height=20 style=\"margin-right: 8px; vertical-align: middle;\"> " + escape(item.text) + "</div>" '
            .'}';

        // Render function for dropdown options (shows logo and description)
        $selectizeProps['render']['option'] = 'function (item, escape) { '
            .'return "<div style=\"display: flex; align-items: center; padding: 5px 0;\">'
            .'<img src=\"" + escape(item.logo) + "\" height=48 style=\"margin-right: 12px; flex-shrink: 0;\">'
            .'<div><strong>" + escape(item.name) + "</strong><br><small style=\"color: #666;\">" + escape(item.text) + "</small></div>'
            .'</div>" '
            .'}';

        $this->selectize($selectizeProps, $this->executorData);
    }

    /**
     * Executors list with metadata for selectize.
     */
    public function loadItems(): array
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Executor');
        $executors = \Ease\Functions::classesInNamespace('MultiFlexi\\Executor');
        $items = [];

        foreach ($executors as $injector) {
            $executorClass = '\\MultiFlexi\\Executor\\'.$injector;

            if (!class_exists($executorClass)) {
                continue;
            }

            $items[$injector] = $executorClass::description();
            $this->executors[$injector] = $executorClass;

            // Prepare data for selectize with logo and description
            $this->executorData[] = [
                'value' => $injector,
                'text' => $executorClass::description(),
                'logo' => $executorClass::logo(),
                'name' => $executorClass::name(),
            ];
        }

        return $items;
    }
}
