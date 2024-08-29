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
 * @author vitex
 */
interface executor
{
    public static function name(): string;

    public static function description(): string;

    public function launch(string $command);

    public function launchJob();

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
     * Logo for Launcher.
     */
    public static function logo();
}
