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
abstract class CommonAction extends \Ease\Sand
{
    use \Ease\Logger\Logging;
    public Job $job;
    public string $stdin;
    public string $stdout;
    public string $stderr;
    public $environment = [];
    public array $outputCache = [];

    /**
     * @param Job   $job
     * @param array $options Action Options
     */
    public function __construct($job, $options = [])
    {
        $this->job = $job;
        $this->setData($options);
        $this->setObjectName();
        $this->setObjectName($job->getMyKey().'@'.\Ease\Logger\Message::getCallerName($this));
        $this->environment = $job->getFullEnvironment();
        $this->loadOptions();
    }

    public function loadOptions(): void
    {
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
     * Get Output cache as plain text.
     */
    public function getOutputCachePlaintext()
    {
        $output = '';

        foreach ($this->outputCache as $line) {
            $output .= $line['line']."\n";
        }

        return $output;
    }

    /**
     * Form Inputs.
     *
     * @return \Ease\Embedable
     */
    public static function inputs(string $action)
    {
        return new \Ease\TWB4\Badge('info', _('No Fields required').' ('.$action.')');
    }

    /**
     * @return \Ease\Embedable
     */
    public static function configForm()
    {
        return new \Ease\TWB4\Badge('info', _('No Configuration required'));
    }

    /**
     * Perform Action.
     */
    public function perform(): void
    {
        $this->addStatusMessage(_('No Action performed'), 'debug');
    }

    /**
     * Is this Action available for Application.
     *
     * @param Application $app
     */
    public static function usableForApp($app): bool
    {
        return false;
    }
    
    public function initialData($param): array {
        return [];
    }
    
}
