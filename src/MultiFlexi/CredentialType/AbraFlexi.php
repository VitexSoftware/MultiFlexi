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
 * Description of AbraFlexi.
 *
 * author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class AbraFlexi extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    public static string $logo = 'AbraFlexi.svg';

    public function __construct()
    {
        parent::__construct();

        // Define internal configuration fields
        $loginField = new \MultiFlexi\ConfigField('ABRAFLEXI_LOGIN', 'string', _('AbraFlexi Login'), _('AbraFlexi user login'));
        $loginField->setHint('winstrom')->setValue('winstrom');

        $passwordField = new \MultiFlexi\ConfigField('ABRAFLEXI_PASSWORD', 'string', _('AbraFlexi Password'), _('AbraFlexi user password'));
        $passwordField->setHint('winstrom')->setValue('winstrom');

        $urlField = new \MultiFlexi\ConfigField('ABRAFLEXI_URL', 'string', _('AbraFlexi Server URI'), _('AbraFlexi server URI'));
        $urlField->setHint('https://demo.flexibee.eu:5434')->setValue('https://demo.flexibee.eu:5434');

        $companyField = new \MultiFlexi\ConfigField('ABRAFLEXI_COMPANY', 'string', _('AbraFlexi Company'), _('Company to be handled'));
        $companyField->setHint('demo')->setValue('demo');

        $this->configFieldsProvided->addField($loginField);
        $this->configFieldsProvided->addField($passwordField);
        $this->configFieldsProvided->addField($urlField);
        $this->configFieldsProvided->addField($companyField);
    }

    #[\Override]
    public function prepareConfigForm(): void
    {
        // Implement the configuration form logic if needed
    }

    public static function name(): string
    {
        return _('AbraFlexi');
    }

    public static function description(): string
    {
        return _('AbraFlexi credential type for integration with AbraFlexi API');
    }

    #[\Override]
    public static function logo(): string
    {
        return self::$logo;
    }
}
