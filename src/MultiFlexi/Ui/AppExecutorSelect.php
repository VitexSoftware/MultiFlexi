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
 *
 * @no-named-arguments
 */
class AppExecutorSelect extends ExecutorSelect
{
    private \MultiFlexi\Application $app;

    /**
     * Choose from launchers available for given Application.
     */
    public function __construct(\MultiFlexi\Application $app, array $items = [], string $defaultValue = 'Native', array $properties = [])
    {
        $this->app = $app;
        parent::__construct('executor', $items, $defaultValue, $properties);
    }

    /**
     * Executors list - filtered by app compatibility.
     */
    #[\Override]
    public function loadItems(): array
    {
        $allExecutors = parent::loadItems();

        // Filter executors that are not usable for this app
        $unusableExecutors = [];

        foreach ($this->executors as $executorName => $executorClass) {
            if ($executorClass::usableForApp($this->app) === false) {
                unset($allExecutors[$executorName]);
                $unusableExecutors[] = $executorName;
            }
        }

        // Also filter executorData for selectize
        if (!empty($unusableExecutors)) {
            $this->executorData = array_filter(
                $this->executorData,
                static function ($item) use ($unusableExecutors) {
                    return !\in_array($item['value'], $unusableExecutors, true);
                },
            );
            // Reindex array
            $this->executorData = array_values($this->executorData);
        }

        return $allExecutors;
    }
}
