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
 * Data encryption manager for sensitive data at rest using AES-256-GCM.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class DataEncryption
{
    public const ALGORITHM_AES_256_GCM = 'AES-256-GCM';
    public const ALGORITHM_AES_256_CBC = 'AES-256-CBC';
    private \PDO $pdo;
    private string $keysTableName;
    private array $encryptionKeys = [];
    private string $defaultAlgorithm;

    public function __construct(
        \PDO $pdo,
        string $keysTableName = 'encryption_keys',
        string $defaultAlgorithm = self::ALGORITHM_AES_256_GCM,
    ) {
        $this->pdo = $pdo;
        $this->keysTableName = $keysTableName;
        $this->defaultAlgorithm = $defaultAlgorithm;
    }

    /**
     * Encrypt sensitive data.
     *
     * @param string      $plaintext Data to encrypt
     * @param string      $keyName   Name of the encryption key to use
     * @param null|string $algorithm Encryption algorithm (optional)
     *
     * @return array Encrypted data with metadata
     */
    public function encrypt(string $plaintext, string $keyName = 'default', ?string $algorithm = null): array
    {
        if (empty($plaintext)) {
            throw new \InvalidArgumentException('Cannot encrypt empty data');
        }

        $algorithm ??= $this->defaultAlgorithm;
        $key = $this->getEncryptionKey($keyName);

        if (!$key) {
            throw new \RuntimeException("Encryption key '{$keyName}' not found");
        }

        switch ($algorithm) {
            case self::ALGORITHM_AES_256_GCM:
                return self::encryptAesGcm($plaintext, $key, $keyName);
            case self::ALGORITHM_AES_256_CBC:
                return self::encryptAesCbc($plaintext, $key, $keyName);

            default:
                throw new \InvalidArgumentException("Unsupported encryption algorithm: {$algorithm}");
        }
    }

    /**
     * Decrypt sensitive data.
     *
     * @param array $encryptedData Encrypted data with metadata
     *
     * @return string Decrypted plaintext
     */
    public function decrypt(array $encryptedData): string
    {
        if (!isset($encryptedData['ciphertext'], $encryptedData['key_name'], $encryptedData['algorithm'])) {
            throw new \InvalidArgumentException('Invalid encrypted data format');
        }

        $key = $this->getEncryptionKey($encryptedData['key_name']);

        if (!$key) {
            throw new \RuntimeException("Encryption key '{$encryptedData['key_name']}' not found");
        }

        switch ($encryptedData['algorithm']) {
            case self::ALGORITHM_AES_256_GCM:
                return self::decryptAesGcm($encryptedData, $key);
            case self::ALGORITHM_AES_256_CBC:
                return self::decryptAesCbc($encryptedData, $key);

            default:
                throw new \InvalidArgumentException("Unsupported decryption algorithm: {$encryptedData['algorithm']}");
        }
    }

    /**
     * Generate a new encryption key.
     *
     * @param string $keyName   Name for the new key
     * @param string $algorithm Algorithm the key will be used for
     */
    public function generateKey(string $keyName, string $algorithm = self::ALGORITHM_AES_256_GCM): bool
    {
        // Generate random key
        $key = random_bytes(32); // 256-bit key

        // Encrypt the key before storing
        $encryptedKey = self::encryptStoredKey($key);

        // Store in database
        $sql = <<<EOD
INSERT INTO `{$this->keysTableName}`
                (key_name, key_data, algorithm, created_at, is_active)
                VALUES (?, ?, ?, NOW(), TRUE)
                ON DUPLICATE KEY UPDATE
                key_data = VALUES(key_data),
                algorithm = VALUES(algorithm),
                rotated_at = NOW()
EOD;

        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute([$keyName, $encryptedKey, $algorithm]);

        if ($success) {
            // Clear cache to force reload
            unset($this->encryptionKeys[$keyName]);

            // Log key generation/rotation
            if (isset($GLOBALS['securityAuditLogger'])) {
                $GLOBALS['securityAuditLogger']->logEvent(
                    'encryption_key_generated',
                    "Encryption key generated/rotated: {$keyName}",
                    'high',
                    null,
                    ['key_name' => $keyName, 'algorithm' => $algorithm],
                );
            }
        }

        return $success;
    }

    /**
     * Rotate an encryption key.
     */
    public function rotateKey(string $keyName): bool
    {
        // Get current algorithm
        $sql = "SELECT algorithm FROM `{$this->keysTableName}` WHERE key_name = ? AND is_active = TRUE";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$keyName]);
        $algorithm = $stmt->fetchColumn();

        if (!$algorithm) {
            throw new \RuntimeException("Key '{$keyName}' not found for rotation");
        }

        return $this->generateKey($keyName, $algorithm);
    }

    /**
     * List available encryption keys.
     */
    public function listKeys(): array
    {
        $sql = <<<EOD
SELECT key_name, algorithm, created_at, rotated_at, is_active
                FROM `{$this->keysTableName}`
                ORDER BY created_at DESC
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Deactivate an encryption key.
     */
    public function deactivateKey(string $keyName): bool
    {
        $sql = "UPDATE `{$this->keysTableName}` SET is_active = FALSE WHERE key_name = ?";
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute([$keyName]);

        if ($success) {
            // Clear from cache
            unset($this->encryptionKeys[$keyName]);

            // Log key deactivation
            if (isset($GLOBALS['securityAuditLogger'])) {
                $GLOBALS['securityAuditLogger']->logEvent(
                    'encryption_key_deactivated',
                    "Encryption key deactivated: {$keyName}",
                    'medium',
                    null,
                    ['key_name' => $keyName],
                );
            }
        }

        return $success;
    }

    /**
     * Test encryption/decryption functionality.
     */
    public function test(string $keyName = 'default'): bool
    {
        try {
            $testData = 'Test encryption data: '.time();

            // Encrypt
            $encrypted = $this->encrypt($testData, $keyName);

            // Decrypt
            $decrypted = $this->decrypt($encrypted);

            return $testData === $decrypted;
        } catch (\Exception $e) {
            error_log('Encryption test failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Bulk encrypt data for migration.
     */
    public function bulkEncrypt(array $data, string $keyName = 'default'): array
    {
        $results = [];

        foreach ($data as $key => $value) {
            if (\is_string($value) && !empty($value)) {
                try {
                    $results[$key] = $this->encrypt($value, $keyName);
                } catch (\Exception $e) {
                    error_log("Failed to encrypt key '{$key}': ".$e->getMessage());
                    $results[$key] = null;
                }
            } else {
                $results[$key] = $value;
            }
        }

        return $results;
    }

    /**
     * Initialize default encryption keys.
     */
    public function initializeDefaultKeys(): void
    {
        $defaultKeys = [
            'default' => self::ALGORITHM_AES_256_GCM,
            'credentials' => self::ALGORITHM_AES_256_GCM,
            'personal_data' => self::ALGORITHM_AES_256_GCM,
        ];

        foreach ($defaultKeys as $keyName => $algorithm) {
            try {
                // Check if key exists
                $existingKey = $this->getEncryptionKey($keyName);

                if (!$existingKey) {
                    $this->generateKey($keyName, $algorithm);
                }
            } catch (\Exception $e) {
                error_log("Failed to initialize key '{$keyName}': ".$e->getMessage());
            }
        }
    }

    /**
     * Encrypt using AES-256-GCM.
     */
    private static function encryptAesGcm(string $plaintext, string $key, string $keyName): array
    {
        $iv = random_bytes(12); // 96-bit IV for GCM
        $tag = '';

        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $key,
            \OPENSSL_RAW_DATA,
            $iv,
            $tag,
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('AES-GCM encryption failed: '.openssl_error_string());
        }

        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
            'algorithm' => self::ALGORITHM_AES_256_GCM,
            'key_name' => $keyName,
            'encrypted_at' => time(),
        ];
    }

    /**
     * Decrypt using AES-256-GCM.
     */
    private static function decryptAesGcm(array $encryptedData, string $key): string
    {
        $plaintext = openssl_decrypt(
            base64_decode($encryptedData['ciphertext'], true),
            'aes-256-gcm',
            $key,
            \OPENSSL_RAW_DATA,
            base64_decode($encryptedData['iv'], true),
            base64_decode($encryptedData['tag'], true),
        );

        if ($plaintext === false) {
            throw new \RuntimeException('AES-GCM decryption failed: '.openssl_error_string());
        }

        return $plaintext;
    }

    /**
     * Encrypt using AES-256-CBC.
     */
    private static function encryptAesCbc(string $plaintext, string $key, string $keyName): array
    {
        $iv = random_bytes(16); // 128-bit IV for CBC

        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-cbc',
            $key,
            \OPENSSL_RAW_DATA,
            $iv,
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('AES-CBC encryption failed: '.openssl_error_string());
        }

        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv' => base64_encode($iv),
            'algorithm' => self::ALGORITHM_AES_256_CBC,
            'key_name' => $keyName,
            'encrypted_at' => time(),
        ];
    }

    /**
     * Decrypt using AES-256-CBC.
     */
    private static function decryptAesCbc(array $encryptedData, string $key): string
    {
        $plaintext = openssl_decrypt(
            base64_decode($encryptedData['ciphertext'], true),
            'aes-256-cbc',
            $key,
            \OPENSSL_RAW_DATA,
            base64_decode($encryptedData['iv'], true),
        );

        if ($plaintext === false) {
            throw new \RuntimeException('AES-CBC decryption failed: '.openssl_error_string());
        }

        return $plaintext;
    }

    /**
     * Get encryption key by name.
     */
    private function getEncryptionKey(string $keyName): ?string
    {
        // Check cache first
        if (isset($this->encryptionKeys[$keyName])) {
            return $this->encryptionKeys[$keyName];
        }

        // Load from database
        $sql = "SELECT key_data FROM `{$this->keysTableName}` WHERE key_name = ? AND is_active = TRUE";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$keyName]);

        $keyData = $stmt->fetchColumn();

        if (!$keyData) {
            return null;
        }

        // Decrypt the stored key (keys are encrypted with master key)
        $key = self::decryptStoredKey($keyData);

        // Cache the key
        $this->encryptionKeys[$keyName] = $key;

        return $key;
    }

    /**
     * Get master key for encrypting stored keys.
     */
    private static function getMasterKey(): string
    {
        // Try environment variable first
        $masterKey = getenv('ENCRYPTION_MASTER_KEY');

        if ($masterKey) {
            return hash('sha256', $masterKey, true);
        }

        // Try alternative environment variable name for backward compatibility
        $masterKey = getenv('MULTIFLEXI_MASTER_KEY');

        if ($masterKey) {
            return hash('sha256', $masterKey, true);
        }

        // Load from config (.env file)
        $masterKey = \Ease\Shared::cfg('ENCRYPTION_MASTER_KEY');

        if ($masterKey) {
            return hash('sha256', $masterKey, true);
        }

        throw new \RuntimeException('No encryption master key found. Set ENCRYPTION_MASTER_KEY in .env file or as environment variable.');
    }

    /**
     * Encrypt key for storage.
     */
    private static function encryptStoredKey(string $key): string
    {
        $masterKey = self::getMasterKey();
        $iv = random_bytes(16);

        $encrypted = openssl_encrypt($key, 'aes-256-cbc', $masterKey, \OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new \RuntimeException('Failed to encrypt storage key');
        }

        return base64_encode($iv.$encrypted);
    }

    /**
     * Decrypt stored key.
     */
    private static function decryptStoredKey(string $encryptedKey): string
    {
        $masterKey = self::getMasterKey();
        $data = base64_decode($encryptedKey, true);

        if ($data === false || \strlen($data) < 16) {
            throw new \RuntimeException('Invalid encrypted key data');
        }

        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        $key = openssl_decrypt($encrypted, 'aes-256-cbc', $masterKey, \OPENSSL_RAW_DATA, $iv);

        if ($key === false) {
            throw new \RuntimeException('Failed to decrypt storage key');
        }

        return $key;
    }
}
