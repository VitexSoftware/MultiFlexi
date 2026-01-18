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
 * Brute force protection with login attempt limiting and account lockout.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class BruteForceProtection
{
    private \PDO $pdo;
    private int $maxAttempts;
    private int $lockoutDuration;
    private int $timeWindow;
    private bool $ipBasedLimiting;
    private string $tableName;

    public function __construct(
        \PDO $pdo,
        int $maxAttempts = 5,
        int $lockoutDuration = 900, // 15 minutes
        int $timeWindow = 300, // 5 minutes
        bool $ipBasedLimiting = true,
        string $tableName = 'login_attempts',
    ) {
        $this->pdo = $pdo;
        $this->maxAttempts = $maxAttempts;
        $this->lockoutDuration = $lockoutDuration;
        $this->timeWindow = $timeWindow;
        $this->ipBasedLimiting = $ipBasedLimiting;
        $this->tableName = $tableName;
    }

    /**
     * Record a login attempt.
     */
    public function recordAttempt(string $username, bool $success, ?string $ipAddress = null): bool
    {
        if ($ipAddress === null) {
            $ipAddress = self::getClientIpAddress();
        }

        $sql = <<<EOD
INSERT INTO `{$this->tableName}` (ip_address, username, attempt_time, success, user_agent)
                VALUES (?, ?, NOW(), ?, ?)
EOD;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            $ipAddress,
            $username,
            $success ? 1 : 0,
            $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);
    }

    /**
     * Check if an IP address is currently locked out.
     */
    public function checkIpLockout(?string $ipAddress = null): array
    {
        if ($ipAddress === null) {
            $ipAddress = self::getClientIpAddress();
        }

        if (!$this->ipBasedLimiting) {
            return ['locked' => false, 'attempts' => 0, 'lockout_until' => null];
        }

        // Get failed attempts within the time window
        $sql = <<<EOD
SELECT COUNT(*) as attempts,
                       MAX(attempt_time) as last_attempt
                FROM `{$this->tableName}`
                WHERE ip_address = ?
                  AND success = FALSE
                  AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$ipAddress, $this->timeWindow]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        $attempts = (int) $result['attempts'];
        $lastAttempt = $result['last_attempt'];

        // Check if locked out
        if ($attempts >= $this->maxAttempts && $lastAttempt) {
            $lockoutUntil = strtotime($lastAttempt) + $this->lockoutDuration;

            if (time() < $lockoutUntil) {
                return [
                    'locked' => true,
                    'attempts' => $attempts,
                    'lockout_until' => $lockoutUntil,
                    'remaining_time' => $lockoutUntil - time(),
                ];
            }
        }

        return ['locked' => false, 'attempts' => $attempts, 'lockout_until' => null];
    }

    /**
     * Check if a username is currently locked out.
     */
    public function checkUsernameLockout(string $username): array
    {
        // Get failed attempts within the time window
        $sql = <<<EOD
SELECT COUNT(*) as attempts,
                       MAX(attempt_time) as last_attempt
                FROM `{$this->tableName}`
                WHERE username = ?
                  AND success = FALSE
                  AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username, $this->timeWindow]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        $attempts = (int) $result['attempts'];
        $lastAttempt = $result['last_attempt'];

        // Check if locked out
        if ($attempts >= $this->maxAttempts && $lastAttempt) {
            $lockoutUntil = strtotime($lastAttempt) + $this->lockoutDuration;

            if (time() < $lockoutUntil) {
                return [
                    'locked' => true,
                    'attempts' => $attempts,
                    'lockout_until' => $lockoutUntil,
                    'remaining_time' => $lockoutUntil - time(),
                ];
            }
        }

        return ['locked' => false, 'attempts' => $attempts, 'lockout_until' => null];
    }

    /**
     * Check if login should be allowed.
     */
    public function canAttemptLogin(string $username, ?string $ipAddress = null): array
    {
        if ($ipAddress === null) {
            $ipAddress = self::getClientIpAddress();
        }

        // Check IP-based lockout
        $ipLockout = $this->checkIpLockout($ipAddress);

        if ($ipLockout['locked']) {
            return [
                'allowed' => false,
                'reason' => 'ip_locked',
                'lockout_info' => $ipLockout,
            ];
        }

        // Check username-based lockout
        $userLockout = $this->checkUsernameLockout($username);

        if ($userLockout['locked']) {
            return [
                'allowed' => false,
                'reason' => 'user_locked',
                'lockout_info' => $userLockout,
            ];
        }

        return [
            'allowed' => true,
            'ip_attempts' => $ipLockout['attempts'],
            'user_attempts' => $userLockout['attempts'],
        ];
    }

    /**
     * Clear successful login attempts for IP and username.
     */
    public function clearAttempts(string $username, ?string $ipAddress = null): bool
    {
        if ($ipAddress === null) {
            $ipAddress = self::getClientIpAddress();
        }

        // Mark recent attempts as resolved by updating them
        $sql = <<<EOD
UPDATE `{$this->tableName}`
                SET success = TRUE
                WHERE (ip_address = ? OR username = ?)
                  AND success = FALSE
                  AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
EOD;

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$ipAddress, $username, $this->timeWindow]);
    }

    /**
     * Get statistics about login attempts.
     */
    public function getStatistics(): array
    {
        // Failed attempts in the last hour
        $sql = <<<EOD
SELECT COUNT(*) as failed_attempts_hour
                FROM `{$this->tableName}`
                WHERE success = FALSE
                  AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
EOD;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $failedHour = $stmt->fetchColumn();

        // Failed attempts today
        $sql = <<<EOD
SELECT COUNT(*) as failed_attempts_day
                FROM `{$this->tableName}`
                WHERE success = FALSE
                  AND DATE(attempt_time) = CURDATE()
EOD;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $failedDay = $stmt->fetchColumn();

        // Currently locked IPs
        $sql = <<<EOD
SELECT COUNT(DISTINCT ip_address) as locked_ips
                FROM `{$this->tableName}` a
                WHERE success = FALSE
                  AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
                  AND (SELECT COUNT(*)
                       FROM `{$this->tableName}` b
                       WHERE b.ip_address = a.ip_address
                         AND b.success = FALSE
                         AND b.attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)) >= ?
EOD;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->timeWindow, $this->timeWindow, $this->maxAttempts]);
        $lockedIps = $stmt->fetchColumn();

        // Top attacking IPs
        $sql = <<<EOD
SELECT ip_address, COUNT(*) as attempts
                FROM `{$this->tableName}`
                WHERE success = FALSE
                  AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 DAY)
                GROUP BY ip_address
                ORDER BY attempts DESC
                LIMIT 10
EOD;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $topAttackers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'failed_attempts_hour' => $failedHour,
            'failed_attempts_day' => $failedDay,
            'locked_ips' => $lockedIps,
            'top_attackers' => $topAttackers,
        ];
    }

    /**
     * Clean up old login attempts.
     *
     * @return int Number of deleted records
     */
    public function cleanup(int $daysToKeep = 30): int
    {
        $sql = <<<EOD
DELETE FROM `{$this->tableName}`
                WHERE attempt_time < DATE_SUB(NOW(), INTERVAL ? DAY)
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$daysToKeep]);

        return $stmt->rowCount();
    }

    /**
     * Create delay based on number of attempts.
     *
     * @return int Delay in seconds
     */
    public function calculateDelay(int $attempts): int
    {
        // Progressive delay: 1s, 2s, 4s, 8s, 16s (max 16s)
        return min(16, 2 ** ($attempts - 1));
    }

    /**
     * Enforce delay to slow down brute force attacks.
     */
    public function enforceDelay(int $attempts): void
    {
        if ($attempts > 1) {
            $delay = $this->calculateDelay($attempts);
            sleep($delay);
        }
    }

    /**
     * Get client IP address with proxy support.
     */
    private static function getClientIpAddress(): string
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];

                // Handle comma-separated IPs (from proxy chains)
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                // Validate IP address
                if (filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
