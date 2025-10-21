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
 * IP Whitelisting system for administrative accounts and sensitive operations.
 */
class IpWhitelist
{
    /**
     * Database connection.
     */
    private \PDO $pdo;

    /**
     * IP whitelist table name.
     */
    private string $tableName;

    /**
     * Default whitelisted IP addresses.
     */
    private array $defaultWhitelist = [
        '127.0.0.1',     // localhost IPv4
        '::1',           // localhost IPv6
        '10.0.0.0/8',    // Private network class A
        '172.16.0.0/12', // Private network class B
        '192.168.0.0/16', // Private network class C
    ];

    /**
     * Constructor.
     *
     * @param \PDO   $pdo       Database connection
     * @param string $tableName IP whitelist table name
     */
    public function __construct(\PDO $pdo, string $tableName = 'ip_whitelist')
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;

        $this->initializeTable();
        $this->initializeDefaultWhitelist();
    }

    /**
     * Check if an IP address is whitelisted.
     *
     * @param string   $ipAddress IP address to check
     * @param string   $scope     Scope to check (admin, api, general, etc.)
     * @param null|int $userId    User ID for user-specific whitelist
     *
     * @return bool True if IP is whitelisted
     */
    public function isWhitelisted(string $ipAddress, string $scope = 'general', ?int $userId = null): bool
    {
        try {
            // First check exact IP match
            $sql = <<<EOD

                SELECT COUNT(*) as count FROM `{$this->tableName}`
                WHERE ip_address = ?
                AND (scope = ? OR scope = 'general')
                AND (user_id IS NULL OR user_id = ?)
                AND is_active = 1

EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$ipAddress, $scope, $userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                return true;
            }

            // Check IP ranges
            $sql = <<<EOD

                SELECT ip_range FROM `{$this->tableName}`
                WHERE ip_range IS NOT NULL
                AND (scope = ? OR scope = 'general')
                AND (user_id IS NULL OR user_id = ?)
                AND is_active = 1

EOD;

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$scope, $userId]);
            $ranges = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($ranges as $range) {
                if (self::ipInRange($ipAddress, $range['ip_range'])) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            error_log('IP whitelist check failed: '.$e->getMessage());

            // On error, allow access to prevent locking out legitimate users
            return true;
        }
    }

    /**
     * Add an IP address or range to the whitelist.
     *
     * @param null|string $ipAddress   Single IP address
     * @param null|string $ipRange     IP range (CIDR notation)
     * @param string      $description Description of the whitelist entry
     * @param null|int    $userId      User ID for user-specific whitelist
     * @param string      $scope       Scope (admin, api, general, etc.)
     * @param null|int    $createdBy   User ID who created this entry
     *
     * @return bool Success status
     */
    public function addToWhitelist(?string $ipAddress, ?string $ipRange, string $description = '', ?int $userId = null, string $scope = 'general', ?int $createdBy = null): bool
    {
        try {
            if ($ipAddress === null && $ipRange === null) {
                throw new \InvalidArgumentException('Either IP address or IP range must be provided');
            }

            // Validate IP address format
            if ($ipAddress !== null && !filter_var($ipAddress, \FILTER_VALIDATE_IP)) {
                throw new \InvalidArgumentException('Invalid IP address format');
            }

            // Validate IP range format
            if ($ipRange !== null && !self::isValidCidr($ipRange)) {
                throw new \InvalidArgumentException('Invalid CIDR range format');
            }

            $sql = <<<EOD

                INSERT INTO `{$this->tableName}`
                (ip_address, ip_range, description, user_id, scope, created_by, is_active)
                VALUES (?, ?, ?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE
                description = VALUES(description),
                is_active = 1,
                updated_at = CURRENT_TIMESTAMP

EOD;

            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([$ipAddress, $ipRange, $description, $userId, $scope, $createdBy]);

            if ($success && isset($GLOBALS['securityAuditLogger'])) {
                $GLOBALS['securityAuditLogger']->logEvent(
                    'ip_whitelist_added',
                    'IP added to whitelist: '.($ipAddress ?: $ipRange)." (scope: {$scope})",
                    'low',
                    $createdBy,
                    [
                        'ip_address' => $ipAddress,
                        'ip_range' => $ipRange,
                        'scope' => $scope,
                        'user_id' => $userId,
                        'description' => $description,
                    ],
                );
            }

            return $success;
        } catch (\Exception $e) {
            error_log('Failed to add IP to whitelist: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Remove an IP address or range from the whitelist.
     *
     * @param int      $id        Whitelist entry ID
     * @param null|int $removedBy User ID who removed this entry
     *
     * @return bool Success status
     */
    public function removeFromWhitelist(int $id, ?int $removedBy = null): bool
    {
        try {
            // Get entry details before deletion for logging
            $stmt = $this->pdo->prepare("SELECT * FROM `{$this->tableName}` WHERE id = ?");
            $stmt->execute([$id]);
            $entry = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$entry) {
                return false;
            }

            $sql = "DELETE FROM `{$this->tableName}` WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([$id]);

            if ($success && isset($GLOBALS['securityAuditLogger'])) {
                $GLOBALS['securityAuditLogger']->logEvent(
                    'ip_whitelist_removed',
                    'IP removed from whitelist: '.($entry['ip_address'] ?: $entry['ip_range'])." (scope: {$entry['scope']})",
                    'low',
                    $removedBy,
                    [
                        'entry_id' => $id,
                        'ip_address' => $entry['ip_address'],
                        'ip_range' => $entry['ip_range'],
                        'scope' => $entry['scope'],
                        'user_id' => $entry['user_id'],
                    ],
                );
            }

            return $success;
        } catch (\Exception $e) {
            error_log('Failed to remove IP from whitelist: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get all whitelist entries.
     *
     * @param null|string $scope      Filter by scope
     * @param null|int    $userId     Filter by user ID
     * @param bool        $activeOnly Include only active entries
     *
     * @return array Whitelist entries
     */
    public function getWhitelist(?string $scope = null, ?int $userId = null, bool $activeOnly = true): array
    {
        try {
            $sql = "SELECT * FROM `{$this->tableName}`";
            $params = [];
            $conditions = [];

            if ($scope !== null) {
                $conditions[] = 'scope = ?';
                $params[] = $scope;
            }

            if ($userId !== null) {
                $conditions[] = 'user_id = ?';
                $params[] = $userId;
            }

            if ($activeOnly) {
                $conditions[] = 'is_active = 1';
            }

            if (!empty($conditions)) {
                $sql .= ' WHERE '.implode(' AND ', $conditions);
            }

            $sql .= ' ORDER BY created_at DESC';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Failed to get whitelist entries: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Validate and enforce IP whitelist for the current request.
     *
     * @param string   $scope      Scope to check
     * @param null|int $userId     User ID for user-specific whitelist
     * @param bool     $exitOnFail Whether to exit with 403 when IP is not whitelisted
     *
     * @return bool True if IP is whitelisted
     */
    public function enforceWhitelist(string $scope = 'general', ?int $userId = null, bool $exitOnFail = true): bool
    {
        $ipAddress = RateLimitHelpers::getClientIpAddress();
        $isWhitelisted = $this->isWhitelisted($ipAddress, $scope, $userId);

        if (!$isWhitelisted) {
            // Log unauthorized access attempt
            if (isset($GLOBALS['securityAuditLogger'])) {
                $GLOBALS['securityAuditLogger']->logEvent(
                    'ip_whitelist_violation',
                    "Unauthorized access attempt from non-whitelisted IP: {$ipAddress} (scope: {$scope})",
                    'high',
                    $userId,
                    [
                        'ip_address' => $ipAddress,
                        'scope' => $scope,
                        'user_id' => $userId,
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                        'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
                    ],
                );
            }

            if ($exitOnFail) {
                self::sendAccessDeniedResponse();

                exit;
            }
        }

        return $isWhitelisted;
    }

    /**
     * Bulk import IP addresses from array.
     *
     * @param array    $ipList    List of IP addresses/ranges with metadata
     * @param string   $scope     Scope for all imported IPs
     * @param null|int $createdBy User ID who created these entries
     *
     * @return int Number of successfully imported entries
     */
    public function bulkImport(array $ipList, string $scope = 'general', ?int $createdBy = null): int
    {
        $imported = 0;

        foreach ($ipList as $entry) {
            $ipAddress = $entry['ip_address'] ?? null;
            $ipRange = $entry['ip_range'] ?? null;
            $description = $entry['description'] ?? '';
            $userId = $entry['user_id'] ?? null;

            if ($this->addToWhitelist($ipAddress, $ipRange, $description, $userId, $scope, $createdBy)) {
                ++$imported;
            }
        }

        return $imported;
    }

    /**
     * Get statistics about the IP whitelist.
     *
     * @return array Statistics data
     */
    public function getStatistics(): array
    {
        try {
            $stats = [];

            // Total entries count
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `{$this->tableName}` WHERE is_active = 1");
            $stats['total_entries'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

            // Entries by scope
            $stmt = $this->pdo->query(<<<EOD

                SELECT scope, COUNT(*) as count
                FROM `{$this->tableName}`
                WHERE is_active = 1
                GROUP BY scope
                ORDER BY count DESC

EOD);
            $stats['by_scope'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Recent entries (last 7 days)
            $stmt = $this->pdo->query(<<<EOD

                SELECT COUNT(*) as count FROM `{$this->tableName}`
                WHERE is_active = 1
                AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)

EOD);
            $stats['recent_entries'] = $stmt->fetch(\PDO::FETCH_ASSOC)['count'];

            return $stats;
        } catch (\Exception $e) {
            error_log('Failed to get IP whitelist statistics: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Initialize the IP whitelist table.
     */
    private function initializeTable(): void
    {
        $this->pdo->exec(<<<EOD

            CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `ip_address` varchar(45) DEFAULT NULL,
                `ip_range` varchar(50) DEFAULT NULL,
                `description` varchar(255) DEFAULT NULL,
                `user_id` int(11) DEFAULT NULL,
                `scope` varchar(50) NOT NULL DEFAULT 'general',
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `created_by` int(11) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_ip_address` (`ip_address`),
                KEY `idx_ip_range` (`ip_range`),
                KEY `idx_user_id` (`user_id`),
                KEY `idx_scope` (`scope`),
                KEY `idx_active` (`is_active`),
                KEY `idx_created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

EOD);
    }

    /**
     * Initialize default IP whitelist entries.
     */
    private function initializeDefaultWhitelist(): void
    {
        foreach ($this->defaultWhitelist as $ipRange) {
            $this->addToWhitelist(null, $ipRange, 'Default localhost/private networks', null, 'system');
        }
    }

    /**
     * Send access denied response.
     */
    private static function sendAccessDeniedResponse(): void
    {
        http_response_code(403);
        header('Content-Type: application/json');

        $response = [
            'error' => 'Access denied',
            'message' => 'Your IP address is not authorized to access this resource',
        ];

        echo json_encode($response);
    }

    /**
     * Check if an IP address is within a CIDR range.
     *
     * @param string $ip    IP address to check
     * @param string $range CIDR range (e.g., 192.168.1.0/24)
     *
     * @return bool True if IP is in range
     */
    private static function ipInRange(string $ip, string $range): bool
    {
        if (!str_contains($range, '/')) {
            // Single IP address comparison
            return $ip === $range;
        }

        [$rangeIp, $netmask] = explode('/', $range, 2);

        // Handle IPv6
        if (str_contains($ip, ':')) {
            return self::ipv6InRange($ip, $rangeIp, (int) $netmask);
        }

        // Handle IPv4
        $rangeDecimal = ip2long($rangeIp);
        $ipDecimal = ip2long($ip);
        $wildcardDecimal = (1 << (32 - $netmask)) - 1;
        $netmaskDecimal = ~$wildcardDecimal;

        return ($ipDecimal & $netmaskDecimal) === ($rangeDecimal & $netmaskDecimal);
    }

    /**
     * Check if an IPv6 address is within a range.
     *
     * @param string $ip           IPv6 address
     * @param string $rangeIp      IPv6 range IP
     * @param int    $prefixLength Prefix length
     *
     * @return bool True if IP is in range
     */
    private static function ipv6InRange(string $ip, string $rangeIp, int $prefixLength): bool
    {
        $ipBin = inet_pton($ip);
        $rangeBin = inet_pton($rangeIp);

        if ($ipBin === false || $rangeBin === false) {
            return false;
        }

        $bytes = (int) ($prefixLength / 8);
        $bits = $prefixLength % 8;

        for ($i = 0; $i < $bytes; ++$i) {
            if ($ipBin[$i] !== $rangeBin[$i]) {
                return false;
            }
        }

        if ($bits > 0) {
            $mask = 0xFF << (8 - $bits);

            return (\ord($ipBin[$bytes]) & $mask) === (\ord($rangeBin[$bytes]) & $mask);
        }

        return true;
    }

    /**
     * Validate CIDR notation format.
     *
     * @param string $cidr CIDR range to validate
     *
     * @return bool True if valid CIDR format
     */
    private static function isValidCidr(string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            // Single IP address
            return filter_var($cidr, \FILTER_VALIDATE_IP) !== false;
        }

        [$ip, $netmask] = explode('/', $cidr, 2);

        if (!filter_var($ip, \FILTER_VALIDATE_IP)) {
            return false;
        }

        if (!is_numeric($netmask)) {
            return false;
        }

        $netmask = (int) $netmask;

        // Check netmask range based on IP version
        if (str_contains($ip, ':')) {
            // IPv6
            return $netmask >= 0 && $netmask <= 128;
        }

        // IPv4
        return $netmask >= 0 && $netmask <= 32;
    }
}
