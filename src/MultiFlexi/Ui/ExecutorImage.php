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

/**
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of ExecutorImage.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class ExecutorImage extends \Ease\Html\ImgTag
{
    public function __construct($executorName, $properties = [])
    {
        $executorClass = '\\MultiFlexi\\Executor\\'.$executorName;

        if (class_exists($executorClass)) {
            $image = $executorClass::logo();
            $properties['title'] = $executorClass::description();
            $name = $executorClass::name();
        } else {
            $image = 'images/cancel.svg';
            $properties['title'] = _('Executor not availble');
            $name = _('Unknown');
        }

        parent::__construct($image, $name, $properties);
    }
}
