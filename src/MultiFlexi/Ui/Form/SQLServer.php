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
 * Description of SQLServer.
 *
 * @author vitex
 */
class SQLServer extends \Ease\TWB4\Panel implements configForm
{
    public static string $logo = 'images/SQLServer.svg';

    public function __construct()
    {
        $header = new \Ease\TWB4\Row();
        $header->addColumn(6, new \Ease\Html\ATag('https://www.stormare.eu/', new \Ease\Html\ImgTag(self::$logo, self::name(), ['height' => 50])));
        $header->addColumn(6, new \Ease\Html\H3Tag(self::name()));

        $body = new \Ease\Html\DivTag();

        $body->addItem(new \Ease\TWB4\FormGroup('DB_CONNECTION', new \Ease\Html\InputTextTag('DB_CONNECTION', 'sqlsrv'), 'sqlsrv', _('Database Connection')));
        $body->addItem(new \Ease\TWB4\FormGroup('DB_HOST', new \Ease\Html\InputTextTag('DB_HOST', '127.0.0.1'), '127.0.0.1', _('Database Host')));
        $body->addItem(new \Ease\TWB4\FormGroup('DB_PORT', new \Ease\Html\InputNumberTag('DB_PORT', 1433), 1433, _('Database Port')));
        $body->addItem(new \Ease\TWB4\FormGroup('DB_DATABASE', new \Ease\Html\InputTextTag('DB_DATABASE', 'StwPh_12345678_2023'), 'StwPh_12345678_2023', _('Database Name')));
        $body->addItem(new \Ease\TWB4\FormGroup('DB_USERNAME', new \Ease\Html\InputTextTag('DB_USERNAME', 'sa'), 'sa', _('Database Username')));
        $body->addItem(new \Ease\TWB4\FormGroup('DB_PASSWORD', new \Ease\Html\InputPasswordTag('DB_PASSWORD', 'pohodaSQLpassword'), 'pohodaSQLpassword', _('Database Password')));
        $body->addItem(new \Ease\TWB4\FormGroup('DB_SETTINGS', new \Ease\Html\InputTextTag('DB_SETTINGS', ''), '', _('Database Settings like encrypt=false')));

        parent::__construct($header, 'inverse', $body, '');
    }

    #[\Override]
    public static function name(): string
    {
        return _('SQL Server');
    }

    #[\Override]
    public static function fields(): array
    {
        return [
            'DB_CONNECTION' => [
                'type' => 'text',
                'description' => _('Database Connection'),
                'defval' => 'sqlsrv',
                'required' => true,
            ],
            'DB_HOST' => [
                'type' => 'text',
                'description' => _('Database Host'),
                'defval' => '127.0.0.1',
                'required' => true,
            ],
            'DB_PORT' => [
                'type' => 'number',
                'description' => _('Database Port'),
                'defval' => 1433,
                'required' => true,
            ],
            'DB_DATABASE' => [
                'type' => 'text',
                'description' => _('Database Name'),
                'defval' => 'StwPh_12345678_2023',
                'required' => true,
            ],
            'DB_USERNAME' => [
                'type' => 'text',
                'description' => _('Database Username'),
                'defval' => 'sa',
                'required' => true,
            ],
            'DB_PASSWORD' => [
                'type' => 'password',
                'description' => _('Database Password'),
                'defval' => 'pohodaSQLpassword',
                'required' => true,
            ],
            'DB_SETTINGS' => [
                'type' => 'text',
                'description' => _('Database Settings like encrypt=false'),
                'defval' => '',
                'required' => false,
            ],
        ];
    }
}
