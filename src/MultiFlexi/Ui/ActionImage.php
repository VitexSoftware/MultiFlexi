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
 * Description of ActionImage
 *
 * @author vitex
 */
class ActionImage extends \Ease\Html\ImgTag
{
    public function __construct($actionName, $properties = [])
    {
        $actionClass = '\\MultiFlexi\\Action\\' . $actionName;
        if (class_exists($actionClass)) {
            $image = $actionClass::logo();
            $properties['title'] = $actionClass::description();
        } else {
            $image = 'img/cancel.svg';
            $properties['title'] = _('Action not availble');
        }

        parent::__construct($image, $actionName, $properties);
    }
}
