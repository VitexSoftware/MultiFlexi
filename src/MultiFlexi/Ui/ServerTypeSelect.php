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
 * Description of EngineSelect.
 *
 * @author vitex
 */
class ServerTypeSelect extends \Ease\Html\SelectTag
{
    public function __construct($name, $value = '')
    {
        parent::__construct($name, ['AbraFlexi' => 'AbraFlexi', 'Pohoda' => _('Stormware Pohoda')], $value);
    }
}
