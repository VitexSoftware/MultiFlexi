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
 * Description of LaunchButton.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class LaunchButton extends \Ease\TWB4\LinkButton
{
    /**
     * @param int                   $appCompanyID
     * @param array<string, string> $properties
     */
    public function __construct($appCompanyID, $properties = [])
    {
        parent::__construct('launch.php?id='.$appCompanyID, [_('Launch').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'warning btn-lg btn-block ', $properties);
    }
}
