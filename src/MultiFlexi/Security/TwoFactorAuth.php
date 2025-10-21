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
 * Two-Factor Authentication implementation using TOTP (Time-based One-Time Password).
 */
class TwoFactorAuth
{
    /**
     * Database connection.
     */
    private \PDO $pdo;

    /**
     * 2FA table name.
     */
    private string $tableName;

    /**
     * TOTP time window in seconds.
     */
    private int $timeWindow = 30;

    /**
     * Number of backup codes to generate.
     */
    private int $backupCodeCount = 10;

    /**
     * Verification attempts limit.
     */
    private int $maxAttempts = 5;

    /**
     * Constructor.
     *
     * @param \PDO   $pdo       Database connection
     * @param string $tableName 2FA table name
     */
    public function __construct(\PDO $pdo, string $tableName = 'two_factor_auth')
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;

        $this->initializeTable();
    }

    /**
     * Generate a random secret for TOTP.
     *
     * @return string Base32-encoded secret
     */
    public function generateSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ($i = 0; $i < 16; ++$i) {
            $secret .= $chars[random_int(0, 31)];
        }

        return $secret;
    }

    /**
     * Set up 2FA for a user.
     *
     * @param int         $userId User ID
     * @param null|string $secret Optional secret (generates new if null)
     *
     * @return array Setup information including secret and QR code URL
     */
    public function setupTwoFactor(int $userId, ?string $secret = null): array
    {
        if ($secret === null) {
            $secret = $this->generateSecret();
        }

        // Generate backup codes
        $backupCodes = $this->generateBackupCodes();

        try {
            $sql = <<<EOD

                INSERT INTO `{$this->tableName}`
                (user_id, secret, backup_codes, is_enabled)
                VALUES (?, ?, ?, 0)
                ON DUPLICATE KEY UPDATE
                secret = VALUES(secret),
                backup_codes = VALUES(backup_codes),
                is_enabled = 0,
                failed_attempts = 0,
                locked_until = NULL,
                updated_at = CURRENT_TIMESTAMP

EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $secret, json_encode($backupCodes)]);

            // Get issuer name from configuration
            $issuer = \Ease\Shared::cfg('APP_NAME', 'MultiFlexi');

            // Get user information for QR code
            $userInfo = $this->getUserInfo($userId);
            $qrCodeUrl = self::generateQrCodeUrl($secret, $userInfo['username'] ?? "user{$userId}", $issuer);

            // Log 2FA setup initiated
            if (isset($GLOBALS['securityAuditLogger'])) {
                $GLOBALS['securityAuditLogger']->logEvent(
                    'two_factor_setup_initiated',
                    "2FA setup initiated for user ID {$userId}",
                    'low',
                    $userId,
                );
            }

            return [
                'secret' => $secret,
                'backup_codes' => $backupCodes,
                'qr_code_url' => $qrCodeUrl,
                'manual_entry_key' => chunk_split($secret, 4, ' '),
            ];
        } catch (\Exception $e) {
            error_log('Failed to setup 2FA: '.$e->getMessage());

            throw new \Exception('Failed to setup two-factor authentication');
        }
    }

    /**
     * Enable 2FA for a user after verification.
     *
     * @param int    $userId           User ID
     * @param string $verificationCode TOTP code for verification
     *
     * @return bool Success status
     */
    public function enableTwoFactor(int $userId, string $verificationCode): bool
    {
        if (!$this->verifyTotp($userId, $verificationCode, false)) {
            return false;
        }

        try {
            $sql = "UPDATE `{$this->tableName}` SET is_enabled = 1, failed_attempts = 0 WHERE user_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([$userId]);

            if ($success) {
                // Log 2FA enabled
                if (isset($GLOBALS['securityAuditLogger'])) {
                    $GLOBALS['securityAuditLogger']->logEvent(
                        'two_factor_enabled',
                        "2FA enabled for user ID {$userId}",
                        'medium',
                        $userId,
                    );
                }
            }

            return $success;
        } catch (\Exception $e) {
            error_log('Failed to enable 2FA: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Disable 2FA for a user.
     *
     * @param int    $userId           User ID
     * @param string $verificationCode TOTP code or backup code for verification
     *
     * @return bool Success status
     */
    public function disableTwoFactor(int $userId, string $verificationCode): bool
    {
        if (!$this->verifyCode($userId, $verificationCode)) {
            return false;
        }

        try {
            $sql = "DELETE FROM `{$this->tableName}` WHERE user_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([$userId]);

            if ($success) {
                // Log 2FA disabled
                if (isset($GLOBALS['securityAuditLogger'])) {
                    $GLOBALS['securityAuditLogger']->logEvent(
                        'two_factor_disabled',
                        "2FA disabled for user ID {$userId}",
                        'high',
                        $userId,
                    );
                }
            }

            return $success;
        } catch (\Exception $e) {
            error_log('Failed to disable 2FA: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Verify TOTP code for a user.
     *
     * @param int    $userId         User ID
     * @param string $code           TOTP code to verify
     * @param bool   $updateAttempts Whether to update failed attempts counter
     *
     * @return bool Verification result
     */
    public function verifyTotp(int $userId, string $code, bool $updateAttempts = true): bool
    {
        try {
            // Get user's 2FA data
            $twoFactorData = $this->getTwoFactorData($userId);

            if (!$twoFactorData) {
                return false;
            }

            // Check if account is locked
            if ($twoFactorData['locked_until'] && new \DateTime($twoFactorData['locked_until']) > new \DateTime()) {
                return false;
            }

            // Prevent reuse of the same code
            if ($twoFactorData['last_used_code'] === $code) {
                if ($updateAttempts) {
                    $this->incrementFailedAttempts($userId);
                }

                return false;
            }

            // Calculate TOTP
            $currentTime = time();
            $timeSlice = (int) ($currentTime / $this->timeWindow);

            // Check current time slice and previous/next for clock skew tolerance
            $validCodes = [
                self::calculateTotp($twoFactorData['secret'], $timeSlice - 1),
                self::calculateTotp($twoFactorData['secret'], $timeSlice),
                self::calculateTotp($twoFactorData['secret'], $timeSlice + 1),
            ];

            $isValid = \in_array($code, $validCodes, true);

            if ($isValid) {
                // Update last used code and reset failed attempts
                $this->updateLastUsedCode($userId, $code);

                return true;
            }

            if ($updateAttempts) {
                $this->incrementFailedAttempts($userId);
            }

            return false;
        } catch (\Exception $e) {
            error_log('TOTP verification failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Verify backup code for a user.
     *
     * @param int    $userId User ID
     * @param string $code   Backup code to verify
     *
     * @return bool Verification result
     */
    public function verifyBackupCode(int $userId, string $code): bool
    {
        try {
            $twoFactorData = $this->getTwoFactorData($userId);

            if (!$twoFactorData || !$twoFactorData['backup_codes']) {
                return false;
            }

            $backupCodes = json_decode($twoFactorData['backup_codes'], true);

            if (!\is_array($backupCodes)) {
                return false;
            }

            // Check if code exists and remove it (single use)
            $codeIndex = array_search($code, $backupCodes, true);

            if ($codeIndex === false) {
                $this->incrementFailedAttempts($userId);

                return false;
            }

            // Remove the used backup code
            unset($backupCodes[$codeIndex]);
            $backupCodes = array_values($backupCodes); // Reindex

            // Update backup codes in database
            $sql = "UPDATE `{$this->tableName}` SET backup_codes = ?, failed_attempts = 0 WHERE user_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([json_encode($backupCodes), $userId]);

            // Log backup code usage
            if (isset($GLOBALS['securityAuditLogger'])) {
                $GLOBALS['securityAuditLogger']->logEvent(
                    'two_factor_backup_used',
                    "Backup code used for user ID {$userId}, ".\count($backupCodes).' codes remaining',
                    'medium',
                    $userId,
                );
            }

            return true;
        } catch (\Exception $e) {
            error_log('Backup code verification failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Verify any type of 2FA code (TOTP or backup).
     *
     * @param int    $userId User ID
     * @param string $code   Code to verify
     *
     * @return bool Verification result
     */
    public function verifyCode(int $userId, string $code): bool
    {
        // Try TOTP first (6 digits)
        if (preg_match('/^\d{6}$/', $code)) {
            if ($this->verifyTotp($userId, $code)) {
                return true;
            }
        }

        // Try backup code (8 characters)
        if (preg_match('/^[A-Z0-9]{8}$/', $code)) {
            return $this->verifyBackupCode($userId, $code);
        }

        return false;
    }

    /**
     * Check if 2FA is enabled for a user.
     *
     * @param int $userId User ID
     *
     * @return bool Whether 2FA is enabled
     */
    public function isEnabled(int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare("SELECT is_enabled FROM `{$this->tableName}` WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result && (bool) $result['is_enabled'];
        } catch (\Exception $e) {
            error_log('Failed to check 2FA status: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Generate new backup codes for a user.
     *
     * @param int $userId User ID
     *
     * @return array New backup codes
     */
    public function regenerateBackupCodes(int $userId): array
    {
        $backupCodes = $this->generateBackupCodes();

        try {
            $sql = "UPDATE `{$this->tableName}` SET backup_codes = ? WHERE user_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([json_encode($backupCodes), $userId]);

            // Log backup codes regenerated
            if (isset($GLOBALS['securityAuditLogger'])) {
                $GLOBALS['securityAuditLogger']->logEvent(
                    'two_factor_backup_regenerated',
                    "Backup codes regenerated for user ID {$userId}",
                    'medium',
                    $userId,
                );
            }

            return $backupCodes;
        } catch (\Exception $e) {
            error_log('Failed to regenerate backup codes: '.$e->getMessage());

            throw new \Exception('Failed to regenerate backup codes');
        }
    }

    /**
     * Get remaining backup codes for a user.
     *
     * @param int $userId User ID
     *
     * @return array Remaining backup codes
     */
    public function getRemainingBackupCodes(int $userId): array
    {
        try {
            $twoFactorData = $this->getTwoFactorData($userId);

            if (!$twoFactorData || !$twoFactorData['backup_codes']) {
                return [];
            }

            $backupCodes = json_decode($twoFactorData['backup_codes'], true);

            return \is_array($backupCodes) ? $backupCodes : [];
        } catch (\Exception $e) {
            error_log('Failed to get backup codes: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get 2FA statistics.
     *
     * @return array Statistics data
     */
    public function getStatistics(): array
    {
        try {
            $stats = [];

            // Total users with 2FA enabled
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `{$this->tableName}` WHERE is_enabled = 1");
            $stats['enabled_users'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

            // Total users with 2FA setup but not enabled
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `{$this->tableName}` WHERE is_enabled = 0");
            $stats['pending_users'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

            // Users with locked accounts
            $stmt = $this->pdo->query(<<<EOD

                SELECT COUNT(*) as count FROM `{$this->tableName}`
                WHERE locked_until IS NOT NULL AND locked_until > NOW()

EOD);
            $stats['locked_users'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

            // Users with low backup codes (< 3)
            $stmt = $this->pdo->query(<<<EOD

                SELECT COUNT(*) as count FROM `{$this->tableName}`
                WHERE is_enabled = 1 AND JSON_LENGTH(backup_codes) < 3

EOD);
            $stats['low_backup_codes'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

            return $stats;
        } catch (\Exception $e) {
            error_log('Failed to get 2FA statistics: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Initialize the 2FA table.
     */
    private function initializeTable(): void
    {
        $this->pdo->exec(<<<EOD

            CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(11) NOT NULL,
                `secret` varchar(32) NOT NULL,
                `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
                `backup_codes` json DEFAULT NULL,
                `last_used_code` varchar(6) DEFAULT NULL,
                `failed_attempts` int(11) NOT NULL DEFAULT 0,
                `locked_until` timestamp NULL DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_user` (`user_id`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_enabled` (`is_enabled`),
                KEY `idx_locked_until` (`locked_until`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

EOD);
    }

    /**
     * Calculate TOTP code.
     *
     * @param string $secret    Base32-encoded secret
     * @param int    $timeSlice Time slice
     *
     * @return string 6-digit TOTP code
     */
    private static function calculateTotp(string $secret, int $timeSlice): string
    {
        // Convert base32 secret to binary
        $secretBinary = self::base32Decode($secret);

        // Pack time slice as big-endian 64-bit integer
        $timeBytes = pack('N*', 0).pack('N*', $timeSlice);

        // Calculate HMAC-SHA1
        $hash = hash_hmac('sha1', $timeBytes, $secretBinary, true);

        // Get dynamic truncation offset
        $offset = \ord($hash[19]) & 0xF;

        // Extract 4 bytes starting at offset
        $truncatedHash = unpack('N', substr($hash, $offset, 4))[1];

        // Remove most significant bit and get 6 digits
        $otp = ($truncatedHash & 0x7FFFFFFF) % 1000000;

        return str_pad((string) $otp, 6, '0', \STR_PAD_LEFT);
    }

    /**
     * Base32 decode function.
     *
     * @param string $data Base32-encoded data
     *
     * @return string Decoded binary data
     */
    private static function base32Decode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $v = 0;
        $vbits = 0;

        for ($i = 0; $i < \strlen($data); ++$i) {
            $value = strpos($alphabet, $data[$i]);

            if ($value === false) {
                continue;
            }

            $v <<= 5;
            $v += $value;
            $vbits += 5;

            if ($vbits >= 8) {
                $output .= \chr(($v >> ($vbits - 8)) & 255);
                $vbits -= 8;
            }
        }

        return $output;
    }

    /**
     * Generate backup codes.
     *
     * @return array Array of backup codes
     */
    private function generateBackupCodes(): array
    {
        $codes = [];
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        for ($i = 0; $i < $this->backupCodeCount; ++$i) {
            $code = '';

            for ($j = 0; $j < 8; ++$j) {
                $code .= $chars[random_int(0, 35)];
            }

            $codes[] = $code;
        }

        return $codes;
    }

    /**
     * Generate QR code URL for authenticator apps.
     *
     * @param string $secret   Base32-encoded secret
     * @param string $username Username or email
     * @param string $issuer   Issuer name
     *
     * @return string QR code URL
     */
    private static function generateQrCodeUrl(string $secret, string $username, string $issuer): string
    {
        $otpauthUrl = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            urlencode($issuer),
            urlencode($username),
            $secret,
            urlencode($issuer),
        );

        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.urlencode($otpauthUrl);
    }

    /**
     * Get 2FA data for a user.
     *
     * @param int $userId User ID
     *
     * @return null|array 2FA data or null if not found
     */
    private function getTwoFactorData(int $userId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM `{$this->tableName}` WHERE user_id = ?");
            $stmt->execute([$userId]);

            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            error_log('Failed to get 2FA data: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Update last used TOTP code.
     *
     * @param int    $userId User ID
     * @param string $code   Last used code
     */
    private function updateLastUsedCode(int $userId, string $code): void
    {
        try {
            $sql = "UPDATE `{$this->tableName}` SET last_used_code = ?, failed_attempts = 0 WHERE user_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$code, $userId]);
        } catch (\Exception $e) {
            error_log('Failed to update last used code: '.$e->getMessage());
        }
    }

    /**
     * Increment failed attempts counter.
     *
     * @param int $userId User ID
     */
    private function incrementFailedAttempts(int $userId): void
    {
        try {
            $this->pdo->beginTransaction();

            // Get current failed attempts
            $stmt = $this->pdo->prepare("SELECT failed_attempts FROM `{$this->tableName}` WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$result) {
                $this->pdo->rollback();

                return;
            }

            $failedAttempts = $result['failed_attempts'] + 1;
            $lockedUntil = null;

            // Lock account if max attempts reached
            if ($failedAttempts >= $this->maxAttempts) {
                $lockedUntil = new \DateTime();
                $lockedUntil->modify('+15 minutes');
                $lockedUntil = $lockedUntil->format('Y-m-d H:i:s');

                // Log account locked
                if (isset($GLOBALS['securityAuditLogger'])) {
                    $GLOBALS['securityAuditLogger']->logEvent(
                        'two_factor_account_locked',
                        "2FA account locked for user ID {$userId} due to {$failedAttempts} failed attempts",
                        'high',
                        $userId,
                    );
                }
            }

            // Update failed attempts and lock status
            $sql = "UPDATE `{$this->tableName}` SET failed_attempts = ?, locked_until = ? WHERE user_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$failedAttempts, $lockedUntil, $userId]);

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollback();
            error_log('Failed to increment failed attempts: '.$e->getMessage());
        }
    }

    /**
     * Get user information for QR code generation.
     *
     * @param int $userId User ID
     *
     * @return array User information
     */
    private function getUserInfo(int $userId): array
    {
        try {
            // Try to get user information from the user table
            $stmt = $this->pdo->prepare('SELECT username, email FROM user WHERE id = ?');
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                return $result;
            }

            // Fallback to generic username
            return ['username' => "user{$userId}"];
        } catch (\Exception $e) {
            error_log('Failed to get user info: '.$e->getMessage());

            return ['username' => "user{$userId}"];
        }
    }
}
