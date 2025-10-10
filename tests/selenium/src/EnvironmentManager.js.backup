const dotenv = require('dotenv');
const path = require('path');
const fs = require('fs');

class EnvironmentManager {
    constructor() {
        // Load appropriate config file based on environment
        this.loadConfigFile();
        
        this.currentEnvironment = process.env.TEST_ENVIRONMENT || 'local';
        this.supportedEnvironments = ['development', 'local', 'staging'];
        
        this.validateEnvironment();
        this.loadEnvironmentConfig();
    }

    loadConfigFile() {
        // Determine which config file to load
        const testConfigPath = path.join(__dirname, '../.env');
        const devProjectPath = '/home/vitex/Projects/Multi/MultiFlexi/.env';
        const packageConfigPath = '/etc/multiflexi/multiflexi.env';
        
        let configPath = testConfigPath;
        let configSource = 'test';
        
        // First, load test-specific config 
        if (fs.existsSync(testConfigPath)) {
            try {
                dotenv.config({ path: testConfigPath });
                configPath = testConfigPath;
                configSource = 'test';
            } catch (error) {
                console.warn(`⚠️ Cannot read test config: ${error.message}`);
            }
        }
        
        // Check if we're in development mode and have project .env
        if (fs.existsSync(devProjectPath)) {
            try {
                const appConfig = this.parseConfigFile(devProjectPath);
                // Map application config to test environment variables if needed
                this.mapApplicationConfigToTest(appConfig);
                configSource = 'development';
                
                console.log(`🔧 Loaded development application config from: ${devProjectPath}`);
            } catch (error) {
                console.warn(`⚠️ Cannot read development config: ${error.message}`);
            }
        }
        
        // For local environment, also try to read package config
        if (process.env.TEST_ENVIRONMENT === 'local' && fs.existsSync(packageConfigPath)) {
            try {
                // Read package config and merge with environment
                const packageConfig = this.parseConfigFile(packageConfigPath);
                Object.assign(process.env, packageConfig);
                console.log(`📦 Loaded package config from: ${packageConfigPath}`);
            } catch (error) {
                console.warn(`⚠️ Cannot read package config: ${error.message}`);
            }
        }
        
        this.configPath = configPath;
        this.configSource = configSource;
        
        console.log(`📋 Config loaded from: ${configPath} (source: ${configSource})`);
    }
    
    parseConfigFile(filePath) {
        const content = fs.readFileSync(filePath, 'utf8');
        const config = {};
        
        content.split('\n').forEach(line => {
            const trimmed = line.trim();
            if (trimmed && !trimmed.startsWith('#')) {
                const [key, ...valueParts] = trimmed.split('=');
                if (key && valueParts.length > 0) {
                    config[key.trim()] = valueParts.join('=').trim().replace(/^["']|["']$/g, '');
                }
            }
        });
        
        return config;
    }

    mapApplicationConfigToTest(appConfig) {
        // Map MultiFlexi application config to test environment variables
        const mapping = {
            // Database mapping - corrected to match actual .env keys
            'DB_HOST': 'DEVELOPMENT_DB_HOST',
            'DB_DATABASE': 'DEVELOPMENT_DB_NAME', 
            'DB_USERNAME': 'DEVELOPMENT_DB_USER',  // Fixed: was DB_USERNAME
            'DB_PASSWORD': 'DEVELOPMENT_DB_PASS',  // Fixed: was DB_PASSWORD  
            'DB_PORT': 'DEVELOPMENT_DB_PORT'
        };
        
        // Apply mappings
        Object.entries(mapping).forEach(([appKey, testKey]) => {
            if (appConfig[appKey] && !process.env[testKey]) {
                process.env[testKey] = appConfig[appKey];
                console.log(`🔗 Mapped ${appKey}=${appConfig[appKey]} to ${testKey}`);
            }
        });
        
        // Set default base URL for development if not already set
        if (!process.env.DEVELOPMENT_BASE_URL) {
            process.env.DEVELOPMENT_BASE_URL = 'http://localhost/MultiFlexi/src/';
        }
        
        // Set database name with test suffix if needed
        if (process.env.DEVELOPMENT_DB_NAME && !process.env.DEVELOPMENT_DB_NAME.includes('test')) {
            process.env.DEVELOPMENT_DB_NAME = process.env.DEVELOPMENT_DB_NAME + '_test';
        }
        
        // Also set fallback values for compatibility
        if (appConfig['DB_HOST']) process.env.DB_HOST = appConfig['DB_HOST'];
        if (appConfig['DB_USERNAME']) process.env.DB_USER = appConfig['DB_USERNAME'];
        if (appConfig['DB_PASSWORD']) process.env.DB_PASSWORD = appConfig['DB_PASSWORD'];
        if (appConfig['DB_PORT']) process.env.DB_PORT = appConfig['DB_PORT'];
        
        // Don't override TEST_ENVIRONMENT if it's explicitly set
        // This allows local environment to use package URLs while still having dev config available
    }

    validateEnvironment() {
        if (!this.supportedEnvironments.includes(this.currentEnvironment)) {
            throw new Error(
                `Unsupported environment: ${this.currentEnvironment}. ` +
                `Supported environments: ${this.supportedEnvironments.join(', ')}`
            );
        }
    }

    loadEnvironmentConfig() {
        const envPrefix = this.currentEnvironment.toUpperCase();
        
        // Set default URLs based on environment  
        const defaultUrls = {
            development: 'http://localhost/MultiFlexi/src/',
            local: 'http://localhost/multiflexi/',
            staging: 'https://vyvojar.spoje.net/multiflexi/'
        };
        
        this.config = {
            environment: this.currentEnvironment,
            baseUrl: process.env[`${envPrefix}_BASE_URL`] || process.env.BASE_URL || defaultUrls[this.currentEnvironment],
            database: {
                host: process.env[`${envPrefix}_DB_HOST`] || process.env.DB_HOST || 'localhost',
                port: parseInt(process.env[`${envPrefix}_DB_PORT`] || process.env.DB_PORT) || 3306,
                name: process.env[`${envPrefix}_DB_NAME`] || process.env.DB_NAME || `multiflexi_${this.currentEnvironment}_test`,
                user: process.env[`${envPrefix}_DB_USER`] || process.env.DB_USER || 'root',
                password: process.env[`${envPrefix}_DB_PASS`] || process.env.DB_PASSWORD || process.env.DB_PASS || ''
            },
            admin: {
                username: process.env.ADMIN_USERNAME || 'admin',
                password: process.env.ADMIN_PASSWORD || 'admin123',
                email: process.env.ADMIN_EMAIL || 'admin@multiflexi.test'
            },
            browser: {
                headless: process.env.HEADLESS === 'true',
                timeout: parseInt(process.env.BROWSER_TIMEOUT) || 30000,
                pageLoadTimeout: parseInt(process.env.PAGE_LOAD_TIMEOUT) || 60000,
                implicitWait: parseInt(process.env.IMPLICIT_WAIT) || 10000
            },
            test: {
                timeout: parseInt(process.env.TEST_TIMEOUT) || 300000,
                scenarioTimeout: parseInt(process.env.SCENARIO_TIMEOUT) || 600000,
                retryAttempts: parseInt(process.env.RETRY_ATTEMPTS) || 3,
                retryDelay: parseInt(process.env.RETRY_DELAY) || 2000
            },
            debug: {
                enabled: process.env.DEBUG === 'true',
                screenshotOnFailure: process.env.SCREENSHOT_ON_FAILURE !== 'false',
                saveBrowserLogs: process.env.SAVE_BROWSER_LOGS !== 'false',
                verboseLogging: process.env.VERBOSE_LOGGING === 'true'
            },
            testData: {
                cleanupAfterTest: process.env.CLEANUP_AFTER_TEST !== 'false',
                preserveTestData: process.env.PRESERVE_TEST_DATA === 'true',
                useRealAbraFlexi: process.env.USE_REAL_ABRAFLEXI === 'true',
                companyName: process.env.TEST_COMPANY_NAME || 'Test Company',
                appName: process.env.TEST_APP_NAME || 'TestApp',
                runTemplateName: process.env.TEST_RUNTEMPLATE_NAME || 'Test RunTemplate'
            }
        };

        this.validateConfig();
    }

    validateConfig() {
        const required = ['baseUrl', 'database.host', 'database.name', 'database.user'];
        const missing = [];

        required.forEach(key => {
            const value = this.getNestedValue(this.config, key);
            if (!value) {
                // Map config keys to correct environment variable names
                const keyMapping = {
                    'baseUrl': 'BASE_URL',
                    'database.host': 'DB_HOST', 
                    'database.name': 'DB_NAME',
                    'database.user': 'DB_USER',
                    'database.password': 'DB_PASS'
                };
                const envKey = keyMapping[key] || key.toUpperCase().replace('.', '_');
                missing.push(`${this.currentEnvironment.toUpperCase()}_${envKey}`);
            }
        });

        if (missing.length > 0) {
            console.warn(`⚠️ Missing config for ${this.currentEnvironment}: ${missing.join(', ')}`);
            console.warn(`   Using fallback values or test config overrides`);
            
            // For simple smoke tests, don't fail on missing database config
            const hasBaseUrl = this.config.baseUrl;
            if (!hasBaseUrl) {
                throw new Error(`Missing base URL for environment: ${this.currentEnvironment}`);
            }
        }
    }

    getNestedValue(obj, path) {
        return path.split('.').reduce((current, key) => current && current[key], obj);
    }

    getConfig() {
        return this.config;
    }

    getEnvironment() {
        return this.currentEnvironment;
    }

    getSupportedEnvironments() {
        return this.supportedEnvironments;
    }

    isLocalEnvironment() {
        return this.currentEnvironment === 'local';
    }

    isDevelopmentEnvironment() {
        return this.currentEnvironment === 'development';
    }

    isStagingEnvironment() {
        return this.currentEnvironment === 'staging';
    }

    getEnvironmentInfo() {
        const environmentDescriptions = {
            development: 'Source code in development',
            local: 'Installed from Debian package',
            staging: 'Testing server'
        };

        return {
            name: this.currentEnvironment,
            description: environmentDescriptions[this.currentEnvironment],
            baseUrl: this.config.baseUrl,
            database: this.config.database.name,
            isLocal: this.isLocalEnvironment(),
            isDevelopment: this.isDevelopmentEnvironment(),
            isStaging: this.isStagingEnvironment()
        };
    }

    logEnvironmentInfo() {
        const info = this.getEnvironmentInfo();
        console.log('🌍 ENVIRONMENT INFO:');
        console.log('='.repeat(50));
        console.log(`📋 Environment: ${info.name}`);
        console.log(`📝 Description: ${info.description}`);
        console.log(`🌐 Base URL: ${info.baseUrl}`);
        console.log(`🗄️ Database: ${this.config.database.name} @ ${this.config.database.host}`);
        console.log(`⚙️ Config source: ${this.configSource} (${this.configPath})`);
        console.log(`🔧 Debug mode: ${this.config.debug.enabled ? 'ON' : 'OFF'}`);
        console.log(`👤 Headless: ${this.config.browser.headless ? 'YES' : 'NO'}`);
        console.log('='.repeat(50));
    }

    // Static method for easy instance creation
    static getInstance() {
        if (!this.instance) {
            this.instance = new EnvironmentManager();
        }
        return this.instance;
    }

    // Method for switching environment at runtime (for testing)
    switchEnvironment(newEnvironment) {
        if (!this.supportedEnvironments.includes(newEnvironment)) {
            throw new Error(`Unsupported environment: ${newEnvironment}`);
        }
        
        this.currentEnvironment = newEnvironment;
        this.loadEnvironmentConfig();
        console.log(`🔄 Switched to environment: ${newEnvironment}`);
    }
}

module.exports = EnvironmentManager;
