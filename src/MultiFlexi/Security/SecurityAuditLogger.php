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
 * Enhanced security audit logger for monitoring security events.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class SecurityAuditLogger
{
    public const SEVERITY_LOW = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH = 'high';
    public const SEVERITY_CRITICAL = 'critical';
    public const EVENT_LOGIN_SUCCESS = 'login_success';
    public const EVENT_LOGIN_FAILURE = 'login_failure';
    public const EVENT_LOGOUT = 'logout';
    public const EVENT_PASSWORD_CHANGE = 'password_change';
    public const EVENT_2FA_ENABLED = '2fa_enabled';
    public const EVENT_2FA_DISABLED = '2fa_disabled';
    public const EVENT_2FA_USED = '2fa_used';
    public const EVENT_ACCOUNT_LOCKED = 'account_locked';
    public const EVENT_ACCOUNT_UNLOCKED = 'account_unlocked';
    public const EVENT_PERMISSION_CHANGE = 'permission_change';
    public const EVENT_ROLE_ASSIGNED = 'role_assigned';
    public const EVENT_ROLE_REMOVED = 'role_removed';
    public const EVENT_SESSION_HIJACK_DETECTED = 'session_hijack_detected';
    public const EVENT_BRUTE_FORCE_DETECTED = 'brute_force_detected';
    public const EVENT_DATA_EXPORT = 'data_export';
    public const EVENT_DATA_DELETION = 'data_deletion';
    public const EVENT_CONFIG_CHANGE = 'config_change';
    public const EVENT_ENCRYPTION_KEY_ROTATION = 'encryption_key_rotation';
    public const EVENT_API_RATE_LIMIT_EXCEEDED = 'api_rate_limit_exceeded';
    public const EVENT_IP_WHITELIST_CHANGE = 'ip_whitelist_change';
    public const EVENT_SECURITY_VIOLATION = 'security_violation';
    private \PDO $pdo;
    private string $tableName;
    private bool $enabled;

    public function __construct(
        \PDO $pdo,
        bool $enabled = true,
        string $tableName = 'security_audit_log',
    ) {
        $this->pdo = $pdo;
        $this->enabled = $enabled;
        $this->tableName = $tableName;
    }

    /**
     * Log a security event.
     */
    public function logEvent(
        string $eventType,
        string $description,
        string $severity = self::SEVERITY_MEDIUM,
        ?int $userId = null,
        array $additionalData = [],
    ): bool {
        if (!$this->enabled) {
            return true;
        }

        try {
            $sql = <<<EOD
INSERT INTO `{$this->tableName}`
                    (user_id, event_type, event_description, ip_address, user_agent, additional_data, severity, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
EOD;

            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([
                $userId,
                $eventType,
                $description,
                self::getClientIpAddress(),
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                json_encode($additionalData),
                $severity,
            ]);
        } catch (\Exception $e) {
            // Fallback to error log if database logging fails
            error_log('Security Audit Logger Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Log successful login.
     */
    public function logLoginSuccess(int $userId, array $additionalData = []): bool
    {
        return $this->logEvent(
            self::EVENT_LOGIN_SUCCESS,
            'User logged in successfully',
            self::SEVERITY_LOW,
            $userId,
            $additionalData,
        );
    }

    /**
     * Log failed login attempt.
     */
    public function logLoginFailure(string $username, string $reason, array $additionalData = []): bool
    {
        return $this->logEvent(
            self::EVENT_LOGIN_FAILURE,
            "Failed login attempt for username: {$username}. Reason: {$reason}",
            self::SEVERITY_MEDIUM,
            null,
            array_merge(['username' => $username, 'reason' => $reason], $additionalData),
        );
    }

    /**
     * Log user logout.
     */
    public function logLogout(int $userId, array $additionalData = []): bool
    {
        return $this->logEvent(
            self::EVENT_LOGOUT,
            'User logged out',
            self::SEVERITY_LOW,
            $userId,
            $additionalData,
        );
    }

    /**
     * Log password change.
     */
    public function logPasswordChange(int $userId, bool $forced = false): bool
    {
        $description = $forced ? 'Password changed (forced)' : 'Password changed by user';
        $severity = $forced ? self::SEVERITY_HIGH : self::SEVERITY_MEDIUM;

        return $this->logEvent(
            self::EVENT_PASSWORD_CHANGE,
            $description,
            $severity,
            $userId,
            ['forced' => $forced],
        );
    }

    /**
     * Log 2FA enabled.
     */
    public function logTwoFactorEnabled(int $userId): bool
    {
        return $this->logEvent(
            self::EVENT_2FA_ENABLED,
            'Two-factor authentication enabled',
            self::SEVERITY_MEDIUM,
            $userId,
        );
    }

    /**
     * Log 2FA disabled.
     */
    public function logTwoFactorDisabled(int $userId): bool
    {
        return $this->logEvent(
            self::EVENT_2FA_DISABLED,
            'Two-factor authentication disabled',
            self::SEVERITY_HIGH,
            $userId,
        );
    }

    /**
     * Log 2FA usage.
     */
    public function logTwoFactorUsed(int $userId, bool $success): bool
    {
        $description = $success ? '2FA verification successful' : '2FA verification failed';
        $severity = $success ? self::SEVERITY_LOW : self::SEVERITY_MEDIUM;

        return $this->logEvent(
            self::EVENT_2FA_USED,
            $description,
            $severity,
            $userId,
            ['success' => $success],
        );
    }

    /**
     * Log account lockout.
     */
    public function logAccountLocked(int $userId, string $reason): bool
    {
        return $this->logEvent(
            self::EVENT_ACCOUNT_LOCKED,
            "Account locked: {$reason}",
            self::SEVERITY_HIGH,
            $userId,
            ['reason' => $reason],
        );
    }

    /**
     * Log account unlock.
     */
    public function logAccountUnlocked(int $userId, int $unlockedBy): bool
    {
        return $this->logEvent(
            self::EVENT_ACCOUNT_UNLOCKED,
            'Account unlocked',
            self::SEVERITY_MEDIUM,
            $userId,
            ['unlocked_by' => $unlockedBy],
        );
    }

    /**
     * Log permission changes.
     */
    public function logPermissionChange(int $userId, string $permission, string $action, int $changedBy): bool
    {
        return $this->logEvent(
            self::EVENT_PERMISSION_CHANGE,
            "Permission '{$permission}' {$action}",
            self::SEVERITY_HIGH,
            $userId,
            ['permission' => $permission, 'action' => $action, 'changed_by' => $changedBy],
        );
    }

    /**
     * Log role assignment.
     */
    public function logRoleAssigned(int $userId, string $roleName, int $assignedBy): bool
    {
        return $this->logEvent(
            self::EVENT_ROLE_ASSIGNED,
            "Role '{$roleName}' assigned to user",
            self::SEVERITY_HIGH,
            $userId,
            ['role' => $roleName, 'assigned_by' => $assignedBy],
        );
    }

    /**
     * Log role removal.
     */
    public function logRoleRemoved(int $userId, string $roleName, int $removedBy): bool
    {
        return $this->logEvent(
            self::EVENT_ROLE_REMOVED,
            "Role '{$roleName}' removed from user",
            self::SEVERITY_HIGH,
            $userId,
            ['role' => $roleName, 'removed_by' => $removedBy],
        );
    }

    /**
     * Log session hijacking detection.
     */
    public function logSessionHijackDetected(int $userId, string $reason): bool
    {
        return $this->logEvent(
            self::EVENT_SESSION_HIJACK_DETECTED,
            "Possible session hijacking detected: {$reason}",
            self::SEVERITY_CRITICAL,
            $userId,
            ['reason' => $reason],
        );
    }

    /**
     * Log brute force detection.
     */
    public function logBruteForceDetected(string $username, int $attempts): bool
    {
        return $this->logEvent(
            self::EVENT_BRUTE_FORCE_DETECTED,
            "Brute force attack detected against username: {$username}",
            self::SEVERITY_CRITICAL,
            null,
            ['username' => $username, 'attempts' => $attempts],
        );
    }

    /**
     * Log data export.
     */
    public function logDataExport(int $userId, string $dataType, array $additionalData = []): bool
    {
        return $this->logEvent(
            self::EVENT_DATA_EXPORT,
            "Data export requested: {$dataType}",
            self::SEVERITY_MEDIUM,
            $userId,
            array_merge(['data_type' => $dataType], $additionalData),
        );
    }

    /**
     * Log data deletion.
     */
    public function logDataDeletion(int $userId, string $dataType, array $additionalData = []): bool
    {
        return $this->logEvent(
            self::EVENT_DATA_DELETION,
            "Data deletion performed: {$dataType}",
            self::SEVERITY_HIGH,
            $userId,
            array_merge(['data_type' => $dataType], $additionalData),
        );
    }

    /**
     * Log configuration changes.
     *
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    public function logConfigChange(int $userId, string $setting, $oldValue, $newValue): bool
    {
        return $this->logEvent(
            self::EVENT_CONFIG_CHANGE,
            "Configuration changed: {$setting}",
            self::SEVERITY_MEDIUM,
            $userId,
            [
                'setting' => $setting,
                'old_value' => $oldValue,
                'new_value' => $newValue,
            ],
        );
    }

    /**
     * Log security violations.
     */
    public function logSecurityViolation(string $violation, ?int $userId = null, array $additionalData = []): bool
    {
        return $this->logEvent(
            self::EVENT_SECURITY_VIOLATION,
            "Security violation: {$violation}",
            self::SEVERITY_CRITICAL,
            $userId,
            $additionalData,
        );
    }

    /**
     * Get recent security events.
     */
    public function getRecentEvents(int $hours = 24, ?string $severity = null, ?string $eventType = null): array
    {
        $sql = "SELECT * FROM `{$this->tableName}` WHERE created_at > DATE_SUB(NOW(), INTERVAL ? HOUR)";
        $params = [$hours];

        if ($severity) {
            $sql .= ' AND severity = ?';
            $params[] = $severity;
        }

        if ($eventType) {
            $sql .= ' AND event_type = ?';
            $params[] = $eventType;
        }

        $sql .= ' ORDER BY created_at DESC LIMIT 1000';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get security statistics.
     */
    public function getSecurityStatistics(int $days = 7): array
    {
        $stats = [];

        // Failed login attempts
        $sql = <<<EOD
SELECT COUNT(*) as count FROM `{$this->tableName}`
                WHERE event_type = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
EOD;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([self::EVENT_LOGIN_FAILURE, $days]);
        $stats['failed_logins'] = $stmt->fetchColumn();

        // Successful logins
        $stmt->execute([self::EVENT_LOGIN_SUCCESS, $days]);
        $stats['successful_logins'] = $stmt->fetchColumn();

        // Critical events
        $sql = <<<EOD
SELECT COUNT(*) as count FROM `{$this->tableName}`
                WHERE severity = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
EOD;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([self::SEVERITY_CRITICAL, $days]);
        $stats['critical_events'] = $stmt->fetchColumn();

        // High severity events
        $stmt->execute([self::SEVERITY_HIGH, $days]);
        $stats['high_severity_events'] = $stmt->fetchColumn();

        // Unique attacking IPs
        $sql = <<<EOD
SELECT COUNT(DISTINCT ip_address) as count FROM `{$this->tableName}`
                WHERE event_type = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
EOD;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([self::EVENT_LOGIN_FAILURE, $days]);
        $stats['attacking_ips'] = $stmt->fetchColumn();

        // Events by type
        $sql = <<<EOD
SELECT event_type, COUNT(*) as count FROM `{$this->tableName}`
                WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY event_type ORDER BY count DESC
EOD;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$days]);
        $stats['events_by_type'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Get security events for a specific user.
     */
    public function getUserSecurityEvents(int $userId, int $days = 30): array
    {
        $sql = <<<EOD
SELECT * FROM `{$this->tableName}`
                WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY created_at DESC LIMIT 100
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $days]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Check for suspicious activity patterns.
     */
    public function detectSuspiciousActivity(): array
    {
        $suspicious = [];

        // Multiple failed logins from same IP
        $sql = <<<EOD
SELECT ip_address, COUNT(*) as attempts
                FROM `{$this->tableName}`
                WHERE event_type = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                GROUP BY ip_address
                HAVING attempts >= 5
                ORDER BY attempts DESC
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([self::EVENT_LOGIN_FAILURE]);
        $suspicious['brute_force_ips'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Multiple accounts accessed from same IP
        $sql = <<<EOD
SELECT ip_address, COUNT(DISTINCT user_id) as users
                FROM `{$this->tableName}`
                WHERE event_type = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
                  AND user_id IS NOT NULL
                GROUP BY ip_address
                HAVING users >= 3
                ORDER BY users DESC
EOD;

        $stmt->execute([self::EVENT_LOGIN_SUCCESS]);
        $suspicious['multi_user_ips'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Off-hours activity
        $sql = <<<EOD
SELECT user_id, ip_address, COUNT(*) as events
                FROM `{$this->tableName}`
                WHERE event_type IN (?, ?)
                  AND (HOUR(created_at) < 6 OR HOUR(created_at) > 22)
                  AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                  AND user_id IS NOT NULL
                GROUP BY user_id, ip_address
                HAVING events >= 5
                ORDER BY events DESC
EOD;

        $stmt->execute([self::EVENT_LOGIN_SUCCESS, self::EVENT_DATA_EXPORT]);
        $suspicious['off_hours_activity'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $suspicious;
    }

    /**
     * Clean up old audit logs.
     *
     * @param bool $keepCritical Keep critical events longer
     *
     * @return int Number of deleted records
     */
    public function cleanup(int $daysToKeep = 90, bool $keepCritical = true): int
    {
        $deleted = 0;

        if ($keepCritical) {
            // Delete non-critical events older than specified days
            $sql = <<<EOD
DELETE FROM `{$this->tableName}`
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                      AND severity NOT IN (?, ?)
EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$daysToKeep, self::SEVERITY_HIGH, self::SEVERITY_CRITICAL]);
            $deleted = $stmt->rowCount();

            // Delete critical events older than 1 year
            $sql = <<<EOD
DELETE FROM `{$this->tableName}`
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL 365 DAY)
EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $deleted += $stmt->rowCount();
        } else {
            $sql = <<<EOD
DELETE FROM `{$this->tableName}`
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$daysToKeep]);
            $deleted = $stmt->rowCount();
        }

        return $deleted;
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
                if (filter_var($ip, \FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
