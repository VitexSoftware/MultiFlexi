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

namespace MultiFlexi;

/**
 * Description of Requirement.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class Requirement
{
    public static function formsAvailable(): array
    {
        $forms = [];
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\Ui\Form');

        foreach (\Ease\Functions::classesInNamespace('MultiFlexi\Ui\Form') as $form) {
            $forms[$form] = '\MultiFlexi\Ui\Form\\'.$form;
        }

        return $forms;
    }
}
