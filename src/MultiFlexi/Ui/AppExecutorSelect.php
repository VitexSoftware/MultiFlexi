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

/**
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of AppExecutorSelect.
 *
 * @author vitex
 */
class AppExecutorSelect extends ExecutorSelect
{
    private \MultiFlexi\Application $app;

    /**
     * Choose from launchers availble for given Application.
     *
     * @param \MultiFlexi\Application $app
     * @param array                   $items
     * @param string                  $defaultValue
     * @param array                   $properties
     */
    public function __construct($app, $items = null, $defaultValue = 'Native', $properties = [])
    {
        $this->app = $app;
        parent::__construct('executor', $items, $defaultValue, $properties);
    }

    /**
     * Executors list.
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
