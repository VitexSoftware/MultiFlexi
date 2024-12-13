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
 */
class IntervalChooser extends \Ease\Html\SelectTag
{
    /**
     * Script run interval chooser.
     */
    public function __construct(string $name, string $defaultValue = '', array $properties = [])
    {
        parent::__construct($name, [
            'n' => RunTemplate::getIntervalEmoji('n').' '._('Disabled'),
            'i' => RunTemplate::getIntervalEmoji('i').' '._('Minutly'),
            'h' => RunTemplate::getIntervalEmoji('h').' '._('Hourly'),
            'd' => RunTemplate::getIntervalEmoji('d').' '._('Daily'),
            'w' => RunTemplate::getIntervalEmoji('w').' '._('Weekly'),
            'm' => RunTemplate::getIntervalEmoji('m').' '._('Monthly'),
            'y' => RunTemplate::getIntervalEmoji('y').' '._('Yearly'),
        ], $defaultValue, $properties);
    }
}
