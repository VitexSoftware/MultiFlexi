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
 * API rate limiting middleware for preventing abuse and DoS attacks.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class ApiRateLimiter
{
    private \PDO $pdo;
    private string $tableName;
    private int $defaultLimit;
    private int $defaultWindow;
    private array $endpointLimits;

    public function __construct(
        \PDO $pdo,
        int $defaultLimit = 100,
        int $defaultWindow = 3600, // 1 hour
        array $endpointLimits = [],
        string $tableName = 'api_rate_limits',
    ) {
        $this->pdo = $pdo;
        $this->defaultLimit = $defaultLimit;
        $this->defaultWindow = $defaultWindow;
        $this->endpointLimits = $endpointLimits;
        $this->tableName = $tableName;
    }

    /**
     * Check if a request is allowed based on rate limits.
     *
     * @param string $identifier IP address or API key
     * @param string $endpoint   Endpoint being accessed
     *
     * @return array Rate limit status
     */
    public function checkRateLimit(string $identifier, string $endpoint = ''): array
    {
        $limit = $this->getEndpointLimit($endpoint);
        $window = $this->getEndpointWindow($endpoint);

        $now = time();
        $windowStart = $now - $window;

        // Get or create rate limit record
        $record = $this->getRateLimitRecord($identifier, $endpoint);

        if (!$record) {
            // Create new record
            $this->createRateLimitRecord($identifier, $endpoint, 1, $now, $now + $window);

            return [
                'allowed' => true,
                'limit' => $limit,
                'remaining' => $limit - 1,
                'reset_time' => $now + $window,
                'retry_after' => null,
            ];
        }

        // Check if window has expired
        if ($record['window_end'] <= $now) {
            // Reset the window
            $this->updateRateLimitRecord(
                $record['id'],
                1,
                $now,
                $now + $window,
                null,
            );

            return [
                'allowed' => true,
                'limit' => $limit,
                'remaining' => $limit - 1,
                'reset_time' => $now + $window,
                'retry_after' => null,
            ];
        }

        // Check if currently blocked
        if ($record['blocked_until'] && $record['blocked_until'] > $now) {
            return [
                'allowed' => false,
                'limit' => $limit,
                'remaining' => 0,
                'reset_time' => $record['window_end'],
                'retry_after' => $record['blocked_until'] - $now,
                'blocked' => true,
            ];
        }

        // Check if limit exceeded
        if ($record['requests'] >= $limit) {
            // Block for remainder of window
            $blockedUntil = $record['window_end'];
            $this->updateRateLimitRecord(
                $record['id'],
                $record['requests'],
                $record['window_start'],
                $record['window_end'],
                $blockedUntil,
            );

            // Log rate limit exceeded
            if (isset($GLOBALS['securityAuditLogger'])) {
                $GLOBALS['securityAuditLogger']->logEvent(
                    'api_rate_limit_exceeded',
                    "API rate limit exceeded for endpoint: {$endpoint}",
                    'high',
                    null,
                    [
                        'identifier' => $identifier,
                        'endpoint' => $endpoint,
                        'requests' => $record['requests'],
                        'limit' => $limit,
                    ],
                );
            }

            return [
                'allowed' => false,
                'limit' => $limit,
                'remaining' => 0,
                'reset_time' => $record['window_end'],
                'retry_after' => $blockedUntil - $now,
                'blocked' => true,
            ];
        }

        // Increment request count
        $newRequests = $record['requests'] + 1;
        $this->updateRateLimitRecord(
            $record['id'],
            $newRequests,
            $record['window_start'],
            $record['window_end'],
            null,
        );

        return [
            'allowed' => true,
            'limit' => $limit,
            'remaining' => $limit - $newRequests,
            'reset_time' => $record['window_end'],
            'retry_after' => null,
        ];
    }

    /**
     * Middleware function for automatic rate limit checking.
     */
    public function middleware(string $identifier = '', string $endpoint = ''): bool
    {
        if (empty($identifier)) {
            $identifier = self::getRequestIdentifier();
        }

        if (empty($endpoint)) {
            $endpoint = self::getRequestEndpoint();
        }

        $result = $this->checkRateLimit($identifier, $endpoint);

        // Set rate limit headers
        self::setRateLimitHeaders($result);

        if (!$result['allowed']) {
            self::handleRateLimitExceeded($result);

            return false;
        }

        return true;
    }

    /**
     * Get rate limit statistics.
     */
    public function getStatistics(int $hours = 24): array
    {
        $stats = [];

        // Top consuming identifiers
        $sql = <<<EOD
SELECT identifier, SUM(requests) as total_requests
                FROM `{$this->tableName}`
                WHERE window_end > DATE_SUB(NOW(), INTERVAL ? HOUR)
                GROUP BY identifier
                ORDER BY total_requests DESC
                LIMIT 10
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$hours]);
        $stats['top_consumers'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Currently blocked identifiers
        $sql = <<<EOD
SELECT identifier, endpoint, blocked_until
                FROM `{$this->tableName}`
                WHERE blocked_until > NOW()
                ORDER BY blocked_until DESC
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['blocked_identifiers'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Endpoint usage
        $sql = <<<EOD
SELECT endpoint, SUM(requests) as total_requests, COUNT(DISTINCT identifier) as unique_clients
                FROM `{$this->tableName}`
                WHERE window_end > DATE_SUB(NOW(), INTERVAL ? HOUR)
                GROUP BY endpoint
                ORDER BY total_requests DESC
                LIMIT 20
EOD;

        $stmt->execute([$hours]);
        $stats['endpoint_usage'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $stats;
    }

    /**
     * Manually block an identifier.
     */
    public function blockIdentifier(string $identifier, int $duration = 3600): void
    {
        $blockedUntil = time() + $duration;

        $sql = <<<EOD
INSERT INTO `{$this->tableName}`
                (identifier, endpoint, requests, window_start, window_end, blocked_until)
                VALUES (?, '', 0, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND), FROM_UNIXTIME(?))
                ON DUPLICATE KEY UPDATE blocked_until = VALUES(blocked_until)
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$identifier, $duration, $blockedUntil]);

        // Log manual block
        if (isset($GLOBALS['securityAuditLogger'])) {
            $GLOBALS['securityAuditLogger']->logEvent(
                'api_identifier_blocked',
                "API identifier manually blocked: {$identifier}",
                'high',
                null,
                [
                    'identifier' => $identifier,
                    'duration' => $duration,
                    'blocked_until' => $blockedUntil,
                ],
            );
        }
    }

    /**
     * Unblock an identifier.
     */
    public function unblockIdentifier(string $identifier): void
    {
        $sql = <<<EOD
UPDATE `{$this->tableName}`
                SET blocked_until = NULL
                WHERE identifier = ?
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$identifier]);

        // Log manual unblock
        if (isset($GLOBALS['securityAuditLogger'])) {
            $GLOBALS['securityAuditLogger']->logEvent(
                'api_identifier_unblocked',
                "API identifier manually unblocked: {$identifier}",
                'medium',
                null,
                ['identifier' => $identifier],
            );
        }
    }

    /**
     * Clean up old rate limit data.
     */
    public function cleanup(int $hoursToKeep = 24): int
    {
        $sql = <<<EOD
DELETE FROM `{$this->tableName}`
                WHERE window_end < DATE_SUB(NOW(), INTERVAL ? HOUR)
                  AND (blocked_until IS NULL OR blocked_until < NOW())
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$hoursToKeep]);

        return $stmt->rowCount();
    }

    /**
     * Check if identifier is currently blocked.
     */
    public function isBlocked(string $identifier): bool
    {
        $sql = <<<EOD
SELECT COUNT(*) FROM `{$this->tableName}`
                WHERE identifier = ? AND blocked_until > NOW()
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$identifier]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get remaining requests for identifier.
     */
    public function getRemainingRequests(string $identifier, string $endpoint = ''): array
    {
        $result = $this->checkRateLimit($identifier, $endpoint);

        return [
            'remaining' => $result['remaining'],
            'limit' => $result['limit'],
            'reset_time' => $result['reset_time'],
        ];
    }

    /**
     * Handle rate limit exceeded.
     */
    private static function handleRateLimitExceeded(array $result): void
    {
        http_response_code(429); // Too Many Requests

        header('Retry-After: '.$result['retry_after']);

        if (self::isJsonRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Rate limit exceeded',
                'message' => 'Too many requests. Please try again later.',
                'limit' => $result['limit'],
                'remaining' => $result['remaining'],
                'reset_time' => $result['reset_time'],
                'retry_after' => $result['retry_after'],
            ]);
        } else {
            echo '<h1>429 - Too Many Requests</h1>';
            echo '<p>Rate limit exceeded. Please try again later.</p>';
            echo '<p>Retry after: '.$result['retry_after'].' seconds</p>';
        }

        exit;
    }

    /**
     * Set rate limit headers.
     */
    private static function setRateLimitHeaders(array $result): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-RateLimit-Limit: '.$result['limit']);
        header('X-RateLimit-Remaining: '.$result['remaining']);
        header('X-RateLimit-Reset: '.$result['reset_time']);

        if (isset($result['retry_after'])) {
            header('X-RateLimit-Retry-After: '.$result['retry_after']);
        }
    }

    /**
     * Get rate limit record from database.
     */
    private function getRateLimitRecord(string $identifier, string $endpoint): ?array
    {
        $sql = "SELECT * FROM `{$this->tableName}` WHERE identifier = ? AND endpoint = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$identifier, $endpoint]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    /**
     * Create new rate limit record.
     */
    private function createRateLimitRecord(
        string $identifier,
        string $endpoint,
        int $requests,
        int $windowStart,
        int $windowEnd,
    ): void {
        $sql = <<<EOD
INSERT INTO `{$this->tableName}`
                (identifier, endpoint, requests, window_start, window_end)
                VALUES (?, ?, ?, FROM_UNIXTIME(?), FROM_UNIXTIME(?))
                ON DUPLICATE KEY UPDATE
                requests = VALUES(requests),
                window_start = VALUES(window_start),
                window_end = VALUES(window_end),
                blocked_until = NULL
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$identifier, $endpoint, $requests, $windowStart, $windowEnd]);
    }

    /**
     * Update existing rate limit record.
     */
    private function updateRateLimitRecord(
        int $id,
        int $requests,
        string $windowStart,
        string $windowEnd,
        ?int $blockedUntil,
    ): void {
        $sql = <<<EOD
UPDATE `{$this->tableName}`
                SET requests = ?, blocked_until = ?
                WHERE id = ?
EOD;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $requests,
            $blockedUntil ? date('Y-m-d H:i:s', $blockedUntil) : null,
            $id,
        ]);
    }

    /**
     * Get request identifier (IP address or API key).
     */
    private static function getRequestIdentifier(): string
    {
        // Check for API key first
        $apiKey = self::getApiKey();

        if ($apiKey) {
            return 'api:'.$apiKey;
        }

        // Fall back to IP address
        return 'ip:'.self::getClientIpAddress();
    }

    /**
     * Get API key from request.
     */
    private static function getApiKey(): ?string
    {
        // Check Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
            return $matches[1];
        }

        // Check API key header
        if (!empty($_SERVER['HTTP_X_API_KEY'])) {
            return $_SERVER['HTTP_X_API_KEY'];
        }

        // Check query parameter
        if (!empty($_GET['api_key'])) {
            return $_GET['api_key'];
        }

        return null;
    }

    /**
     * Get client IP address.
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

    /**
     * Get current request endpoint.
     */
    private static function getRequestEndpoint(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $pathInfo = $_SERVER['PATH_INFO'] ?? '';

        return $script.$pathInfo;
    }

    /**
     * Check if request expects JSON response.
     */
    private static function isJsonRequest(): bool
    {
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        return str_contains($acceptHeader, 'application/json')
               || str_contains($contentType, 'application/json')
               || str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/');
    }

    /**
     * Get rate limit for specific endpoint.
     */
    private function getEndpointLimit(string $endpoint): int
    {
        foreach ($this->endpointLimits as $pattern => $config) {
            if (fnmatch($pattern, $endpoint)) {
                return $config['limit'] ?? $this->defaultLimit;
            }
        }

        return $this->defaultLimit;
    }

    /**
     * Get time window for specific endpoint.
     */
    private function getEndpointWindow(string $endpoint): int
    {
        foreach ($this->endpointLimits as $pattern => $config) {
            if (fnmatch($pattern, $endpoint)) {
                return $config['window'] ?? $this->defaultWindow;
            }
        }

        return $this->defaultWindow;
    }
}
