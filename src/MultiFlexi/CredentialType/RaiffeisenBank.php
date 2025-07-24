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
 * Description of RaiffeisenBank.
 *
 * author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class RaiffeisenBank extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    public static string $logo = 'RaiffeisenBank.svg';

    public function __construct()
    {
        parent::__construct();

        // Define internal configuration fields
        $accountNumberField = new \MultiFlexi\ConfigField('ACCOUNT_NUMBER', 'string', _('Raiffeisen Bank Account Number'), _('Number of the Raiffeisen Bank account'));
        $accountNumberField->setHint('123456789/5500')->setValue('');

        $currencyField = new \MultiFlexi\ConfigField('ACCOUNT_CURRENCY', 'string', _('Account Currency'), _('Currency of the account (e.g., CZK, EUR, USD)'));
        $currencyField->setHint('CZK')->setValue('CZK');

        $certFileField = new \MultiFlexi\ConfigField('CERT_FILE', 'string', _('Certificate File Path'), _('Path to the certificate file'));
        $certFileField->setHint('/path/to/certificate.p12')->setValue('');

        $certPassField = new \MultiFlexi\ConfigField('CERT_PASS', 'password', _('Certificate Password'), _('Password for the certificate file'));
        $certPassField->setHint('your-password')->setValue('');

        $clientIdField = new \MultiFlexi\ConfigField('XIBMCLIENTID', 'string', _('Client ID'), _('X-IBM-Client-Id for API access'));
        $clientIdField->setHint('your-client-id')->setValue('');

        $this->configFieldsInternal->addField($accountNumberField);
        $this->configFieldsInternal->addField($currencyField);
        $this->configFieldsInternal->addField($certFileField);
        $this->configFieldsInternal->addField($certPassField);
        $this->configFieldsInternal->addField($clientIdField);
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
    public function prepareConfigForm(): void
    {
        // Implement the configuration form logic if needed
    }

    public static function name(): string
    {
        return _('Raiffeisen Bank');
    }

    public static function description(): string
    {
        return _('Raiffeisen Bank Premium API');
    }

    #[\Override]
    public static function logo(): string
    {
        return self::$logo;
    }
}
