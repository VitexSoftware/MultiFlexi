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
 * Custom Command Action UI Class.
 *
 * @author vitex
 */
class CustomCommand
{
    /**
     * Generate configuration form inputs for Custom Command action.
     *
     * @param string $prefix Form field prefix
     *
     * @return \Ease\Embedable Form field(s)
     */
    public static function inputs(string $prefix): \Ease\Embedable
    {
        return new \Ease\TWB4\FormGroup(_('Command'), new \Ease\Html\InputTextTag($prefix.'[CustomCommand][command]'), '', _('Bash shell is used'));
    }
}
