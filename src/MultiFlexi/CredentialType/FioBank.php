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
 * Description of FioBank.
 *
 * author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class FioBank extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    public static string $logo = 'Fio.svg';

    public function __construct()
    {
        parent::__construct();

        // Define internal configuration fields
        $accountNumberField = new \MultiFlexi\ConfigField('ACCOUNT_NUMBER', 'string', _('Fio Bank Account Number'), _('Number of the Fio Bank account'));
        $accountNumberField->setHint('123456789/2010')->setValue('');

        $tokenField = new \MultiFlexi\ConfigField('FIO_TOKEN', 'string', _('Fio Bank Token'), _('Token for accessing the Fio Bank API'));
        $tokenField->setHint(_('AXWxJN18IqwbY....xccP2eyxvWDFLe2'))->setRequired(true)->setValue('');

        $tokenNameField = new \MultiFlexi\ConfigField('FIO_TOKEN_NAME', 'string', _('Fio Token Name'), _('Name of the token used for identification'));
        $tokenNameField->setHint('default-token')->setValue('');

        $this->configFieldsInternal->addField($accountNumberField);
        $this->configFieldsInternal->addField($tokenField);
        $this->configFieldsInternal->addField($tokenNameField);
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
        return _('Fio Bank');
    }

    public static function description(): string
    {
        return _('Fio Bank credential type for integration with Fio Bank API');
    }

    #[\Override]
    public static function logo(): string
    {
        return self::$logo;
    }
}
