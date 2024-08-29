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

/**
 * Description of IntervalChooser.
 *
 * @author vitex
 */
class IntervalChooser extends \Ease\Html\SelectTag
{
    /**
     * Script run interval chooser.
     *
     * @param string $name
     * @param string $defaultValue
     * @param array  $properties
     */
    public function __construct($name, $defaultValue = null, $properties = [])
    {
        parent::__construct($name, [
            'n' => _('Disabled'),
            'i' => _('Minutly'),
            'h' => _('Hourly'),
            'd' => _('Daily'),
            'w' => _('Weekly'),
            'm' => _('Monthly'),
            'y' => _('Yearly'),
        ], $defaultValue, $properties);
    }
}
