# MultiFlexi Selenium Test Suite

This directory contains comprehensive Selenium tests for the MultiFlexi web interface.

## Overview

The test suite provides end-to-end testing of the MultiFlexi web application, including:

- User authentication (registration and login)
- RunTemplate creation and management  
- Job scheduling and execution
- Database setup and cleanup

## 🌍 Internationalization

**Important**: This test suite has been fully internationalized to English to support MultiFlexi's global ambitions.

### Language Standards
- All console messages are in English
- Test descriptions use English terminology  
- Error messages follow English localization
- Documentation maintains English-first approach
- Code comments are written in English

### Multi-Environment Support
The test suite supports three environments with English messaging:
- **Development**: Source code testing (`http://localhost/MultiFlexi/src/`)
- **Local**: Debian package testing (`http://localhost/multiflexi/`) 
- **Staging**: Remote server testing (`https://vyvojar.spoje.net/multiflexi/`)

## Prerequisites

### Software Requirements

- Node.js 16+ and npm
- Google Chrome browser
- MySQL server
- MultiFlexi web application running locally

### Database Setup

The tests require a MySQL database with appropriate permissions. The test user should be able to:
- Create and drop databases
- Run migrations
- Insert and modify data

## Installation

1. Navigate to the test directory:
```bash
cd tests/selenium
```

2. Install dependencies:
```bash
npm install
```

3. Configure environment:
```bash
cp .env.example .env
# Edit .env with your configuration
```

## Configuration

### Config File Locations

The test suite uses different configuration files based on the environment:

- **🔧 Development**: `/home/vitex/Projects/Multi/MultiFlexi/.env` (application config) + `tests/selenium/.env` (test overrides)
- **📦 Local Package**: `/etc/multiflexi/multiflexi.env` (package config) + `tests/selenium/.env` (test config)
- **🧪 Test Only**: `tests/selenium/.env` (standalone test config)

### Config Priority

1. Application `.env` (development only) - provides database and basic settings
2. Test `.env` - can override or supplement application config  
3. Package config - for local (installed) environment
4. Environment variables - highest priority

### Quick Setup

For development testing:
```bash
npm run config:create development
```

For package testing:
```bash  
npm run config:create local
```

Check configuration status:
```bash
npm run config:check
npm run config:info
npm run config:validate development
```

Edit the test `.env` file to match your environment:

```env
# Base URL of your MultiFlexi installation
BASE_URL=http://localhost/MultiFlexi

# Database configuration
DB_HOST=localhost
DB_NAME=multiflexi_test
DB_USER=multiflexi_test
DB_PASSWORD=test_password

# Test configuration
HEADLESS=false          # Set to true for CI/CD
DEBUG=false             # Enable debug output
TIMEOUT=30000           # Test timeout in milliseconds

# Admin credentials for tests
ADMIN_USERNAME=admin
ADMIN_PASSWORD=admin123
ADMIN_EMAIL=admin@example.com
```

## Running Tests

### Quick Environment Runners

**For Development (source code):**
```bash
./run-dev.sh          # Interactive English menu for development testing
npm run simple-smoke   # Quick 1-minute frontend test with English output
```

**For Local Package (installed):**
```bash
./run-local.sh         # Interactive menu for package testing
TEST_ENVIRONMENT=local npm run simple-smoke
```

**For Multi-Environment:**
```bash
./run-multi-env.sh     # Test all 3 environments
```

### Complete Test Suite

Run all tests with database setup and cleanup:
```bash
npm run full-test
```

### Individual Test Categories

Run specific test suites:
```bash
# Quick smoke tests (no database)
npm run simple-smoke

# Full smoke tests (with database)
npm run test:smoke

# Authentication tests only
npm test tests/auth.test.js

# RunTemplate tests only  
npm test tests/runtemplate.test.js

# Business scenarios
npm run test:scenarios
npm run test:abraflexi
npm run test:multicompany
```

### Environment-Specific Testing

```bash
# Development environment tests
npm run dev:smoke
npm run dev:abraflexi
npm run dev:all

# Local package tests
npm run test:local:smoke
npm run test:local:abraflexi

# Staging tests
npm run test:staging:smoke
```

### Headless Mode

For CI/CD or automated testing:
```bash
HEADLESS=true npm run simple-smoke
npm run test:headless
```

### Debug Mode

For development and troubleshooting:
```bash
npm run test:debug
DEBUG=true npm run simple-smoke
```

## Test Structure

### Page Object Model

The tests use Page Object Model (POM) pattern:

- `src/WebDriverHelper.js` - Base WebDriver utilities
- `src/AuthPage.js` - Authentication page interactions
- `src/RunTemplatePage.js` - RunTemplate management interactions

### Test Files

- `tests/auth.test.js` - User authentication tests
- `tests/runtemplate.test.js` - RunTemplate functionality tests
- `tests/multiflexi.e2e.test.js` - Complete end-to-end scenarios

### Support Scripts

- `scripts/setupDatabase.js` - Database initialization and cleanup
- Screenshots are saved to `screenshots/` directory when tests fail

## Test Scenarios

### 1. Complete E2E Test (`multiflexi.e2e.test.js`)

This test performs a complete user journey:

1. **Database Setup**: Creates clean database with migrations
2. **User Registration**: Registers admin account in web interface
3. **Authentication**: Logs in as admin user
4. **RunTemplate Creation**: Creates and configures a new RunTemplate
5. **Job Execution**: Schedules and executes the RunTemplate
6. **Verification**: Checks execution results and system state
7. **Cleanup**: Logs out and cleans database

### 2. Authentication Tests (`auth.test.js`)

Focused testing of user management:
- User registration with validation
- Login/logout functionality
- Error handling for invalid credentials
- Password confirmation validation

### 3. RunTemplate Tests (`runtemplate.test.js`)

Comprehensive RunTemplate testing:
- Basic RunTemplate creation
- Scheduling configuration (intervals, timeouts)
- Environment variable management
- Manual execution
- Template listing and deletion

## Database Management

### Automatic Setup

Tests automatically:
1. Create a clean test database
2. Apply all Phinx migrations
3. Insert basic test data (test application)
4. Clean up after tests complete

### Manual Database Operations

```bash
# Setup test database manually
npm run setup-db

# Cleanup test database
npm run cleanup-db
```

## Debugging

### Screenshots

Failed tests automatically capture screenshots saved to `screenshots/` directory.

### Debug Output

Enable debug mode for detailed logging:
```bash
DEBUG=true npm test
```

### Browser Visibility

Run tests with visible browser for debugging:
```bash
HEADLESS=false npm test
```

## CI/CD Integration

For continuous integration:

```bash
# Install Chrome in CI environment
# Install dependencies
npm install

# Run headless tests
npm run test:headless
```

### Environment Variables for CI

```env
BASE_URL=http://localhost/MultiFlexi
HEADLESS=true
DEBUG=false
DB_HOST=localhost
DB_NAME=multiflexi_test
DB_USER=test_user
DB_PASSWORD=test_password
```

## Troubleshooting

### Common Issues

1. **Chrome not found**: Install Google Chrome or update chromedriver
2. **Database connection**: Verify MySQL is running and credentials are correct
3. **Timeout errors**: Increase timeout values in .env file
4. **Permission errors**: Ensure test user has database creation privileges

### Debug Steps

1. Enable debug mode: `DEBUG=true`
2. Run tests in visible browser: `HEADLESS=false`
3. Check screenshots in `screenshots/` directory
4. Verify MultiFlexi application is accessible at BASE_URL
5. Test database connection manually

## Contributing

When adding new tests:

1. Follow the Page Object Model pattern
2. Use descriptive test names and descriptions
3. Include proper cleanup in `after`/`afterEach` hooks
4. Add error handling and meaningful assertions
5. Update this README with new test scenarios

## Best Practices

- Always run tests against a dedicated test database
- Use environment variables for configuration
- Implement proper wait strategies (avoid hard-coded sleeps)
- Take screenshots on failures for debugging
- Clean up test data between test runs
- Use meaningful test data and assertions