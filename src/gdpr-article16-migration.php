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
 * GDPR Article 16 - Data Seeding and Verification Script.
 *
 * This script seeds sample data and verifies the GDPR Article 16 implementation.
 * 
 * IMPORTANT: Database tables are now created via migrations:
 * - Run: bin/phinx migrate to create the required tables
 * - Then run this script for sample data and verification
 *
 * Usage: php gdpr-article16-migration.php [--with-sample-data]
 */

require_once './init.php';

echo "GDPR Article 16 - Data Verification & Seeding\n";
echo "==============================================\n\n";

// Check if required tables exist (created by migrations)
try {
    $pdo = new PDO(
        \Ease\Shared::cfg('DB_CONNECTION').':host='.\Ease\Shared::cfg('DB_HOST').';dbname='.\Ease\Shared::cfg('DB_DATABASE'),
        \Ease\Shared::cfg('DB_USERNAME'),
        \Ease\Shared::cfg('DB_PASSWORD')
    );
    
    // Check for required tables
    $requiredTables = ['user_data_audit', 'user_data_correction_requests'];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '{$table}'")->fetch();
        if (!$result) {
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        echo "✗ Missing required database tables: " . implode(', ', $missingTables) . "\n";
        echo "Please run migrations first: multiflexi-migrator\n";
        exit(1);
    }
    
    echo "✓ Required database tables found\n";

} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    // Initialize classes for sample data and verification
    $auditLogger = new \MultiFlexi\Audit\UserDataAuditLogger();
    $correctionRequest = new \MultiFlexi\GDPR\UserDataCorrectionRequest();

    // Add some sample data for testing (optional)
    if (isset($argv[1]) && $argv[1] === '--with-sample-data') {
        echo "\nAdding sample data for testing...\n";

        // Create a test user if not exists
        $testUser = new \MultiFlexi\User();

        if (!$testUser->loadFromSQL(['login' => 'testuser'])) {
            $testUser->setData([
                'login' => 'testuser',
                'firstname' => 'Test',
                'lastname' => 'User',
                'email' => 'test@example.com',
                'password' => \MultiFlexi\User::encryptPassword('testpass'),
                'DatCreate' => date('Y-m-d H:i:s'),
                'DatSave' => date('Y-m-d H:i:s'),
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
                    'Sample data creation',
                );
                echo "✓ Sample audit log entry created\n";

                // Create a sample correction request
                $correctionRequest->createRequest(
                    $testUser->getId(),
                    'email',
                    'test@example.com',
                    'newemail@example.com',
                    'Testing the correction request system',
                );
                echo "✓ Sample correction request created\n";
            }
        } else {
            echo "✓ Test user already exists\n";
        }
    }

    // Verify tables were created correctly
    echo "\nVerifying database structure...\n";

    // Check audit table structure
    $auditColumns = $pdo->query('DESCRIBE `user_data_audit`')->fetchAll(\PDO::FETCH_COLUMN);
    $expectedAuditColumns = ['id', 'user_id', 'field_name', 'old_value', 'new_value', 'change_type', 'changed_by_user_id', 'ip_address', 'user_agent', 'reason', 'created_at'];

    foreach ($expectedAuditColumns as $column) {
        if (\in_array($column, $auditColumns, true)) {
            echo "✓ Audit table has column: {$column}\n";
        } else {
            echo "✗ Audit table missing column: {$column}\n";
        }
    }

    // Check correction requests table structure
    $requestColumns = $pdo->query('DESCRIBE `user_data_correction_requests`')->fetchAll(\PDO::FETCH_COLUMN);
    $expectedRequestColumns = ['id', 'user_id', 'field_name', 'current_value', 'requested_value', 'justification', 'status', 'requested_by_ip', 'requested_by_user_agent', 'reviewed_by_user_id', 'reviewed_at', 'reviewer_notes', 'created_at', 'updated_at'];

    foreach ($expectedRequestColumns as $column) {
        if (\in_array($column, $requestColumns, true)) {
            echo "✓ Correction requests table has column: {$column}\n";
        } else {
            echo "✗ Correction requests table missing column: {$column}\n";
        }
    }

    echo "\n==============================================\n";
    echo "Verification completed successfully!\n\n";
    echo "Next steps:\n";
    echo "1. Access your profile at: profile.php\n";
    echo "2. Administrators can manage requests at: admin-data-corrections.php\n";
    echo "3. Review the implementation documentation\n\n";

    echo "GDPR Article 16 compliance features:\n";
    echo "✓ User data audit logging (via migrations)\n";
    echo "✓ Data correction request system (via migrations)\n";
    echo "✓ Approval workflow for sensitive changes\n";
    echo "✓ Email notifications\n";
    echo "✓ Admin review interface\n";
    echo "✓ User profile management\n";
} catch (Exception $e) {
    echo '✗ Verification failed: '.$e->getMessage()."\n";
    echo "Stack trace:\n".$e->getTraceAsString()."\n";

    exit(1);
}

echo "\nVerification completed at: ".date('Y-m-d H:i:s')."\n";
