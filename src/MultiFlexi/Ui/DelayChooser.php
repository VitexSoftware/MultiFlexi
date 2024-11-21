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
 * Description of DelayChooser.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class DelayChooser extends \Ease\Html\InputTag
{
    /**
     * DelayChooser.
     *
     * @param int                       $value      seconds
     * @param array<string, int|string> $properties
     */
    public function __construct(string $name, int $value, array $properties = [])
    {
        $properties['type'] = 'text';
        $properties['maxlength'] = 8;
        $properties['pattern'] = '^((\d+:)?\d+:)?\d*$';
        $properties['title'] = _('The amount of seconds, optionally preceded by minutes: or by hours:minutes: (empty or zero leads for immediate launch).');
        $properties['placeholder'] = _('hh:mm:ss (leave blank or zero for immediate launch)');
        $properties['size'] = 30;
        parent::__construct($name, gmdate('H:i:s', $value), $properties);
    }
}
