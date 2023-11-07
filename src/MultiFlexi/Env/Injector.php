<?php

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Env;

/**
 *
 * @author vitex
 */
interface Injector
{
    /**
     * List of all known keys
     *
     * @return array
     */
    public static function allKeysHandled();

    /**
     * Computed keys with values
     *
     * @return array
     */
    public function getEnvironment();
}
