#!/usr/bin/env node
/**
 * MultiFlexi Test Configuration Manager
 * 
 * Helps manage configuration files for different environments:
 * - Development: /home/vitex/Projects/Multi/MultiFlexi/.env
 * - Local (package): /etc/multiflexi/multiflexi.env  
 * - Test: tests/selenium/.env
 */

const fs = require('fs');
const path = require('path');

class ConfigManager {
    constructor() {
        this.paths = {
            development: '/home/vitex/Projects/Multi/MultiFlexi/.env',
            package: '/etc/multiflexi/multiflexi.env',
            test: path.join(__dirname, '../.env'),
            testExample: path.join(__dirname, '../.env.example')
        };
    }

    checkConfigFiles() {
        console.log('🔍 CONFIGURATION FILES CHECK');
        console.log('='.repeat(50));
        
        Object.entries(this.paths).forEach(([name, filePath]) => {
            const exists = fs.existsSync(filePath);
            const icon = exists ? '✅' : '❌';
            const status = exists ? 'EXISTS' : 'MISSING';
            
            console.log(`${icon} ${name.toUpperCase()}: ${status}`);
            console.log(`   📂 ${filePath}`);
            
            if (exists) {
                try {
                    const stats = fs.statSync(filePath);
                    console.log(`   📅 Modified: ${stats.mtime.toLocaleString()}`);
                    console.log(`   📏 Size: ${stats.size} bytes`);
                } catch (error) {
                    console.log(`   ⚠️ Cannot read file stats: ${error.message}`);
                }
            }
            console.log();
        });
    }

    readConfig(environment) {
        let configPath;
        
        switch(environment) {
            case 'development':
                configPath = this.paths.development;
                break;
            case 'local':
                configPath = this.paths.package;
                break;
            case 'test':
                configPath = this.paths.test;
                break;
            default:
                console.error(`❌ Unknown environment: ${environment}`);
                return null;
        }
        
        if (!fs.existsSync(configPath)) {
            console.error(`❌ Config file not found: ${configPath}`);
            return null;
        }
        
        try {
            const content = fs.readFileSync(configPath, 'utf8');
            const config = this.parseConfigContent(content);
            
            // If development, also load and merge mapped application config
            if (environment === 'development') {
                const appConfig = this.mapApplicationConfigForValidation(config);
                Object.assign(config, appConfig);
            }
            
            console.log(`✅ Config loaded from: ${configPath}`);
            return config;
        } catch (error) {
            console.error(`❌ Error reading config: ${error.message}`);
            return null;
        }
    }

    mapApplicationConfigForValidation(appConfig) {
        // Map application config to test variables for validation
        const mapped = {};
        
        if (appConfig.DB_HOST) mapped.DEVELOPMENT_DB_HOST = appConfig.DB_HOST;
        if (appConfig.DB_DATABASE) mapped.DEVELOPMENT_DB_NAME = appConfig.DB_DATABASE + '_test';
        if (appConfig.DB_USERNAME) mapped.DEVELOPMENT_DB_USER = appConfig.DB_USERNAME;
        if (appConfig.DB_PASSWORD) mapped.DEVELOPMENT_DB_PASS = appConfig.DB_PASSWORD;
        if (appConfig.DB_PORT) mapped.DEVELOPMENT_DB_PORT = appConfig.DB_PORT;
        
        // Always set default base URL for development
        mapped.DEVELOPMENT_BASE_URL = 'http://localhost/MultiFlexi/src/';
        
        return mapped;
    }

    parseConfigContent(content) {
        const config = {};
        
        content.split('\n').forEach((line, index) => {
            const trimmed = line.trim();
            if (trimmed && !trimmed.startsWith('#')) {
                const equalIndex = trimmed.indexOf('=');
                if (equalIndex > 0) {
                    const key = trimmed.substring(0, equalIndex).trim();
                    const value = trimmed.substring(equalIndex + 1).trim().replace(/^["']|["']$/g, '');
                    config[key] = value;
                }
            }
        });
        
        return config;
    }

    createTestConfig(environment = 'development') {
        const testConfigPath = this.paths.test;
        
        if (fs.existsSync(testConfigPath)) {
            console.log(`⚠️ Test config already exists: ${testConfigPath}`);
            return false;
        }
        
        try {
            // Copy from example
            const exampleContent = fs.readFileSync(this.paths.testExample, 'utf8');
            let content = exampleContent.replace('TEST_ENVIRONMENT=local', `TEST_ENVIRONMENT=${environment}`);
            
            // Enable debug for development
            if (environment === 'development') {
                content = content.replace('DEBUG=false', 'DEBUG=true');
                content = content.replace('HEADLESS=false', 'HEADLESS=false');
            }
            
            fs.writeFileSync(testConfigPath, content);
            console.log(`✅ Test config created: ${testConfigPath}`);
            console.log(`🎯 Environment set to: ${environment}`);
            return true;
        } catch (error) {
            console.error(`❌ Error creating config: ${error.message}`);
            return false;
        }
    }

    showEnvironmentInfo() {
        console.log('🌍 MULTIFLEXI ENVIRONMENTS');
        console.log('='.repeat(50));
        console.log('🔧 DEVELOPMENT - Source Code');
        console.log('   📂 /home/vitex/Projects/Multi/MultiFlexi/src/');
        console.log('   ⚙️ /home/vitex/Projects/Multi/MultiFlexi/.env');
        console.log('   🌐 http://localhost/MultiFlexi/src/');
        console.log();
        console.log('📦 LOCAL - Installed Package');
        console.log('   📂 /usr/share/multiflexi/');
        console.log('   ⚙️ /etc/multiflexi/multiflexi.env');
        console.log('   🌐 http://localhost/multiflexi/');
        console.log();
        console.log('☁️ STAGING - Testing Server');
        console.log('   🌐 https://vyvojar.spoje.net/multiflexi/');
        console.log();
    }

    validateEnvironmentConfig(environment) {
        console.log(`🔍 CONFIGURATION VALIDATION: ${environment.toUpperCase()}`);
        console.log('='.repeat(50));
        
        const config = this.readConfig(environment);
        if (!config) {
            return false;
        }
        
        const required = {
            development: ['DEVELOPMENT_BASE_URL', 'DEVELOPMENT_DB_HOST', 'DEVELOPMENT_DB_NAME'],
            local: ['LOCAL_BASE_URL', 'LOCAL_DB_HOST', 'LOCAL_DB_NAME'],
            staging: ['STAGING_BASE_URL', 'STAGING_DB_HOST', 'STAGING_DB_NAME']
        };
        
        const requiredKeys = required[environment] || [];
        let valid = true;
        
        requiredKeys.forEach(key => {
            if (config[key]) {
                console.log(`✅ ${key}: ${config[key]}`);
            } else {
                console.log(`❌ ${key}: MISSING`);
                valid = false;
            }
        });
        
        return valid;
    }
}

// CLI interface
if (require.main === module) {
    const manager = new ConfigManager();
    const command = process.argv[2];
    
    switch (command) {
        case 'check':
            manager.checkConfigFiles();
            break;
            
        case 'read':
            const env = process.argv[3] || 'development';
            const config = manager.readConfig(env);
            if (config) {
                console.log(JSON.stringify(config, null, 2));
            }
            break;
            
        case 'create':
            const targetEnv = process.argv[3] || 'development';
            manager.createTestConfig(targetEnv);
            break;
            
        case 'info':
            manager.showEnvironmentInfo();
            break;
            
        case 'validate':
            const validateEnv = process.argv[3] || 'development';
            manager.validateEnvironmentConfig(validateEnv);
            break;
            
        default:
            console.log('🛠️ MultiFlexi Config Manager');
            console.log('Usage:');
            console.log('  node config-manager.js check                    # Zkontrolovat všechny config soubory');
            console.log('  node config-manager.js read [environment]       # Přečíst config pro prostředí');
            console.log('  node config-manager.js create [environment]     # Vytvořit test config');
            console.log('  node config-manager.js info                     # Zobrazit info o prostředích');
            console.log('  node config-manager.js validate [environment]   # Validovat config');
            console.log();
            console.log('Environments: development, local, staging');
    }
}

module.exports = ConfigManager;