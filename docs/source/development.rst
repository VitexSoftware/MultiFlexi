MultiFlexi Development
======================

Welcome to the MultiFlexi development documentation. This guide will help you get started with developing and contributing to the MultiFlexi project.

.. toctree::
   :maxdepth: 2
   :caption: Development Topics

   project-components
   selenium-testing

.. image:: https://wakatime.com/badge/user/5abba9ca-813e-43ac-9b5f-b1cfdf3dc1c7/project/28a38241-3585-4ce7-b365-d7341c69e635.svg
   :target: https://wakatime.com/



Architecture
------------

MultiFlexi features a sophisticated layered architecture:

**Database Layer**
  ORM-based data management with Phinx migrations handling applications, companies, run templates, jobs, and credentials

**Application Management**
  External application definitions with JSON-based metadata, validation, and lifecycle management

**Job Execution System**
  Multi-environment execution with automatic environment variable injection and support for various executors

**Credential Framework**
  Extensible credential types supporting various integrations including secret management systems

**Configuration Management**
  Environment-based configuration with type-safe field definitions and validation

**Security Layer**
  Comprehensive authentication, authorization, and secure credential handling with encryption

**GDPR Compliance**
  Complete GDPR compliance framework with automated data retention, breach response, and comprehensive documentation

Environment Variables
---------------------

MultiFlexi automatically configures environment variables for executed applications. For example:

**AbraFlexi Integration:**

- ``ABRAFLEXI_URL`` - AbraFlexi server endpoint
- ``ABRAFLEXI_LOGIN`` - Authentication username
- ``ABRAFLEXI_PASSWORD`` - Authentication password
- ``ABRAFLEXI_COMPANY`` - Company code/identifier

**Pohoda Integration:**

- ``POHODA_ICO`` - Company identification number
- ``POHODA_URL`` - Pohoda server endpoint
- ``POHODA_USERNAME`` - Authentication username
- ``POHODA_PASSWORD`` - Authentication password

**System Variables:**

- ``MULTIFLEXI_JOB_ID`` - Current job identifier
- Custom variables based on application and company configuration

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

Technical Configuration for Developers
======================================

This section contains technical implementation details for configuring MultiFlexi. For end-user configuration guidance, see :doc:`configuration`.

JSON Configuration Implementation
---------------------------------

MultiFlexi applications define configuration fields using JSON schema with specific type definitions and validation rules.

**Environment Variable Configuration Types**

When defining configuration fields in application JSON:

.. code-block:: json

   {
       "environment": {
           "GDPR_LAWFUL_BASIS": {
               "type": "set",
               "description": "GDPR lawful basis for processing",
               "options": ["consent", "contract", "legal_obligation", "vital_interests", "public_task", "legitimate_interests"],
               "defval": "legitimate_interests",
               "required": true
           },
           "DATA_RETENTION_PERIOD": {
               "type": "integer",
               "description": "Data retention period in days",
               "defval": "365",
               "required": false
           },
           "PRIVACY_NOTICE_URL": {
               "type": "url",
               "description": "URL to privacy notice",
               "required": false
           }
       }
   }

OpenTelemetry Implementation Details
------------------------------------

MultiFlexi supports exporting metrics to OpenTelemetry-compatible backends for observability and monitoring.

**Basic Configuration**

.. code-block:: bash

   # Enable OpenTelemetry metrics export
   OTEL_ENABLED=true
   OTEL_SERVICE_NAME=multiflexi
   OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4318
   OTEL_EXPORTER_OTLP_PROTOCOL=http/json

**Configuration Options**

- ``OTEL_ENABLED`` - Enable/disable OpenTelemetry export (default: ``false``)
- ``OTEL_SERVICE_NAME`` - Service identifier in OTLP (default: ``multiflexi``)
- ``OTEL_EXPORTER_OTLP_ENDPOINT`` - OTLP collector endpoint URL
- ``OTEL_EXPORTER_OTLP_PROTOCOL`` - Protocol (``http/json`` or ``grpc``)

**Protocol Selection**

HTTP/JSON (recommended for simplicity):

.. code-block:: bash

   OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4318
   OTEL_EXPORTER_OTLP_PROTOCOL=http/json

gRPC (recommended for performance):

.. code-block:: bash

   OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4317
   OTEL_EXPORTER_OTLP_PROTOCOL=grpc

**Testing the Configuration**

Use the CLI command to verify your OpenTelemetry setup:

.. code-block:: bash

   multiflexi-cli telemetry:test

For complete OpenTelemetry integration details, deployment examples, and Grafana dashboards, see :doc:`opentelemetry`.

Security Configuration Implementation
-------------------------------------

MultiFlexi includes comprehensive security configuration options for developers implementing GDPR and security features:

**Security Configuration (Phase 3)**

.. code-block:: bash

   # Security settings
   SECURITY_AUDIT_ENABLED=true           # Enable comprehensive security event logging
   DATA_ENCRYPTION_ENABLED=true          # Enable AES-256 data encryption
   RATE_LIMITING_ENABLED=true            # Enable API rate limiting
   IP_WHITELIST_ENABLED=false            # Enable IP whitelisting for admin access
   ENCRYPTION_MASTER_KEY=<secret_key>    # Master encryption key (required for data encryption)

**Data Retention Configuration (Phase 4)**

.. code-block:: bash

   # Data retention and cleanup settings
   DATA_RETENTION_ENABLED=true                    # Enable automated data retention and cleanup
   RETENTION_GRACE_PERIOD_DAYS=30                 # Default grace period before final deletion
   RETENTION_ARCHIVE_PATH=/var/lib/multiflexi/archives  # Path for archived data storage
   RETENTION_CLEANUP_SCHEDULE="0 2 * * *"         # Cron expression for automated cleanup


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

Application Development Framework
=================================

This section provides comprehensive technical details for developing MultiFlexi applications.

Application Definition Schema
-----------------------------

MultiFlexi uses **JSON Schema version 3.0.0** to validate application definitions. The schema ensures correctness of structure, types, and constraints.

**Schema Location:**

.. code-block:: text

    https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json

**Schema Version:** 3.0.0

**Key Schema Features:**

- **Localized Strings**: Support for multi-language names, descriptions, and hints
- **Strict Field Validation**: Environment variable names must match ``^[A-Z0-9_]+$``
- **Type Safety**: Environment variable types are strictly validated
- **UUID Format**: Application UUIDs must conform to UUID v4 format
- **URI Validation**: Homepage and schema references validated as URIs
- **OCI Image Pattern**: Docker/OCI images validated with regex pattern
- **Category Enums**: Environment categories limited to: API, Database, Behavior, Security, Other
- **Data Flow**: ``produces`` and ``consumes`` sections for input/output declaration

**Required Fields:**

- ``$schema``: Must reference the official schema URL
- ``name``: Application name (string or localized object)
- ``description``: Application description (string or localized object)
- ``executable``: Command to execute
- ``environment``: Environment variables object (can be empty ``{}``)

**Validation Command:**

.. code-block:: bash

    multiflexi-cli application validate-json --json /path/to/app.json

**Common Validation Errors:**

1. **Missing ``$schema`` field**: Always include the schema reference at the top of your JSON
2. **Invalid environment variable names**: Must be uppercase with underscores (``MY_VAR``, not ``myVar``)
3. **Wrong type enum**: Use exact values from schema (``file-path``, not ``filepath``)
4. **Invalid UUID format**: Use proper UUID v4 format (e.g., ``550e8400-e29b-41d4-a716-446655440000``)
5. **Invalid category**: Use only: API, Database, Behavior, Security, Other
6. **Localized string format**: Must be either string OR object with language codes

**Migration from Older Schemas:**

If you have older application definitions:

- Add ``$schema`` field at the top
- Change ``topics`` string to ``tags`` array: ``"topics": "A,B,C"`` → ``"tags": ["A", "B", "C"]``
- Change ``cmdparams`` to ``cmdparamsTemplate`` with ``${VAR}`` syntax instead of ``{VAR}``
- Remove deprecated fields: ``setup``, ``deploy``, ``requirements``, ``multiflexi``
- Add ``schemaVersion": "3.0.0"`` to explicitly declare compatibility

Advanced Application Examples
-----------------------------

**Basic Example:**

Here is a simple example conforming to schema version 3.0.0:

.. code-block:: json

  {
      "$schema": "https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json",
      "name": "RB transaction report",
      "description": "Raiffeisenbank transaction report",
      "executable": "raiffeisenbank-transaction-report",
      "uuid": "97f30cf9-2d9e-4d91-ad65-9bdd8b4663cd",
      "version": "1.0.0",
      "author": "Spoje.Net",
      "license": "MIT",
      "homepage": "https://github.com/Spoje-NET/raiffeisenbank-statement-tools",
      "ociimage": "docker.io/spojenet/raiffeisenbank-statement-tools:latest",
      "category": "Banking",
      "tags": ["Bank", "RaiffeisenBank", "Transactions", "Report"],
      "environment": {
          "ACCOUNT_NUMBER": {
              "type": "string",
              "category": "API",
              "description": "Bank Account Number",
              "required": true
          },
          "CERT_PASS": {
              "type": "password",
              "category": "Security",
              "description": "Certificate Password",
              "required": true
          }
      }
  }

**Advanced Example with Localization, Artifacts, and Data Flow:**

.. code-block:: json

  {
      "$schema": "https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json",
      "schemaVersion": "3.0.0",
      "name": {
          "en": "Invoice Processor",
          "cs": "Zpracovatel faktur",
          "de": "Rechnungsverarbeiter"
      },
      "description": {
          "en": "Processes invoices and generates reports",
          "cs": "Zpracovává faktury a generuje reporty",
          "de": "Verarbeitet Rechnungen und erstellt Berichte"
      },
      "executable": "invoice-processor",
      "uuid": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
      "version": "2.1.0",
      "author": "Your Company",
      "license": "GPL-3.0",
      "category": "Accounting",
      "tags": ["Invoice", "Accounting", "Report", "PDF"],
      "cmdparamsTemplate": "--config ${CONFIG_FILE} --output ${OUTPUT_DIR}",
      "environment": {
          "CONFIG_FILE": {
              "type": "file-path",
              "category": "Behavior",
              "description": {
                  "en": "Path to configuration file",
                  "cs": "Cesta ke konfiguračnímu souboru"
              },
              "hint": {
                  "en": "Use absolute path or relative to working directory",
                  "cs": "Použijte absolutní cestu nebo relativní k pracovnímu adresáři"
              },
              "defval": "/etc/invoice-processor/config.yaml",
              "required": false
          },
          "OUTPUT_DIR": {
              "type": "file-path",
              "category": "Behavior",
              "description": "Output directory for generated files",
              "defval": "/tmp/invoices",
              "required": true
          },
          "DB_CONNECTION": {
              "type": "string",
              "category": "Database",
              "description": "Database connection string",
              "required": true
          },
          "LOG_LEVEL": {
              "type": "set",
              "category": "Behavior",
              "description": "Logging verbosity level",
              "defval": "info",
              "options": ["debug", "info", "warning", "error"],
              "required": false
          }
      },
      "artifacts": [
          {
              "name": "invoice-report",
              "path": "${OUTPUT_DIR}/invoice-report.pdf",
              "type": "application/pdf",
              "description": "Generated invoice report in PDF format"
          },
          {
              "name": "metrics",
              "path": "${OUTPUT_DIR}/metrics.json",
              "type": "application/json",
              "description": {
                  "en": "Processing metrics and statistics",
                  "cs": "Metriky a statistiky zpracování"
              }
          }
      ],
      "produces": {
          "invoice-data": {
              "description": "Processed invoice data in JSON format",
              "format": "json",
              "patterns": ["${OUTPUT_DIR}/*.json"]
          },
          "reports": {
              "description": "PDF reports",
              "format": "file",
              "patterns": ["${OUTPUT_DIR}/*.pdf"]
          }
      },
      "consumes": {
          "raw-invoices": {
              "description": "Raw invoice files to process",
              "required": true,
              "format": "file"
          }
      }
  }

Report JSON Schema for Developers
----------------------------------

Applications can optionally emit structured execution reports consumed by MultiFlexi (and e.g. exported to monitoring/analysis systems). These reports are validated by a separate JSON Schema.

Purpose:

* Provide a consistent machine-readable structure for application output summaries
* Allow validation before ingestion (fail fast on malformed data)
* Enable tooling (dashboards, exporters) to rely on stable field names

Key Concepts (as defined in the report schema):

* Metadata about the producing application (UUID, name, version)
* Timing information (start/end timestamps, duration)
* Result classification (status / severity / exit code)
* Produced artifacts (paths, checksums, sizes) when relevant
* Metrics (numeric values with units or context)
* Messages / log excerpts (structured list)
* Optional links (URLs to external resources or dashboards)

Validation Schema:

`multiflexi.report.schema.json <https://github.com/VitexSoftware/php-vitexsoftware-multiflexi-core/blob/main/multiflexi.report.schema.json>`_

Basic Report Example:

.. code-block:: json

   {
     "app_uuid": "97f30cf9-2d9e-4d91-ad65-9bdd8b4663cd",
     "app_name": "RB transaction report",
     "generated_at": "2025-09-24T12:34:56Z",
     "status": "success",
     "duration_ms": 8421,
     "metrics": [
       { "name": "transactions_processed", "value": 128, "unit": "count" },
       { "name": "total_amount", "value": 51234.77, "unit": "CZK" }
     ],
     "artifacts": [
       { "path": "output/report-2025-09-24.json", "size": 20480 }
     ],
     "messages": [
       { "level": "info", "text": "Processing completed" }
     ]
   }

Implementation Notes:

* If your application already writes a domain-specific JSON output, you can wrap or transform it into the report schema just before exit.
* Keep timestamps in ISO 8601 (UTC) for portability.
* Use stable metric names—prefer lowercase with underscores.
* Omit sections (e.g. artifacts, metrics) rather than sending empty arrays if not applicable (schema usually allows absence).
* Validate locally during development with any JSON Schema validator before integrating.

Application Development Examples
--------------------------------

Examples for App developers can be found at:

- `MultiFlexi-Python-App-example <https://github.com/VitexSoftware/MultiFlexi-Python-App-example>`_
- `MultiFlexi-Java-App-Example <https://github.com/VitexSoftware/MultiFlexi-Java-App-Example>`_
- `MultiFlexi-Rust-App-Example <https://github.com/VitexSoftware/MultiFlexi-Rust-App-Example>`_

Examples for other languages coming soon.

