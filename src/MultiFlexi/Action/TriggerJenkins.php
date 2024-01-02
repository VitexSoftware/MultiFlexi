<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Action;

/**
 * Description of TriggerJenkins
 *
 * @author vitex
 */
class TriggerJenkins extends \MultiFlexi\CommonAction
{
    /**
     * Module Caption
     *
     * @return string
     */
    public static function name()
    {
        return _('Jenkins');
    }

    /**
     * Module Description
     *
     * @return string
     */
    public static function description()
    {
        return _('Trigger Jenkins job');
    }

    public static function logo()
    {
        return '';
    }
}
