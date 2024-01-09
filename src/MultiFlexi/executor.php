<?php

/**
 * Multi Flexi - Job Executor Interface
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi;

/**
 *
 * @author vitex
 */
interface executor
{
    /**
     *
     * @return string
     */
    public static function name(): string;

    /**
     *
     * @return string
     */
    public static function description(): string;

    public function launch();

    public function getErrorOutput();

    public function getOutput();

    public function getExitCode();

    public function storeLogs();

    public function commandline();

    /**
     * Can this Executor execute given application ?
     *
     * @param Application $app
     */
    public static function usableForApp($app): bool;

    /**
     * Logo for Launcher
     */
    public static function logo();
}
