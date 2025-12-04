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

namespace MultiFlexi;

use Ease\Shared;
use MultiFlexi\Ui\WebPage;

require_once '../vendor/autoload.php';

Shared::init(
    ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'ENCRYPTION_MASTER_KEY'],
    \dirname(__DIR__).'/.env',
);

new \MultiFlexi\Defaults();

// Initialize enhanced session security (disable for now if classes don't exist)
if (class_exists('\MultiFlexi\Security\SessionManager')) {
    $sessionManager = new \MultiFlexi\Security\SessionManager(
        \Ease\Shared::cfg('SESSION_TIMEOUT', 3600),
        \Ease\Shared::cfg('SESSION_REGENERATION_INTERVAL', 300),
        \Ease\Shared::cfg('SESSION_STRICT_USER_AGENT', true),
        \Ease\Shared::cfg('SESSION_STRICT_IP_ADDRESS', false),
    );

    if (!$sessionManager->startSecureSession()) {
        // Session security validation failed, redirect to login
        if (basename($_SERVER['SCRIPT_NAME']) !== 'login.php') {
            $currentUrl = $_SERVER['REQUEST_URI'];
            header('Location: login.php?session_expired=1&redirect='.urlencode($currentUrl));

            exit;
        }
    }

    // Set security headers
    $sessionManager->setSecurityHeaders();

    // Store session manager globally
    $GLOBALS['sessionManager'] = $sessionManager;

    // Initialize CSRF protection if available
    if (class_exists('\MultiFlexi\Security\CsrfProtection')) {
        $csrfProtection = new \MultiFlexi\Security\CsrfProtection($sessionManager);
        $GLOBALS['csrfProtection'] = $csrfProtection;

        // Validate CSRF for POST requests (unless explicitly bypassed)
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && \Ease\Shared::cfg('CSRF_PROTECTION_ENABLED', true) && !\defined('BYPASS_CSRF_PROTECTION')) {
            $csrfProtection->middleware();
        }
    }
} else {
    // Fallback to basic session if security classes don't exist
    if (session_status() === \PHP_SESSION_NONE) {
        session_start();
    }
}

// Get PDO connection for security components
$pdo = null;

try {
    $dsn = \Ease\Shared::cfg('DB_CONNECTION').':host='.\Ease\Shared::cfg('DB_HOST').';port='.\Ease\Shared::cfg('DB_PORT', 3306).';dbname='.\Ease\Shared::cfg('DB_DATABASE').';charset=utf8mb4';
    $pdo = new \PDO(
        $dsn,
        \Ease\Shared::cfg('DB_USERNAME'),
        \Ease\Shared::cfg('DB_PASSWORD'),
        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION],
    );
} catch (\Exception $e) {
    error_log('Failed to initialize database connection: '.$e->getMessage());
}

// Initialize brute force protection (disabled temporarily)
if ($pdo && \Ease\Shared::cfg('BRUTE_FORCE_PROTECTION_ENABLED', true) && class_exists('\MultiFlexi\Security\BruteForceProtection')) {
    try {
        $bruteForceProtection = new \MultiFlexi\Security\BruteForceProtection(
            $pdo,
            \Ease\Shared::cfg('BRUTE_FORCE_MAX_ATTEMPTS', 5),
            \Ease\Shared::cfg('BRUTE_FORCE_LOCKOUT_DURATION', 900), // 15 minutes
            \Ease\Shared::cfg('BRUTE_FORCE_TIME_WINDOW', 300), // 5 minutes
            \Ease\Shared::cfg('BRUTE_FORCE_IP_LIMITING', true),
        );
        $GLOBALS['bruteForceProtection'] = $bruteForceProtection;
    } catch (\Exception $e) {
        // Log error but don't break the application
        error_log('Failed to initialize brute force protection: '.$e->getMessage());
    }
}

// Initialize security audit logger (disabled temporarily)
if ($pdo && \Ease\Shared::cfg('SECURITY_LOGGING_ENABLED', true) && class_exists('\MultiFlexi\Security\SecurityAuditLogger')) {
    try {
        $securityAuditLogger = new \MultiFlexi\Security\SecurityAuditLogger($pdo);
        $GLOBALS['securityAuditLogger'] = $securityAuditLogger;
    } catch (\Exception $e) {
        // Log error but don't break the application
        error_log('Failed to initialize security audit logger: '.$e->getMessage());
    }
}

// Initialize data encryption (disabled temporarily due to key initialization issues)
if ($pdo && \Ease\Shared::cfg('DATA_ENCRYPTION_ENABLED', true) && class_exists('\MultiFlexi\Security\DataEncryption')) {
    try {
        $dataEncryption = new \MultiFlexi\Security\DataEncryption($pdo);

        // Initialize default encryption keys if they don't exist
        $dataEncryption->initializeDefaultKeys();

        $GLOBALS['dataEncryption'] = $dataEncryption;
    } catch (\Exception $e) {
        // Log error but don't break the application
        error_log('Failed to initialize data encryption: '.$e->getMessage());
    }
}

// Initialize rate limiter (disabled temporarily)
if ($pdo && \Ease\Shared::cfg('RATE_LIMITING_ENABLED', true) && class_exists('\MultiFlexi\Security\RateLimiter')) {
    try {
        $rateLimiter = new \MultiFlexi\Security\RateLimiter($pdo);

        $GLOBALS['rateLimiter'] = $rateLimiter;
    } catch (\Exception $e) {
        // Log error but don't break the application
        error_log('Failed to initialize rate limiter: '.$e->getMessage());
    }
}

// Initialize IP whitelist (disabled temporarily)
if ($pdo && \Ease\Shared::cfg('IP_WHITELIST_ENABLED', false) && class_exists('\MultiFlexi\Security\IpWhitelist')) {
    try {
        $ipWhitelist = new \MultiFlexi\Security\IpWhitelist($pdo);

        $GLOBALS['ipWhitelist'] = $ipWhitelist;
    } catch (\Exception $e) {
        // Log error but don't break the application
        error_log('Failed to initialize IP whitelist: '.$e->getMessage());
    }
}

// Initialize Two-Factor Authentication (disabled temporarily)
if ($pdo && \Ease\Shared::cfg('TWO_FACTOR_AUTH_ENABLED', true) && class_exists('\MultiFlexi\Security\TwoFactorAuth')) {
    try {
        $twoFactorAuth = new \MultiFlexi\Security\TwoFactorAuth($pdo);

        $GLOBALS['twoFactorAuth'] = $twoFactorAuth;
    } catch (\Exception $e) {
        // Log error but don't break the application
        error_log('Failed to initialize Two-Factor Authentication: '.$e->getMessage());
    }
}

// Initialize Role-Based Access Control (RBAC) (disabled temporarily)
if ($pdo && \Ease\Shared::cfg('RBAC_ENABLED', true) && class_exists('\MultiFlexi\Security\RoleBasedAccessControl')) {
    try {
        $rbac = new \MultiFlexi\Security\RoleBasedAccessControl($pdo);

        $GLOBALS['rbac'] = $rbac;
    } catch (\Exception $e) {
        // Log error but don't break the application
        error_log('Failed to initialize RBAC: '.$e->getMessage());
    }
}

\Ease\Locale::singleton(null, '../i18n', 'multiflexi');

// Configure file logging
if (!Shared::cfg('LOG_DIRECTORY')) {
    Shared::singleton()->setConfigValue('LOG_DIRECTORY', '/var/log/multiflexi');
}

$loggers = ['syslog', 'file', '\MultiFlexi\LogToSQL'];

if (Shared::cfg('ZABBIX_SERVER')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

\define('EASE_LOGGER', implode('|', $loggers));

// Add missing log style for security messages to prevent undefined key warning
\Ease\Logger\Regent::singleton()->logStyles['security'] = 'color: #800080; font-weight: bold;';

Shared::user(null, '\MultiFlexi\User');

/**
 * @global WebPage $oPage
 */
$oPage = new WebPage('');
WebPage::singleton($oPage);

// Add consent banner JavaScript to pages (except API endpoints)
$currentScript = basename($_SERVER['SCRIPT_NAME']);

if (!\in_array($currentScript, ['consent-api.php', 'ajax.php', 'api.php'], true)) {
    WebPage::singleton()->includeJavaScript('js/consent-banner.js');

    // Enhance page with consent helpers
    \MultiFlexi\Consent\ConsentHelper::enhancePageWithConsent($oPage);
}

date_default_timezone_set('Europe/Prague');

$script_tz = date_default_timezone_get();

if (strcmp($script_tz, \ini_get('date.timezone'))) {
    //    echo 'Script timezone differs from ini-set timezone.';
}
//    echo 'Script timezone and ini-set timezone match.';
