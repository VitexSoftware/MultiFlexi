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

namespace MultiFlexi\Ui\Form;

/**
 * Description of FioBank.
 *
 * @author vitex
 */
class FioBank extends \Ease\TWB4\Panel implements configForm
{
    public static string $logo = 'images/FioBank.svg';
    #[\Override]
    public static function fields(): array
    {
        return [
            'ACCOUNT_NUMBER' => [
                'type' => 'string',
                'description' => _('Number of Account'),
                'defval' => '',
                'required' => 0,
            ],
            'FIO_TOKEN' => [
                'type' => 'string',
                'description' => _('Token for account'),
                'defval' => '',
                'required' => 0,
            ],
            'FIO_TOKEN_NAME' => [
                'type' => 'string',
                'description' => _('Name of Token used'),
                'defval' => '',
                'required' => 0,
            ],
        ];
    }

    #[\Override]
    public static function name(): string
    {
        return _('Fio Bank');
    }
}
