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
 * Github Action UI Class.
 *
 * @author vitex
 */
class Github
{
    /**
     * Generate configuration form inputs for Github action.
     *
     * @param string $action Form field prefix (note: parameter name differs from other actions)
     *
     * @return \Ease\Embedable Form field(s)
     */
    public static function inputs(string $action): \Ease\Embedable
    {
        return new \Ease\TWB4\Badge('info', _('No Fields required').' ('.$action.')');
    }
}
