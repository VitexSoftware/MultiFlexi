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

/**
 * Database Migration for GDPR Article 16 - Right of Rectification
 * 
 * This script creates the necessary database tables for the GDPR Article 16 implementation
 * 
 * Usage: php gdpr-article16-migration.php
 */

require_once './init.php';

echo "GDPR Article 16 - Database Migration\n";
echo "====================================\n\n";

try {
    // Create User Data Audit Logger table
    echo "Creating user data audit table...\n";
    $auditLogger = new \MultiFlexi\Audit\UserDataAuditLogger();
    if ($auditLogger->createTable()) {
        echo "✓ User data audit table created successfully\n";
    } else {
        echo "✗ Failed to create user data audit table\n";
        exit(1);
    }

    // Create User Data Correction Requests table
    echo "Creating user data correction requests table...\n";
    $correctionRequest = new \MultiFlexi\GDPR\UserDataCorrectionRequest();
    if ($correctionRequest->createTable()) {
        echo "✓ User data correction requests table created successfully\n";
    } else {
        echo "✗ Failed to create user data correction requests table\n";
        exit(1);
    }

    // Add some sample data for testing (optional)
    if (isset($argv[1]) && $argv[1] === '--with-sample-data') {
        echo "\nAdding sample data for testing...\n";
        
        // Create a test user if not exists
        $testUser = new \MultiFlexi\User();
        if (!$testUser->loadFromSQL(['login' => 'testuser'])) {
            $testUser->setDataValues([
                'login' => 'testuser',
                'firstname' => 'Test',
                'lastname' => 'User',
                'email' => 'test@example.com',
                'password' => \MultiFlexi\User::encryptPassword('testpass'),
                'DatCreate' => date('Y-m-d H:i:s'),
                'DatSave' => date('Y-m-d H:i:s')
            ]);
            
            if ($testUser->dbsync()) {
                echo "✓ Test user created (login: testuser, password: testpass)\n";
                
                // Create a sample audit log entry
                $auditLogger->logDataChange(
                    $testUser->getId(),
                    'firstname',
                    'Test',
                    'TestUpdated',
                    'direct',
                    $testUser->getId(),
                    '127.0.0.1',
                    'Migration Script',
                    'Sample data creation'
                );
                echo "✓ Sample audit log entry created\n";
                
                // Create a sample correction request
                $correctionRequest->createRequest(
                    $testUser->getId(),
                    'email',
                    'test@example.com',
                    'newemail@example.com',
                    'Testing the correction request system'
                );
                echo "✓ Sample correction request created\n";
            }
        } else {
            echo "✓ Test user already exists\n";
        }
    }

    // Verify tables were created correctly
    echo "\nVerifying database structure...\n";
    
    $pdo = $auditLogger->pdo;
    
    // Check audit table structure
    $auditColumns = $pdo->query("DESCRIBE `user_data_audit`")->fetchAll(\PDO::FETCH_COLUMN);
    $expectedAuditColumns = ['id', 'user_id', 'field_name', 'old_value', 'new_value', 'change_type', 'changed_by_user_id', 'ip_address', 'user_agent', 'reason', 'created_at'];
    
    foreach ($expectedAuditColumns as $column) {
        if (in_array($column, $auditColumns)) {
            echo "✓ Audit table has column: $column\n";
        } else {
            echo "✗ Audit table missing column: $column\n";
        }
    }
    
    // Check correction requests table structure
    $requestColumns = $pdo->query("DESCRIBE `user_data_correction_requests`")->fetchAll(\PDO::FETCH_COLUMN);
    $expectedRequestColumns = ['id', 'user_id', 'field_name', 'current_value', 'requested_value', 'justification', 'status', 'requested_by_ip', 'requested_by_user_agent', 'reviewed_by_user_id', 'reviewed_at', 'reviewer_notes', 'created_at', 'updated_at'];
    
    foreach ($expectedRequestColumns as $column) {
        if (in_array($column, $requestColumns)) {
            echo "✓ Correction requests table has column: $column\n";
        } else {
            echo "✗ Correction requests table missing column: $column\n";
        }
    }

    echo "\n====================================\n";
    echo "Migration completed successfully!\n\n";
    echo "Next steps:\n";
    echo "1. Access your profile at: profile.php\n";
    echo "2. Administrators can manage requests at: admin-data-corrections.php\n";
    echo "3. Review the implementation documentation\n\n";
    
    echo "GDPR Article 16 compliance features:\n";
    echo "✓ User data audit logging\n";
    echo "✓ Data correction request system\n";
    echo "✓ Approval workflow for sensitive changes\n";
    echo "✓ Email notifications\n";
    echo "✓ Admin review interface\n";
    echo "✓ User profile management\n";

} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nMigration completed at: " . date('Y-m-d H:i:s') . "\n";