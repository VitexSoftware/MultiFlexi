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
 * Description of Wizard.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class Wizard extends \Ease\TWB5\Panel
{
    public $step = 0;

    public function __construct($heading = null, $type = null, $body = null, $footer = null)
    {
        parent::__construct($heading, $type, $body, $footer);
    }

    public function getPreviousStep()
    {
        return $this->step - 1;
    }

    public function getNextStep()
    {
        return $this->step + 1;
    }

    public function getStepLabel()
    {
        $steps = $this->steps();

        return \array_key_exists($this->step, $steps) ? $steps[$this->step] : _('n/a');
    }

    public function getStepPercent()
    {
        $totalSteps = \count($this->steps());
        $stepPercent = ($this->step + 1) / $totalSteps * 100;

        return round($stepPercent);
    }

    public function getStepBody(): mixed
    {
    }
}
