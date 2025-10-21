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
 * Enhanced session security manager with timeout, regeneration, and hijacking protection.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class SessionManager
{
    private int $sessionTimeout;
    private int $regenerationInterval;
    private bool $strictUserAgent;
    private bool $strictIpAddress;
    private string $cookieName;

    public function __construct(
        int $sessionTimeout = 3600,
        int $regenerationInterval = 300,
        bool $strictUserAgent = true,
        bool $strictIpAddress = false,
        string $cookieName = 'PHPSESSID',
    ) {
        $this->sessionTimeout = $sessionTimeout;
        $this->regenerationInterval = $regenerationInterval;
        $this->strictUserAgent = $strictUserAgent;
        $this->strictIpAddress = $strictIpAddress;
        $this->cookieName = $cookieName;
    }

    /**
     * Start secure session with enhanced security settings.
     */
    public function startSecureSession(): bool
    {
        // Set secure session parameters before starting
        $this->setSecureSessionParams();

        if (session_status() === \PHP_SESSION_ACTIVE) {
            return true;
        }

        if (!session_start()) {
            return false;
        }

        // Initialize session security
        if (!$this->initializeSessionSecurity()) {
            $this->destroySession();

            return false;
        }

        // Check for session hijacking
        if (!$this->validateSessionSecurity()) {
            $this->destroySession();

            return false;
        }

        // Handle session timeout
        if ($this->isSessionExpired()) {
            $this->destroySession();

            return false;
        }

        // Regenerate session ID periodically
        if ($this->shouldRegenerateSession()) {
            self::regenerateSession();
        }

        // Update last activity timestamp
        self::updateLastActivity();

        return true;
    }

    /**
     * Destroy session securely.
     */
    public function destroySession(): bool
    {
        if (session_status() === \PHP_SESSION_ACTIVE) {
            // Clear session data
            $_SESSION = [];

            // Delete session cookie
            if (\ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly'],
                );
            }

            // Destroy session
            return session_destroy();
        }

        return true;
    }

    /**
     * Generate CSRF token.
     */
    public function generateCsrfToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Get current CSRF token.
     */
    public function getCsrfToken(): string
    {
        if (!isset($_SESSION['security']['csrf_token'])) {
            $_SESSION['security']['csrf_token'] = $this->generateCsrfToken();
        }

        return $_SESSION['security']['csrf_token'];
    }

    /**
     * Validate CSRF token.
     */
    public function validateCsrfToken(string $token): bool
    {
        $sessionToken = $this->getCsrfToken();

        return hash_equals($sessionToken, $token);
    }

    /**
     * Regenerate CSRF token.
     */
    public function regenerateCsrfToken(): string
    {
        $_SESSION['security']['csrf_token'] = $this->generateCsrfToken();

        return $_SESSION['security']['csrf_token'];
    }

    /**
     * Get session information.
     */
    public function getSessionInfo(): array
    {
        if (!isset($_SESSION['security'])) {
            return [];
        }

        $security = $_SESSION['security'];
        $now = time();

        return [
            'session_id' => session_id(),
            'created' => $security['created'],
            'last_activity' => $security['last_activity'],
            'last_regeneration' => $security['last_regeneration'],
            'age' => $now - $security['created'],
            'idle_time' => $now - $security['last_activity'],
            'time_to_timeout' => $this->sessionTimeout - ($now - $security['last_activity']),
            'user_agent' => $security['user_agent'],
            'ip_address' => $security['ip_address'],
        ];
    }

    /**
     * Set security headers.
     */
    public function setSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        header('Content-Security-Policy: frame-ancestors \'none\'');

        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');

        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');

        // Strict transport security (if HTTPS)
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Permissions policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }

    /**
     * Set secure session parameters.
     */
    private function setSecureSessionParams(): void
    {
        // Set session cookie parameters
        session_set_cookie_params([
            'lifetime' => 0, // Session cookie (expires when browser closes)
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => self::isHttps(), // Only send over HTTPS
            'httponly' => true, // Prevent JavaScript access
            'samesite' => 'Strict', // CSRF protection
        ]);

        // Set session name
        session_name($this->cookieName);

        // Configure session settings
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', self::isHttps() ? '1' : '0');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.entropy_length', '32');
        ini_set('session.hash_function', 'sha256');
        ini_set('session.hash_bits_per_character', '6');
    }

    /**
     * Initialize session security data.
     */
    private function initializeSessionSecurity(): bool
    {
        if (!isset($_SESSION['security'])) {
            $_SESSION['security'] = [
                'created' => time(),
                'last_activity' => time(),
                'last_regeneration' => time(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip_address' => self::getClientIpAddress(),
                'csrf_token' => $this->generateCsrfToken(),
            ];
        }

        return true;
    }

    /**
     * Validate session security against hijacking attempts.
     */
    private function validateSessionSecurity(): bool
    {
        if (!isset($_SESSION['security'])) {
            return false;
        }

        $security = $_SESSION['security'];

        // Validate User-Agent consistency
        if ($this->strictUserAgent) {
            $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            if ($security['user_agent'] !== $currentUserAgent) {
                self::logSecurityEvent('Session hijacking detected: User-Agent mismatch', [
                    'expected' => $security['user_agent'],
                    'actual' => $currentUserAgent,
                ]);

                return false;
            }
        }

        // Validate IP address consistency
        if ($this->strictIpAddress) {
            $currentIp = self::getClientIpAddress();

            if ($security['ip_address'] !== $currentIp) {
                self::logSecurityEvent('Session hijacking detected: IP address mismatch', [
                    'expected' => $security['ip_address'],
                    'actual' => $currentIp,
                ]);

                return false;
            }
        }

        return true;
    }

    /**
     * Check if session has expired.
     */
    private function isSessionExpired(): bool
    {
        if (!isset($_SESSION['security']['last_activity'])) {
            return true;
        }

        $lastActivity = $_SESSION['security']['last_activity'];
        $timeSinceLastActivity = time() - $lastActivity;

        if ($timeSinceLastActivity > $this->sessionTimeout) {
            self::logSecurityEvent('Session expired due to inactivity', [
                'last_activity' => $lastActivity,
                'timeout' => $this->sessionTimeout,
                'inactive_time' => $timeSinceLastActivity,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Check if session ID should be regenerated.
     */
    private function shouldRegenerateSession(): bool
    {
        if (!isset($_SESSION['security']['last_regeneration'])) {
            return true;
        }

        $timeSinceRegeneration = time() - $_SESSION['security']['last_regeneration'];

        return $timeSinceRegeneration > $this->regenerationInterval;
    }

    /**
     * Regenerate session ID safely.
     */
    private static function regenerateSession(): bool
    {
        if (!session_regenerate_id(true)) {
            return false;
        }

        $_SESSION['security']['last_regeneration'] = time();
        self::logSecurityEvent('Session ID regenerated', [
            'session_id' => session_id(),
        ]);

        return true;
    }

    /**
     * Update last activity timestamp.
     */
    private static function updateLastActivity(): void
    {
        $_SESSION['security']['last_activity'] = time();
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

    /**
     * Check if HTTPS is being used.
     */
    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
               || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
               || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === 443);
    }

    /**
     * Log security events.
     */
    private static function logSecurityEvent(string $message, array $context = []): void
    {
        if (class_exists('\\MultiFlexi\\LogToSQL')) {
            $logger = \MultiFlexi\LogToSQL::singleton();
            $logger->addStatusMessage($message, 'security');
        } else {
            error_log("MultiFlexi Security: {$message} - ".json_encode($context));
        }
    }
}
