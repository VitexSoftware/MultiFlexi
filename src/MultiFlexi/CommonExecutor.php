<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of Executor
 *
 * @author vitex
 */
abstract class CommonExecutor extends \Ease\Sand
{
    use \Ease\Logger\Logging;

    /**
     *
     * @var Job
     */
    public $job;


    /**
     *
     * @var string
     */
    public $stdin;
    /**
     *
     * @var string
     */
    public $stdout;
    /**
     *
     * @var string
     */
    public $stderr;


    /**
     *
     * @param Job $job
     */
    public function __construct($job)
    {
        $this->job = $job;
    }
}
