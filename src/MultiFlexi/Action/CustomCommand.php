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
namespace MultiFlexi\Action;

/**
 * Description of CustomCommand
 *
 * @author vitex
 */
class CustomCommand extends \MultiFlexi\CommonAction
{
    /**
     * Module Caption
     *
     * @return string
     */
    public static function name()
    {
        return _('Reschedule');
    }

    /**
     * Module Description
     *
     * @return string
     */
    public static function description()
    {
        return _('');
    }

    public static function logo()
    {
        return '';
    }
}
