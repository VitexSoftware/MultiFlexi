<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of ExitCode
 *
 * @author vitex
 */
class ExitCode extends \Ease\TWB4\Badge
{

    public function __construct($exitcode, $properties = [])
    {
        switch (intval($exitcode)) {
            case 0:
                $type = 'success';
                break;
            case 127: 
                $type = 'warning';
                break;
            default:
                $type = 'error';
                break;
        }
        parent::__construct($type, '&nbsp'.$exitcode.'&nbsp', $properties);
    }
}
