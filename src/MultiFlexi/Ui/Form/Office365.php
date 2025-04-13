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
 * Description of Office365.
 *
 * @deprecated since version 1.27
 *
 * @author vitex
 */
class Office365 extends \Ease\TWB4\Panel implements configForm
{
    public static $logo = 'images/Office365.svg';

    public function __construct()
    {
        $header = new \Ease\TWB4\Row();
        $header->addColumn(6, new \Ease\Html\ATag('https://www.office.com', new \Ease\Html\ImgTag(self::$logo, self::name(), ['height' => 50])));
        $header->addColumn(6, new \Ease\Html\H3Tag(self::name()));

        $body = new \Ease\Html\DivTag();

        $body->addItem(new \Ease\TWB4\FormGroup('OFFICE365_USERNAME', new \Ease\Html\InputTextTag('OFFICE365_USERNAME', ''), '', _('Office 365 Username')));
        $body->addItem(new \Ease\TWB4\FormGroup('OFFICE365_PASSWORD', new \Ease\Html\InputPasswordTag('OFFICE365_PASSWORD', ''), '', _('Office 365 Password')));
        $body->addItem(new \Ease\TWB4\FormGroup('OFFICE365_CLIENTID', new \Ease\Html\InputTextTag('OFFICE365_CLIENTID', ''), '', _('Office 365 Client ID')));
        $body->addItem(new \Ease\TWB4\FormGroup('OFFICE365_SECRET', new \Ease\Html\InputTextTag('OFFICE365_SECRET', ''), '', _('Office 365 Secret')));
        $body->addItem(new \Ease\TWB4\FormGroup('OFFICE365_CLSECRET', new \Ease\Html\InputTextTag('OFFICE365_CLSECRET', ''), '', _('Office 365 Client Secret')));
        $body->addItem(new \Ease\TWB4\FormGroup('OFFICE365_TENANT', new \Ease\Html\InputTextTag('OFFICE365_TENANT', ''), '', _('Office 365 Tenant')));

        parent::__construct($header, 'inverse', $body, '');
    }

    #[\Override]
    public static function fields(): array
    {
        return [
            'OFFICE365_USERNAME' => [
                'type' => 'text',
                'description' => _('Office 365 Username'),
                'defval' => '',
                'required' => false,
            ],
            'OFFICE365_PASSWORD' => [
                'type' => 'password',
                'description' => _('Office 365 Password'),
                'defval' => '',
                'required' => false,
            ],
            'OFFICE365_CLIENTID' => [
                'type' => 'text',
                'description' => _('Office 365 Client ID'),
                'defval' => '',
                'required' => false,
            ],
            'OFFICE365_SECRET' => [
                'type' => 'text',
                'description' => _('Office 365 Secret'),
                'defval' => '',
                'required' => false,
            ],
            'OFFICE365_CLSECRET' => [
                'type' => 'text',
                'description' => _('Office 365 Client Secret'),
                'defval' => '',
                'required' => false,
            ],
            'OFFICE365_TENANT' => [
                'type' => 'text',
                'description' => _('Office 365 Tenant'),
                'defval' => '',
                'required' => true,
            ],
        ];
    }

    #[\Override]
    public static function name(): string
    {
        return _('Office 365');
    }
}
