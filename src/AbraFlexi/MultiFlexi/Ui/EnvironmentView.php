<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

/**
 * Description of EnvironmentView
 *
 * @author vitex
 */
class EnvironmentView extends \Ease\Html\TableTag
{

    public function __construct($environment = null, $properties = [])
    {
        $properties['class'] = 'table';
        parent::__construct(null, $properties);
        foreach ($environment as $key => $value) {
            if (stristr($key, 'pass')) {
                $value = preg_replace('(.)', '*', $value);
            }
            parent::addRowColumns([$key, $value]);
        }
    }
}
