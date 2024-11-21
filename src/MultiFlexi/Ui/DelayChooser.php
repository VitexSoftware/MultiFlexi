<?php
declare(strict_types=1);

/**
 * MultiFlexi - DelayChooser
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2024 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of DelayChooser
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class DelayChooser extends \Ease\Html\InputTag {
    /**
     * DelayChooser
     *
     * @param string $name
     * @param int $value seconds
     * @param array<string, int|string> $properties
     */
    public function __construct(string $name, int $value,array $properties = []) {
        $time = date('H:i:s', $value);
        $properties['type'] = 'text';
        $properties['maxlength'] = 8;
        $properties['pattern'] = '^((\d+:)?\d+:)?\d*$';
        $properties['title'] = _('The amount of seconds, optionally preceded by minutes: or by hours:minutes: (empty or zero leads for immediate launch).');
        $properties['placeholder'] = _('hh:mm:ss (leave blank or zero for immediate launch)');
        $properties['size'] = 30;
        parent::__construct($name, $time, $properties);
    }
}
