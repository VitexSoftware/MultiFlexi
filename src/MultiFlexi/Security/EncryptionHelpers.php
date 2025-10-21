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

namespace MultiFlexi\Security;

/**
 * Encryption helper functions for application usage.
 */
class EncryptionHelpers
{
    /**
     * Encrypt sensitive data using the global data encryption instance.
     *
     * @param string $data  Data to encrypt
     * @param string $keyId Optional key ID to use (defaults to active key)
     *
     * @throws \Exception If encryption is not initialized
     *
     * @return string Encrypted data
     */
    public static function encryptData(string $data, string $keyId = ''): string
    {
        if (!isset($GLOBALS['dataEncryption'])) {
            throw new \Exception('Data encryption is not initialized');
        }

        return $GLOBALS['dataEncryption']->encrypt($data, $keyId);
    }

    /**
     * Decrypt sensitive data using the global data encryption instance.
     *
     * @param string $encryptedData Encrypted data to decrypt
     *
     * @throws \Exception If encryption is not initialized or decryption fails
     *
     * @return string Decrypted data
     */
    public static function decryptData(string $encryptedData): string
    {
        if (!isset($GLOBALS['dataEncryption'])) {
            throw new \Exception('Data encryption is not initialized');
        }

        return $GLOBALS['dataEncryption']->decrypt($encryptedData);
    }

    /**
     * Encrypt credentials for storage.
     *
     * @param array  $credentials Associative array of credential data
     * @param string $keyId       Optional key ID to use (defaults to active key)
     *
     * @throws \Exception If encryption fails
     *
     * @return array Encrypted credentials array
     */
    public static function encryptCredentials(array $credentials, string $keyId = ''): array
    {
        $encryptedCredentials = [];

        foreach ($credentials as $key => $value) {
            if (self::isSensitiveField($key) && !empty($value)) {
                $encryptedCredentials[$key] = self::encryptData($value, $keyId);
            } else {
                $encryptedCredentials[$key] = $value;
            }
        }

        return $encryptedCredentials;
    }

    /**
     * Decrypt credentials from storage.
     *
     * @param array $encryptedCredentials Encrypted credentials array
     *
     * @throws \Exception If decryption fails
     *
     * @return array Decrypted credentials array
     */
    public static function decryptCredentials(array $encryptedCredentials): array
    {
        $decryptedCredentials = [];

        foreach ($encryptedCredentials as $key => $value) {
            if (self::isSensitiveField($key) && !empty($value)) {
                try {
                    $decryptedCredentials[$key] = self::decryptData($value);
                } catch (\Exception $e) {
                    // If decryption fails, log the error and keep original value
                    error_log('Failed to decrypt credential field '.$key.': '.$e->getMessage());
                    $decryptedCredentials[$key] = $value;
                }
            } else {
                $decryptedCredentials[$key] = $value;
            }
        }

        return $decryptedCredentials;
    }

    /**
     * Check if data encryption is available and enabled.
     *
     * @return bool True if encryption is available
     */
    public static function isEncryptionAvailable(): bool
    {
        return isset($GLOBALS['dataEncryption'])
            && \Ease\Shared::cfg('DATA_ENCRYPTION_ENABLED', true);
    }

    /**
     * Get the current active encryption key ID.
     *
     * @return string Active key ID or empty string if not available
     */
    public static function getActiveKeyId(): string
    {
        if (!self::isEncryptionAvailable()) {
            return '';
        }

        try {
            return $GLOBALS['dataEncryption']->getActiveKeyId();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Rotate encryption keys (generate new active key and deactivate old one).
     *
     * @return bool True if key rotation was successful
     */
    public static function rotateKeys(): bool
    {
        if (!self::isEncryptionAvailable()) {
            return false;
        }

        try {
            $oldKeyId = $GLOBALS['dataEncryption']->getActiveKeyId();
            $newKeyId = $GLOBALS['dataEncryption']->generateKey();

            if ($oldKeyId) {
                $GLOBALS['dataEncryption']->deactivateKey($oldKeyId);
            }

            return true;
        } catch (\Exception $e) {
            error_log('Failed to rotate encryption keys: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Test encryption/decryption functionality.
     *
     * @return bool True if encryption is working correctly
     */
    public static function testEncryption(): bool
    {
        if (!self::isEncryptionAvailable()) {
            return false;
        }

        try {
            $testData = 'test_encryption_'.uniqid();
            $encrypted = self::encryptData($testData);
            $decrypted = self::decryptData($encrypted);

            return $testData === $decrypted;
        } catch (\Exception $e) {
            error_log('Encryption test failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Check if a field name indicates sensitive data that should be encrypted.
     *
     * @param string $fieldName Name of the field to check
     *
     * @return bool True if field should be encrypted
     */
    private static function isSensitiveField(string $fieldName): bool
    {
        $sensitiveFields = [
            'password',
            'passwd',
            'pass',
            'pwd',
            'secret',
            'key',
            'token',
            'auth',
            'credential',
            'private_key',
            'api_key',
            'access_token',
            'refresh_token',
            'client_secret',
        ];

        $fieldNameLower = strtolower($fieldName);

        foreach ($sensitiveFields as $sensitiveField) {
            if (str_contains($fieldNameLower, $sensitiveField)) {
                return true;
            }
        }

        return false;
    }
}
