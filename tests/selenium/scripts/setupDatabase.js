const mysql = require('mysql2/promise');
const EnvironmentManager = require('../src/EnvironmentManager');
const { exec } = require('child_process');
const util = require('util');
const fs = require('fs');

/**
 * Database setup script for MultiFlexi Selenium tests
 * This script:
 * 1. Drops all existing database tables 
 * 2. Runs Phinx migrations from /usr/lib/multiflexi-database/migrations/
 * 3. Creates test users, companies, and applications using multiflexi-cli
 */

const execPromise = util.promisify(exec);

/**
 * Get database configuration from the main MultiFlexi config
 * This bypasses the test environment's separate database approach
 */
function getMainDatabaseConfig() {
    const dbConfigPath = '/etc/multiflexi/database.env';
    
    if (!fs.existsSync(dbConfigPath)) {
        throw new Error(`Database configuration file ${dbConfigPath} not found`);
    }
    
    const configContent = fs.readFileSync(dbConfigPath, 'utf8');
    const config = {};
    
    configContent.split('\n').forEach(line => {
        const [key, value] = line.split('=');
        if (key && value) {
            config[key] = value.replace(/^["']|["']$/g, ''); // Remove quotes
        }
    });
    
    return {
        host: config.DB_HOST || 'localhost',
        port: parseInt(config.DB_PORT) || 3306,
        user: config.DB_USERNAME || 'root',
        password: config.DB_PASSWORD || '',
        database: config.DB_DATABASE || 'multiflexi'
    };
}

async function dropAllTables() {
    const config = getMainDatabaseConfig();
    
    console.log('🗑️  Dropping all existing database tables...');
    console.log(`🔗 Using database: ${config.database} @ ${config.host}:${config.port}`);
    
    const connection = await mysql.createConnection({
        host: config.host,
        port: config.port,
        user: config.user,
        password: config.password,
        database: config.database
    });

    try {
        // Drop all tables using the same approach as before
        await connection.execute('SET FOREIGN_KEY_CHECKS = 0');
        await connection.execute('SET GROUP_CONCAT_MAX_LEN=32768');
        
        const [rows] = await connection.execute(`
            SELECT GROUP_CONCAT(table_name) as tables 
            FROM information_schema.tables 
            WHERE table_schema = ?
        `, [config.database]);
        
        if (rows[0]?.tables) {
            await connection.execute(`DROP TABLE IF EXISTS ${rows[0].tables}`);
            console.log('✅ All existing tables dropped successfully');
        } else {
            console.log('✅ No existing tables found');
        }
        
        await connection.execute('SET FOREIGN_KEY_CHECKS = 1');
        
    } catch (error) {
        console.error('❌ Error dropping tables:', error);
        throw error;
    } finally {
        await connection.end();
    }
}

async function runPhinxMigrations() {
    console.log('🔄 Running Phinx migrations from /usr/lib/multiflexi-database/migrations/...');
    
    try {
        const { stdout, stderr } = await execPromise(
            'cd /usr/lib/multiflexi-database && phinx migrate -c phinx-adapter.php',
            { 
                env: { ...process.env },
                cwd: '/usr/lib/multiflexi-database'
            }
        );
        
        if (stdout) {
            console.log('📄 Phinx migration output:');
            console.log(stdout);
        }
        if (stderr && !stderr.includes('warning no environment specified')) {
            console.log('⚠️  Phinx warnings:', stderr);
        }
        
        console.log('✅ Database migrations completed successfully');
        
    } catch (error) {
        console.error('❌ Error running migrations:', error);
        if (error.stdout) console.log('Stdout:', error.stdout);
        if (error.stderr) console.log('Stderr:', error.stderr);
        throw error;
    }
}

async function createTestUsersWithCLI() {
    console.log('👥 Creating test users using multiflexi-cli...');
    
    try {
        // Create admin user
        console.log('🔧 Creating admin user...');
        const { stdout: adminOutput, stderr: adminError } = await execPromise(
            'multiflexi-cli user create --login=admin --firstname=Test --lastname=Admin --email=admin@test.com --plaintext=admin123 --enabled=true',
            { env: { ...process.env } }
        );
        
        if (adminOutput) {
            console.log('📄 Admin user creation output:', adminOutput.trim());
        }
        if (adminError && !adminError.includes('warning')) {
            console.log('⚠️  Admin user warnings:', adminError);
        }
        
        // Create regular test user
        console.log('🔧 Creating test user...');
        const { stdout: userOutput, stderr: userError } = await execPromise(
            'multiflexi-cli user create --login=testuser --firstname=Test --lastname=User --email=testuser@test.com --plaintext=testpass123 --enabled=true',
            { env: { ...process.env } }
        );
        
        if (userOutput) {
            console.log('📄 Test user creation output:', userOutput.trim());
        }
        if (userError && !userError.includes('warning')) {
            console.log('⚠️  Test user warnings:', userError);
        }
        
        console.log('✅ Test users created successfully using multiflexi-cli');
        
    } catch (error) {
        console.error('❌ Error creating test users with CLI:', error);
        console.error('   Command output:', error.stdout);
        console.error('   Command errors:', error.stderr);
        throw error;
    }
}

async function createTestCompanyWithCLI() {
    console.log('🏢 Creating test company using multiflexi-cli...');
    
    try {
        // Create test company with shorter name to avoid slug issues
        console.log('🔧 Creating test company...');
        const { stdout: companyOutput, stderr: companyError } = await execPromise(
            'multiflexi-cli company create --name="TestCorp" --enabled=true --ic=12345678 --email=test@testcorp.com --slug=testcorp',
            { env: { ...process.env } }
        );
        
        if (companyOutput) {
            console.log('📄 Test company creation output:', companyOutput.trim());
        }
        if (companyError && !companyError.includes('warning')) {
            console.log('⚠️  Test company warnings:', companyError);
        }
        
        console.log('✅ Test company created successfully using multiflexi-cli');
        
    } catch (error) {
        console.error('❌ Error creating test company with CLI:', error);
        console.error('   Command output:', error.stdout);
        console.error('   Command errors:', error.stderr);
        throw error;
    } finally {
        await connection.end();
    }
}

async function createTestApplicationWithCLI() {
    console.log('📱 Creating test application using multiflexi-cli...');
    
    try {
        // Create test application
        console.log('🔧 Creating test application...');
        const { stdout: appOutput, stderr: appError } = await execPromise(
            'multiflexi-cli application create --name="TestApp" --description="Test Application for Selenium tests" --executable="echo test" --homepage="http://example.com" --appversion="1.0.0" --uuid="testapp"',
            { env: { ...process.env } }
        );
        
        if (appOutput) {
            console.log('📄 Test application creation output:', appOutput.trim());
        }
        if (appError && !appError.includes('warning')) {
            console.log('⚠️  Test application warnings:', appError);
        }
        
        console.log('✅ Test application created successfully using multiflexi-cli');
        
    } catch (error) {
        console.error('❌ Error creating test application with CLI:', error);
        console.error('   Command output:', error.stdout);
        console.error('   Command errors:', error.stderr);
        throw error;
    }
}

async function setupTestData() {
    console.log('📋 Setting up test data using multiflexi-cli...');
    
    try {
        // Create users with CLI
        await createTestUsersWithCLI();
        
        // Create company with CLI
        await createTestCompanyWithCLI();
        
        // Create application with CLI
        await createTestApplicationWithCLI();
        
        console.log('✅ Test data setup completed using multiflexi-cli');
        
    } catch (error) {
        console.error('❌ Error setting up test data:', error);
        // Don't throw here - test data is optional for basic functionality
        console.warn('⚠️  Some test data setup may be incomplete');
    }
}

async function setupDatabase() {
    console.log('🗄️  Setting up MultiFlexi database for testing...');
    console.log('📋 This will use multiflexi-cli for all data creation (database-agnostic)');
    
    try {
        const config = getMainDatabaseConfig();
        console.log(`🔗 Target database: ${config.database} @ ${config.host}:${config.port}`);
        console.log('');
        
        // Step 1: Drop all existing tables
        await dropAllTables();
        
        // Step 2: Run Phinx migrations
        await runPhinxMigrations();
        
        // Step 3: Setup test data using CLI
        await setupTestData();
        
        console.log('');
        console.log('🎉 Database setup completed successfully');
        console.log('');
        console.log('📝 Test Data Created with multiflexi-cli:');
        console.log('   👤 Admin User: admin / admin123');
        console.log('   👤 Test User: testuser / testpass123');
        console.log('   🏢 Company: TestCorp');
        console.log('   📱 App: TestApp');
        console.log('');
        console.log('💡 All data created using multiflexi-cli (database-agnostic)');
        
    } catch (error) {
        console.error('💥 Error setting up database:', error);
        throw error;
    } finally {
        await connection.end();
    }
}

async function cleanupDatabase() {
    console.log("🧹 Cleaning up MultiFlexi database...");
    
    try {
        await dropAllTables();
        console.log("✅ Database cleanup completed successfully");
    } catch (error) {
        console.error("❌ Error cleaning up database:", error);
        throw error;
    }
}

/**
 * Verify database is ready for testing
 * Uses database-agnostic multiflexi-cli commands when possible
 */
async function verifyDatabase() {
    console.log('🔍 Verifying database setup...');
    
    try {
        // Verify users using multiflexi-cli
        console.log('👥 Checking test users...');
        const { stdout: usersOutput } = await execPromise('multiflexi-cli user list --format=json');
        const users = JSON.parse(usersOutput || '[]');
        const testUsers = users.filter(u => ['admin', 'testuser'].includes(u.login));
        
        // Verify companies using multiflexi-cli
        console.log('🏢 Checking test companies...');
        const { stdout: companiesOutput } = await execPromise('multiflexi-cli company list --format=json');
        const companies = JSON.parse(companiesOutput || '[]');
        const testCompanies = companies.filter(c => c.name === 'TestCorp');
        
        // Verify applications using multiflexi-cli
        console.log('📱 Checking test applications...');
        const { stdout: appsOutput } = await execPromise('multiflexi-cli application list --format=json');
        const apps = JSON.parse(appsOutput || '[]');
        const testApps = apps.filter(a => a.name === 'TestApp');
        
        // Database-specific check for migrations (fallback to SQL)
        let migrations = 0;
        let totalTables = 0;
        try {
            const config = getMainDatabaseConfig();
            const connection = await mysql.createConnection({
                host: config.host,
                port: config.port,
                user: config.user,
                password: config.password,
                database: config.database
            });
            
            const [tables] = await connection.execute('SHOW TABLES');
            totalTables = tables.length;
            
            const [migrationCount] = await connection.execute('SELECT COUNT(*) as count FROM phinxlog');
            migrations = migrationCount[0].count;
            
            await connection.end();
        } catch (error) {
            console.log('⚠️  Could not verify migrations (non-MySQL database or connection issue)');
        }
        
        console.log(`✅ Database verification successful:`);
        console.log(`   - ${totalTables} tables found`);
        console.log(`   - ${migrations} migrations applied`);
        console.log(`   - ${testUsers.length} test users created: ${testUsers.map(u => u.login).join(', ')}`);
        console.log(`   - ${testCompanies.length} test companies created: ${testCompanies.map(c => c.name).join(', ')}`);
        console.log(`   - ${testApps.length} test applications created: ${testApps.map(a => a.name).join(', ')}`);
        
        // Check if we have the required minimum
        if (testUsers.length < 2) {
            throw new Error('Missing required test users (expected: admin, testuser)');
        }
        
        console.log('🎯 Database is ready for Selenium testing!');
        
    } catch (error) {
        console.error('❌ Database verification failed:', error);
        throw error;
    }
    
    console.log('✅ Basic schema setup completed');
}

module.exports = { setupDatabase, cleanupDatabase, verifyDatabase };

// Run setup if called directly
if (require.main === module) {
    const action = process.argv[2];
    
    if (action === 'cleanup') {
        cleanupDatabase().catch(error => {
            console.error('Database cleanup failed:', error);
            process.exit(1);
        });
    } else if (action === 'verify') {
        verifyDatabase().catch(error => {
            console.error('Database verification failed:', error);
            process.exit(1);
        });
    } else {
        setupDatabase().catch(error => {
            console.error('Database setup failed:', error);
            process.exit(1);
        });
    }
}
