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
abstract class CommonAction extends \Ease\Sand
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
     * @param Job   $job
     * @param array $options Action Options
     */
    public function __construct($job, $options = [])
    {
        $this->job = $job;
        $this->setData($options);
        $this->setObjectName();
        $this->setObjectName($job->getMyKey() . '@' . \Ease\Logger\Message::getCallerName($this));
        $this->environment = $job->getFullEnvironment();
        $this->loadOptions();
    }

    public function loadOptions()
    {
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

    /**
     * Form Inputs
     *
     * @return \Ease\Embedable
     */
    public static function inputs(string $action)
    {
        return new \Ease\TWB4\Badge('info', _('No Fields required') . ' (' . $action . ')');
    }

    /**
     *
     * @return \Ease\Embedable
     */
    public static function configForm()
    {
        return new \Ease\TWB4\Badge('info', _('No Configuration required'));
    }

    /**
     * Perform Action
     */
    public function perform()
    {
        $this->addStatusMessage(_('No Action performed'), 'debug');
    }

    /**
     * Is this Action Situable for Application
     *
     * @param Application $app
     */
    public static function usableForApp($app): bool
    {
        return false;
    }
}
