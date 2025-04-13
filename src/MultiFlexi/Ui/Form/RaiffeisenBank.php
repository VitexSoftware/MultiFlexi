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

use Ease\Html\ATag;

/**
 * Description of Csas.
 *
 * @deprecated since version 1.27
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class RaiffeisenBank extends \Ease\TWB4\Panel implements configForm
{
    public static string $logo = 'images/RaiffeisenBank.svg';

    public function __construct()
    {
        $header = new \Ease\TWB4\Row();
        $header->addColumn(6, new \Ease\Html\ATag('https://www.rb.cz/firmy/transakcni-bankovnictvi/elektronicke-bankovnictvi/premium-api', new \Ease\Html\ImgTag(self::$logo, _('RB Premium Api'), ['height' => 50])));
        $header->addColumn(6, new \Ease\Html\H3Tag(_('Raiffeisen Bank Premium API')));

        $body = new \Ease\Html\DivTag();

        $body->addItem(new \Ease\TWB4\FormGroup('ACCOUNT_NUMBER', new \Ease\Html\InputTextTag('ACCOUNT_NUMBER'), '', _('Raiffeisen Bank Account Number')));
        $body->addItem(new \Ease\TWB4\FormGroup('ACCOUNT_CURRENCY', new \Ease\Html\InputTextTag('ACCOUNT_CURRENCY'), 'CZK', _('Raiffeisen Bank Account Currency Code: CZK,EUR,USD,...')));
        $body->addItem(new \Ease\TWB4\FormGroup('CERT_FILE', new \Ease\Html\InputTextTag('CERT_FILE'), '/path/to/certificate/file.p12', _('Path to Certificate File')));
        $body->addItem(new \Ease\TWB4\FormGroup('CERT_PASS', new \Ease\Html\InputTextTag('CERT_PASS'), \Ease\Functions::randomString(20), _('Password for Certificate')));
        $body->addItem(new \Ease\TWB4\FormGroup('XIBMCLIENTID', new \Ease\Html\InputTextTag('XIBMCLIENTID'), '', new ATag('https://developers.rb.cz/premium/applications', _('X-IBM-Client-Id'))));

        parent::__construct($header, 'inverse', $body, '');
    }

    public static function name(): string
    {
        return _('Raiffeisen Bank Premium API');
    }

    #[\Override]
    public static function fields(): array
    {
        return [
            'ACCOUNT_NUMBER' => [
                'type' => 'text',
                'description' => _('Bank Account Number'),
                'defval' => '',
                'required' => true,
            ],
            'ACCOUNT_CURRENCY' => [
                'type' => 'text',
                'description' => _('Bank Account Currency'),
                'defval' => 'CZK',
                'required' => false,
            ],
            'CERT_PASS' => [
                'type' => 'password',
                'description' => _('Certificate password'),
                'defval' => '',
                'required' => true,
            ],
            'CERT_FILE' => [
                'type' => 'string',
                'description' => _('Path to RB Certificate file'),
                'defval' => '',
                'required' => true,
            ],
            'XIBMCLIENTID' => [
                'type' => 'text',
                'description' => _('ClientID'),
                'defval' => '',
                'required' => true,
            ],
        ];
    }
}
