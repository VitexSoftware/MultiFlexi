MultiFlexi Development
======================

Welcome to the MultiFlexi development documentation. This guide will help you get started with developing and contributing to the MultiFlexi project.

.. toctree::
   :maxdepth: 2
   :caption: Development Topics

   project-components
   selenium-testing

.. image:: https://wakatime.com/badge/user/5abba9ca-813e-43ac-9b5f-b1cfdf3dc1c7/project/28a38241-3585-4ce7-b365-d7341c69e635.svg
   :target: https://wakatime.com



Setting Up Your Development Environment
---------------------------------------

**System Requirements**

* Minimum 2GB RAM (required for database migration operations)
* PHP 8.1 or higher
* Composer
* Database server (MySQL, PostgreSQL, or SQLite for testing)
* Web server (Apache2, Nginx, or PHP built-in server for development)

1. **Clone the Repository**: Start by cloning the MultiFlexi repository from GitHub.

   .. code-block:: bash

      git clone https://github.com/VitexSoftware/MultiFlexi.git
      cd MultiFlexi

Code Structure
--------------

The MultiFlexi project is organized into several key components:

* **src/**: Contains the main source code for the project.
* **tests/**: Contains test cases for the project.
* **db/**: Contains database migrations and seed data.

Analysis
--------

The most complex file appears to be ``src/MultiFlexi/Job.php``. Here's why:

**Complexity Factors:**

* **Multiple Dependencies**: It depends on several other classes within the MultiFlexi namespace, including:

  * ``\MultiFlexi\executor`` (Interface)
  * ``\MultiFlexi\Zabbix\Request\Metric``
  * ``\MultiFlexi\Zabbix\Request\Packet``
  * ``\MultiFlexi\ZabbixSender``
  * ``\MultiFlexi\Application``
  * ``\MultiFlexi\Company``
  * ``\MultiFlexi\RunTemplate``
  * ``\Ease\Shared``
  * ``\Ease\Logger\Logging``

* **Business Logic**: It encapsulates core business logic related to job execution:

  * ``prepareJob``: Sets up the job environment and creates a new job record in the database.
  * ``runBegin``: Prepares the job for execution, setting up logging and Zabbix reporting.
  * ``runEnd``: Performs actions after the job has run, including updating the database, reporting to Zabbix, and handling output.
  * ``performJob``: Orchestrates the actual job execution using an executor.
  * ``scheduleJobRun``: Schedules the job to run at a specific time.
  * ``reportToZabbix``: Handles sending data to Zabbix.
  * ``cleanUp``: Removes temporary files after job execution.
  * ``launcherScript``: Generates a shell script for executing the job.

* **Environment Handling**: It manages the environment variables for the job, including fetching data from various sources and applying macros.

  * ``getFullEnvironment()``: Retrieves the full environment for the job, including data from the application, company, and run template.
  * ``compileEnv()``: Compiles the environment variables into a flat array.
  * ``applyMarcros()``: Populates the environment with values from the job's context.

* **Database Interaction**: It interacts with the database for creating, updating, and deleting job records, as well as related data.

  * Uses ``Ease\SQL\Engine`` for database operations.

* **Plugin System**: It is designed to work with different executors.

  * Uses an executor interface to allow flexibility in how jobs are executed.

* **Zabbix Integration**: It has logic to send data to Zabbix monitoring system.

  * Uses ``MultiFlexi\Zabbix\Request\Metric`` and ``MultiFlexi\Zabbix\Request\Packet`` to create Zabbix messages.
  * Uses ``MultiFlexi\ZabbixSender`` to send data to Zabbix.

* **State Management**: It keeps track of the job's state and provides methods to set and update the state.

* **Complexity of Methods**: Some methods, like ``prepareJob`` and ``runEnd``, perform a series of complex actions.

* **Integration with Other Modules**: It integrates with other parts of the system, such as the scheduler, configuration, and action modules.

**Other Notable Files:**

* ``src/MultiFlexi/Ui/DBDataTable.php``: Handles the generation of DataTables (rendering & configuration logic).
* ``src/MultiFlexi/Api/Server/DefaultApi.php``: Complex in routing and orchestration logic for API requests.
* ``src/MultiFlexi/Action/Github.php``: Integration with external GitHub services and API communication.

**Conclusion:**

While other files have their own complexities, ``src/MultiFlexi/Job.php`` stands out due to its combination of core business logic, database interactions, environment handling, plugin system integration, and external service integration. It is the central orchestrator for job execution in the MultiFlexi system.

Contributing
------------

We welcome contributions from the community. To contribute, follow these steps:

1. **Fork the Repository**: Fork the MultiFlexi repository to your GitHub account.
2. **Create a Branch**: Create a new branch for your feature or bugfix.

   .. code-block:: bash

      git checkout -b feature-name

3. **Make Changes**: Make your changes and commit them with a descriptive message.

   .. code-block:: bash

      git commit -m "Description of the feature or fix"

4. **Push Changes**: Push your changes to your forked repository.

   .. code-block:: bash

      git push origin feature-name

5. **Create a Pull Request**: Open a pull request on the original repository.

Thank you for contributing to MultiFlexi!

Development Workflow
--------------------

**Git Workflow**

MultiFlexi uses a standard Git workflow with feature branches:

1. **Create Feature Branch**: Always create a new branch for features or fixes

   .. code-block:: bash

      git checkout -b feature/new-executor-type
      git checkout -b fix/job-timeout-issue

2. **Commit Guidelines**: Use conventional commit messages

   .. code-block:: bash

      git commit -m "feat: add Kubernetes executor support"
      git commit -m "fix: resolve infinite recursion in Docker executor"
      git commit -m "docs: update API documentation"

3. **Testing Before Push**: Always run tests before pushing

   .. code-block:: bash

      ./vendor/bin/phpunit
      ./vendor/bin/phpstan analyse

**CI/CD Pipeline**

The project uses Jenkins for continuous integration:

1. **Source Push**: Code pushed to GitHub triggers Jenkins build
2. **Package Build**: ``debian/Jenkinsfile`` creates .deb packages (~10 minutes)
3. **Unstable Repository**: Packages available at:
   - ``http://repo.vitexsoftware.cz/``
   - ``http://repo.vitexsoftware.com/``
4. **Release Process**: Manual Jenkins trigger using ``debian/Jenkinsfile-release``
5. **Production Repository**: Released packages at ``https://repo.multiflexi.eu/``

**Deployment Environments**

* **Development**: ``http://localhost/MultiFlexi/`` (source code)
* **Local Package**: ``http://localhost/multiflexi/`` (installed .deb)
* **Testing**: ``https://vyvojar.spoje.net/multiflexi/`` (CI packages)
* **Demo**: ``https://demo.multiflexi.eu/`` (Ansible deployed)
* **Production**:
  - ``https://multiflexi.vitexsoftware.com/``
  - ``https://multiflexi.spojenet.cz/``

Handling Multiple Database Types
--------------------------------

MultiFlexi supports multiple database types including MySQL, SQLite, PostgreSQL, MSSQL, and almost every PDO-capable database engine. When writing queries, you need to ensure compatibility with these databases.

Here is an example method ``todaysCond`` that generates a condition to fetch records for the current day, compatible with different database types:

.. code-block:: php

   public function todaysCond(string $column = 'begin'): string {
       $databaseType = $this->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

       switch ($databaseType) {
           case 'mysql':
               $cond = ('DATE(' . $column . ') = CURDATE()');
               break;
           case 'sqlite':
               $cond = ("DATE(" . $column . ") = DATE('now')");
               break;
           case 'pgsql':
               $cond = ('DATE(' . $column . ') = CURRENT_DATE');
               break;
           case 'sqlsrv':
               $cond = ('CAST(' . $column . ' AS DATE) = CAST(GETDATE() AS DATE)');
               break;
           default:
               throw new \Exception('Unsupported database type ' . $databaseType);
       }

       return $cond;
   }

This method checks the database type and returns the appropriate condition for fetching records for the current day based on the specified column.

By following this approach, you can ensure that your queries are compatible with multiple database types, making your application more flexible and robust.

GDPR Compliance Development
---------------------------

When developing MultiFlexi features, developers must consider GDPR compliance requirements:

**Data Processing Considerations**

- Implement privacy by design principles
- Minimize data collection and processing
- Ensure lawful basis for all processing activities
- Document data flows and processing purposes

**Security Requirements**

- Use encryption for sensitive data (AES-256)
- Implement proper access controls and logging
- Follow secure coding practices
- Regular security assessments

**Data Retention Implementation**

.. code-block:: php

   // Example: Implementing retention-aware data processing
   class DataProcessor {
       public function processWithRetention($data, $retentionPeriod) {
           // Set retention metadata
           $data['retention_expires'] = date('Y-m-d', strtotime('+' . $retentionPeriod));
           
           // Process data
           $this->processData($data);
           
           // Log for audit trail
           $this->addStatusMessage('Data processed with retention: ' . $retentionPeriod, 'info');
       }
   }

**GDPR-Compliant Logging**

.. code-block:: php

   // Avoid logging personal data
   $this->addStatusMessage('User login successful for ID: ' . $userId, 'info');
   // Instead of: 'User ' . $email . ' logged in'
   
   // Use structured logging for audit trails
   $this->addStatusMessage([
       'event' => 'data_access',
       'user_id' => $userId,
       'resource' => $resourceType,
       'timestamp' => date('c')
   ], 'audit');

For complete GDPR compliance documentation, see :doc:`gdpr-compliance`.

Coding Standards and Best Practices
------------------------------------

**PHP Standards**

MultiFlexi follows PSR-12 coding standards with additional project-specific conventions:

* **Classes**: PascalCase (``RunTemplate``, ``JobExecutor``)
* **Methods**: camelCase (``executeJob``, ``getCompanyList``)
* **Variables**: camelCase (``$companyId``, ``$jobResult``)
* **Constants**: SCREAMING_SNAKE_CASE (``DB_HOST``, ``DEFAULT_TIMEOUT``)
* **Database**: snake_case (``run_template``, ``company_id``)

**Documentation Requirements**

All public methods must include PHPDoc comments:

.. code-block:: php

   /**
    * Execute a job with the specified parameters
    *
    * @param int $jobId The job identifier
    * @param array $params Execution parameters
    * @return bool True on success, false on failure
    * @throws \Exception When job cannot be found
    */
   public function executeJob(int $jobId, array $params = []): bool

**Environment Configuration**

Use environment variables for configuration, with sensible defaults:

.. code-block:: php

   $logLevel = getenv('LOG_LEVEL') ?: 'info';
   $dbHost = getenv('DB_HOST') ?: 'localhost';

**Logging Best Practices**

Use the Ease logging framework with appropriate log levels:

.. code-block:: php

   $this->addStatusMessage('Job started', 'info');
   $this->addStatusMessage('Processing company: ' . $companyName, 'debug');
   $this->addStatusMessage('Job failed: ' . $error, 'error');

Application JSON Schema Validation
-----------------------------------

MultiFlexi enforces JSON schema validation for application definitions to ensure consistency and prevent configuration errors.

**Schema URL**: https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json

**Validation Command**:

.. code-block:: bash

    multiflexi-cli application validate-json --json path/to/app.json

**Common Validation Errors**:

* **Invalid type values**: Environment variable types must be one of: ``string``, ``file-path``, ``email``, ``url``, ``integer``, ``float``, ``bool``, ``password``, ``set``, ``text``
* **Array vs Object**: Fields like ``topics``, ``requirements``, and ``artifacts`` must be arrays, not objects
* **Missing required fields**: All required fields in the schema must be present

**Example of correct environment variable definition**:

.. code-block:: json

    "environment": {
        "FORCE_EXITCODE": {
            "type": "integer",
            "description": "Force specific exit code",
            "defval": "0",
            "required": false
        }
    }


Testing Strategy
----------------

MultiFlexi employs a comprehensive testing strategy to ensure code quality and system reliability:

**Unit Testing**
  PHPUnit tests for individual components and classes, focusing on business logic validation and error handling.

**Integration Testing**  
  Database connectivity testing and API endpoint validation across different environments.

**End-to-End Web Testing**
  Comprehensive Selenium-based web interface testing with support for multiple environments and internationalization. 
  See :doc:`selenium-testing` for detailed information.

**Key Testing Features:**

- **Multi-Environment Support**: Testing across development, local package, and staging environments
- **International Standards**: Full English localization for global development teams  
- **Business Scenario Testing**: Real-world workflow validation including AbraFlexi integration
- **Automated CI/CD Integration**: Headless testing support for continuous integration pipelines

**Running Tests:**

.. code-block:: bash

    # PHP Unit Tests
    ./vendor/bin/phpunit
    
    # Static Analysis
    ./vendor/bin/phpstan analyse
    
    # Selenium Web Tests
    cd tests/selenium
    npm install
    npm run dev:simple        # Quick smoke test
    npm run test:scenarios    # Business scenarios

