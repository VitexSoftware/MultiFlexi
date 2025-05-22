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
 * @deprecated since version 1.27
 *
 * @author vitex
 */
class FioBank extends \Ease\TWB5\Panel implements configForm
{
    public static string $logo = 'images/FioBank.svg';

    public function __construct()
    {
        $header = new \Ease\TWB5\Row();
        $header->addColumn(6, new \Ease\Html\ATag('', new \Ease\Html\ImgTag(self::$logo, _('Fio Api'), ['height' => 50])));
        $header->addColumn(6, new \Ease\Html\H3Tag(_('Fio Bank API')));

        $body = new \Ease\Html\DivTag();

        $body->addItem(new \Ease\TWB5\FormGroup('ACCOUNT_NUMBER', new \Ease\Html\InputTextTag('ACCOUNT_NUMBER'), '', _('Fio Bank Account Number')));
        $body->addItem(new \Ease\TWB5\FormGroup('FIO_TOKEN', new \Ease\Html\InputTextTag('FIO_TOKEN'), '', _('Token for account')));
        $body->addItem(new \Ease\TWB5\FormGroup('FIO_TOKEN_NAME', new \Ease\Html\InputTextTag('FIO_TOKEN_NAME'), '', _('Name of Token used')));

        parent::__construct($header, 'inverse', $body, '');
    }

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
