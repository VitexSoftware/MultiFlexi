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

use Ease\Embedable;
use Ease\Logger\Logging;
use Ease\Logger\Message;
use Ease\Sand;
use Ease\TWB4\Badge;

/**
 * Description of Executor.
 *
 * @author vitex
 */
abstract class CommonAction extends Sand
{
    use Logging;
    public RunTemplate $runtemplate;
    public string $stdin;
    public string $stdout;
    public string $stderr;
    public $environment = [];
    public array $outputCache = [];

    /**
     * @param array $options Action Options
     */
    public function __construct(RunTemplate $runtemplate, $options = [])
    {
        $this->runtemplate = $runtemplate;
        $this->setData($options);
        $this->setObjectName();
        $this->setObjectName($runtemplate->getMyKey().'@'.Message::getCallerName($this));
        $this->environment = $runtemplate->getAppEnvironment();
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
     * @return Embedable
     */
    public static function inputs(string $action)
    {
        return new Badge('info', _('No Fields required').' ('.$action.')');
    }

    /**
     * @return Embedable
     */
    public static function configForm()
    {
        return new Badge('info', _('No Configuration required'));
    }

    /**
     * Perform Action.
     */
    public function perform(Job $job): void
    {
        $this->addStatusMessage(_('No Action performed'), 'debug');
    }

    /**
     * Is this Action available for Application.
     */
    public static function usableForApp(Application $app): bool
    {
        return false;
    }

    public function initialData(string $mode): array
    {
        return [];
    }
}
