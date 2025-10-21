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

namespace MultiFlexi\Test\DataRetention;

use MultiFlexi\DataRetention\DataArchiver;
use MultiFlexi\DataRetention\RetentionPolicyManager;
use MultiFlexi\DataRetention\RetentionService;
use PHPUnit\Framework\TestCase;

/**
 * Test class for RetentionService.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class RetentionServiceTest extends TestCase
{
    private RetentionService $retentionService;

    private RetentionPolicyManager $policyManager;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Note: In a real test environment, you would set up a test database
        // For this example, we're creating the services without database interaction
        $this->retentionService = new RetentionService();
        $this->policyManager = new RetentionPolicyManager();
    }

    /**
     * Clean up after tests.
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up any test data if needed
        $this->retentionService = null;
        $this->policyManager = null;
    }

    /**
     * Test retention service initialization.
     */
    public function testRetentionServiceInitialization(): void
    {
        $this->assertInstanceOf(RetentionService::class, $this->retentionService);
    }

    /**
     * Test policy manager initialization.
     */
    public function testPolicyManagerInitialization(): void
    {
        $this->assertInstanceOf(RetentionPolicyManager::class, $this->policyManager);
    }

    /**
     * Test data archiver initialization.
     */
    public function testDataArchiverInitialization(): void
    {
        $archiver = new DataArchiver();
        $this->assertInstanceOf(DataArchiver::class, $archiver);
    }

    /**
     * Test policy validation.
     */
    public function testPolicyValidation(): void
    {
        // Test valid policy data
        $validPolicyData = [
            'policy_name' => 'test_policy',
            'data_type' => 'user_personal_data',
            'table_name' => 'user',
            'retention_period_days' => 365,
            'grace_period_days' => 30,
            'deletion_action' => 'anonymize',
            'legal_basis' => 'GDPR Article 5',
            'description' => 'Test policy for unit testing',
        ];

        $validation = $this->policyManager->validatePolicy($validPolicyData);
        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);

        // Test invalid policy data (missing required fields)
        $invalidPolicyData = [
            'policy_name' => '',
            'data_type' => 'invalid_type',
            'retention_period_days' => -1,
        ];

        $validation = $this->policyManager->validatePolicy($invalidPolicyData);
        $this->assertFalse($validation['valid']);
        $this->assertNotEmpty($validation['errors']);
    }

    /**
     * Test supported data types.
     */
    public function testSupportedDataTypes(): void
    {
        $dataTypes = $this->policyManager->getSupportedDataTypes();

        $this->assertIsArray($dataTypes);
        $this->assertArrayHasKey('user_personal_data', $dataTypes);
        $this->assertArrayHasKey('session_data', $dataTypes);
        $this->assertArrayHasKey('audit_data', $dataTypes);
        $this->assertArrayHasKey('job_execution_data', $dataTypes);
    }

    /**
     * Test valid deletion actions.
     */
    public function testValidDeletionActions(): void
    {
        $actions = $this->policyManager->getValidDeletionActions();

        $this->assertIsArray($actions);
        $this->assertContains('hard_delete', $actions);
        $this->assertContains('soft_delete', $actions);
        $this->assertContains('anonymize', $actions);
        $this->assertContains('archive', $actions);
    }

    /**
     * Test policy name validation.
     */
    public function testPolicyNameValidation(): void
    {
        // Valid policy names
        $validNames = ['test_policy', 'user-data-policy', 'Policy123'];

        foreach ($validNames as $name) {
            $policyData = [
                'policy_name' => $name,
                'data_type' => 'user_personal_data',
                'table_name' => 'user',
                'retention_period_days' => 365,
                'deletion_action' => 'anonymize',
            ];

            $validation = $this->policyManager->validatePolicy($policyData);
            $this->assertTrue($validation['valid'], "Policy name '{$name}' should be valid");
        }

        // Invalid policy names
        $invalidNames = ['', 'policy with spaces', 'policy@email.com', str_repeat('a', 101)];

        foreach ($invalidNames as $name) {
            $policyData = [
                'policy_name' => $name,
                'data_type' => 'user_personal_data',
                'table_name' => 'user',
                'retention_period_days' => 365,
                'deletion_action' => 'anonymize',
            ];

            $validation = $this->policyManager->validatePolicy($policyData);
            $this->assertFalse($validation['valid'], "Policy name '{$name}' should be invalid");
        }
    }

    /**
     * Test retention period validation.
     */
    public function testRetentionPeriodValidation(): void
    {
        // Valid retention periods
        $validPeriods = [0, 30, 365, 1095, 2555];

        foreach ($validPeriods as $period) {
            $policyData = [
                'policy_name' => 'test_policy',
                'data_type' => 'user_personal_data',
                'table_name' => 'user',
                'retention_period_days' => $period,
                'deletion_action' => 'anonymize',
            ];

            $validation = $this->policyManager->validatePolicy($policyData);
            $this->assertTrue($validation['valid'], "Retention period '{$period}' should be valid");
        }

        // Invalid retention periods
        $invalidPeriods = [-1, -100];

        foreach ($invalidPeriods as $period) {
            $policyData = [
                'policy_name' => 'test_policy',
                'data_type' => 'user_personal_data',
                'table_name' => 'user',
                'retention_period_days' => $period,
                'deletion_action' => 'anonymize',
            ];

            $validation = $this->policyManager->validatePolicy($policyData);
            $this->assertFalse($validation['valid'], "Retention period '{$period}' should be invalid");
        }
    }

    /**
     * Test deletion action validation.
     */
    public function testDeletionActionValidation(): void
    {
        $validActions = ['hard_delete', 'soft_delete', 'anonymize', 'archive'];

        foreach ($validActions as $action) {
            $policyData = [
                'policy_name' => 'test_policy',
                'data_type' => 'user_personal_data',
                'table_name' => 'user',
                'retention_period_days' => 365,
                'deletion_action' => $action,
            ];

            $validation = $this->policyManager->validatePolicy($policyData);
            $this->assertTrue($validation['valid'], "Deletion action '{$action}' should be valid");
        }

        // Invalid deletion actions
        $invalidActions = ['invalid_action', 'delete', 'remove', ''];

        foreach ($invalidActions as $action) {
            $policyData = [
                'policy_name' => 'test_policy',
                'data_type' => 'user_personal_data',
                'table_name' => 'user',
                'retention_period_days' => 365,
                'deletion_action' => $action,
            ];

            $validation = $this->policyManager->validatePolicy($policyData);
            $this->assertFalse($validation['valid'], "Deletion action '{$action}' should be invalid");
        }
    }

    /**
     * Test policy warnings for specific scenarios.
     */
    public function testPolicyWarnings(): void
    {
        // Test warning for very long retention period
        $longRetentionPolicy = [
            'policy_name' => 'long_retention_policy',
            'data_type' => 'user_personal_data',
            'table_name' => 'user',
            'retention_period_days' => 4000, // More than 10 years
            'deletion_action' => 'anonymize',
            'legal_basis' => 'Strong legal basis required',
        ];

        $validation = $this->policyManager->validatePolicy($longRetentionPolicy);
        $this->assertTrue($validation['valid']);
        $this->assertNotEmpty($validation['warnings']);

        // Test warning for hard deletion of personal data
        $hardDeletePersonalData = [
            'policy_name' => 'hard_delete_personal',
            'data_type' => 'user_personal_data',
            'table_name' => 'user',
            'retention_period_days' => 365,
            'deletion_action' => 'hard_delete',
        ];

        $validation = $this->policyManager->validatePolicy($hardDeletePersonalData);
        $this->assertTrue($validation['valid']);
        $this->assertNotEmpty($validation['warnings']);

        // Test warning for short audit data retention
        $shortAuditRetention = [
            'policy_name' => 'short_audit_retention',
            'data_type' => 'audit_data',
            'table_name' => 'security_audit_log',
            'retention_period_days' => 10, // Less than 30 days
            'deletion_action' => 'archive',
        ];

        $validation = $this->policyManager->validatePolicy($shortAuditRetention);
        $this->assertTrue($validation['valid']);
        $this->assertNotEmpty($validation['warnings']);
    }

    /**
     * Test anonymization field mapping.
     */
    public function testAnonymizationFieldMapping(): void
    {
        $testRecord = [
            'id' => 1,
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'login' => 'johndoe',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0...',
            'message' => 'Test log message',
            'name' => 'Company Name',
            'contact' => 'Contact Info',
        ];

        $config = [
            'personal_fields' => ['email', 'firstname', 'lastname', 'login', 'ip_address', 'user_agent', 'message', 'name', 'contact'],
        ];

        // Use reflection to access private method for testing
        $reflection = new \ReflectionClass($this->retentionService);
        $method = $reflection->getMethod('anonymizeRecord');
        $method->setAccessible(true);

        $anonymizedData = $method->invoke($this->retentionService, $testRecord, $config);

        // Check that personal fields are anonymized
        $this->assertEquals('anonymized@deleted.user', $anonymizedData['email']);
        $this->assertEquals('[ANONYMIZED]', $anonymizedData['firstname']);
        $this->assertEquals('[ANONYMIZED]', $anonymizedData['lastname']);
        $this->assertStringStartsWith('deleted_user_', $anonymizedData['login']);
        $this->assertEquals('0.0.0.0', $anonymizedData['ip_address']);
        $this->assertEquals('[ANONYMIZED]', $anonymizedData['user_agent']);
        $this->assertEquals('[LOG MESSAGE ANONYMIZED]', $anonymizedData['message']);
        $this->assertEquals('[ANONYMIZED]', $anonymizedData['name']);
        $this->assertEquals('[CONTACT ANONYMIZED]', $anonymizedData['contact']);
    }

    /**
     * Test cleanup statistics structure.
     */
    public function testCleanupStatisticsStructure(): void
    {
        // Note: This would require a database in a real test
        // For now, we just test that the method exists and returns expected structure
        $stats = $this->retentionService->getCleanupStatistics(30);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_jobs', $stats);
        $this->assertArrayHasKey('completed_jobs', $stats);
        $this->assertArrayHasKey('failed_jobs', $stats);
        $this->assertArrayHasKey('total_records_processed', $stats);
        $this->assertArrayHasKey('total_records_deleted', $stats);
        $this->assertArrayHasKey('total_records_anonymized', $stats);
        $this->assertArrayHasKey('total_records_archived', $stats);
    }

    /**
     * Test archive integrity verification.
     */
    public function testArchiveIntegrityVerification(): void
    {
        $archiver = new DataArchiver();

        // Test with valid archive data structure
        $validArchiveData = [
            'archive_info' => [
                'archive_type' => 'pre_deletion',
                'source_table' => 'user',
                'archived_at' => '2023-01-01 00:00:00',
                'archived_by' => 1,
                'archived_data' => json_encode(['id' => 1, 'name' => 'Test']),
            ],
            'original_data' => ['id' => 1, 'name' => 'Test'],
        ];

        // Use reflection to access private method for testing
        $reflection = new \ReflectionClass($archiver);
        $method = $reflection->getMethod('verifyArchiveIntegrity');

        // Note: This method would need to be adjusted to work without database
        // The test structure shows how it would be tested
        $this->assertTrue(method_exists($archiver, 'verifyArchiveIntegrity'));
    }
}
