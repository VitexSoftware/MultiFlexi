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

namespace MultiFlexi\Tests\Security;

use MultiFlexi\Security\ApiRateLimiter;
use MultiFlexi\Security\BruteForceProtection;
use MultiFlexi\Security\CsrfProtection;
use MultiFlexi\Security\PasswordValidator;
use MultiFlexi\Security\SecurityAuditLogger;
use MultiFlexi\Security\SessionManager;
use PHPUnit\Framework\TestCase;

/**
 * Security Test Suite for GDPR Phase 3 Security Enhancements.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class SecurityTestSuite extends TestCase
{
    private ?\PDO $pdo = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Create in-memory SQLite database for testing
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Create necessary tables for testing
        $this->createTestTables();
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
        parent::tearDown();
    }

    /**
     * Test password validation functionality.
     */
    public function testPasswordValidator(): void
    {
        $validator = new PasswordValidator(8, true, true, true, true);

        // Test weak password
        $result = $validator->validate('123');
        $this->assertFalse($result['valid']);
        $this->assertGreaterThan(0, \count($result['errors']));

        // Test medium strength password
        $result = $validator->validate('Password1');
        $this->assertFalse($result['valid']); // Should fail due to no special chars

        // Test strong password
        $result = $validator->validate('StrongP@ssw0rd!');
        $this->assertTrue($result['valid']);
        $this->assertCount(0, $result['errors']);
        $this->assertGreaterThan(70, $result['strength']);

        // Test common password rejection
        $result = $validator->validate('password');
        $this->assertFalse($result['valid']);
        $this->assertContains('Password is too common and easily guessable', $result['errors']);
    }

    /**
     * Test CSRF protection functionality.
     */
    public function testCsrfProtection(): void
    {
        // Mock session manager
        $sessionManager = $this->createMock(SessionManager::class);
        $sessionManager->method('getCsrfToken')->willReturn('test_token_123');
        $sessionManager->method('validateCsrfToken')->willReturnCallback(
            static function ($token) {
                return $token === 'test_token_123';
            },
        );

        $csrfProtection = new CsrfProtection($sessionManager);

        // Test token generation
        $token = $csrfProtection->generateToken();
        $this->assertEquals('test_token_123', $token);

        // Test valid token validation
        $this->assertTrue($csrfProtection->validateToken('test_token_123'));

        // Test invalid token validation
        $this->assertFalse($csrfProtection->validateToken('invalid_token'));

        // Test token input creation
        $input = $csrfProtection->createTokenInput();
        $this->assertStringContainsString('csrf_token', $input);
        $this->assertStringContainsString('test_token_123', $input);
    }

    /**
     * Test brute force protection functionality.
     */
    public function testBruteForceProtection(): void
    {
        $protection = new BruteForceProtection($this->pdo, 3, 300, 60, true);

        $username = 'testuser';
        $ipAddress = '192.168.1.100';

        // Test initial login attempt allowance
        $result = $protection->canAttemptLogin($username, $ipAddress);
        $this->assertTrue($result['allowed']);

        // Record failed attempts
        for ($i = 0; $i < 3; ++$i) {
            $protection->recordAttempt($username, false, $ipAddress);
        }

        // Test that user is now locked out
        $result = $protection->canAttemptLogin($username, $ipAddress);
        $this->assertFalse($result['allowed']);
        $this->assertEquals('user_locked', $result['reason']);

        // Test successful login clears attempts
        $protection->clearAttempts($username, $ipAddress);
        $result = $protection->canAttemptLogin($username, $ipAddress);
        $this->assertTrue($result['allowed']);
    }

    /**
     * Test security audit logging functionality.
     */
    public function testSecurityAuditLogger(): void
    {
        $logger = new SecurityAuditLogger($this->pdo);

        // Test logging a security event
        $result = $logger->logEvent(
            'test_event',
            'This is a test event',
            SecurityAuditLogger::SEVERITY_HIGH,
            123,
            ['test' => 'data'],
        );
        $this->assertTrue($result);

        // Test specific event logging methods
        $this->assertTrue($logger->logLoginSuccess(123));
        $this->assertTrue($logger->logLoginFailure('testuser', 'Invalid password'));
        $this->assertTrue($logger->logPasswordChange(123, false));

        // Test retrieving recent events
        $events = $logger->getRecentEvents(24);
        $this->assertGreaterThan(0, \count($events));

        // Test security statistics
        $stats = $logger->getSecurityStatistics(7);
        $this->assertArrayHasKey('failed_logins', $stats);
        $this->assertArrayHasKey('successful_logins', $stats);
    }

    /**
     * Test API rate limiting functionality.
     */
    public function testApiRateLimiter(): void
    {
        $rateLimiter = new ApiRateLimiter($this->pdo, 5, 3600);

        $identifier = 'test_ip_123';
        $endpoint = '/api/test';

        // Test initial requests are allowed
        for ($i = 0; $i < 5; ++$i) {
            $result = $rateLimiter->checkRateLimit($identifier, $endpoint);
            $this->assertTrue($result['allowed']);
            $this->assertEquals(5, $result['limit']);
            $this->assertEquals(4 - $i, $result['remaining']);
        }

        // Test that 6th request is blocked
        $result = $rateLimiter->checkRateLimit($identifier, $endpoint);
        $this->assertFalse($result['allowed']);
        $this->assertEquals(0, $result['remaining']);

        // Test manual blocking
        $rateLimiter->blockIdentifier('blocked_user', 3600);
        $this->assertTrue($rateLimiter->isBlocked('blocked_user'));

        // Test manual unblocking
        $rateLimiter->unblockIdentifier('blocked_user');
        $this->assertFalse($rateLimiter->isBlocked('blocked_user'));
    }

    /**
     * Test User class password hashing improvements.
     */
    public function testUserPasswordHashing(): void
    {
        // Test bcrypt password encryption
        $password = 'TestPassword123!';
        $hash = \MultiFlexi\User::encryptPassword($password);

        // Verify it's a bcrypt hash
        $this->assertStringStartsWith('$2y$', $hash);

        // Test password validation
        $this->assertTrue(\MultiFlexi\User::passwordValidation($password, $hash));
        $this->assertFalse(\MultiFlexi\User::passwordValidation('wrong_password', $hash));

        // Test legacy MD5 hash support
        $legacyHash = 'e10adc3949ba59abbe56e057f20f883e:yo'; // MD5 hash of 'password' with salt 'yo'
        $this->assertTrue(\MultiFlexi\User::passwordValidation('password', $legacyHash));
    }

    /**
     * Test security configuration management.
     */
    public function testSecurityConfig(): void
    {
        $config = new \MultiFlexi\Security\SecurityConfig();

        // Test default values
        $this->assertEquals(8, $config->get('PASSWORD_MIN_LENGTH'));
        $this->assertTrue($config->get('CSRF_PROTECTION_ENABLED'));
        $this->assertTrue($config->get('BRUTE_FORCE_PROTECTION_ENABLED'));

        // Test setting values
        $config->set('PASSWORD_MIN_LENGTH', 12);
        $this->assertEquals(12, $config->get('PASSWORD_MIN_LENGTH'));

        // Test validation
        $validation = $config->validate();
        $this->assertArrayHasKey('valid', $validation);
        $this->assertArrayHasKey('errors', $validation);
        $this->assertArrayHasKey('warnings', $validation);

        // Test recommendations
        $recommendations = $config->getSecurityRecommendations();
        $this->assertIsArray($recommendations);
    }

    /**
     * Integration test for multiple security features.
     */
    public function testSecurityIntegration(): void
    {
        // Test that security components work together
        $sessionManager = new SessionManager(3600, 300, true, false);
        $csrfProtection = new CsrfProtection($sessionManager);
        $bruteForceProtection = new BruteForceProtection($this->pdo);
        $auditLogger = new SecurityAuditLogger($this->pdo);

        // Simulate a failed login attempt
        $username = 'integrationtest';
        $ipAddress = '192.168.1.200';

        // Check if login is allowed
        $loginCheck = $bruteForceProtection->canAttemptLogin($username, $ipAddress);
        $this->assertTrue($loginCheck['allowed']);

        // Record failed attempt
        $bruteForceProtection->recordAttempt($username, false, $ipAddress);
        $auditLogger->logLoginFailure($username, 'Invalid password');

        // Generate CSRF token
        $csrfToken = $csrfProtection->generateToken();
        $this->assertNotEmpty($csrfToken);

        // Verify audit log has entries
        $recentEvents = $auditLogger->getRecentEvents(1);
        $this->assertGreaterThan(0, \count($recentEvents));
    }

    /**
     * Test security headers functionality.
     */
    public function testSecurityHeaders(): void
    {
        // This test would require mocking headers_sent() function
        // For now, we'll just test that the session manager has the method
        $sessionManager = new SessionManager();

        // Test that the method exists and can be called
        $this->assertTrue(method_exists($sessionManager, 'setSecurityHeaders'));

        // In a real test environment, you would capture headers and verify them
    }

    private function createTestTables(): void
    {
        // Login attempts table
        $this->pdo->exec(<<<'EOD'

            CREATE TABLE login_attempts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ip_address VARCHAR(45) NOT NULL,
                username VARCHAR(255),
                attempt_time DATETIME NOT NULL,
                success BOOLEAN NOT NULL DEFAULT FALSE,
                user_agent TEXT,
                failure_reason VARCHAR(100)
            )

EOD);

        // Security audit log table
        $this->pdo->exec(<<<'EOD'

            CREATE TABLE security_audit_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                event_type VARCHAR(50) NOT NULL,
                event_description TEXT NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                additional_data TEXT,
                severity VARCHAR(10) NOT NULL DEFAULT 'medium',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )

EOD);

        // API rate limits table
        $this->pdo->exec(<<<'EOD'

            CREATE TABLE api_rate_limits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                identifier VARCHAR(255) NOT NULL,
                endpoint VARCHAR(255),
                requests INTEGER NOT NULL DEFAULT 0,
                window_start DATETIME NOT NULL,
                window_end DATETIME NOT NULL,
                blocked_until DATETIME,
                UNIQUE(identifier, endpoint)
            )

EOD);
    }
}

/**
 * Example of how to run these tests:
 *
 * vendor/bin/phpunit tests/Security/SecurityTestSuite.php
 *
 * Or with specific test methods:
 * vendor/bin/phpunit --filter testPasswordValidator tests/Security/SecurityTestSuite.php
 */
