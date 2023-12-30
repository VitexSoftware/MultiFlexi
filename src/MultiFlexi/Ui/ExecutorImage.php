<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

/**
 *
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of ExecutorImage
 *
 * @author vitex
 */
class ExecutorImage extends \Ease\Html\ImgTag
{
    public function __construct($executorName, $properties = [])
    {
        $executorClass = '\\MultiFlexi\\Executor\\' . $executorName;
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
