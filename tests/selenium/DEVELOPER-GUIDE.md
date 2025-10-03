# MultiFlexi Selenium Test Suite - Developer Guide

## 🎯 Overview

This comprehensive guide covers the MultiFlexi Selenium test suite, which provides automated end-to-end testing for the MultiFlexi web application with complete international support.

## 🌍 Internationalization

**Important:** All test components have been internationalized to English for worldwide use of MultiFlexi.

### Language Standards
- **Console Messages**: All in English  
- **Test Descriptions**: English only
- **Error Messages**: English localization
- **Documentation**: English-first approach
- **Comments**: English throughout codebase

## 🚀 Quick Start

### 1. Prerequisites
```bash
# Required software
- Node.js 16+ and npm
- Google Chrome/Chromium browser  
- ChromeDriver (compatible version)
- MySQL server
- MultiFlexi application running
```

### 2. Installation  
```bash
cd tests/selenium
npm install
```

### 3. Configuration
```bash
cp .env.example .env
# Edit .env with your settings
```

### 4. Run Tests
```bash
# Development environment (source code)
npm run dev:simple        # Quick smoke test (1 min)
npm run dev:full          # Complete test (2 min)  
npm run dev:scenarios     # All business scenarios (15 min)

# Local environment (Debian package)
npm run local:simple      # Quick smoke test
npm run local:full        # Complete test

# Multi-environment testing
./run-multi-env.sh        # Interactive multi-env runner
```

## 📁 Project Structure

```
tests/selenium/
├── src/                      # Page Object Model classes
│   ├── AuthPage.js          # Authentication handling
│   ├── DashboardPage.js     # Dashboard interactions  
│   ├── CompanyPage.js       # Company management
│   ├── RunTemplatePage.js   # RunTemplate operations
│   ├── JobPage.js           # Job monitoring
│   └── EnvironmentManager.js # Multi-env configuration
├── tests/                    # Test files
│   ├── simple-smoke.test.js  # Quick frontend-only test
│   ├── smoke-test.test.js    # Full smoke test with DB
│   ├── scenario-*.test.js    # Business scenario tests
│   └── pages/               # Individual page tests
├── config/                   # Configuration utilities
│   └── config-manager.js    # Environment config management
├── run-*.sh                 # Interactive test runners
└── docs/                    # Documentation
```

## 🧪 Test Types

### 1. Simple Smoke Test (`simple-smoke.test.js`)
- **Purpose**: Quick frontend validation without database
- **Runtime**: ~1 minute  
- **Use Case**: Rapid development validation
- **Coverage**: Homepage, forms, navigation, responsive design

```bash
npm run dev:simple    # Development environment
npm run local:simple  # Local package environment
```

### 2. Full Smoke Test (`smoke-test.test.js`)  
- **Purpose**: Complete system validation with database
- **Runtime**: ~2 minutes
- **Use Case**: Pre-deployment verification
- **Coverage**: Full user workflow, database operations

```bash
npm run test:smoke
```

### 3. Business Scenarios
Comprehensive real-world workflow testing:

#### AbraFlexi Complete Workflow (`scenario-abraflexi-workflow.test.js`)
- **Purpose**: Complete company setup with AbraFlexi integration
- **Runtime**: ~5 minutes
- **Coverage**: Company creation, credentials, RunTemplate, job execution

#### Multi-Company Setup (`scenario-multi-company.test.js`)  
- **Purpose**: Multiple company management testing
- **Runtime**: ~4 minutes
- **Coverage**: Company isolation, concurrent jobs, data separation

#### Job Error Recovery (`scenario-error-recovery.test.js`)
- **Purpose**: System robustness during failures  
- **Runtime**: ~5 minutes  
- **Coverage**: Error handling, retry mechanisms, recovery workflows

## 🌐 Multi-Environment Support

The test suite supports three environments:

### 1. Development Environment
- **URL**: `http://localhost/MultiFlexi/src/`
- **Purpose**: Source code in development
- **Database**: `multiflexi_dev_test`
- **Runner**: `./run-dev.sh`

### 2. Local Environment  
- **URL**: `http://localhost/multiflexi/`
- **Purpose**: Installed Debian package testing
- **Database**: `multiflexi_local_test`  
- **Runner**: `./run-local.sh`

### 3. Staging Environment
- **URL**: `https://vyvojar.spoje.net/multiflexi/`
- **Purpose**: Remote testing server
- **Database**: `multiflexi_staging_test`
- **Runner**: `./run-multi-env.sh`

## ⚙️ Configuration Management

### Environment Configuration (`.env`)
```bash
# Environment selection
TEST_ENVIRONMENT=development

# Development Environment - Source code in development  
DEVELOPMENT_BASE_URL=http://localhost/MultiFlexi/src/
DEVELOPMENT_DB_HOST=localhost
DEVELOPMENT_DB_NAME=multiflexi_dev_test

# Local Environment - Installed package
LOCAL_BASE_URL=http://localhost/multiflexi/
LOCAL_DB_HOST=localhost  
LOCAL_DB_NAME=multiflexi_local_test

# Staging Environment - Testing server
STAGING_BASE_URL=https://vyvojar.spoje.net/multiflexi/
STAGING_DB_HOST=vyvojar.spoje.net
STAGING_DB_NAME=multiflexi_staging_test
```

### Dynamic Configuration Loading
The `EnvironmentManager` class automatically:
- Detects current environment
- Loads appropriate configuration
- Validates connectivity  
- Provides runtime environment info

## 🔧 Development Workflows

### Adding New Tests
1. Create test file in `tests/` directory
2. Follow Page Object Model pattern
3. Use English localization throughout
4. Add to appropriate npm script in `package.json`
5. Update documentation

### Page Object Model Guidelines  
```javascript
// Example: NewFeaturePage.js
const BasePage = require('./BasePage');

class NewFeaturePage extends BasePage {
    constructor() {
        super();
        // English selectors and messages only
        this.selectors = {
            submitButton: '[data-testid="submit-feature"]',
            statusMessage: '.status-message'
        };
    }
    
    async performAction() {
        console.log('🔧 Performing new feature action...');
        // English console messages
    }
}
```

### Error Handling Best Practices
```javascript
try {
    await this.performAction();
    console.log('✅ Action completed successfully');
} catch (error) {
    console.log(`❌ Action failed: ${error.message}`);
    throw error;
}
```

## 📊 Test Execution Modes

### Interactive Runners
```bash
./run-dev.sh         # Development environment menu
./run-local.sh       # Local package environment menu  
./run-multi-env.sh   # Multi-environment selection
./run-scenarios.sh   # Business scenario menu
```

### Direct NPM Scripts
```bash
# Single tests
npm run dev:simple           # Development simple smoke
npm run local:simple         # Local simple smoke  
npm run test:abraflexi      # AbraFlexi workflow
npm run test:multicompany   # Multi-company setup
npm run test:errors         # Error recovery

# Test combinations  
npm run dev:scenarios       # All development scenarios
npm run test:pages         # All page tests
npm run test:all          # Complete test suite
```

### CI/CD Integration
```bash
npm run test:ci       # Optimized for CI pipelines
```

## 🐛 Troubleshooting

### ChromeDriver Issues
```bash
# Check versions
chromium --version
chromedriver --version

# Update ChromeDriver if needed
# Ensure versions are compatible
```

### Database Connection Issues  
```bash
# Verify MySQL service
systemctl status mysql

# Test connection
mysql -h localhost -u root -p multiflexi_dev_test
```

### Environment Configuration Issues
```bash
# Verify .env file exists and is readable
cat .env

# Check environment detection
npm run config:check
```

### Common Error Solutions
- **SessionNotCreatedError**: Update ChromeDriver to match browser version
- **Connection refused**: Ensure MultiFlexi application is running
- **Database errors**: Verify MySQL service and credentials
- **Timeout errors**: Increase timeout values or check network connectivity

## 📈 Performance Optimization

### Test Execution Speed
- Use `simple-smoke` for rapid feedback during development
- Run full scenarios only when needed
- Parallelize independent tests where possible
- Use headless mode for CI: `HEADLESS=true npm test`

### Resource Management  
- Proper WebDriver cleanup in `after()` hooks
- Database connection pooling
- Efficient element waiting strategies

## 🔄 Continuous Integration

### GitHub Actions Example
```yaml
name: MultiFlexi Selenium Tests
on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: '18'
      - run: cd tests/selenium && npm install
      - run: cd tests/selenium && npm run test:ci
```

## 📚 Additional Resources

- [README.md](./README.md) - Basic setup and usage
- [QUICKSTART.md](./QUICKSTART.md) - Quick start guide  
- [MULTI-ENVIRONMENT.md](./MULTI-ENVIRONMENT.md) - Multi-env testing
- [SCENARIOS.md](./SCENARIOS.md) - Business scenario details
- [IMPLEMENTATION-SUMMARY.md](./IMPLEMENTATION-SUMMARY.md) - Implementation details

---

**Note**: This test suite has been fully internationalized to English to support MultiFlexi's ambition to become a globally used program. All messages, comments, and documentation follow English-first standards for international development teams.