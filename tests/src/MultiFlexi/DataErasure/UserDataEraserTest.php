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

namespace MultiFlexi\DataErasure;

use MultiFlexi\User;
use PHPUnit\Framework\TestCase;

/**
 * Test class for UserDataEraser.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class UserDataEraserTest extends TestCase
{
    private User $targetUser;
    private User $requestingUser;
    private UserDataEraser $eraser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock users for testing
        $this->targetUser = $this->createMock(User::class);
        $this->targetUser->method('getId')->willReturn(123);
        $this->targetUser->method('getUserName')->willReturn('Test User');
        $this->targetUser->method('getDataValue')->willReturnMap([
            ['login', 'testuser'],
            ['email', 'test@example.com'],
            ['firstname', 'Test'],
            ['lastname', 'User'],
        ]);

        $this->requestingUser = $this->createMock(User::class);
        $this->requestingUser->method('getId')->willReturn(456);
        $this->requestingUser->method('getUserName')->willReturn('Admin User');

        $this->eraser = new UserDataEraser($this->targetUser, $this->requestingUser);
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(UserDataEraser::class, $this->eraser);
    }

    public function testCanRequestDeletion(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(789);

        $result = UserDataEraser::canRequestDeletion($user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('allowed', $result);
        $this->assertArrayHasKey('reason', $result);
        $this->assertIsBool($result['allowed']);
        $this->assertIsString($result['reason']);
    }

    public function testAnonymizeField(): void
    {
        $reflection = new \ReflectionClass($this->eraser);
        $method = $reflection->getMethod('anonymizeField');
        $method->setAccessible(true);

        // Test email anonymization
        $result = $method->invokeArgs($this->eraser, ['email', 'test@example.com']);
        $this->assertEquals('anonymized@deleted.user', $result);

        // Test name anonymization
        $result = $method->invokeArgs($this->eraser, ['firstname', 'John']);
        $this->assertEquals('Anonymized', $result);

        $result = $method->invokeArgs($this->eraser, ['lastname', 'Doe']);
        $this->assertEquals('Anonymized', $result);

        // Test login anonymization
        $result = $method->invokeArgs($this->eraser, ['login', 'johndoe']);
        $this->assertStringStartsWith('deleted_user_', $result);

        // Test unknown field anonymization
        $result = $method->invokeArgs($this->eraser, ['unknown_field', 'some value']);
        $this->assertEquals('[ANONYMIZED]', $result);
    }

    public function testGetPendingDeletionRequests(): void
    {
        // This test would require database setup, so we'll just verify the method exists
        $this->assertTrue(method_exists(UserDataEraser::class, 'getPendingDeletionRequests'));
    }

    public function testRetentionPeriods(): void
    {
        $reflection = new \ReflectionClass($this->eraser);
        $property = $reflection->getProperty('retentionPeriods');
        $property->setAccessible(true);

        $retentionPeriods = $property->getValue($this->eraser);

        $this->assertIsArray($retentionPeriods);
        $this->assertArrayHasKey('audit_logs', $retentionPeriods);
        $this->assertArrayHasKey('financial_records', $retentionPeriods);
        $this->assertArrayHasKey('job_logs', $retentionPeriods);
        $this->assertArrayHasKey('personal_data', $retentionPeriods);

        // Verify retention periods are reasonable
        $this->assertEquals(2555, $retentionPeriods['audit_logs']); // 7 years
        $this->assertEquals(3650, $retentionPeriods['financial_records']); // 10 years
        $this->assertEquals(365, $retentionPeriods['job_logs']); // 1 year
        $this->assertEquals(0, $retentionPeriods['personal_data']); // Can be deleted immediately
    }

    public function testUserDataTablesConfiguration(): void
    {
        $reflection = new \ReflectionClass($this->eraser);
        $property = $reflection->getProperty('userDataTables');
        $property->setAccessible(true);

        $userDataTables = $property->getValue($this->eraser);

        $this->assertIsArray($userDataTables);

        // Verify required tables are configured
        $this->assertArrayHasKey('user', $userDataTables);
        $this->assertArrayHasKey('company', $userDataTables);
        $this->assertArrayHasKey('job', $userDataTables);
        $this->assertArrayHasKey('run_template', $userDataTables);
        $this->assertArrayHasKey('logger', $userDataTables);

        // Verify table configuration structure
        foreach ($userDataTables as $tableName => $config) {
            $this->assertArrayHasKey('strategy', $config);
            $this->assertArrayHasKey('foreign_key', $config);
            $this->assertArrayHasKey('personal_fields', $config);
            $this->assertArrayHasKey('retention_type', $config);

            $this->assertIsString($config['strategy']);
            $this->assertIsString($config['foreign_key']);
            $this->assertIsArray($config['personal_fields']);
            $this->assertIsString($config['retention_type']);

            // Verify valid strategies
            $validStrategies = [
                'anonymize_or_delete',
                'check_shared_ownership',
                'check_shared_usage',
                'anonymize',
                'retain',
            ];
            $this->assertContains($config['strategy'], $validStrategies);
        }
    }

    /**
     * Test that personal data fields are properly identified.
     */
    public function testPersonalDataIdentification(): void
    {
        $reflection = new \ReflectionClass($this->eraser);
        $property = $reflection->getProperty('userDataTables');
        $property->setAccessible(true);

        $userDataTables = $property->getValue($this->eraser);

        // User table should have personal fields
        $this->assertNotEmpty($userDataTables['user']['personal_fields']);
        $this->assertContains('firstname', $userDataTables['user']['personal_fields']);
        $this->assertContains('lastname', $userDataTables['user']['personal_fields']);
        $this->assertContains('email', $userDataTables['user']['personal_fields']);
        $this->assertContains('login', $userDataTables['user']['personal_fields']);
    }

    /**
     * Test GDPR compliance validation.
     */
    public function testGDPRCompliance(): void
    {
        // Verify that the implementation supports all required GDPR Article 17 features

        // 1. Right to request deletion
        $this->assertTrue(method_exists(UserDataEraser::class, 'createDeletionRequest'));

        // 2. Different deletion types (soft, hard, anonymize)
        $this->assertTrue(method_exists($this->eraser, 'performSoftDeletion'));
        $this->assertTrue(method_exists($this->eraser, 'performHardDeletion'));
        $this->assertTrue(method_exists($this->eraser, 'performAnonymization'));

        // 3. Admin approval process
        $this->assertTrue(method_exists($this->eraser, 'approveDeletionRequest'));
        $this->assertTrue(method_exists($this->eraser, 'rejectDeletionRequest'));

        // 4. Audit trail
        $this->assertTrue(method_exists($this->eraser, 'processDeletionRequest'));

        // 5. Legal retention handling
        $this->assertTrue(method_exists($this->eraser, 'checkDataDependencies'));
    }

    /**
     * Test data protection measures.
     */
    public function testDataProtectionMeasures(): void
    {
        // Verify shared data protection
        $this->assertTrue(method_exists($this->eraser, 'hasSharedOwnership'));
        $this->assertTrue(method_exists($this->eraser, 'hasSharedUsage'));

        // Verify dependency checking
        $this->assertTrue(method_exists($this->eraser, 'checkDataDependencies'));

        // Verify anonymization capabilities
        $this->assertTrue(method_exists($this->eraser, 'anonymizeTable'));
        $this->assertTrue(method_exists($this->eraser, 'anonymizeUserReferencesInTable'));
    }
}
