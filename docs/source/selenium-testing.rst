Selenium Web Testing
===================

MultiFlexi includes a comprehensive Selenium-based web testing suite that provides automated end-to-end testing of the web interface. This testing framework ensures the web application works correctly across different environments and supports the project's international goals with full English localization.

.. image:: https://img.shields.io/badge/Selenium-WebDriver-green.svg
   :target: https://selenium.dev/
   :alt: Selenium WebDriver

.. image:: https://img.shields.io/badge/Testing-E2E-blue.svg
   :alt: End-to-End Testing

.. image:: https://img.shields.io/badge/Database-Agnostic-orange.svg
   :alt: Database Agnostic

Overview
--------

The Selenium test suite is designed to validate the MultiFlexi web interface through automated browser testing. It covers complete user workflows from authentication to job execution, ensuring system reliability and user experience quality.

**Key Features:**

- **Multi-Environment Testing**: Support for development, local package, and staging environments
- **Database-Agnostic Setup**: Uses :doc:`multiflexi-cli` for database-independent test data creation
- **Internationalization**: Full English localization for global development teams
- **Page Object Model**: Maintainable test architecture with reusable components
- **Business Scenarios**: Real-world workflow testing including AbraFlexi integration
- **Cross-Browser Support**: Chrome/Chromium browser automation
- **CI/CD Ready**: Integration support for continuous integration pipelines

Database Setup & Management
---------------------------

The Selenium test suite uses a sophisticated database setup process that ensures clean, consistent testing environments across any database backend supported by MultiFlexi.

Database-Agnostic Architecture
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The test framework is completely database-agnostic, using :doc:`multiflexi-cli` commands for all data creation:

- **Works with any database**: MySQL, PostgreSQL, SQLite, and other PDO-supported databases
- **Consistent behavior**: Same test data creation process regardless of backend
- **Production-like data**: Uses MultiFlexi's own user creation and validation logic
- **Proper security**: Test users created with appropriate password hashing

Database Setup Process
~~~~~~~~~~~~~~~~~~~~~

The automated database setup follows these steps:

1. **Drop All Tables**
   
   - Removes all existing database tables to ensure clean state
   - Handles foreign key constraints properly
   - Database-specific cleanup (currently optimized for MySQL)

2. **Run Phinx Migrations**

   .. code-block:: bash

      cd /usr/lib/multiflexi-database && phinx migrate -c phinx-adapter.php

   - Applies all 98 production migrations
   - Creates complete MultiFlexi schema
   - Uses official migration files from ``/usr/lib/multiflexi-database/migrations/``

3. **Create Test Data with multiflexi-cli**

   Test users, companies, and applications are created using CLI commands:

   .. code-block:: bash

      # Create admin user
      multiflexi-cli user create --login=admin --firstname=Test --lastname=Admin \
        --email=admin@test.com --plaintext=admin123 --enabled=true

      # Create test user
      multiflexi-cli user create --login=testuser --firstname=Test --lastname=User \
        --email=testuser@test.com --plaintext=testpass123 --enabled=true

      # Create test company
      multiflexi-cli company create --name="TestCorp" --enabled=true \
        --ic=12345678 --email=test@testcorp.com --slug=testcorp

      # Create test application
      multiflexi-cli application create --name="TestApp" \
        --description="Test Application for Selenium tests" --executable="echo test" \
        --homepage="http://example.com" --appversion="1.0.0" --uuid="testapp"

Test Credentials
~~~~~~~~~~~~~~

The database setup creates the following test accounts:

**Admin User**
  - Username: ``admin``
  - Password: ``admin123``
  - Email: ``admin@test.com``
  - Role: Administrator

**Test User**
  - Username: ``testuser``
  - Password: ``testpass123``
  - Email: ``testuser@test.com``
  - Role: Regular user

**Test Company**
  - Name: ``TestCorp``
  - IC: ``12345678``
  - Email: ``test@testcorp.com``
  - Slug: ``testcorp``

**Test Application**
  - Name: ``TestApp``
  - UUID: ``testapp``
  - Version: ``1.0.0``

Database Management Commands
~~~~~~~~~~~~~~~~~~~~~~~~~~

The test suite provides several database management commands:

.. code-block:: bash

   # Setup database with test data
   ./run-tests.sh db-setup

   # Verify database is ready for testing
   ./run-tests.sh db-verify

   # Clean database (drop all tables)
   ./run-tests.sh db-cleanup

   # Run tests with fresh database setup
   ./run-tests.sh fresh smoke true

   # Direct script usage
   node scripts/setupDatabase.js          # Setup
   node scripts/setupDatabase.js verify   # Verify
   node scripts/setupDatabase.js cleanup  # Cleanup

Database Verification
~~~~~~~~~~~~~~~~~~~~

The verification process uses ``multiflexi-cli`` commands to ensure database-agnostic checking:

.. code-block:: bash

   # Verify users
   multiflexi-cli user list --format=json

   # Verify companies
   multiflexi-cli company list --format=json

   # Verify applications
   multiflexi-cli application list --format=json

Expected verification results:

- ✅ 28 tables created
- ✅ 98 migrations applied
- ✅ 2 test users created (admin, testuser)
- ✅ 1 test company created (TestCorp)
- ✅ 1 test application created (TestApp)

Test Suite Structure
--------------------

The Selenium tests are organized in the ``tests/selenium/`` directory:

.. code-block:: text

   tests/selenium/
   ├── src/                          # Page Object Model classes
   │   ├── AuthPage.js              # Authentication handling  
   │   ├── DashboardPage.js         # Dashboard interactions
   │   ├── CompanyPage.js           # Company management
   │   ├── RunTemplatePage.js       # RunTemplate operations
   │   ├── JobPage.js               # Job monitoring
   │   └── EnvironmentManager.js    # Multi-environment configuration
   ├── tests/                        # Test files
   │   ├── simple-smoke.test.js     # Quick frontend validation
   │   ├── smoke-test.test.js       # Complete system validation
   │   ├── scenario-*.test.js       # Business scenario tests
   │   └── pages/                   # Individual page tests
   ├── scripts/                      # Database and utility scripts
   │   ├── setupDatabase.js         # Database-agnostic setup
   │   └── database-setup.sh        # Shell script helpers
   ├── config/                       # Configuration utilities
   │   └── config-manager.js        # Environment management
   ├── run-*.sh                     # Interactive test runners
   └── docs/                        # Documentation

Quick Start
-----------

**Prerequisites**

.. code-block:: bash

   # Required software
   - Node.js 16+ and npm
   - Google Chrome/Chromium browser
   - ChromeDriver (compatible version)
   - Database server (MySQL, PostgreSQL, SQLite, etc.)
   - MultiFlexi application with multiflexi-cli
   - Phinx migration tool

**Installation**

.. code-block:: bash

   cd tests/selenium
   npm install

**Database Setup**

.. code-block:: bash

   # Check dependencies (including multiflexi-cli)
   ./run-tests.sh check

   # Setup database with migrations and test data
   ./run-tests.sh db-setup

**Basic Configuration**

.. code-block:: bash

   cp .env.example .env
   # Edit .env with your settings

**Run Quick Test**

.. code-block:: bash

   # Development environment (1 minute)
   npm run dev:simple
   
   # Local package environment  
   npm run local:simple

Authentication Integration
-------------------------

The ``AuthPage`` class provides seamless integration with the CLI-created test users:

Login Methods
~~~~~~~~~~~

.. code-block:: javascript

   const authPage = new AuthPage();

   // Login with specific test users
   await authPage.loginAsAdmin();      // Uses admin/admin123
   await authPage.loginAsTestUser();   // Uses testuser/testpass123
   await authPage.loginAsCustomer();   // Uses testcustomer/testpass123

   // Generic login method
   await authPage.login('username', 'password');

Credential Access
~~~~~~~~~~~~~~~

.. code-block:: javascript

   // Get credentials for test configuration
   const adminCreds = authPage.getAdminCredentials();
   // Returns: { username: 'admin', password: 'admin123', email: 'admin@test.com' }

   const testUserCreds = authPage.getTestUserCredentials();
   // Returns: { username: 'testuser', password: 'testpass123', email: 'testuser@test.com' }

Environment Configuration
~~~~~~~~~~~~~~~~~~~~~~~~

Test credentials are configurable via environment variables:

.. code-block:: bash

   # Admin credentials (created by multiflexi-cli)
   ADMIN_USERNAME=admin
   ADMIN_PASSWORD=admin123
   ADMIN_EMAIL=admin@test.com

   # Test user credentials (created by multiflexi-cli)
   TEST_USER_USERNAME=testuser
   TEST_USER_PASSWORD=testpass123
   TEST_USER_EMAIL=testuser@test.com

   # Test company data
   TEST_COMPANY_NAME=TestCorp
   TEST_COMPANY_IC=12345678
   TEST_COMPANY_EMAIL=test@testcorp.com

Test Types
----------

Simple Smoke Tests
~~~~~~~~~~~~~~~~~

Quick frontend validation without database operations:

.. code-block:: bash

   npm run dev:simple     # Development environment
   npm run local:simple   # Local package environment

**Purpose**: Rapid development feedback
**Runtime**: ~1 minute
**Coverage**: Homepage, forms, navigation, responsive design

Full Smoke Tests
~~~~~~~~~~~~~~

Complete system validation with database operations:

.. code-block:: bash

   npm run test:smoke

**Purpose**: Pre-deployment verification  
**Runtime**: ~2 minutes
**Coverage**: Full user workflow, database operations, authentication

Business Scenarios
~~~~~~~~~~~~~~~~

Real-world workflow testing:

AbraFlexi Complete Workflow
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: bash

   npm run test:abraflexi

**Purpose**: Complete company setup with AbraFlexi integration
**Runtime**: ~5 minutes
**Coverage**: Company creation, credentials, RunTemplate, job execution

Multi-Company Setup
^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: bash

   npm run test:multicompany

**Purpose**: Multiple company management testing
**Runtime**: ~4 minutes  
**Coverage**: Company isolation, concurrent jobs, data separation

Job Error Recovery
^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: bash

   npm run test:errors

**Purpose**: System robustness during failures
**Runtime**: ~5 minutes
**Coverage**: Error handling, retry mechanisms, recovery workflows

Multi-Environment Support
-------------------------

The test suite supports three distinct environments with database-agnostic setup:

Development Environment
~~~~~~~~~~~~~~~~~~~~~

**URL**: ``http://localhost/MultiFlexi/src/``
**Purpose**: Source code testing during development
**Database**: Uses main database configured in ``/etc/multiflexi/database.env``

.. code-block:: bash

   # Interactive menu
   ./run-dev.sh
   
   # Direct execution  
   npm run dev:simple
   npm run dev:scenarios

Local Environment  
~~~~~~~~~~~~~~~~

**URL**: ``http://localhost/multiflexi/``
**Purpose**: Installed Debian package testing
**Database**: Uses main database configured in ``/etc/multiflexi/database.env``

.. code-block:: bash

   # Interactive menu
   ./run-local.sh
   
   # Direct execution
   npm run local:simple
   npm run local:full

Staging Environment
~~~~~~~~~~~~~~~~~

**URL**: ``https://vyvojar.spoje.net/multiflexi/``
**Purpose**: Remote testing server validation
**Database**: Uses staging database configuration

.. code-block:: bash

   # Multi-environment runner
   ./run-multi-env.sh

Configuration
-------------

Environment Configuration
~~~~~~~~~~~~~~~~~~~~~~~

The test suite uses ``.env`` files for configuration:

.. code-block:: bash

   # Environment selection
   TEST_ENVIRONMENT=development
   
   # Development Environment - Source code in development
   DEVELOPMENT_BASE_URL=http://localhost/MultiFlexi/src/
   
   # Local Environment - Installed package  
   LOCAL_BASE_URL=http://localhost/multiflexi/
   
   # Staging Environment - Testing server
   STAGING_BASE_URL=https://vyvojar.spoje.net/multiflexi/

Database Configuration
~~~~~~~~~~~~~~~~~~~~~

Database configuration is read from the main MultiFlexi configuration:

.. code-block:: bash

   # Main database configuration
   /etc/multiflexi/database.env

   # Contains database connection details:
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=multiflexi
   DB_USERNAME=multiflexi
   DB_PASSWORD=your_password

Dynamic Configuration Loading
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``EnvironmentManager`` class automatically:

- Detects current environment
- Loads appropriate configuration  
- Validates connectivity
- Provides runtime environment information
- Supports test user credentials

Interactive Test Runners
-----------------------

Enhanced Test Runner
~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   ./run-tests.sh

The main test runner provides comprehensive database management:

.. code-block:: text

   MultiFlexi Selenium Test Runner

   Commands:
     check                    - Check dependencies (including Phinx and database config)
     setup                    - Setup test environment
     db-setup                 - Setup test database (drop tables + run Phinx migrations)
     db-cleanup               - Cleanup test database (drop all tables)
     db-verify                - Verify database setup
     test <type> [headless]   - Run specific tests
     full                     - Run complete test suite with DB setup/cleanup
     fresh [type] [headless]  - Fresh database setup + run tests (no cleanup)
     ci                       - Run tests in CI mode (headless)

   Database Setup Process:
     1. Drops all existing database tables
     2. Runs Phinx migrations from /usr/lib/multiflexi-database/migrations/
     3. Creates test users and data using multiflexi-cli

Development Runner
~~~~~~~~~~~~~~~~

.. code-block:: bash

   ./run-dev.sh

Provides an interactive menu for development environment testing with automatic database setup.

Multi-Environment Runner
~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   ./run-multi-env.sh

Enables testing across all environments with database preparation for each.

Page Object Model
-----------------

The test suite uses the Page Object Model pattern with enhanced authentication support:

Authentication Page
~~~~~~~~~~~~~~~~

.. code-block:: javascript

   // AuthPage.js - Enhanced with CLI user support
   class AuthPage extends BasePage {
       async loginAsAdmin() {
           const username = process.env.ADMIN_USERNAME || 'admin';
           const password = process.env.ADMIN_PASSWORD || 'admin123';
           await this.login(username, password);
       }
       
       async loginAsTestUser() {
           const username = process.env.TEST_USER_USERNAME || 'testuser';
           const password = process.env.TEST_USER_PASSWORD || 'testpass123';
           await this.login(username, password);
       }
       
       getAdminCredentials() {
           return {
               username: process.env.ADMIN_USERNAME || 'admin',
               password: process.env.ADMIN_PASSWORD || 'admin123',
               email: process.env.ADMIN_EMAIL || 'admin@test.com'
           };
       }
   }

Dashboard Page
~~~~~~~~~~~~

.. code-block:: javascript

   // DashboardPage.js - Dashboard interactions
   class DashboardPage extends BasePage {
       async navigateToCompanies() {
           console.log('🏢 Navigating to companies...');
           // Navigation implementation
       }
   }

Development Guidelines
---------------------

Adding New Tests
~~~~~~~~~~~~~~

1. Create test files in ``tests/`` directory
2. Follow Page Object Model pattern
3. Use English localization throughout
4. Leverage CLI-created test users
5. Add appropriate npm scripts in ``package.json``

.. code-block:: javascript

   // Example test structure with authentication
   describe('New Feature Test', function() {
       before(async function() {
           console.log('🔧 Setting up new feature test...');
           // Database setup is handled automatically
           this.authPage = new AuthPage();
           await this.authPage.loginAsTestUser();
       });
       
       it('should perform feature action', async function() {
           console.log('⚡ Testing feature functionality...');
           // Test implementation with authenticated user
       });
       
       after(async function() {
           console.log('🧹 Cleaning up after test...');
           // Cleanup code
       });
   });

Database Testing Best Practices
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: javascript

   // Use CLI-created test data
   const adminCreds = authPage.getAdminCredentials();
   const testUserCreds = authPage.getTestUserCredentials();

   // Verify test company exists
   const companies = await this.companyPage.listCompanies();
   expect(companies).to.include('TestCorp');

   // Test with proper authentication
   await authPage.loginAsAdmin();
   // Perform admin-only actions
   
   await authPage.loginAsTestUser();
   // Perform regular user actions

Error Handling Best Practices
~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: javascript

   try {
       await this.performDatabaseAction();
       console.log('✅ Database action completed successfully');
   } catch (error) {
       console.log(`❌ Database action failed: ${error.message}`);
       throw error;
   }

CI/CD Integration
----------------

The test suite is designed for continuous integration with database-agnostic setup:

GitHub Actions Example
~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

   name: MultiFlexi Selenium Tests
   on: [push, pull_request]
   
   jobs:
     test:
       runs-on: ubuntu-latest
       services:
         mysql:
           image: mysql:8.0
           env:
             MYSQL_ROOT_PASSWORD: root
             MYSQL_DATABASE: multiflexi
             MYSQL_USER: multiflexi
             MYSQL_PASSWORD: password
           options: >-
             --health-cmd="mysqladmin ping"
             --health-interval=10s
             --health-timeout=5s
             --health-retries=3
       steps:
         - uses: actions/checkout@v3
         - uses: actions/setup-node@v3
           with:
             node-version: '18'
         - name: Install MultiFlexi and setup database
           run: |
             # Install MultiFlexi with multiflexi-cli
             # Setup database configuration
             echo "DB_CONNECTION=mysql" > /etc/multiflexi/database.env
             echo "DB_HOST=127.0.0.1" >> /etc/multiflexi/database.env
             echo "DB_DATABASE=multiflexi" >> /etc/multiflexi/database.env
             echo "DB_USERNAME=multiflexi" >> /etc/multiflexi/database.env
             echo "DB_PASSWORD=password" >> /etc/multiflexi/database.env
         - name: Install Selenium dependencies
           run: cd tests/selenium && npm install
         - name: Setup database with CLI
           run: cd tests/selenium && ./run-tests.sh db-setup
         - name: Run tests
           run: cd tests/selenium && ./run-tests.sh ci

Docker Integration
~~~~~~~~~~~~~~~

.. code-block:: dockerfile

   FROM multiflexi/multiflexi:latest
   
   # Install test dependencies
   RUN cd /var/www/multiflexi/tests/selenium && npm install
   
   # Setup test database
   RUN cd /var/www/multiflexi/tests/selenium && ./run-tests.sh db-setup
   
   # Run tests
   CMD cd /var/www/multiflexi/tests/selenium && ./run-tests.sh ci

Troubleshooting
--------------

Common Issues
~~~~~~~~~~~

**multiflexi-cli Command Not Found**

.. code-block:: bash

   # Check installation
   which multiflexi-cli
   
   # Verify PATH
   echo $PATH | grep -o '/usr/bin'
   
   # Test CLI connectivity
   multiflexi-cli user list

**Database Configuration Issues**

.. code-block:: bash

   # Verify configuration file
   cat /etc/multiflexi/database.env
   
   # Test database connection
   mysql -h localhost -u multiflexi -p multiflexi
   
   # Check migration directory
   ls -la /usr/lib/multiflexi-database/migrations/

**User Creation Failures**

.. code-block:: bash

   # Test CLI user creation directly
   multiflexi-cli user create --login=testuser --firstname=Test --lastname=User \
     --email=test@example.com --plaintext=password123 --enabled=true
   
   # Check existing users
   multiflexi-cli user list

**Company Creation Issues**

.. code-block:: bash

   # Verify company creation parameters
   multiflexi-cli company create --name="TestCorp" --enabled=true \
     --ic=12345678 --email=test@testcorp.com --slug=testcorp
   
   # List existing companies
   multiflexi-cli company list

Database Verification Issues
~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Manual verification
   ./run-tests.sh db-verify
   
   # Check specific components
   multiflexi-cli user list --format=json
   multiflexi-cli company list --format=json  
   multiflexi-cli application list --format=json

Debug Mode
~~~~~~~~

Enable detailed logging:

.. code-block:: bash

   DEBUG=true ./run-tests.sh db-setup
   npm run test:debug

Performance Optimization
-----------------------

Database Setup Speed
~~~~~~~~~~~~~~~~~~~

- Database setup typically takes 1-2 minutes
- Migrations are run once per test session
- CLI commands are optimized for speed
- Use ``./run-tests.sh fresh`` for optimal workflow

Test Execution Speed
~~~~~~~~~~~~~~~~~~

- Use ``simple-smoke`` for rapid development feedback
- Run full scenarios only when needed  
- Use headless mode for CI: ``HEADLESS=true npm test``
- Database setup is cached between test runs

Resource Management
~~~~~~~~~~~~~~~~

- Proper WebDriver cleanup in ``after()`` hooks
- Efficient element waiting strategies
- Database connections managed by CLI
- Clean separation between test data and production data

Best Practices
--------------

Database Management
~~~~~~~~~~~~~~~~~

- Always use ``multiflexi-cli`` for test data creation
- Don't create test data with direct SQL queries
- Use the provided database setup scripts
- Verify database state before running tests

Test Organization
~~~~~~~~~~~~~~~

- Group related tests in describe blocks
- Use clear, descriptive test names in English
- Implement proper setup and teardown
- Leverage CLI-created test users

Code Quality
~~~~~~~~~~

- Follow consistent coding standards
- Use meaningful variable names
- Add comments for complex logic
- Handle errors gracefully
- Use environment variables for credentials

Security
~~~~~~

- Test users are created with proper password hashing
- Credentials are managed through environment variables
- Database cleanup ensures no test data leakage
- Authentication follows production patterns

Maintenance
~~~~~~~~~

- Keep ChromeDriver updated
- Regular dependency updates  
- Monitor test execution times
- Review and update documentation
- Verify CLI compatibility with MultiFlexi updates

Further Reading
--------------

**Documentation**

- :doc:`multiflexi-cli` - CLI command reference
- :doc:`development` - General development guide
- :doc:`project-components` - Project architecture
- :doc:`api` - API documentation

**External Resources**

- `Selenium WebDriver Documentation <https://selenium.dev/documentation/>`_
- `Mocha Testing Framework <https://mochajs.org/>`_
- `Chai Assertion Library <https://www.chaijs.com/>`_
- `Node.js Best Practices <https://github.com/goldbergyoni/nodebestpractices>`_

**Test Suite Files**

- ``tests/selenium/README.md`` - Detailed setup guide
- ``tests/selenium/MULTIFLEXI-CLI-SETUP.md`` - CLI setup documentation
- ``tests/selenium/DEVELOPER-GUIDE.md`` - Comprehensive developer guide  
- ``tests/selenium/QUICKSTART.md`` - Quick start instructions
