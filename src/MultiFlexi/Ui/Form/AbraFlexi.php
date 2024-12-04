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
 * Description of AbraFlexi.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class AbraFlexi extends \Ease\TWB4\Panel implements configForm
{
    public static string $logo = 'images/AbraFlexi.svg';

    public function __construct()
    {
        $header = new \Ease\TWB4\Row();
        $header->addColumn(6, new \Ease\Html\ATag('https://www.abra.eu/flexi/', new \Ease\Html\ImgTag(self::$logo, _('AbraFlexi'), ['height' => 50])));
        $header->addColumn(6, new \Ease\Html\H3Tag(_('AbraFlexi')));

        $body = new \Ease\Html\DivTag();

        $body->addItem(new \Ease\TWB4\FormGroup(_('Login'), new \Ease\Html\InputTextTag('ABRAFLEXI_LOGIN'), 'winstrom', _('AbraFlexi user login')));
        $body->addItem(new \Ease\TWB4\FormGroup(_('Password'), new \Ease\Html\InputTextTag('ABRAFLEXI_PASSWORD'), 'winstrom', _('AbraFlexi user password')));
        $body->addItem(new \Ease\TWB4\FormGroup(_('Server URL'), new \Ease\Html\InputTextTag('ABRAFLEXI_URL'), 'winstrom', _('AbraFlexi server URI')));
        $body->addItem(new \Ease\TWB4\FormGroup(_('Company'), new \Ease\Html\InputTextTag('ABRAFLEXI_COMPANY'), 'demo', _('Company to be handled')));

        parent::__construct($header, 'inverse', $body, '');
    }

    public static function name(): string
    {
        return _('AbraFlexi');
    }

    #[\Override]
    public static function fields(): array
    {
        return [
            'ABRAFLEXI_COMPANY' => [
                'type' => 'string',
                'description' => '',
                'defval' => 'demo_de',
                'required' => false,
            ],
            'ABRAFLEXI_LOGIN' => [
                'type' => 'string',
                'description' => _('AbraFlexi Login'),
                'defval' => 'winstrom',
                'required' => false,
            ],
            'ABRAFLEXI_PASSWORD' => [
                'type' => 'string',
                'description' => _('AbraFlexi password'),
                'defval' => 'winstrom',
                'required' => false,
            ],
            'ABRAFLEXI_URL' => [
                'type' => 'string',
                'description' => _('AbraFlexi Server URI'),
                'defval' => 'https://demo.flexibee.eu:5434',
                'required' => false,
            ],
        ];
    }
}
