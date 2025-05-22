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
 * Description of Office365.
 *
 * author Vitex <info@vitexsoftware.cz>
 */
class Office365 extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    public static string $logo = 'Office365.svg';

    public function __construct()
    {
        parent::__construct();

        // Define internal configuration fields
        $usernameField = new \MultiFlexi\ConfigField('OFFICE365_USERNAME', 'string', _('Office 365 Username'), _('Username for Office 365'));
        $usernameField->setHint('user@domain.com')->setValue('');

        $passwordField = new \MultiFlexi\ConfigField('OFFICE365_PASSWORD', 'password', _('Office 365 Password'), _('Password for Office 365'));
        $passwordField->setHint('your-password')->setValue('');

        $clientIdField = new \MultiFlexi\ConfigField('OFFICE365_CLIENTID', 'string', _('Office 365 Client ID'), _('Client ID for Office 365 API'));
        $clientIdField->setHint('your-client-id')->setValue('');

        $clientSecretField = new \MultiFlexi\ConfigField('OFFICE365_SECRET', 'string', _('Office 365 Secret'), _('Secret for Office 365 API'));
        $clientSecretField->setHint('your-secret')->setValue('');

        $tenantField = new \MultiFlexi\ConfigField('OFFICE365_TENANT', 'string', _('Office 365 Tenant'), _('Tenant ID for Office 365'));
        $tenantField->setHint('your-tenant-id')->setValue('');

        $this->configFieldsInternal->addField($usernameField);
        $this->configFieldsInternal->addField($passwordField);
        $this->configFieldsInternal->addField($clientIdField);
        $this->configFieldsInternal->addField($clientSecretField);
        $this->configFieldsInternal->addField($tenantField);
    }

    public function load(int $credTypeId)
    {
        $loaded = parent::load($credTypeId);

        // Load provided configuration fields
        foreach ($this->configFieldsInternal->getFields() as $field) {
            $this->configFieldsProvided->addField($field);
        }

        return $loaded;
    }

    #[\Override]
    public function configForm(): void
    {
        // Implement the configuration form logic if needed
    }

    public static function name(): string
    {
        return _('Office 365');
    }

    public static function description(): string
    {
        return _('Credential type for integration with Office 365 API');
    }

    #[\Override]
    public static function logo(): string
    {
        return self::$logo;
    }
}
