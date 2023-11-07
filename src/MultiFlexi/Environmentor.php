<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of Environmentor
 *
 * @author vitex
 */
abstract class Environmentor
{
    /**
     *
     * @var Job
     */
    public $engine;

    /**
     *
     * @param Job $engine
     */
    public function __construct(Job $engine)
    {
        $this->engine = $engine;
    }
}
