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
 * Stormware Pohoda Connect Configuration form.
 *
 * author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class mServer extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    public static string $logo = 'mServer.svg';

    public function __construct()
    {
        parent::__construct();

        // Define internal configuration fields
        $icoField = new \MultiFlexi\ConfigField('POHODA_ICO', 'string', _('Organization Number'), _('Organization Number for Pohoda'));
        $icoField->setHint('123245678')->setRequired(true);

        $urlField = new \MultiFlexi\ConfigField('POHODA_URL', 'string', _('mServer API Endpoint'), _('URL of the mServer API'));
        $urlField->setHint('http://pohoda:40000')->setRequired(true);

        $usernameField = new \MultiFlexi\ConfigField('POHODA_USERNAME', 'string', _('mServer API Username'), _('Username for the mServer API'));
        $usernameField->setHint('winstrom')->setRequired(true);

        $passwordField = new \MultiFlexi\ConfigField('POHODA_PASSWORD', 'password', _('mServer API Password'), _('Password for the mServer API'));
        $passwordField->setHint('pohoda')->setRequired(true);

        $this->configFieldsProvided->addField($icoField);
        $this->configFieldsProvided->addField($urlField);
        $this->configFieldsProvided->addField($usernameField);
        $this->configFieldsProvided->addField($passwordField);
    }

    #[\Override]
    public function prepareConfigForm(): void
    {
        // Implement the configuration form logic if needed
    }

    public static function name(): string
    {
        return _('Stormware Pohoda');
    }

    public static function description(): string
    {
        return _('Credential type for connecting to Stormware Pohoda mServer API');
    }

    #[\Override]
    public static function logo(): string
    {
        return self::$logo;
    }
}
