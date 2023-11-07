<?php
declare(strict_types=1);
/**
 * Multi Flexi - AbraFlexi environment variables handler
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Env;

/**
 * Description of AbraFlexi
 *
 * @author vitex
 */
class AbraFlexi implements Injector
{
    public static function allKeysHandled()
    {
        return [
            'ABRAFLEXI_URL',
            'ABRAFLEXI_LOGIN',
            'ABRAFLEXI_PASSWORD',
            'ABRAFLEXI_COMPANY'
            ];
    }
}
