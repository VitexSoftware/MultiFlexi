<?php

/**
 * Multi Flexi - Runner Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of Runner
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
     * SystemD service name
     *
     * @param string $service
     *
     * @return bool
     */
    public static function isServiceActive($service)
    {
        return (trim(shell_exec("systemctl is-active $service")) == "active");
    }
}
