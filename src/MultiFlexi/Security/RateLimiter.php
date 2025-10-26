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
 * Rate limiting middleware for API endpoints protection.
 */
class RateLimiter
{
    /**
     * Database connection.
     */
    private \PDO $pdo;

    /**
     * Rate limiting rules table name.
     */
    private string $rulesTableName;

    /**
     * Rate limiting attempts table name.
     */
    private string $attemptsTableName;

    /**
     * Default rate limits per endpoint type.
     */
    private array $defaultLimits = [
        'login' => ['requests' => 5, 'window' => 900], // 5 requests per 15 minutes
        'api' => ['requests' => 100, 'window' => 3600], // 100 requests per hour
        'admin' => ['requests' => 200, 'window' => 3600], // 200 requests per hour
        'public' => ['requests' => 50, 'window' => 3600], // 50 requests per hour
        'upload' => ['requests' => 10, 'window' => 3600], // 10 uploads per hour
    ];

    /**
     * Constructor.
     *
     * @param \PDO   $pdo               Database connection
     * @param string $rulesTableName    Rate limiting rules table name
     * @param string $attemptsTableName Rate limiting attempts table name
     */
    public function __construct(\PDO $pdo, string $rulesTableName = 'rate_limiting_rules', string $attemptsTableName = 'rate_limiting_attempts')
    {
        $this->pdo = $pdo;
        $this->rulesTableName = $rulesTableName;
        $this->attemptsTableName = $attemptsTableName;

        $this->initializeTables();
        $this->initializeDefaultRules();
    }

    /**
     * Check if a request should be rate limited.
     *
     * @param string      $ipAddress    Client IP address
     * @param string      $endpointType Type of endpoint (login, api, admin, etc.)
     * @param null|string $endpointPath Specific endpoint path
     * @param null|int    $userId       User ID if authenticated
     *
     * @return array Rate limit check result with 'allowed' boolean and metadata
     */
    public function checkRateLimit(string $ipAddress, string $endpointType, ?string $endpointPath = null, ?int $userId = null): array
    {
        try {
            // Get rate limiting rule
            $rule = $this->getRule($endpointType, $endpointPath);

            if (!$rule || !$rule['is_active']) {
                return ['allowed' => true, 'message' => 'No rate limiting applied'];
            }

            // Get or create attempt record
            $attempt = $this->getAttempt($ipAddress, $endpointType, $endpointPath);

            // Check if currently blocked
            if ($attempt && $attempt['blocked_until'] && new \DateTime($attempt['blocked_until']) > new \DateTime()) {
                return [
                    'allowed' => false,
                    'message' => 'Rate limit exceeded. Try again later.',
                    'blocked_until' => $attempt['blocked_until'],
                    'rule' => $rule,
                ];
            }

            // Check if time window has passed
            $timeWindow = $rule['time_window'];
            $windowStart = new \DateTime();
            $windowStart->modify("-{$timeWindow} seconds");

            if ($attempt && new \DateTime($attempt['first_attempt_at']) < $windowStart) {
                // Reset attempt counter
                $this->resetAttempt($ipAddress, $endpointType, $endpointPath);
                $attempt = null;
            }

            // Check current attempt count
            $currentAttempts = $attempt ? $attempt['attempts'] : 0;

            if ($currentAttempts >= $rule['max_requests']) {
                // Block the client
                $blockDuration = min($rule['time_window'] * 2, 3600); // Block for double the window or 1 hour max
                $blockedUntil = new \DateTime();
                $blockedUntil->modify("+{$blockDuration} seconds");

                $this->blockClient($ipAddress, $endpointType, $endpointPath, $blockedUntil->format('Y-m-d H:i:s'));

                // Log rate limit exceeded
                if (isset($GLOBALS['securityAuditLogger'])) {
                    $GLOBALS['securityAuditLogger']->logEvent(
                        'rate_limit_exceeded',
                        "Rate limit exceeded for {$endpointType} from IP {$ipAddress}",
                        'medium',
                        $userId,
                        [
                            'ip_address' => $ipAddress,
                            'endpoint_type' => $endpointType,
                            'endpoint_path' => $endpointPath,
                            'attempts' => $currentAttempts,
                            'max_requests' => $rule['max_requests'],
                            'blocked_until' => $blockedUntil->format('Y-m-d H:i:s'),
                        ],
                    );
                }

                return [
                    'allowed' => false,
                    'message' => 'Rate limit exceeded. Try again later.',
                    'blocked_until' => $blockedUntil->format('Y-m-d H:i:s'),
                    'rule' => $rule,
                ];
            }

            // Increment attempt counter
            $this->incrementAttempt($ipAddress, $endpointType, $endpointPath, $userId);

            return [
                'allowed' => true,
                'message' => 'Request allowed',
                'attempts' => $currentAttempts + 1,
                'max_requests' => $rule['max_requests'],
                'window_remaining' => $timeWindow - (time() - strtotime($attempt['first_attempt_at'] ?? 'now')),
                'rule' => $rule,
            ];
        } catch (\Exception $e) {
            error_log('Rate limiter error: '.$e->getMessage());

            // Allow request on error to prevent blocking legitimate users
            return ['allowed' => true, 'message' => 'Rate limiter error', 'error' => $e->getMessage()];
        }
    }

    /**
     * Add or update a rate limiting rule.
     *
     * @param string      $endpointType Type of endpoint
     * @param null|string $endpointPath Specific endpoint path
     * @param int         $maxRequests  Maximum requests allowed
     * @param int         $timeWindow   Time window in seconds
     *
     * @return bool Success status
     */
    public function addRule(string $endpointType, ?string $endpointPath, int $maxRequests, int $timeWindow): bool
    {
        try {
            $sql = <<<EOD

                INSERT INTO `{$this->rulesTableName}`
                (endpoint_type, endpoint_path, max_requests, time_window, is_active)
                VALUES (?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE
                max_requests = VALUES(max_requests),
                time_window = VALUES(time_window),
                is_active = 1,
                updated_at = CURRENT_TIMESTAMP

EOD;

            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([$endpointType, $endpointPath, $maxRequests, $timeWindow]);
        } catch (\Exception $e) {
            error_log('Failed to add rate limiting rule: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Remove a rate limiting rule.
     *
     * @param string      $endpointType Type of endpoint
     * @param null|string $endpointPath Specific endpoint path
     *
     * @return bool Success status
     */
    public function removeRule(string $endpointType, ?string $endpointPath = null): bool
    {
        try {
            $sql = "DELETE FROM `{$this->rulesTableName}` WHERE endpoint_type = ?";
            $params = [$endpointType];

            if ($endpointPath !== null) {
                $sql .= ' AND endpoint_path = ?';
                $params[] = $endpointPath;
            } else {
                $sql .= ' AND endpoint_path IS NULL';
            }

            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute($params);
        } catch (\Exception $e) {
            error_log('Failed to remove rate limiting rule: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Clear rate limit attempts for successful actions (e.g., successful login).
     *
     * @param string      $ipAddress    IP address
     * @param string      $endpointType Endpoint type
     * @param null|string $endpointPath Endpoint path
     *
     * @return bool Success status
     */
    public function clearRateLimit(string $ipAddress, string $endpointType, ?string $endpointPath = null): bool
    {
        try {
            $this->resetAttempt($ipAddress, $endpointType, $endpointPath);

            return true;
        } catch (\Exception $e) {
            error_log('Failed to clear rate limit: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Manually unblock a client.
     *
     * @param string      $ipAddress    IP address to unblock
     * @param null|string $endpointType Specific endpoint type to unblock
     *
     * @return bool Success status
     */
    public function unblockClient(string $ipAddress, ?string $endpointType = null): bool
    {
        try {
            $sql = "UPDATE `{$this->attemptsTableName}` SET blocked_until = NULL WHERE ip_address = ?";
            $params = [$ipAddress];

            if ($endpointType !== null) {
                $sql .= ' AND endpoint_type = ?';
                $params[] = $endpointType;
            }

            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute($params);
        } catch (\Exception $e) {
            error_log('Failed to unblock client: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get blocked clients.
     *
     * @return array List of currently blocked clients
     */
    public function getBlockedClients(): array
    {
        try {
            $sql = <<<EOD

                SELECT ip_address, endpoint_type, endpoint_path, attempts, blocked_until, last_attempt_at
                FROM `{$this->attemptsTableName}`
                WHERE blocked_until IS NOT NULL AND blocked_until > NOW()
                ORDER BY blocked_until DESC

EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Failed to get blocked clients: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Clean up expired blocks and old attempt records.
     */
    public function cleanup(): void
    {
        try {
            // Remove expired blocks
            $this->pdo->exec(<<<EOD

                UPDATE `{$this->attemptsTableName}`
                SET blocked_until = NULL
                WHERE blocked_until IS NOT NULL AND blocked_until <= NOW()

EOD);

            // Remove old attempt records (older than 7 days)
            $this->pdo->exec(<<<EOD

                DELETE FROM `{$this->attemptsTableName}`
                WHERE last_attempt_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND blocked_until IS NULL

EOD);
        } catch (\Exception $e) {
            error_log('Rate limiter cleanup failed: '.$e->getMessage());
        }
    }

    /**
     * Get rate limiting statistics.
     *
     * @return array Statistics data
     */
    public function getStatistics(): array
    {
        try {
            $stats = [];

            // Active rules count
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `{$this->rulesTableName}` WHERE is_active = 1");
            $stats['active_rules'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

            // Currently blocked clients
            $stmt = $this->pdo->query(<<<EOD

                SELECT COUNT(*) as count FROM `{$this->attemptsTableName}`
                WHERE blocked_until IS NOT NULL AND blocked_until > NOW()

EOD);
            $stats['blocked_clients'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

            // Attempts in last 24 hours
            $stmt = $this->pdo->query(<<<EOD

                SELECT COUNT(*) as count FROM `{$this->attemptsTableName}`
                WHERE last_attempt_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)

EOD);
            $stats['attempts_24h'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

            // Top blocked endpoint types
            $stmt = $this->pdo->query(<<<EOD

                SELECT endpoint_type, COUNT(*) as blocks
                FROM `{$this->attemptsTableName}`
                WHERE blocked_until IS NOT NULL
                GROUP BY endpoint_type
                ORDER BY blocks DESC
                LIMIT 10

EOD);
            $stats['top_blocked_endpoints'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return $stats;
        } catch (\Exception $e) {
            error_log('Failed to get rate limiting statistics: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Initialize the rate limiting tables.
     */
    private function initializeTables(): void
    {
        // Rate limiting rules table
        $this->pdo->exec(<<<EOD

            CREATE TABLE IF NOT EXISTS `{$this->rulesTableName}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `endpoint_type` varchar(50) NOT NULL,
                `endpoint_path` varchar(255) DEFAULT NULL,
                `max_requests` int(11) NOT NULL DEFAULT 100,
                `time_window` int(11) NOT NULL DEFAULT 3600,
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_endpoint` (`endpoint_type`, `endpoint_path`),
                KEY `idx_endpoint_type` (`endpoint_type`),
                KEY `idx_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

EOD);

        // Rate limiting attempts table
        $this->pdo->exec(<<<EOD

            CREATE TABLE IF NOT EXISTS `{$this->attemptsTableName}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `ip_address` varchar(45) NOT NULL,
                `user_id` int(11) DEFAULT NULL,
                `endpoint_type` varchar(50) NOT NULL,
                `endpoint_path` varchar(255) DEFAULT NULL,
                `attempts` int(11) NOT NULL DEFAULT 1,
                `blocked_until` timestamp NULL DEFAULT NULL,
                `first_attempt_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_attempt_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_limiter` (`ip_address`, `endpoint_type`, `endpoint_path`),
                KEY `idx_ip_address` (`ip_address`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_endpoint_type` (`endpoint_type`),
                KEY `idx_blocked_until` (`blocked_until`),
                KEY `idx_last_attempt` (`last_attempt_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

EOD);
    }

    /**
     * Initialize default rate limiting rules.
     */
    private function initializeDefaultRules(): void
    {
        foreach ($this->defaultLimits as $endpointType => $limits) {
            $this->addRule($endpointType, null, $limits['requests'], $limits['window']);
        }
    }

    /**
     * Get a rate limiting rule.
     *
     * @param string      $endpointType Type of endpoint
     * @param null|string $endpointPath Specific endpoint path
     *
     * @return null|array Rule data or null if not found
     */
    private function getRule(string $endpointType, ?string $endpointPath = null): ?array
    {
        // First try to get specific path rule
        if ($endpointPath !== null) {
            $sql = "SELECT * FROM `{$this->rulesTableName}` WHERE endpoint_type = ? AND endpoint_path = ? AND is_active = 1 LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$endpointType, $endpointPath]);
            $rule = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($rule) {
                return $rule;
            }
        }

        // Fall back to general endpoint type rule
        $sql = "SELECT * FROM `{$this->rulesTableName}` WHERE endpoint_type = ? AND endpoint_path IS NULL AND is_active = 1 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$endpointType]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get attempt record for IP and endpoint.
     *
     * @param string      $ipAddress    IP address
     * @param string      $endpointType Endpoint type
     * @param null|string $endpointPath Endpoint path
     *
     * @return null|array Attempt record or null
     */
    private function getAttempt(string $ipAddress, string $endpointType, ?string $endpointPath = null): ?array
    {
        $sql = "SELECT * FROM `{$this->attemptsTableName}` WHERE ip_address = ? AND endpoint_type = ?";
        $params = [$ipAddress, $endpointType];

        if ($endpointPath !== null) {
            $sql .= ' AND endpoint_path = ?';
            $params[] = $endpointPath;
        } else {
            $sql .= ' AND endpoint_path IS NULL';
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Increment attempt counter.
     *
     * @param string      $ipAddress    IP address
     * @param string      $endpointType Endpoint type
     * @param null|string $endpointPath Endpoint path
     * @param null|int    $userId       User ID
     */
    private function incrementAttempt(string $ipAddress, string $endpointType, ?string $endpointPath, ?int $userId = null): void
    {
        $sql = <<<EOD

            INSERT INTO `{$this->attemptsTableName}`
            (ip_address, user_id, endpoint_type, endpoint_path, attempts, first_attempt_at, last_attempt_at)
            VALUES (?, ?, ?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE
            attempts = attempts + 1,
            user_id = COALESCE(user_id, VALUES(user_id)),
            last_attempt_at = CURRENT_TIMESTAMP

EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$ipAddress, $userId, $endpointType, $endpointPath]);
    }

    /**
     * Reset attempt counter.
     *
     * @param string      $ipAddress    IP address
     * @param string      $endpointType Endpoint type
     * @param null|string $endpointPath Endpoint path
     */
    private function resetAttempt(string $ipAddress, string $endpointType, ?string $endpointPath = null): void
    {
        $sql = "DELETE FROM `{$this->attemptsTableName}` WHERE ip_address = ? AND endpoint_type = ?";
        $params = [$ipAddress, $endpointType];

        if ($endpointPath !== null) {
            $sql .= ' AND endpoint_path = ?';
            $params[] = $endpointPath;
        } else {
            $sql .= ' AND endpoint_path IS NULL';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Block a client.
     *
     * @param string      $ipAddress    IP address to block
     * @param string      $endpointType Endpoint type
     * @param null|string $endpointPath Endpoint path
     * @param string      $blockedUntil Block expiration datetime
     */
    private function blockClient(string $ipAddress, string $endpointType, ?string $endpointPath, string $blockedUntil): void
    {
        $sql = <<<EOD

            UPDATE `{$this->attemptsTableName}`
            SET blocked_until = ?
            WHERE ip_address = ? AND endpoint_type = ?

EOD;
        $params = [$blockedUntil, $ipAddress, $endpointType];

        if ($endpointPath !== null) {
            $sql .= ' AND endpoint_path = ?';
            $params[] = $endpointPath;
        } else {
            $sql .= ' AND endpoint_path IS NULL';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
}
