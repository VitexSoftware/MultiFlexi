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
 */
class ExecutorImage extends \Ease\Html\ImgTag
{
    public function __construct($executorName, $properties = [])
    {
        $executorClass = '\\MultiFlexi\\Executor\\'.$executorName;

        if (class_exists($executorClass)) {
            $image = $executorClass::logo();
            $properties['title'] = $executorClass::description();
        } else {
            $image = 'img/cancel.svg';
            $properties['title'] = _('Executor not availble');
        }

        parent::__construct($image, $executorName, $properties);
    }
}
