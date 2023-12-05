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
        parent::__construct(self::status($exitcode), '&nbsp' . $exitcode . '&nbsp', $properties);
    }

    /**
     * Exit Code
     *
     * @param int $exitcode
     *
     * @return string bootstrap color
     */
    public static function status($exitcode)
    {
        switch (intval($exitcode)) {
            case -1:
                $type = 'inverse';
                break;
            case 0:
                $type = 'success';
                break;
            case 127:
                $type = 'warning';
                break;
            default:
                $type = 'danger';
                break;
        }
        return $type;
    }
}
