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
 * Description of ActionImage.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class ActionImage extends \Ease\Html\ImgTag
{
    /**
     * Action Image.
     *
     * @param array<string, string> $properties
     */
    public function __construct(string $actionName, array $properties = [])
    {
        $actionClass = '\\MultiFlexi\\Action\\'.$actionName;

        if (class_exists($actionClass)) {
            $image = $actionClass::logo();
            $properties['title'] = $actionClass::description();
        } else {
            $image = 'images/cancel.svg';
            $properties['title'] = _('Action not availble');
        }

        parent::__construct($image, $actionName, $properties);
    }
}
