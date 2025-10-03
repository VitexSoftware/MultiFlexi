Selenium Web Testing
===================

MultiFlexi includes a comprehensive Selenium-based web testing suite that provides automated end-to-end testing of the web interface. This testing framework ensures the web application works correctly across different environments and supports the project's international goals with full English localization.

.. image:: https://img.shields.io/badge/Selenium-WebDriver-green.svg
   :target: https://selenium.dev/
   :alt: Selenium WebDriver

.. image:: https://img.shields.io/badge/Testing-E2E-blue.svg
   :alt: End-to-End Testing

Overview
--------

The Selenium test suite is designed to validate the MultiFlexi web interface through automated browser testing. It covers complete user workflows from authentication to job execution, ensuring system reliability and user experience quality.

**Key Features:**

- **Multi-Environment Testing**: Support for development, local package, and staging environments
- **Internationalization**: Full English localization for global development teams
- **Page Object Model**: Maintainable test architecture with reusable components
- **Business Scenarios**: Real-world workflow testing including AbraFlexi integration
- **Cross-Browser Support**: Chrome/Chromium browser automation
- **CI/CD Ready**: Integration support for continuous integration pipelines

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
   - MySQL server
   - MultiFlexi application running

**Installation**

.. code-block:: bash

   cd tests/selenium
   npm install

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

Test Types
----------

Simple Smoke Tests
~~~~~~~~~~~~~~~~~~

Quick frontend validation without database operations:

.. code-block:: bash

   npm run dev:simple     # Development environment
   npm run local:simple   # Local package environment

**Purpose**: Rapid development feedback
**Runtime**: ~1 minute
**Coverage**: Homepage, forms, navigation, responsive design

Full Smoke Tests
~~~~~~~~~~~~~~~

Complete system validation with database operations:

.. code-block:: bash

   npm run test:smoke

**Purpose**: Pre-deployment verification  
**Runtime**: ~2 minutes
**Coverage**: Full user workflow, database operations

Business Scenarios
~~~~~~~~~~~~~~~~~

Real-world workflow testing:

AbraFlexi Complete Workflow
^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. code-block:: bash

   npm run test:abraflexi

**Purpose**: Complete company setup with AbraFlexi integration
**Runtime**: ~5 minutes
**Coverage**: Company creation, credentials, RunTemplate, job execution

Multi-Company Setup
^^^^^^^^^^^^^^^^^^^

.. code-block:: bash

   npm run test:multicompany

**Purpose**: Multiple company management testing
**Runtime**: ~4 minutes  
**Coverage**: Company isolation, concurrent jobs, data separation

Job Error Recovery
^^^^^^^^^^^^^^^^^

.. code-block:: bash

   npm run test:errors

**Purpose**: System robustness during failures
**Runtime**: ~5 minutes
**Coverage**: Error handling, retry mechanisms, recovery workflows

Multi-Environment Support
-------------------------

The test suite supports three distinct environments:

Development Environment
~~~~~~~~~~~~~~~~~~~~~~

**URL**: ``http://localhost/MultiFlexi/src/``
**Purpose**: Source code testing during development
**Database**: ``multiflexi_dev_test``

.. code-block:: bash

   # Interactive menu
   ./run-dev.sh
   
   # Direct execution  
   npm run dev:simple
   npm run dev:scenarios

Local Environment  
~~~~~~~~~~~~~~~~~

**URL**: ``http://localhost/multiflexi/``
**Purpose**: Installed Debian package testing
**Database**: ``multiflexi_local_test``

.. code-block:: bash

   # Interactive menu
   ./run-local.sh
   
   # Direct execution
   npm run local:simple
   npm run local:full

Staging Environment
~~~~~~~~~~~~~~~~~~

**URL**: ``https://vyvojar.spoje.net/multiflexi/``
**Purpose**: Remote testing server validation
**Database**: ``multiflexi_staging_test``

.. code-block:: bash

   # Multi-environment runner
   ./run-multi-env.sh

Configuration
-------------

Environment Configuration
~~~~~~~~~~~~~~~~~~~~~~~~

The test suite uses ``.env`` files for configuration:

.. code-block:: bash

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

Dynamic Configuration Loading
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``EnvironmentManager`` class automatically:

- Detects current environment
- Loads appropriate configuration  
- Validates connectivity
- Provides runtime environment information

Interactive Test Runners
-----------------------

Development Runner
~~~~~~~~~~~~~~~~~

.. code-block:: bash

   ./run-dev.sh

Provides an interactive menu for development environment testing:

.. code-block:: text

   🖥️  MultiFlexi Development Environment Tests
   ==========================================
   🌐 URL: http://localhost/MultiFlexi/src/
   
   Select test to run:
   1) ⚡ Simple Smoke Test (1 min) - No database, frontend only
   2) 🔥 Full Smoke Test (2 min) - With database, complete check  
   3) ⭐⭐⭐ AbraFlexi Complete Workflow (5 min)
   4) ⭐⭐ Multi-Company Setup (4 min)
   5) ⭐⭐ Job Error Recovery (5 min)
   6) 📋 All page tests (10 min)
   7) 🎯 All business scenarios (15 min)
   8) 🚀 Complete test suite (20 min)
   0) Exit

Multi-Environment Runner
~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   ./run-multi-env.sh

Enables testing across all three environments with interactive selection.

Page Object Model
-----------------

The test suite uses the Page Object Model pattern for maintainable and reusable test code:

Base Page Structure
~~~~~~~~~~~~~~~~~~

.. code-block:: javascript

   // Example: BasePage.js
   class BasePage {
       constructor() {
           this.driver = null;
           this.baseUrl = process.env.BASE_URL;
       }
       
       async navigate(path) {
           await this.driver.get(`${this.baseUrl}${path}`);
       }
   }

Authentication Page
~~~~~~~~~~~~~~~~~

.. code-block:: javascript

   // AuthPage.js - Handles login and registration
   class AuthPage extends BasePage {
       async registerUser(userData) {
           console.log('🔐 Creating user account...');
           // Registration implementation
       }
       
       async login(username, password) {
           console.log('🔑 Logging in user...');
           // Login implementation
       }
   }

Dashboard Page
~~~~~~~~~~~~~

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
~~~~~~~~~~~~~~~

1. Create test files in ``tests/`` directory
2. Follow Page Object Model pattern
3. Use English localization throughout
4. Add appropriate npm scripts in ``package.json``

.. code-block:: javascript

   // Example test structure
   describe('New Feature Test', function() {
       before(async function() {
           console.log('🔧 Setting up new feature test...');
           // Setup code
       });
       
       it('should perform feature action', async function() {
           console.log('⚡ Testing feature functionality...');
           // Test implementation
       });
       
       after(async function() {
           console.log('🧹 Cleaning up after test...');
           // Cleanup code
       });
   });

Error Handling Best Practices
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: javascript

   try {
       await this.performAction();
       console.log('✅ Action completed successfully');
   } catch (error) {
       console.log(`❌ Action failed: ${error.message}`);
       throw error;
   }

Internationalization Standards
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

All test components follow English-first standards:

- **Console Messages**: English output for global teams
- **Test Descriptions**: Clear English terminology  
- **Error Messages**: Standardized English error reporting
- **Documentation**: English documentation throughout

CI/CD Integration
----------------

The test suite is designed for continuous integration:

GitHub Actions Example
~~~~~~~~~~~~~~~~~~~~~

.. code-block:: yaml

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

Headless Mode
~~~~~~~~~~~~

For automated environments:

.. code-block:: bash

   HEADLESS=true npm run test:smoke
   npm run test:headless

Troubleshooting
--------------

Common Issues
~~~~~~~~~~~~

**ChromeDriver Version Mismatch**

.. code-block:: bash

   # Check versions
   chromium --version
   chromedriver --version
   
   # Update ChromeDriver if versions don't match

**Database Connection Issues**

.. code-block:: bash

   # Verify MySQL service
   systemctl status mysql
   
   # Test connection
   mysql -h localhost -u root -p multiflexi_dev_test

**Configuration Issues**

.. code-block:: bash

   # Verify .env file
   cat tests/selenium/.env
   
   # Check environment detection
   npm run config:check

Debug Mode
~~~~~~~~~

Enable detailed logging:

.. code-block:: bash

   DEBUG=true npm run dev:simple
   npm run test:debug

Browser Visibility
~~~~~~~~~~~~~~~~~

Run with visible browser for debugging:

.. code-block:: bash

   HEADLESS=false npm test

Performance Optimization
-----------------------

Test Execution Speed
~~~~~~~~~~~~~~~~~~~

- Use ``simple-smoke`` for rapid development feedback
- Run full scenarios only when needed  
- Use headless mode for CI: ``HEADLESS=true npm test``

Resource Management
~~~~~~~~~~~~~~~~~

- Proper WebDriver cleanup in ``after()`` hooks
- Efficient element waiting strategies
- Connection pooling for database operations

Best Practices
--------------

Test Organization
~~~~~~~~~~~~~~~~

- Group related tests in describe blocks
- Use clear, descriptive test names in English
- Implement proper setup and teardown

Code Quality
~~~~~~~~~~~

- Follow consistent coding standards
- Use meaningful variable names
- Add comments for complex logic
- Handle errors gracefully

Maintenance
~~~~~~~~~~

- Keep ChromeDriver updated
- Regular dependency updates  
- Monitor test execution times
- Review and update documentation

Further Reading
--------------

**Documentation**

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
- ``tests/selenium/DEVELOPER-GUIDE.md`` - Comprehensive developer guide  
- ``tests/selenium/QUICKSTART.md`` - Quick start instructions