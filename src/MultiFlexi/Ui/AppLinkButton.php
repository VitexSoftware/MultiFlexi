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
 * Description of AppLinkButton.
 *
 * @author vitex
 */
class AppLinkButton extends \Ease\TWB5\LinkButton
{
    public function __construct(\MultiFlexi\Application $app, $properties = [])
    {
        parent::__construct('app.php?id='.$app->getMyKey(), [new AppLogo($app, ['style' => 'height: 64px']), '&nbsp;', _($app->getRecordName())], 'inverse', $properties);
    }
}
