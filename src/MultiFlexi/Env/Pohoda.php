<?php
declare(strict_types=1);
/**
 * Multi Flexi - Stormware Pohoda Environment handler
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Env;

/**
 * Description of Pohoda
 *
 * @author vitex
 */
class Pohoda implements Injector
{

    public static function allKeysHandled()
    {
        return [
            'POHODA_URL',
            'POHODA_USERNAME',
            'POHODA_PASSWORD',
            'POHODA_ICO'
        ];
    }
}
