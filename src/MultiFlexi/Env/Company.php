<?php
declare(strict_types=1);
/**
 * Multi Flexi - Company Environment Handler
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Env;

/**
 * Description of Company
 *
 * @author vitex
 */
class Company implements Injector
{
    public static function allKeysHandled()
    {
        return ['EASE_LOGGER'];
    }
}
