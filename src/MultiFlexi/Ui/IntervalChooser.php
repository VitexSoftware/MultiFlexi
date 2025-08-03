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

namespace MultiFlexi\Ui;

use MultiFlexi\RunTemplate;

/**
 * Description of IntervalChooser.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class IntervalChooser extends \Ease\Html\SelectTag
{
    /**
     * Script run interval chooser.
     */
    public function __construct(string $name, string $defaultValue = '', array $properties = [])
    {
        parent::__construct($name, [
            \MultiFlexi\Scheduler::$intervCron['n'] => RunTemplate::getIntervalEmoji('n').' '._('Disabled'),
            \MultiFlexi\Scheduler::$intervCron['i'] => RunTemplate::getIntervalEmoji('i').' '._('Minutly'),
            \MultiFlexi\Scheduler::$intervCron['h'] => RunTemplate::getIntervalEmoji('h').' '._('Hourly'),
            \MultiFlexi\Scheduler::$intervCron['d'] => RunTemplate::getIntervalEmoji('d').' '._('Daily'),
            \MultiFlexi\Scheduler::$intervCron['w'] => RunTemplate::getIntervalEmoji('w').' '._('Weekly'),
            \MultiFlexi\Scheduler::$intervCron['m'] => RunTemplate::getIntervalEmoji('m').' '._('Monthly'),
            \MultiFlexi\Scheduler::$intervCron['y'] => RunTemplate::getIntervalEmoji('y').' '._('Yearly'),
        ], $defaultValue, $properties);
    }
}
