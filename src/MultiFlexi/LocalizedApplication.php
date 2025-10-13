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
 * Application with localization support.
 *
 * This class extends the base Application class with translation capabilities
 */
class LocalizedApplication extends Application
{
    use ApplicationTranslation;

    /**
     * Override the base method to check if method exists
     * This allows compatibility with existing code.
     *
     * @param mixed $method
     * @param mixed $args
     */
    public function __call($method, $args)
    {
        // Check if it's a localization method
        if (method_exists($this, $method)) {
            return \call_user_func_array([$this, $method], $args);
        }

        // Fall back to parent
        if (method_exists(parent::class, '__call')) {
            return parent::__call($method, $args);
        }

        throw new \BadMethodCallException("Method {$method} does not exist");
    }
}
