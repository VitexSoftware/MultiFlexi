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
    public $environment = [];

    /**
     * @var array
     */
    public $outputCache = [];

    /**
     *
     * @param Job $job
     */
    public function __construct($job)
    {
        $this->job = $job;
        $this->setObjectName($job->getMyKey() . '@' . \Ease\Logger\Message::getCallerName($this));
        $this->environment = $job->getFullEnvironment();
    }

    /**
     * Add Output line into cache
     */
    public function addOutput($line, $type)
    {
        $this->outputCache[microtime()] = ['line' => $line, 'type' => $type];
    }

    /**
     * Get Output cache as plaintext
     */
    public function getOutputCachePlaintext()
    {
        $output = '';
        foreach ($this->outputCache as $line) {
            $output .= $line['line'] . "\n";
        }
        return $output;
    }
}
