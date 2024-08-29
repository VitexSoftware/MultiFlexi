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

namespace MultiFlexi\Env;

/**
 * @author vitex
 */
interface injector
{
    /**
     * List of all known keys.
     *
     * @return array
     */
    public static function allKeysHandled();

    /**
     * Computed keys with values.
     *
     * @return array
     */
    public function getEnvironment();

    /**
     * Add MetaData to Environment Fields.
     *
     * @return array
     */
    public function addMetaData(array $environment);

    /**
     * @return string name
     */
    public static function name();

    /**
     * @return string name
     */
    public static function description();
}
