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
 *
 * @no-named-arguments
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

        $this->configFieldsProvided->addField($usernameField);
        $this->configFieldsProvided->addField($passwordField);
        $this->configFieldsProvided->addField($clientIdField);
        $this->configFieldsProvided->addField($clientSecretField);
        $this->configFieldsProvided->addField($tenantField);
    }

    #[\Override]
    public function prepareConfigForm(): void
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

    /**
     * Check if required Office365 credential fields are provided.
     *
     * Returns true if either OFFICE365_USERNAME and OFFICE365_PASSWORD and OFFICE365_TENANT
     * or OFFICE365_CLIENTID and OFFICE365_SECRET and OFFICE365_TENANT are set and non-empty.
     *
     * @return bool true if required fields are set, false otherwise
     */
    public function checkProvidedFields(): bool
    {
        $username = $this->configFieldsProvided->getFieldByCode('OFFICE365_USERNAME')->getValue();
        $password = $this->configFieldsProvided->getFieldByCode('OFFICE365_PASSWORD')->getValue();
        $clientId = $this->configFieldsProvided->getFieldByCode('OFFICE365_CLIENTID')->getValue();
        $clientSecret = $this->configFieldsProvided->getFieldByCode('OFFICE365_SECRET')->getValue();
        $tenant = $this->configFieldsProvided->getFieldByCode('OFFICE365_TENANT')->getValue();

        if ((!empty($username) && !empty($password) && !empty($tenant))
            || (!empty($clientId) && !empty($clientSecret) && !empty($tenant))) {
            return true;
        }

        return false;
    }
}
