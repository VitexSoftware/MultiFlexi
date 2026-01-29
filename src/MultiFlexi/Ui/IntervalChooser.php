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
            'n' => \MultiFlexi\Scheduler::getIntervalEmoji('n').' '._('Disabled'),
            'i' => \MultiFlexi\Scheduler::getIntervalEmoji('i').' '._('Minutly'),
            'h' => \MultiFlexi\Scheduler::getIntervalEmoji('h').' '._('Hourly'),
            'd' => \MultiFlexi\Scheduler::getIntervalEmoji('d').' '._('Daily'),
            'w' => \MultiFlexi\Scheduler::getIntervalEmoji('w').' '._('Weekly'),
            'm' => \MultiFlexi\Scheduler::getIntervalEmoji('m').' '._('Monthly'),
            'y' => \MultiFlexi\Scheduler::getIntervalEmoji('y').' '._('Yearly'),
            'c' => \MultiFlexi\Scheduler::getIntervalEmoji('c').' '._('Custom'),
        ], $defaultValue, $properties);
    }
}
