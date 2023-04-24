<?php

/**
 * Multi Flexi - Envirnnment view
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

/**
 * Description of EnvironmentView
 *
 * @author vitex
 */
class EnvironmentView extends \Ease\Html\TableTag
{

    /**
     * 
     * @param array $environment
     * @param array $properties
     */
    public function __construct($environment = null, $properties = [])
    {
        $properties['class'] = 'table';
        parent::__construct(null, $properties);
        foreach ($environment as $key => $value) {
            if (stristr($key, 'pass')) {
                $value = preg_replace('(.)', '*', $value);
            }
            $this->addRowColumns([$key, $value]);
        }
    }
}
