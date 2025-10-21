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
 * Security configuration manager with secure defaults.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class SecurityConfig
{
    /**
     * Default security configuration values.
     */
    public const DEFAULT_CONFIG = [
        // Password Policy
        'PASSWORD_MIN_LENGTH' => 8,
        'PASSWORD_REQUIRE_UPPERCASE' => true,
        'PASSWORD_REQUIRE_LOWERCASE' => true,
        'PASSWORD_REQUIRE_NUMBERS' => true,
        'PASSWORD_REQUIRE_SPECIAL_CHARS' => true,
        'PASSWORD_STRENGTH_INDICATOR' => true,
        // Session Security
        'SESSION_TIMEOUT' => 3600, // 1 hour
        'SESSION_REGENERATION_INTERVAL' => 300, // 5 minutes
        'SESSION_STRICT_USER_AGENT' => true,
        'SESSION_STRICT_IP_ADDRESS' => false, // Can break with load balancers
        'SESSION_COOKIE_SECURE' => 'auto', // auto-detect HTTPS
        'SESSION_COOKIE_HTTPONLY' => true,
        'SESSION_COOKIE_SAMESITE' => 'Strict',
        // CSRF Protection
        'CSRF_PROTECTION_ENABLED' => true,
        'CSRF_TOKEN_REGENERATION' => 'auto', // auto, manual, never
        // Brute Force Protection
        'BRUTE_FORCE_PROTECTION_ENABLED' => true,
        'BRUTE_FORCE_MAX_ATTEMPTS' => 5,
        'BRUTE_FORCE_LOCKOUT_DURATION' => 900, // 15 minutes
        'BRUTE_FORCE_TIME_WINDOW' => 300, // 5 minutes
        'BRUTE_FORCE_IP_LIMITING' => true,
        'BRUTE_FORCE_PROGRESSIVE_DELAY' => true,
        // Two-Factor Authentication
        'TWO_FACTOR_AUTH_ENABLED' => true,
        'TWO_FACTOR_AUTH_REQUIRED_FOR_ADMIN' => false,
        'TWO_FACTOR_AUTH_RECOVERY_CODES' => 10,
        'TWO_FACTOR_AUTH_ISSUER' => 'MultiFlexi',
        // Account Security
        'ACCOUNT_LOCKOUT_ENABLED' => true,
        'ACCOUNT_LOCKOUT_THRESHOLD' => 5,
        'ACCOUNT_LOCKOUT_DURATION' => 1800, // 30 minutes
        'PASSWORD_EXPIRATION_ENABLED' => false,
        'PASSWORD_EXPIRATION_DAYS' => 90,
        // IP Whitelisting
        'IP_WHITELISTING_ENABLED' => false,
        'IP_WHITELISTING_ADMIN_ONLY' => true,
        'IP_WHITELISTING_STRICT_MODE' => false,
        // API Security
        'API_RATE_LIMITING_ENABLED' => true,
        'API_RATE_LIMIT_REQUESTS' => 100,
        'API_RATE_LIMIT_WINDOW' => 3600, // 1 hour
        'API_REQUIRE_AUTHENTICATION' => true,
        // Data Encryption
        'DATA_ENCRYPTION_ENABLED' => true,
        'DATA_ENCRYPTION_ALGORITHM' => 'AES-256-GCM',
        'CREDENTIALS_ENCRYPTION_ENABLED' => true,
        // Security Headers
        'SECURITY_HEADERS_ENABLED' => true,
        'SECURITY_HEADER_HSTS_ENABLED' => true,
        'SECURITY_HEADER_CSP_ENABLED' => true,
        'SECURITY_HEADER_X_FRAME_OPTIONS' => 'DENY',
        // Logging and Monitoring
        'SECURITY_LOGGING_ENABLED' => true,
        'SECURITY_AUDIT_TRAIL_ENABLED' => true,
        'FAILED_LOGIN_LOGGING' => true,
        'PRIVILEGE_ESCALATION_LOGGING' => true,
        // Maintenance and Cleanup
        'SECURITY_CLEANUP_ENABLED' => true,
        'SECURITY_CLEANUP_DAYS' => 90,
        'SECURITY_CLEANUP_SCHEDULE' => 'daily',
    ];
    private array $config;
    private array $environmentOverrides;

    public function __construct(array $config = [])
    {
        $this->config = array_merge(self::DEFAULT_CONFIG, $config);
        $this->environmentOverrides = self::loadEnvironmentOverrides();
        $this->applyEnvironmentOverrides();
    }

    /**
     * Get configuration value with environment variable override support.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        // Check environment override first
        if (isset($this->environmentOverrides[$key])) {
            return $this->environmentOverrides[$key];
        }

        // Check loaded config
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * Set configuration value.
     *
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $this->config[$key] = $value;
    }

    /**
     * Get all configuration values.
     */
    public function getAll(): array
    {
        return array_merge($this->config, $this->environmentOverrides);
    }

    /**
     * Validate security configuration.
     *
     * @return array Validation results
     */
    public function validate(): array
    {
        $errors = [];
        $warnings = [];

        // Password policy validation
        if ($this->get('PASSWORD_MIN_LENGTH') < 8) {
            $warnings[] = 'Password minimum length is less than 8 characters';
        }

        // Session timeout validation
        if ($this->get('SESSION_TIMEOUT') > 86400) { // 24 hours
            $warnings[] = 'Session timeout is longer than 24 hours, consider reducing for better security';
        }

        // Brute force protection validation
        if ($this->get('BRUTE_FORCE_MAX_ATTEMPTS') > 10) {
            $warnings[] = 'Brute force max attempts is high, consider reducing to improve security';
        }

        // HTTPS validation
        if ($this->get('SESSION_COOKIE_SECURE') === true && !self::isHttps()) {
            $errors[] = 'Secure cookies are enabled but HTTPS is not detected';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Get security recommendations based on current configuration.
     */
    public function getSecurityRecommendations(): array
    {
        $recommendations = [];

        if (!$this->get('TWO_FACTOR_AUTH_ENABLED')) {
            $recommendations[] = [
                'level' => 'high',
                'message' => 'Enable two-factor authentication for enhanced security',
            ];
        }

        if (!$this->get('BRUTE_FORCE_PROTECTION_ENABLED')) {
            $recommendations[] = [
                'level' => 'high',
                'message' => 'Enable brute force protection to prevent unauthorized access attempts',
            ];
        }

        if ($this->get('SESSION_TIMEOUT') > 7200) { // 2 hours
            $recommendations[] = [
                'level' => 'medium',
                'message' => 'Consider reducing session timeout for improved security',
            ];
        }

        if (!$this->get('CSRF_PROTECTION_ENABLED')) {
            $recommendations[] = [
                'level' => 'high',
                'message' => 'Enable CSRF protection to prevent cross-site request forgery attacks',
            ];
        }

        if (!$this->get('DATA_ENCRYPTION_ENABLED')) {
            $recommendations[] = [
                'level' => 'high',
                'message' => 'Enable data encryption for sensitive information',
            ];
        }

        if (!self::isHttps()) {
            $recommendations[] = [
                'level' => 'critical',
                'message' => 'Use HTTPS to encrypt data in transit',
            ];
        }

        return $recommendations;
    }

    /**
     * Export configuration for environment file.
     */
    public function exportToEnv(): string
    {
        $envContent = "# MultiFlexi Security Configuration\n";
        $envContent .= '# Generated on '.date('Y-m-d H:i:s')."\n\n";

        $categories = [
            'Password Policy' => ['PASSWORD_'],
            'Session Security' => ['SESSION_'],
            'CSRF Protection' => ['CSRF_'],
            'Brute Force Protection' => ['BRUTE_FORCE_'],
            'Two-Factor Authentication' => ['TWO_FACTOR_'],
            'Account Security' => ['ACCOUNT_'],
            'IP Whitelisting' => ['IP_WHITELISTING_'],
            'API Security' => ['API_'],
            'Data Encryption' => ['DATA_ENCRYPTION_', 'CREDENTIALS_'],
            'Security Headers' => ['SECURITY_HEADER_'],
            'Logging and Monitoring' => ['SECURITY_LOGGING_', 'SECURITY_AUDIT_', 'FAILED_LOGIN_', 'PRIVILEGE_'],
            'Maintenance' => ['SECURITY_CLEANUP_'],
        ];

        foreach ($categories as $category => $prefixes) {
            $envContent .= "# {$category}\n";

            foreach ($this->config as $key => $value) {
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($key, $prefix)) {
                        $envValue = \is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
                        $envContent .= "{$key}={$envValue}\n";

                        break;
                    }
                }
            }

            $envContent .= "\n";
        }

        return $envContent;
    }

    /**
     * Load environment variable overrides.
     */
    private static function loadEnvironmentOverrides(): array
    {
        $overrides = [];

        foreach (self::DEFAULT_CONFIG as $key => $defaultValue) {
            $envValue = getenv($key);

            if ($envValue !== false) {
                $overrides[$key] = self::castEnvironmentValue($envValue, $defaultValue);
            }
        }

        return $overrides;
    }

    /**
     * Cast environment variable value to appropriate type.
     *
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    private static function castEnvironmentValue(string $value, $defaultValue)
    {
        if (\is_bool($defaultValue)) {
            return filter_var($value, \FILTER_VALIDATE_BOOLEAN);
        }

        if (\is_int($defaultValue)) {
            return (int) $value;
        }

        if (\is_float($defaultValue)) {
            return (float) $value;
        }

        return $value;
    }

    /**
     * Apply environment overrides to config.
     */
    private function applyEnvironmentOverrides(): void
    {
        $this->config = array_merge($this->config, $this->environmentOverrides);
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
}
