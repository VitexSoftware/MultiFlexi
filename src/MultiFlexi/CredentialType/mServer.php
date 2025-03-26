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
 */
class mServer extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    public static string $logo = 'images/mServer.svg';

    public function __construct()
    {
        parent::__construct();

        // Define internal configuration fields
        $icoField = new \MultiFlexi\ConfigField('POHODA_ICO', 'string', _('Organization Number'), _('Organization Number for Pohoda'));
        $icoField->setHint('123245678')->setValue('');

        $urlField = new \MultiFlexi\ConfigField('POHODA_URL', 'string', _('mServer API Endpoint'), _('URL of the mServer API'));
        $urlField->setHint('http://pohoda:40000')->setValue('');

        $usernameField = new \MultiFlexi\ConfigField('POHODA_USERNAME', 'string', _('mServer API Username'), _('Username for the mServer API'));
        $usernameField->setHint('winstrom')->setValue('');

        $passwordField = new \MultiFlexi\ConfigField('POHODA_PASSWORD', 'password', _('mServer API Password'), _('Password for the mServer API'));
        $passwordField->setHint('pohoda')->setValue('');

        $this->configFieldsInternal->addField($icoField);
        $this->configFieldsInternal->addField($urlField);
        $this->configFieldsInternal->addField($usernameField);
        $this->configFieldsInternal->addField($passwordField);
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
