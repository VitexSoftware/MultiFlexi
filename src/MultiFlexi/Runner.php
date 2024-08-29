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

namespace MultiFlexi;

/**
 * Description of Runner.
 *
 * @author vitex
 */
class Runner extends \Ease\Sand
{
    public function __construct()
    {
        $this->setObjectName();
    }

    /**
     * SystemD service name.
     *
     * @param string $service
     *
     * @return bool
     */
    public static function isServiceActive($service)
    {
        return trim((string) shell_exec("systemctl is-active {$service}")) === 'active';
    }
}
