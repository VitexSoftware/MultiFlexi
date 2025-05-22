<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi\Ui;

/**
 * Description of ExitCode.
 *
 * @author vitex
 */
class ExitCode extends \Ease\TWB5\Badge
{
    public function __construct($exitcode, $properties = [])
    {
        parent::__construct(self::status($exitcode), '&nbsp'.(null === $exitcode ? '⏳' : $exitcode).'&nbsp', $properties);
    }

    /**
     * Exit Code.
     *
     * @param int $exitcode
     *
     * @return string bootstrap color
     */
    public static function status($exitcode)
    {
        switch ($exitcode) {
            case -1:
                $type = 'secondary';

                break;
            case 0:
                if (null === $exitcode) {
                    $type = 'info';
                } else {
                    $type = 'success';
                }

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
