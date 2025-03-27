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
 * Description of Csas.
 *
 * @author vitex
 */
class Csas extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    public function __construct()
    {
        parent::__construct();
        $envFile = new \MultiFlexi\ConfigField('TOKEN_ID', 'string', _('Token unique identifier'), _('Token UUID'));
        $envFile->setHint('71004963-e3d4-471f-96fc-1aef79d17ec1');

        $this->configFieldsInternal->addField($envFile);

        // vitex@gamer:~/Projects/Multi/MultiFlexi$ csas-access-token -tac6846e0-6094-4a9c-a8ab-f95d59b8e313

        $csasApiKey = new \MultiFlexi\ConfigField('CSAS_API_KEY', 'string', _('CSAS API Key'), _('API Key for CSAS services'));
        $csasApiKey->setHint('c5f91ec2-0237-4af2-9f90-c8366e209ff8')->setValue('c5f91ec2-0237-4af2-9f90-c8366e209ff8');
        $this->configFieldsProvided->addField($csasApiKey);

        $csasSandboxMode = new \MultiFlexi\ConfigField('CSAS_SANDBOX_MODE', 'bool', _('CSAS Sandbox Mode'), _('Enable or disable sandbox mode for CSAS services'));
        $csasSandboxMode->setHint('true')->setValue('true');
        $this->configFieldsProvided->addField($csasSandboxMode);

        $csasAccessToken = new \MultiFlexi\ConfigField('CSAS_ACCESS_TOKEN', 'string', _('CSAS Access Token'), _('Access token for CSAS services'));
        $csasAccessToken->setHint('ewogIC.....uNjQzWiIKfQ==');
        $this->configFieldsProvided->addField($csasAccessToken);
    }

    #[\Override]
    public static function name(): string
    {
        return _('Česká Spořitelna a.s.');
    }

    public static function description(): string
    {
        return _('Interact with Erste APIs');
    }

    #[\Override]
    public function configForm(): void
    {
    }

    #[\Override]
    public function fieldsInternal(): \MultiFlexi\ConfigFields
    {
        return $this->configFieldsInternal;
    }

    #[\Override]
    public function fieldsProvided(): \MultiFlexi\ConfigFields
    {
        return $this->configFieldsProvided;
    }

    #[\Override]
    public static function logo(): string
    {
        return 'images/csas-authorize.svg';
    }
}
