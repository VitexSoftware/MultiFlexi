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

namespace MultiFlexi\Ui\Action;

/**
 * Sleep Action UI Class.
 *
 * @author vitex
 */
class Sleep
{
    /**
     * Generate configuration form inputs for Sleep action.
     *
     * @param string $prefix Form field prefix
     *
     * @return array Form field(s)
     */
    public static function inputs(string $prefix): array
    {
        return [
            new \Ease\TWB4\FormGroup(_('Number of seconds'), new \Ease\Html\InputTextTag($prefix.'[Sleep][seconds]'), '60', _('Number of Seconds to wait')),
        ];
    }
}
