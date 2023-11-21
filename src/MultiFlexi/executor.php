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
    public function launch();
    public function storeLogs();
}
