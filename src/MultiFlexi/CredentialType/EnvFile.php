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
 * Description of EnvFile.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class EnvFile extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    public function __construct() {
        parent::__construct();
        $envFile = new \MultiFlexi\ConfigField('ENV_FILE_PATH', 'file-path', _('.env'), _('Path to .env file'));
        $envFile->setHint('/path/to/.env');

        $this->configFieldsInternal->addField($envFile);
        
    }
    
    #[\Override]
    public function configForm(): void
    {
    }

    public static function name(): string
    {
        return _('.env file');
    }

    public static function description(): string
    {
        return _('Load configuration values from .env file');
    }

}
