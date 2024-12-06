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
 * Stormware Pohoda Connect Configuration form.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class mServer extends \Ease\TWB4\Panel implements configForm
{
    public static string $logo = 'images/mServer.svg';

    public function __construct()
    {
        $header = new \Ease\TWB4\Row();
        $header->addColumn(6, new \Ease\Html\ATag('https://www.stormware.eu/', new \Ease\Html\ImgTag(self::$logo, _('Stormware Pohoda'), ['height' => 50])));
        $header->addColumn(6, new \Ease\Html\H3Tag(_('Stormware Pohoda')));

        $body = new \Ease\Html\DivTag();

        $body->addItem(new \Ease\TWB4\FormGroup(_('Organization Number'), new \Ease\Html\InputTextTag('POHODA_ICO'), '123245678', _('Organization Number')));
        $body->addItem(new \Ease\TWB4\FormGroup(_('mServer Api Endpoint'), new \Ease\Html\InputTextTag('POHODA_URL'), 'winstrom', _('mServer Api Endpoint')));
        $body->addItem(new \Ease\TWB4\FormGroup(_('mServer Api Username'), new \Ease\Html\InputTextTag('POHODA_USERNAME'), 'http://pohoda:40000', _('mServer Api Username')));
        $body->addItem(new \Ease\TWB4\FormGroup(_('mServer Api Pasword'), new \Ease\Html\InputTextTag('POHODA_PASSWORD'), 'pohoda', _('mServer Api Pasword')));

        parent::__construct($header, 'inverse', $body, '');
    }

    #[\Override]
    public static function name(): string
    {
        return _('Stormware Pohoda');
    }

    #[\Override]
    public static function fields(): array
    {
        return [
            'POHODA_ICO' => [
                'type' => 'string',
                'description' => _('Organization Number'),
                'defval' => '',
                'required' => true,
            ],
            'POHODA_PASSWORD' => [
                'type' => 'password',
                'description' => _('mServer Api Pasword'),
                'defval' => '',
                'required' => true,
            ],
            'POHODA_URL' => [
                'type' => 'string',
                'description' => _('mServer Api Endpoint'),
                'defval' => '',
                'required' => true,
            ],
            'POHODA_USERNAME' => [
                'type' => 'string',
                'description' => '',
                'defval' => '',
                'required' => true,
            ],
        ];
    }
}
