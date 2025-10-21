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
 * Rate limiting helper functions for application usage.
 */
class RateLimitHelpers
{
    /**
     * Check rate limit for the current request.
     *
     * @param string      $endpointType Type of endpoint (login, api, admin, etc.)
     * @param null|string $endpointPath Specific endpoint path
     *
     * @return array Rate limit check result
     */
    public static function checkCurrentRequest(string $endpointType, ?string $endpointPath = null): array
    {
        if (!self::isRateLimitingAvailable()) {
            return ['allowed' => true, 'message' => 'Rate limiting not available'];
        }

        $ipAddress = self::getClientIpAddress();
        $userId = self::getCurrentUserId();

        return $GLOBALS['rateLimiter']->checkRateLimit($ipAddress, $endpointType, $endpointPath, $userId);
    }

    /**
     * Apply rate limiting to the current request and handle the response.
     *
     * @param string      $endpointType Type of endpoint
     * @param null|string $endpointPath Specific endpoint path
     * @param bool        $exitOnLimit  Whether to exit with HTTP 429 when rate limit is exceeded
     */
    public static function applyRateLimit(string $endpointType, ?string $endpointPath = null, bool $exitOnLimit = true): void
    {
        $result = self::checkCurrentRequest($endpointType, $endpointPath);

        if (!$result['allowed']) {
            if ($exitOnLimit) {
                self::sendRateLimitResponse($result);

                exit;
            }
        }

        // Add rate limit headers to response
        if (isset($result['max_requests'], $result['attempts'])) {
            header('X-RateLimit-Limit: '.$result['max_requests']);
            header('X-RateLimit-Remaining: '.max(0, $result['max_requests'] - $result['attempts']));
        }

        if (isset($result['rule']['time_window'])) {
            header('X-RateLimit-Window: '.$result['rule']['time_window']);
        }
    }

    /**
     * Send rate limit exceeded response.
     *
     * @param array $rateLimitResult Rate limit check result
     */
    public static function sendRateLimitResponse(array $rateLimitResult): void
    {
        http_response_code(429);
        header('Content-Type: application/json');

        if (isset($rateLimitResult['blocked_until'])) {
            header('Retry-After: '.max(1, strtotime($rateLimitResult['blocked_until']) - time()));
        }

        $response = [
            'error' => 'Rate limit exceeded',
            'message' => $rateLimitResult['message'] ?? 'Too many requests',
        ];

        if (isset($rateLimitResult['blocked_until'])) {
            $response['retry_after'] = $rateLimitResult['blocked_until'];
        }

        if (isset($rateLimitResult['rule'])) {
            $response['limit_info'] = [
                'max_requests' => $rateLimitResult['rule']['max_requests'],
                'time_window' => $rateLimitResult['rule']['time_window'],
            ];
        }

        echo json_encode($response);
    }

    /**
     * Create middleware for specific endpoint types.
     *
     * @param string $endpointType Endpoint type to protect
     *
     * @return callable Middleware function
     */
    public static function createMiddleware(string $endpointType): callable
    {
        return static function () use ($endpointType): void {
            $endpointPath = $_SERVER['REQUEST_URI'] ?? null;
            self::applyRateLimit($endpointType, $endpointPath, true);
        };
    }

    /**
     * Check if rate limiting is available and enabled.
     *
     * @return bool True if rate limiting is available
     */
    public static function isRateLimitingAvailable(): bool
    {
        return isset($GLOBALS['rateLimiter'])
            && \Ease\Shared::cfg('RATE_LIMITING_ENABLED', true);
    }

    /**
     * Get the client IP address, handling proxy headers.
     *
     * @return string Client IP address
     */
    public static function getClientIpAddress(): string
    {
        // Check for various IP headers (for proxy/load balancer setups)
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($ipHeaders as $header) {
            if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);

                // Validate IP address
                if (filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Get the current user ID if authenticated.
     *
     * @return null|int User ID or null if not authenticated
     */
    public static function getCurrentUserId(): ?int
    {
        // Try various methods to get user ID
        if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }

        if (isset($_SESSION['USER_ID']) && is_numeric($_SESSION['USER_ID'])) {
            return (int) $_SESSION['USER_ID'];
        }

        // Check if using Ease framework user system
        if (class_exists('\\Ease\\User') && method_exists('\\Ease\\User', 'singleton')) {
            $user = \Ease\User::singleton();

            if (method_exists($user, 'getUserID') && $user->getUserID()) {
                return (int) $user->getUserID();
            }
        }

        return null;
    }

    /**
     * Manually block an IP address.
     *
     * @param string $ipAddress       IP address to block
     * @param string $endpointType    Endpoint type to block for
     * @param int    $durationSeconds Block duration in seconds
     *
     * @return bool Success status
     */
    public static function blockIpAddress(string $ipAddress, string $endpointType = 'all', int $durationSeconds = 3600): bool
    {
        if (!self::isRateLimitingAvailable()) {
            return false;
        }

        try {
            $blockedUntil = new \DateTime();
            $blockedUntil->modify("+{$durationSeconds} seconds");

            // Insert or update attempt record with block
            $pdo = \Ease\Shared::singleton()->pdo();
            $sql = <<<'EOD'

                INSERT INTO rate_limiting_attempts
                (ip_address, endpoint_type, attempts, blocked_until, first_attempt_at, last_attempt_at)
                VALUES (?, ?, 999, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE
                blocked_until = VALUES(blocked_until),
                last_attempt_at = CURRENT_TIMESTAMP

EOD;

            $stmt = $pdo->prepare($sql);

            return $stmt->execute([$ipAddress, $endpointType, $blockedUntil->format('Y-m-d H:i:s')]);
        } catch (\Exception $e) {
            error_log('Failed to block IP address: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Unblock an IP address.
     *
     * @param string      $ipAddress    IP address to unblock
     * @param null|string $endpointType Specific endpoint type to unblock
     *
     * @return bool Success status
     */
    public static function unblockIpAddress(string $ipAddress, ?string $endpointType = null): bool
    {
        if (!self::isRateLimitingAvailable()) {
            return false;
        }

        return $GLOBALS['rateLimiter']->unblockClient($ipAddress, $endpointType);
    }

    /**
     * Get rate limiting statistics.
     *
     * @return array Statistics data
     */
    public static function getStatistics(): array
    {
        if (!self::isRateLimitingAvailable()) {
            return [];
        }

        return $GLOBALS['rateLimiter']->getStatistics();
    }

    /**
     * Get list of currently blocked clients.
     *
     * @return array List of blocked clients
     */
    public static function getBlockedClients(): array
    {
        if (!self::isRateLimitingAvailable()) {
            return [];
        }

        return $GLOBALS['rateLimiter']->getBlockedClients();
    }

    /**
     * Add custom rate limiting rule.
     *
     * @param string      $endpointType Endpoint type
     * @param int         $maxRequests  Maximum requests allowed
     * @param int         $timeWindow   Time window in seconds
     * @param null|string $endpointPath Specific endpoint path
     *
     * @return bool Success status
     */
    public static function addRule(string $endpointType, int $maxRequests, int $timeWindow, ?string $endpointPath = null): bool
    {
        if (!self::isRateLimitingAvailable()) {
            return false;
        }

        return $GLOBALS['rateLimiter']->addRule($endpointType, $endpointPath, $maxRequests, $timeWindow);
    }

    /**
     * Clean up expired rate limiting data.
     */
    public static function cleanup(): void
    {
        if (!self::isRateLimitingAvailable()) {
            return;
        }

        $GLOBALS['rateLimiter']->cleanup();
    }

    /**
     * Check if an IP address is currently blocked.
     *
     * @param string $ipAddress    IP address to check
     * @param string $endpointType Endpoint type to check
     *
     * @return bool True if blocked
     */
    public static function isBlocked(string $ipAddress, string $endpointType): bool
    {
        if (!self::isRateLimitingAvailable()) {
            return false;
        }

        $result = $GLOBALS['rateLimiter']->checkRateLimit($ipAddress, $endpointType);

        return !$result['allowed'] && isset($result['blocked_until']);
    }
}
