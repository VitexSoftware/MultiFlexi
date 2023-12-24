<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

/**
 *
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of AppExecutorSelect
 *
 * @author vitex
 */
class AppExecutorSelect extends ExecutorSelect
{
    /**
     *
     * @var \MultiFlexi\Application
     */
    private $app;

    /**
     * Choose from launchers availble for given Application
     *
     * @param \MultiFlexi\Application $app
     * @param array  $items
     * @param string $defaultValue
     * @param array  $properties
     */
    public function __construct($app, $items = null, $defaultValue = 'Native', $properties = [])
    {
        $this->app = $app;
        parent::__construct('executor', $items, $defaultValue, $properties);
    }

    /**
     * Executors list
     *
     * @return array
     */
    public function loadItems(): array
    {
        $allExectutors = parent::loadItems();
        foreach ($this->executors as $executorName => $executorClass) {
            if ($executorClass::usableForApp($this->app) === false) {
                unset($allExectutors[$executorName]);
            }
        }
        return $allExectutors;
    }
}
