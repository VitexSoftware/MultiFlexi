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
    public function __construct()
    {
        parent::__construct();
        $envFile = new \MultiFlexi\ConfigField('ENV_FILE_PATH', 'file-path', _('.env'), _('Path to .env file'));
        $envFile->setHint('/path/to/.env');

        $this->configFieldsInternal->addField($envFile);

        //        if($this->get){}
    }

    public function load(int $credTypeId)
    {
        $loaded = parent::load($credTypeId);

        $configFile = $this->configFieldsInternal->getFieldByCode('ENV_FILE_PATH')->getValue();

        $configuration = [];

        if (file_exists($configFile)) {
            if (is_readable($configFile)) {
                foreach (file($configFile) as $cfgRow) {
                    if (strstr($cfgRow, '=')) {
                        [$key, $value] = preg_split('/=/', $cfgRow, 2);
                        $configuration[$key] = trim($value, " \t\n\r\0\x0B'\"");
                    }
                }

                foreach ($configuration as $key => $value) {
                    $envFile = new \MultiFlexi\ConfigField($key, 'string', $key, sprintf(_('Value from .env file %s'), $configFile));
                    $envFile->setHint($value)->setValue($value);

                    $this->configFieldsProvided->addField($envFile);
                }
            } else {
                $this->addStatusMessage(sprintf(_('File %s is not readable'), $configFile), 'warning');
            }
        } else {
            $this->addStatusMessage(sprintf(_('File %s does not exists'), $configFile), 'warning');
        }

        return $loaded;
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
