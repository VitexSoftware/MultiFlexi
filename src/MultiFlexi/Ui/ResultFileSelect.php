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
 * Description of ResultFileSelect.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class ResultFileSelect extends \Ease\Html\SelectTag
{
    public function __construct(\MultiFlexi\Application $engine)
    {
        $appConfigs = \MultiFlexi\Conffield::getAppConfigs($engine->getMyKey());
        $items = ['' => _('None')];

        foreach ($appConfigs as $appConfigField) {
            $items[$appConfigField['keyname']] = $appConfigField['keyname'];
        }

        parent::__construct('resultfile', $items, '');
    }
}