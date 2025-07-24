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

namespace MultiFlexi\CredentialType;

/**
 * Description of SQLServer.
 *
 * author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class SQLServer extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    public static string $logo = 'SQLServer.svg';

    public function __construct()
    {
        parent::__construct();

        // Define internal configuration fields
        $connectionField = new \MultiFlexi\ConfigField('DB_CONNECTION', 'string', _('Database Connection'), _('Database connection type (e.g., sqlsrv)'));
        $connectionField->setHint('sqlsrv')->setRequired(true);

        $hostField = new \MultiFlexi\ConfigField('DB_HOST', 'string', _('Database Host'), _('Host of the SQL Server'));
        $hostField->setHint('127.0.0.1')->setDefaultValue('127.0.0.1');

        $portField = new \MultiFlexi\ConfigField('DB_PORT', 'integer', _('Database Port'), _('Port of the SQL Server'));
        $portField->setHint('1433')->setValue('1433');

        $databaseField = new \MultiFlexi\ConfigField('DB_DATABASE', 'string', _('Database Name'), _('Name of the database'));
        $databaseField->setHint('StwPh_12345678_2023')->setRequired(true);

        $usernameField = new \MultiFlexi\ConfigField('DB_USERNAME', 'string', _('Database Username'), _('Username for the database'));
        $usernameField->setHint('sa')->setValue('');

        $passwordField = new \MultiFlexi\ConfigField('DB_PASSWORD', 'password', _('Database Password'), _('Password for the database'));
        $passwordField->setHint('your-password')->setValue('');

        $settingsField = new \MultiFlexi\ConfigField('DB_SETTINGS', 'string', _('Database Settings'), _('Additional database settings (e.g., encrypt=false)'));
        $settingsField->setHint('encrypt=false')->setValue('');

        $this->configFieldsProvided->addField($connectionField);
        $this->configFieldsProvided->addField($hostField);
        $this->configFieldsProvided->addField($portField);
        $this->configFieldsProvided->addField($databaseField);
        $this->configFieldsProvided->addField($usernameField);
        $this->configFieldsProvided->addField($passwordField);
        $this->configFieldsProvided->addField($settingsField);
    }

    #[\Override]
    public function prepareConfigForm(): void
    {
        // Implement the configuration form logic if needed
    }

    public static function name(): string
    {
        return _('SQL Server');
    }

    public static function description(): string
    {
        return _('Credential type for connecting to Microsoft SQL Server');
    }

    #[\Override]
    public static function logo(): string
    {
        return self::$logo;
    }
}
