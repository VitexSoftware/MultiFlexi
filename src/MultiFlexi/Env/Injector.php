<?php

/**
 * Multi Flexi - Environment Injector Interface
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

    /**
     * Add MetaData to Environment Fields
     *
     * @param array $environment
     *
     * @return array
     */
    public function addMetaData(array $environment);
}
