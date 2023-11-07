<?php
declare(strict_types=1);
/**
 * Multi Flexi - Handle Loging Environment variables 
 *
 * @author    Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2023 Vitex Software
 */

namespace MultiFlexi\Env;

/**
 * Description of Logger
 *
 * @author vitex
 */
class Logger implements Injector
{
    
    public static function allKeysHandled()
    {
        return ['EASE_LOGGER'];
    }
    
}
