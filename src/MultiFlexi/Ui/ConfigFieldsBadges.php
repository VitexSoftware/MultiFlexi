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

use Ease\Container;
use Ease\TWB4\Badge;

/**
 * Description of Confiffields.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class ConfigFieldsBadges extends Container
{
    /**
     * @param mixed $content
     */
    public function __construct($content = null)
    {
        parent::__construct();

        foreach ($content as $conf) {
            $this->addItem(new Badge(\array_key_exists('state', $conf) ? $conf['state'] : 'secondary', $conf['type'].' '.$conf['keyname']));
            $this->addItem(' ');
        }
    }
}
