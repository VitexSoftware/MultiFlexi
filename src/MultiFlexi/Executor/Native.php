<?php

declare(strict_types=1);

/**
 * Multi Flexi - native Executor
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Executor;

/**
 * Description of Native
 *
 * @author vitex
 */
class Native extends \Ease\Sand implements \MultiFlexi\executor
{
    use \Ease\Logger\Logging;

    /**
     *
     * @return string
     */
    public static function name(): string
    {
        return _('Native');
    }

    /**
     *
     * @return string
     */
    public static function description(): string
    {
        return _('Run Job on same machine as MultiFlexi itself');
    }

    public function launch()
    {
    }

    public function storeLogs()
    {
        ;
    }
}
