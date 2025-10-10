Database Testing
===============

MultiFlexi employs a sophisticated database testing approach that ensures consistency across different database backends while maintaining production-like test environments. The testing framework leverages :doc:`multiflexi-cli` for database-agnostic test data creation and Phinx migrations for schema management.

.. contents::
   :local:
   :depth: 2

Overview
--------

The database testing strategy addresses several key challenges:

- **Database Backend Independence**: Tests work with MySQL, PostgreSQL, SQLite, and other PDO-supported databases
- **Production Schema Consistency**: Uses official Phinx migrations from production
- **Proper Data Creation**: Leverages MultiFlexi's business logic for test data
- **Clean Test Environments**: Automated setup and cleanup for each test run

Architecture
------------

Database-Agnostic Design
~~~~~~~~~~~~~~~~~~~~~~~

The testing framework is completely database-agnostic through several design principles:

**CLI-Based Data Creation**
  All test data is created using ``multiflexi-cli`` commands, which work consistently across database backends:

  .. code-block:: bash

     # These commands work with any supported database
     multiflexi-cli user create --login=testuser --plaintext=password123
     multiflexi-cli company create --name="TestCorp" --enabled=true
     multiflexi-cli application create --name="TestApp" --uuid="testapp"

**Migration-Based Schema**
  Database schema is created using production Phinx migrations:

  .. code-block:: bash

     cd /usr/lib/multiflexi-database && phinx migrate -c phinx-adapter.php

**Configuration Abstraction**
  Database configuration is read from MultiFlexi's main configuration file:

  .. code-block:: bash

     # Configuration read from /etc/multiflexi/database.env
     DB_CONNECTION=mysql  # or postgresql, sqlite, etc.
     DB_HOST=localhost
     DB_DATABASE=multiflexi
     DB_USERNAME=multiflexi
     DB_PASSWORD=your_password

Setup Process
-------------

The automated database setup follows a three-phase approach:

Phase 1: Database Cleanup
~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: javascript

   // 1. Drop all existing tables
   await connection.execute('SET FOREIGN_KEY_CHECKS = 0');
   const [rows] = await connection.execute(`
       SELECT GROUP_CONCAT(table_name) as tables 
       FROM information_schema.tables 
       WHERE table_schema = ?
   `, [databaseName]);
   
   if (rows[0]?.tables) {
       await connection.execute(`DROP TABLE IF EXISTS ${rows[0].tables}`);
   }
   await connection.execute('SET FOREIGN_KEY_CHECKS = 1');

This ensures a completely clean state for each test run, preventing data contamination between test sessions.

Phase 2: Schema Creation
~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Run all production migrations
   cd /usr/lib/multiflexi-database && phinx migrate -c phinx-adapter.php

**Benefits:**
- Uses identical schema to production
- Applies all 98 production migrations
- No schema drift between test and production
- Automatic compatibility with schema changes

Phase 3: Test Data Creation
~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Create test users with proper password hashing
   multiflexi-cli user create --login=admin --firstname=Test --lastname=Admin \
     --email=admin@test.com --plaintext=admin123 --enabled=true

   multiflexi-cli user create --login=testuser --firstname=Test --lastname=User \
     --email=testuser@test.com --plaintext=testpass123 --enabled=true

   # Create test company
   multiflexi-cli company create --name="TestCorp" --enabled=true \
     --ic=12345678 --email=test@testcorp.com --slug=testcorp

   # Create test application
   multiflexi-cli application create --name="TestApp" \
     --description="Test Application for Selenium tests" \
     --executable="echo test" --homepage="http://example.com" \
     --appversion="1.0.0" --uuid="testapp"

**Advantages of CLI-Based Creation:**
- Uses production validation logic
- Proper password hashing and security
- Business rules enforcement
- Database-agnostic operation

Test Data Standards
-------------------

User Accounts
~~~~~~~~~~~

The testing framework creates standardized test user accounts:

**Administrator Account**
  - Username: ``admin``
  - Password: ``admin123`` (automatically hashed)
  - Email: ``admin@test.com``
  - Privileges: Full administrative access

**Regular User Account**
  - Username: ``testuser``  
  - Password: ``testpass123`` (automatically hashed)
  - Email: ``testuser@test.com``
  - Privileges: Standard user access

**Customer Account** (optional)
  - Username: ``testcustomer``
  - Password: ``testpass123`` (automatically hashed)
  - Email: ``customer@test.com``
  - Privileges: Customer-level access

Company Data
~~~~~~~~~~

**Test Company**
  - Name: ``TestCorp``
  - IC (Identification): ``12345678``
  - Email: ``test@testcorp.com``
  - Slug: ``testcorp`` (≤ 10 characters for MySQL compatibility)
  - Status: Enabled

Application Data
~~~~~~~~~~~~~~

**Test Application**
  - Name: ``TestApp``
  - UUID: ``testapp``
  - Version: ``1.0.0``
  - Description: ``Test Application for Selenium tests``
  - Executable: ``echo test``
  - Homepage: ``http://example.com``

Database Verification
---------------------

The testing framework includes comprehensive verification to ensure database readiness:

Structural Verification
~~~~~~~~~~~~~~~~~~~~~

.. code-block:: javascript

   // Verify table creation
   const [tables] = await connection.execute('SHOW TABLES');
   const tableNames = tables.map(row => Object.values(row)[0]);
   
   const requiredTables = ['user', 'customer', 'company', 'apps', 'runtemplate', 'job'];
   const missingTables = requiredTables.filter(table => !tableNames.includes(table));
   
   if (missingTables.length > 0) {
       throw new Error(`Missing required tables: ${missingTables.join(', ')}`);
   }

Migration Verification
~~~~~~~~~~~~~~~~~~~~

.. code-block:: javascript

   // Verify migration tracking
   const [migrationCheck] = await connection.execute('SHOW TABLES LIKE "phinxlog"');
   if (migrationCheck.length === 0) {
       throw new Error('Phinx migration tracking table (phinxlog) not found');
   }
   
   // Count applied migrations
   const [migrationCount] = await connection.execute('SELECT COUNT(*) as count FROM phinxlog');
   const migrations = migrationCount[0].count; // Expected: 98

Data Verification
~~~~~~~~~~~~~~~

.. code-block:: bash

   # Verify users using CLI
   multiflexi-cli user list --format=json

   # Verify companies using CLI  
   multiflexi-cli company list --format=json

   # Verify applications using CLI
   multiflexi-cli application list --format=json

Expected Results:
- 28 database tables created
- 98 migrations applied
- 2+ test users created
- 1+ test companies created
- 1+ test applications created

Cross-Database Compatibility
----------------------------

The testing framework has been designed to work with multiple database backends:

MySQL/MariaDB
~~~~~~~~~~~

**Configuration:**

.. code-block:: bash

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=multiflexi
   DB_USERNAME=multiflexi
   DB_PASSWORD=your_password

**Considerations:**
- Foreign key constraint handling during cleanup
- String length limitations (slug ≤ 10 characters)
- UTF-8 encoding support

PostgreSQL
~~~~~~~~

**Configuration:**

.. code-block:: bash

   DB_CONNECTION=pgsql
   DB_HOST=localhost
   DB_PORT=5432
   DB_DATABASE=multiflexi
   DB_USERNAME=multiflexi
   DB_PASSWORD=your_password

**Considerations:**
- Case-sensitive table names
- Different SQL syntax for some operations
- Transaction handling differences

SQLite
~~~~~

**Configuration:**

.. code-block:: bash

   DB_CONNECTION=sqlite
   DB_DATABASE=/path/to/database.sqlite

**Considerations:**
- File-based database storage
- Limited foreign key constraint support
- Different data type handling

Integration with Selenium Tests
------------------------------

The database testing framework integrates seamlessly with Selenium web tests:

Automated Setup
~~~~~~~~~~~~~

.. code-block:: bash

   # Selenium tests automatically trigger database setup
   ./tests/selenium/run-tests.sh db-setup
   
   # Or run with fresh database
   ./tests/selenium/run-tests.sh fresh smoke

Test Authentication
~~~~~~~~~~~~~~~~~

.. code-block:: javascript

   // Selenium tests use CLI-created users
   const authPage = new AuthPage();
   
   // Login with admin user
   await authPage.loginAsAdmin(); // Uses admin/admin123
   
   // Login with test user  
   await authPage.loginAsTestUser(); // Uses testuser/testpass123

Data Validation
~~~~~~~~~~~~~

.. code-block:: javascript

   // Tests can verify test data existence
   const companies = await companyPage.listCompanies();
   expect(companies).to.include('TestCorp');
   
   const applications = await applicationPage.listApplications();
   expect(applications).to.include('TestApp');

Performance Considerations
-------------------------

Database Setup Timing
~~~~~~~~~~~~~~~~~~~~

Typical timing for database operations:

- **Table cleanup**: 1-2 seconds
- **Migration execution**: 30-60 seconds (98 migrations)
- **Test data creation**: 5-10 seconds
- **Total setup time**: ~1-2 minutes

Optimization Strategies
~~~~~~~~~~~~~~~~~~~~~

**Caching**
  - Database setup is cached between test runs when possible
  - Only full cleanup/setup when explicitly requested

**Parallel Execution**
  - Independent test data creation commands can run in parallel
  - Database verification can be performed concurrently

**Selective Setup**
  - Option to skip data creation if only testing frontend
  - Incremental setup for specific test scenarios

CI/CD Integration
----------------

The database testing framework supports continuous integration environments:

GitHub Actions
~~~~~~~~~~~~

.. code-block:: yaml

   name: Database Tests
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
       
       steps:
         - uses: actions/checkout@v3
         - name: Setup database configuration
           run: |
             echo "DB_CONNECTION=mysql" > /etc/multiflexi/database.env
             echo "DB_HOST=127.0.0.1" >> /etc/multiflexi/database.env
             echo "DB_DATABASE=multiflexi" >> /etc/multiflexi/database.env
             echo "DB_USERNAME=multiflexi" >> /etc/multiflexi/database.env
             echo "DB_PASSWORD=password" >> /etc/multiflexi/database.env
         - name: Run database tests
           run: cd tests/selenium && ./run-tests.sh db-setup

Docker Integration
~~~~~~~~~~~~~~~~

.. code-block:: dockerfile

   FROM multiflexi/multiflexi:latest
   
   # Install test dependencies
   RUN cd /var/www/multiflexi/tests/selenium && npm install
   
   # Environment-specific database setup
   ENV DB_CONNECTION=mysql
   ENV DB_HOST=db
   ENV DB_DATABASE=multiflexi_test
   
   # Run database setup and tests
   CMD cd /var/www/multiflexi/tests/selenium && \
       ./run-tests.sh db-setup && \
       ./run-tests.sh ci

Best Practices
-------------

Development Workflow
~~~~~~~~~~~~~~~~~~

**Local Development**

.. code-block:: bash

   # Setup development database
   ./tests/selenium/run-tests.sh db-setup
   
   # Run quick tests during development  
   ./tests/selenium/run-tests.sh test smoke
   
   # Clean up when needed
   ./tests/selenium/run-tests.sh db-cleanup

**Team Collaboration**
- Always use CLI commands for test data creation
- Document any special database requirements
- Keep database configuration in version control
- Use consistent naming conventions for test data

Production Safety
~~~~~~~~~~~~~~~

**Separation Concerns**
- Tests never run against production databases
- Separate database instances for testing
- Clear configuration separation
- Explicit test data markers

**Data Security**
- Test passwords are separate from production
- No real customer data in test databases  
- Proper cleanup after test completion
- Secure credential management

Troubleshooting
--------------

Common Issues
~~~~~~~~~~~

**CLI Command Failures**

.. code-block:: bash

   # Verify CLI installation
   which multiflexi-cli
   
   # Test CLI connectivity
   multiflexi-cli user list
   
   # Check database configuration
   cat /etc/multiflexi/database.env

**Migration Issues**

.. code-block:: bash

   # Verify migrations directory
   ls -la /usr/lib/multiflexi-database/migrations/
   
   # Check Phinx configuration
   ls -la /usr/lib/multiflexi-database/phinx-adapter.php
   
   # Test migration manually
   cd /usr/lib/multiflexi-database && phinx status -c phinx-adapter.php

**Database Connection Problems**

.. code-block:: bash

   # Test database connection
   mysql -h localhost -u multiflexi -p multiflexi
   
   # Verify database exists
   mysql -e "SHOW DATABASES LIKE 'multiflexi'"
   
   # Check user permissions
   mysql -e "SHOW GRANTS FOR 'multiflexi'@'localhost'"

Debug Mode
~~~~~~~~

Enable detailed logging for troubleshooting:

.. code-block:: bash

   # Enable debug mode
   DEBUG=true ./tests/selenium/run-tests.sh db-setup
   
   # Verbose CLI output
   multiflexi-cli user create --login=test -v

Advanced Topics
--------------

Custom Test Data
~~~~~~~~~~~~~~

Create application-specific test data:

.. code-block:: bash

   # Create custom test entities
   multiflexi-cli user create --login=customuser --plaintext=password
   multiflexi-cli company create --name="CustomCorp" --slug=customcorp
   multiflexi-cli runtemplate create --name="CustomTemplate" --app_id=1 --company_id=1

Database Seeding
~~~~~~~~~~~~~~

For complex scenarios, create database seeding scripts:

.. code-block:: javascript

   // Custom seeding function
   async function seedCustomData() {
       // Create multiple test companies
       for (let i = 1; i <= 5; i++) {
           await execPromise(
               `multiflexi-cli company create --name="Company${i}" --slug=comp${i}`
           );
       }
       
       // Create test relationships
       await execPromise(
           'multiflexi-cli runtemplate create --name="Bulk Import" --app_id=1 --company_id=1'
       );
   }

Schema Validation
~~~~~~~~~~~~~~~

Validate database schema matches expectations:

.. code-block:: javascript

   async function validateSchema() {
       // Check table structure
       const [columns] = await connection.execute('DESCRIBE user');
       const expectedColumns = ['id', 'login', 'password', 'email', 'enabled'];
       
       for (const expected of expectedColumns) {
           const found = columns.some(col => col.Field === expected);
           if (!found) {
               throw new Error(`Missing column: ${expected} in user table`);
           }
       }
   }

Further Reading
--------------

**Related Documentation**

- :doc:`multiflexi-cli` - CLI command reference
- :doc:`selenium-testing` - Web testing framework
- :doc:`development` - Development guidelines

**External Resources**

- `Phinx Documentation <https://phinx.org/>`_ - Database migrations
- `PDO Documentation <https://www.php.net/manual/en/book.pdo.php>`_ - Database abstraction
- `Database Testing Best Practices <https://martinfowler.com/articles/nonDeterminism.html>`_ - Testing strategies

**Configuration Files**

- ``/etc/multiflexi/database.env`` - Main database configuration
- ``/usr/lib/multiflexi-database/phinx-adapter.php`` - Migration configuration
- ``tests/selenium/.env`` - Test environment configuration
