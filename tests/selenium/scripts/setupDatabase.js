const mysql = require('mysql2/promise');
const EnvironmentManager = require('../src/EnvironmentManager');

/**
 * Database setup script for MultiFlexi Selenium tests
 * This script:
 * 1. Creates a clean test database
 * 2. Applies all migrations to get the database to initial state
 * 3. Sets up basic configuration
 */

async function setupDatabase() {
    const envManager = EnvironmentManager.getInstance();
    const config = envManager.getConfig();
    const envInfo = envManager.getEnvironmentInfo();
    
    console.log(`🗄️ Setting up MultiFlexi test database for environment: ${envInfo.name}`);
    console.log(`📋 Description: ${envInfo.description}`);
    console.log(`🌐 Base URL: ${envInfo.baseUrl}`);
    
    const connection = await mysql.createConnection({
        host: config.database.host,
        port: config.database.port,
        user: config.database.user,
        password: config.database.password || ''
    });

    try {
        // Create test database
        const dbName = config.database.name;
        console.log(`🗄️ Creating database: ${dbName}`);
        await connection.execute(`DROP DATABASE IF EXISTS ${dbName}`);
        await connection.execute(`CREATE DATABASE ${dbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`);
        console.log(`✅ Database ${dbName} created successfully`);

        // Switch to test database
        await connection.execute(`USE ${dbName}`);

        // Only run migrations for local/development environments
        if (envManager.isLocalEnvironment() || envManager.isDevelopmentEnvironment()) {
            // Run migrations using Phinx
            const { exec } = require('child_process');
            const util = require('util');
            const execPromise = util.promisify(exec);
            
            const phinxConfigPath = '../../../phinx-adapter.php';
            
            console.log('🔄 Running database migrations...');
            try {
                const { stdout, stderr } = await execPromise(
                    `cd ../../.. && vendor/bin/phinx migrate -c ${phinxConfigPath} -e testing`,
                    { env: { ...process.env, DB_DATABASE: dbName } }
                );
                
                if (stdout) console.log('📄 Phinx output:', stdout);
                if (stderr) console.log('⚠️  Phinx warnings:', stderr);
                
                console.log('✅ Database migrations completed');
            } catch (error) {
                console.log('⚠️ Migration failed, using basic schema setup...');
                await setupBasicSchema(connection);
            }
        } else {
            console.log('📋 Staging environment detected - using basic schema setup');
            await setupBasicSchema(connection);
        }
        
        if (stderr) {
            console.warn('Migration warnings:', stderr);
        }
        console.log('✓ Migrations completed');

        // Insert basic test data
        console.log('Inserting basic test data...');
        
        // Insert test application
        await connection.execute(`
            INSERT INTO applications (name, description, executable, homepage, version, enabled)
            VALUES ('TestApp', 'Test Application for Selenium tests', 'echo', 'http://example.com', '1.0.0', 1)
        `);

        console.log('✓ Test database setup completed successfully');
        
    } catch (error) {
        console.error('Error setting up database:', error);
        throw error;
    } finally {
        await connection.end();
    }
}

async function cleanupDatabase() {
    console.log('Cleaning up MultiFlexi test database...');
    
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST || 'localhost',
        port: process.env.DB_PORT || 3306,
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || ''
    });

    try {
        const dbName = process.env.DB_NAME || 'multiflexi_test';
        await connection.execute(`DROP DATABASE IF EXISTS ${dbName}`);
        console.log('✓ Test database cleaned up');
    } catch (error) {
        console.error('Error cleaning up database:', error);
        throw error;
    } finally {
        await connection.end();
    }
}

/**
 * Setup basic schema for environments without Phinx
 */
async function setupBasicSchema(connection) {
    console.log('🔧 Setting up basic database schema...');
    
    const basicTables = [
        `CREATE TABLE IF NOT EXISTS companies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            ico VARCHAR(20),
            enabled BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )`,
        
        `CREATE TABLE IF NOT EXISTS credentials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            type VARCHAR(100) NOT NULL,
            url TEXT,
            login VARCHAR(255),
            password VARCHAR(255),
            enabled BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )`,
        
        `CREATE TABLE IF NOT EXISTS applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            executable VARCHAR(255),
            description TEXT,
            homepage VARCHAR(500),
            version VARCHAR(50),
            enabled BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )`,
        
        `CREATE TABLE IF NOT EXISTS runtemplates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            application_id INT,
            schedule_type ENUM('manual', 'interval', 'cron') DEFAULT 'manual',
            interval_value INT DEFAULT 60,
            interval_unit ENUM('minutes', 'hours', 'days') DEFAULT 'minutes',
            enabled BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (application_id) REFERENCES applications(id)
        )`,
        
        `CREATE TABLE IF NOT EXISTS jobs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            runtemplate_id INT,
            status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
            output TEXT,
            error_message TEXT,
            started_at TIMESTAMP NULL,
            completed_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (runtemplate_id) REFERENCES runtemplates(id)
        )`,
        
        `CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            is_admin BOOLEAN DEFAULT FALSE,
            enabled BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )`
    ];
    
    for (const table of basicTables) {
        await connection.execute(table);
    }
    
    console.log('✅ Basic schema setup completed');
}

module.exports = { setupDatabase, cleanupDatabase };

// Run setup if called directly
if (require.main === module) {
    const action = process.argv[2];
    if (action === 'cleanup') {
        cleanupDatabase().catch(console.error);
    } else {
        setupDatabase().catch(console.error);
    }
}