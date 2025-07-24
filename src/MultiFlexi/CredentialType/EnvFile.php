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
 *
 * @no-named-arguments
 */
class EnvFile extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    public function __construct()
    {
        parent::__construct();
        $envFile = new \MultiFlexi\ConfigField('ENV_FILE_PATH', 'file-path', _('.env'), _('Path to .env file'));
        $envFile->setHint('/path/to/.env');

        $this->configFieldsInternal->addField($envFile);
    }

    public function load(int $credTypeId)
    {
        $loaded = parent::load($credTypeId);

        $configFile = $this->configFieldsInternal->getFieldByCode('ENV_FILE_PATH')->getValue();

        $configuration = [];

        if ($configFile) {
            if (file_exists($configFile)) {
                if (is_readable($configFile)) {
                    $configuration = $this->readEnvFile($configFile);

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
        } else {
            $this->addStatusMessage(_('.env file must be filled'));
        }

        return $loaded;
    }

    public static function readEnvFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('File %s does not exist', $filePath));
        }

        $env = [];
        $lines = file($filePath, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue; // Skip comments
            }

            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }

        return $env;
    }

    #[\Override]
    public function prepareConfigForm(): void
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

    #[\Override]
    public static function logo(): string
    {
        return 'env-file.svg';
    }

    #[\Override]
    public function query(): \MultiFlexi\ConfigFields
    {
        $configFile = $this->configFieldsInternal->getFieldByCode('ENV_FILE_PATH')->getValue();
        $envData = $this->readEnvFile($configFile);

        foreach (array_keys($this->configFieldsProvided->getFields()) as $configField) {
            if (\array_key_exists($configField, $envData)) {
                $this->configFieldsProvided->getFieldByCode($configField)->setValue($envData[$configField]);
                $this->configFieldsProvided->getFieldByCode($configField)->setSource($configFile);
                $this->configFieldsProvided->getFieldByCode($configField)->setNote(sprintf(_('Populated by .env file %s '), $configFile));
                $this->configFieldsProvided->getFieldByCode($configField)->setValue($envData[$configField]);
            }
        }

        return parent::query();
    }
}
