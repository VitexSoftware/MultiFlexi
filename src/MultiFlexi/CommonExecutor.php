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
 * Description of Executor.
 *
 * @author vitex
 */
abstract class CommonExecutor extends \Ease\Sand
{
    use \Ease\Logger\Logging;
    public Job $job;
    public string $stdin;
    public string $stdout;
    public string $stderr;
    public $environment = [];
    public array $outputCache = [];

    /**
     * @param Job $job
     */
    public function __construct($job)
    {
        $this->job = $job;
        $this->setObjectName();
        $this->setObjectName($job->getMyKey().'@'.\Ease\Logger\Message::getCallerName($this));
        $this->environment = $job->getFullEnvironment();
    }

    /**
     * Add Output line into cache.
     *
     * @param mixed $line
     * @param mixed $type
     */
    public function addOutput($line, $type): void
    {
        $this->outputCache[microtime()] = ['line' => $line, 'type' => $type];
    }

    /**
     * Get Output cache as plaintext.
     */
    public function getOutputCachePlaintext()
    {
        $output = '';

        foreach ($this->outputCache as $line) {
            $output .= $line['line']."\n";
        }

        return $output;
    }
}
