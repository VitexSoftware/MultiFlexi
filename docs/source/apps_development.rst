=================================
MultiFlexi Application Development
=================================

This guide explains how to create MultiFlexi applications that can be managed and executed by the MultiFlexi platform.

Overview
========

A MultiFlexi application consists of:

1. **Application JSON definition** - Describes the application, its configuration, and environment variables
2. **Executable script(s)** - The actual program that performs the work
3. **Source code** - Business logic implementation
4. **Configuration files** - composer.json, .env.example, etc.

Project Structure
=================

A typical MultiFlexi application project should have the following structure:

.. code-block:: text

    your-application/
    ├── bin/
    │   ├── app-command-1
    │   └── app-command-2
    ├── src/
    │   └── YourApplication.php
    ├── multiflexi/
    │   ├── app-name-1.multiflexi.app.json
    │   └── app-name-2.multiflexi.app.json
    ├── composer.json
    ├── .env.example
    └── README.md

Application JSON Definition
===========================

The application definition is a JSON file that conforms to the MultiFlexi schema.

Schema Location
---------------

The schema is available at:
https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json

Basic Structure
---------------

.. code-block:: json

    {
      "$schema": "https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json",
      "schemaVersion": "3.2.1",
      "uuid": "unique-uuid-here",
      "version": "1.0.0",
      "name": {
        "en": "Application Name",
        "cs": "Název aplikace"
      },
      "description": {
        "en": "Application description",
        "cs": "Popis aplikace"
      },
      "tags": [
        "Tag1",
        "Tag2"
      ],
      "homepage": "https://github.com/yourorg/your-app",
      "executable": "command-name",
      "environment": {
        "ENV_VAR_NAME": {
          "type": "string",
          "description": {
            "en": "Description",
            "cs": "Popis"
          },
          "defval": "default_value",
          "required": true,
          "category": "API"
        }
      }
    }

Required Fields
---------------

* **$schema** - Reference to the MultiFlexi schema
* **schemaVersion** - Current version is "3.2.1"
* **uuid** - Unique identifier for your application (generate using uuidgen)
* **version** - Application version (semantic versioning)
* **name** - Localized application name (at least "en" and "cs")
* **description** - Localized description
* **tags** - Array of tags for categorization
* **homepage** - Project homepage URL
* **executable** - Name of the command to execute
* **environment** - Environment variable definitions

Environment Variable Types
--------------------------

Supported types:

* **string** - Text value
* **password** - Sensitive text (will be hidden in UI)
* **bool** - Boolean value (true/false)
* **integer** - Numeric value
* **set** - Predefined set of options

Environment Variable Categories
-------------------------------

* **API** - API credentials and endpoints
* **Behavior** - Application behavior settings
* **Security** - Security-related settings
* **Other** - Other configuration

Example Environment Variable
----------------------------

.. code-block:: json

    "ABRAFLEXI_URL": {
      "type": "string",
      "description": {
        "en": "AbraFlexi Server URI",
        "cs": "Adresa serveru AbraFlexi"
      },
      "defval": "https://demo.flexibee.eu:5434",
      "required": true,
      "category": "API"
    }

Example with Set Type
---------------------

.. code-block:: json

    "LANG": {
      "type": "set",
      "description": {
        "en": "Locale for this application",
        "cs": "Jazykové nastavení aplikace"
      },
      "defval": "cs_CZ",
      "required": false,
      "options": [
        "cs_CZ",
        "en_US"
      ],
      "category": "Other"
    }

Composer Configuration
======================

Create a ``composer.json`` file to define dependencies:

.. code-block:: json

    {
      "name": "yourorg/your-application",
      "description": "Your application description",
      "version": "1.0.0",
      "type": "project",
      "license": "MIT",
      "authors": [
        {
          "name": "Your Name",
          "email": "your.email@example.com"
        }
      ],
      "require": {
        "php": ">=7.4",
        "vitexsoftware/ease-core": "*",
        "vitexsoftware/multiflexi-core": "*"
      },
      "autoload": {
        "psr-4": {
          "YourNamespace\\": "src/"
        }
      },
      "minimum-stability": "stable",
      "prefer-stable": true
    }

Executable Scripts
==================

Create executable scripts in the ``bin/`` directory.

Example (bin/your-command):

.. code-block:: bash

    #!/bin/bash
    php -f /usr/share/your-application/YourScript.php -- $@

Make the script executable:

.. code-block:: bash

    chmod +x bin/your-command

PHP Application Code
====================

Create your application logic in the ``src/`` directory.

Basic Structure
---------------

.. code-block:: php

    <?php

    declare(strict_types=1);

    namespace YourNamespace;

    use Ease\Shared;

    require_once '../vendor/autoload.php';

    \define('EASE_APPNAME', 'YourApplication');
    $exitcode = 0;

    // Parse command line options
    $options = getopt('o::e::', ['output::environment::']);

    // Initialize configuration from environment
    Shared::init(
        [
            'REQUIRED_VAR_1',
            'REQUIRED_VAR_2',
            'ABRAFLEXI_URL',
            'ABRAFLEXI_LOGIN',
            'ABRAFLEXI_PASSWORD',
        ],
        \array_key_exists('environment', $options) ? $options['environment'] : 
            (\array_key_exists('e', $options) ? $options['e'] : '../.env'),
    );

    // Set output destination
    $destination = \array_key_exists('output', $options) ? 
        $options['output'] : Shared::cfg('RESULT_FILE', 'php://stdout');

    // Initialize localization if needed
    \Ease\Locale::singleton(null, '../i18n', 'your-app');

    // Your application logic here
    $yourApp = new YourApplication();

    if (Shared::cfg('APP_DEBUG', false)) {
        $yourApp->logBanner();
    }

    $report = $yourApp->process();

    // Generate MultiFlexi-compliant report
    $multiFlexiReport = $yourApp->generateMultiFlexiReport(
        $report, 
        'operation_type', 
        $exitcode
    );

    $written = file_put_contents(
        $destination, 
        json_encode($multiFlexiReport, \JSON_PRETTY_PRINT)
    );

    $yourApp->addStatusMessage(
        sprintf('MultiFlexi report saved to %s', $destination),
        $written ? 'success' : 'error'
    );

    exit($exitcode);

Environment Configuration
=========================

Create a ``.env.example`` file with placeholder values:

.. code-block:: bash

    # AbraFlexi Configuration
    ABRAFLEXI_URL=https://demo.flexibee.eu:5434
    ABRAFLEXI_LOGIN=winstrom
    ABRAFLEXI_PASSWORD=placeholder_password
    ABRAFLEXI_COMPANY=demo_company

    # Application Configuration
    EASE_LOGGER=console|syslog
    RESULT_FILE=result.json

    # Application Behavior
    APP_DEBUG=false

**Important:** Do not include real passwords in ``.env.example``

MultiFlexi Report Generation
=============================

All MultiFlexi applications must produce a JSON report that conforms to the MultiFlexi report schema.

Report Schema
-------------

The report schema is available at:
https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/schema/report.json

Required Fields
---------------

* **producer** - Name of the script/application that generated the report (string)
* **status** - Application execution result: "success", "error", or "warning" (string)
* **timestamp** - Execution completion time in ISO8601 format (string)

Optional Fields
---------------

* **message** - Human-readable message about the execution result (string)
* **artifacts** - Artifacts produced by the application, keys correspond to names in 'produces' section (object)
* **metrics** - Additional execution metrics with numeric or string values (object)

Report Structure
----------------

.. code-block:: json

    {
      "producer": "YourApplicationName",
      "status": "success",
      "timestamp": "2025-01-21T17:45:00+01:00",
      "message": "Processing completed successfully",
      "artifacts": {
        "invoices": [
          "/path/to/invoice1.pdf",
          "https://example.com/invoice/123"
        ]
      },
      "metrics": {
        "processed_count": 42,
        "created_count": 38,
        "skipped_count": 4,
        "total_amount": 15000.50,
        "exit_code": 0
      }
    }

Generating Reports in PHP
-------------------------

Here's a complete example of generating a compliant report:

.. code-block:: php

    <?php
    
    // Track metrics during processing
    $processedCount = 0;
    $successCount = 0;
    $errorCount = 0;
    $exitcode = 0;
    
    // Your application logic here
    // ...
    
    // Determine overall status
    $hasErrors = false;
    $hasWarnings = false;
    
    // Check status messages for errors/warnings
    foreach ($yourApp->getStatusMessages() as $message) {
        if (isset($message['type'])) {
            if ($message['type'] === 'error') {
                $hasErrors = true;
                $exitcode = 1;
            }
            if ($message['type'] === 'warning') {
                $hasWarnings = true;
            }
        }
    }
    
    // Set status based on errors and warnings
    if ($hasErrors) {
        $status = 'error';
        $message = 'Processing completed with errors';
    } elseif ($hasWarnings) {
        $status = 'warning';
        $message = sprintf(
            'Processed %d items with %d warnings',
            $processedCount,
            $warningCount
        );
    } else {
        $status = 'success';
        $message = sprintf(
            'Successfully processed %d items',
            $successCount
        );
    }
    
    // Build the report
    $report = [
        'producer' => 'YourApplicationName',
        'status' => $status,
        'timestamp' => date('c'),  // ISO8601 format
        'message' => $message,
        'metrics' => [
            'processed_count' => $processedCount,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'exit_code' => $exitcode,
        ],
    ];
    
    // Optionally add artifacts if your app produces files/URLs
    if (!empty($createdFiles)) {
        $report['artifacts'] = [
            'documents' => $createdFiles,
        ];
    }
    
    // Write report to destination
    $written = file_put_contents(
        $destination, 
        json_encode($report, \JSON_PRETTY_PRINT)
    );
    
    exit($exitcode);

Complete Report Example
-----------------------

Here's a real-world example from the BlockNet application:

.. code-block:: php

    $report = [
        'producer' => 'BlockNet',
        'status' => 'warning',
        'timestamp' => '2025-01-21T17:45:00+01:00',
        'message' => 'Blocked 15 clients with ODPOJEN label. Skipped 2 VIP and 1 NODISCONNECT clients.',
        'metrics' => [
            'total_disconnected_customers' => 18,
            'clients_blocked' => 15,
            'vip_skipped' => 2,
            'no_disconnect_skipped' => 1,
            'exit_code' => 0,
        ],
    ];

Status Determination Guidelines
--------------------------------

1. **success** - All operations completed without errors or warnings
2. **warning** - Operations completed but with non-critical issues (e.g., skipped items)
3. **error** - Critical errors occurred, operations may have failed

Best Practices for Reports
--------------------------

1. **Always include producer** - Use a consistent, descriptive name for your application
2. **Use ISO8601 timestamps** - Use ``date('c')`` in PHP for proper formatting
3. **Provide clear messages** - Make messages human-readable and informative
4. **Include relevant metrics** - Add metrics that help understand what happened
5. **Match exit codes** - Include exit_code in metrics and use it with exit()
6. **Document artifacts** - If your app produces files/URLs, list them in artifacts
7. **Be consistent** - Use the same metric names across similar applications

Validating Application JSON
===========================

Use the multiflexi-cli tool to validate your application JSON:

.. code-block:: bash

    multiflexi-cli application validate-json --file multiflexi/your-app.multiflexi.app.json

Successful validation will output:

.. code-block:: text

    JSON is valid

Common Environment Variables
============================

AbraFlexi Integration
---------------------

For applications that integrate with AbraFlexi:

* **ABRAFLEXI_URL** - AbraFlexi server URL
* **ABRAFLEXI_LOGIN** - Username
* **ABRAFLEXI_PASSWORD** - Password (type: password)
* **ABRAFLEXI_COMPANY** - Company code

Logging and Output
------------------

* **EASE_LOGGER** - Logger type (e.g., "console|syslog")
* **RESULT_FILE** - Output file path for results
* **APP_DEBUG** - Debug mode flag (type: bool)

Localization
------------

* **LANG** - Application locale (e.g., "cs_CZ", "en_US")

Complete Example: ISP Tools
===========================

Here's a complete example of a MultiFlexi application:

Directory Structure
-------------------

.. code-block:: text

    isp-tools/
    ├── bin/
    │   ├── blocknet
    │   └── unblocknet
    ├── src/
    │   ├── BlockNet.php
    │   └── UnblockNet.php
    ├── multiflexi/
    │   ├── blocknet.multiflexi.app.json
    │   └── unblocknet.multiflexi.app.json
    ├── composer.json
    ├── .env.example
    └── README.md

Application JSON (blocknet.multiflexi.app.json)
-----------------------------------------------

.. code-block:: json

    {
      "$schema": "https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json",
      "schemaVersion": "3.2.1",
      "uuid": "8f3c4a5e-9b2d-4e1f-a6d8-7c9e0f1a2b3c",
      "version": "1.0.0",
      "name": {
        "en": "Block Internet Access",
        "cs": "Zablokovat přístup k internetu"
      },
      "description": {
        "en": "Block internet access for all clients with ODPOJEN label",
        "cs": "Zablokuje internet všem klientům kteří mají štítek ODPOJEN"
      },
      "tags": [
        "AbraFlexi",
        "ISP",
        "Network"
      ],
      "homepage": "https://github.com/Spoje-NET/isp-tools",
      "executable": "blocknet",
      "environment": {
        "ABRAFLEXI_COMPANY": {
          "type": "string",
          "description": {
            "en": "AbraFlexi Company code",
            "cs": "Kód společnosti v AbraFlexi"
          },
          "defval": "",
          "required": true,
          "category": "API"
        },
        "ABRAFLEXI_LOGIN": {
          "type": "string",
          "description": {
            "en": "AbraFlexi Login",
            "cs": "Přihlašovací jméno do AbraFlexi"
          },
          "defval": "",
          "required": true,
          "category": "API"
        },
        "ABRAFLEXI_PASSWORD": {
          "type": "password",
          "description": {
            "en": "AbraFlexi password",
            "cs": "Heslo do AbraFlexi"
          },
          "defval": "",
          "required": true,
          "category": "API"
        },
        "ABRAFLEXI_URL": {
          "type": "string",
          "description": {
            "en": "AbraFlexi Server URI",
            "cs": "Adresa serveru AbraFlexi"
          },
          "defval": "https://demo.flexibee.eu:5434",
          "required": true,
          "category": "API"
        },
        "RESULT_FILE": {
          "type": "string",
          "description": {
            "en": "Write output json data to",
            "cs": "Zapsat výstupní json data do"
          },
          "defval": "blocknet_result.json",
          "required": false,
          "category": "Other"
        },
        "EASE_LOGGER": {
          "type": "string",
          "description": {
            "en": "Write log messages using",
            "cs": "Způsob zápisu logovacích zpráv"
          },
          "defval": "console|syslog",
          "required": false,
          "category": "Behavior"
        }
      }
    }

Executable Script (bin/blocknet)
---------------------------------

.. code-block:: bash

    #!/bin/bash
    php -f /usr/share/isp-tools/BlockNet.php -- $@

Best Practices
==============

1. **Use Unique UUIDs** - Generate unique UUIDs for each application using ``uuidgen``
2. **Provide Localization** - Include at least English ("en") and Czech ("cs") translations
3. **Use Semantic Versioning** - Follow semver for the version field
4. **Validate Before Commit** - Always validate JSON files before committing
5. **Secure Passwords** - Mark password fields with ``"type": "password"``
6. **Document Environment Variables** - Provide clear descriptions for all variables
7. **Use Standard Categories** - Stick to standard categories (API, Behavior, Security, Other)
8. **Generate MultiFlexi Reports** - Output JSON reports compatible with MultiFlexi
9. **Support Command Line Options** - Accept ``-e`` for environment file and ``-o`` for output
10. **Handle Exit Codes** - Return appropriate exit codes (0 for success)

Testing Your Application
========================

1. Validate the JSON definition:

   .. code-block:: bash

       multiflexi-cli application validate-json --file multiflexi/your-app.multiflexi.app.json

2. Install dependencies:

   .. code-block:: bash

       composer install

3. Create a test environment file:

   .. code-block:: bash

       cp .env.example .env
       # Edit .env with test credentials

4. Run the application:

   .. code-block:: bash

       bin/your-command

Deployment
==========

For production deployment:

1. Package your application (Debian package, Docker image, etc.)
2. Install to standard locations (``/usr/share/your-app/`` for code)
3. Place executables in ``/usr/bin/`` or ``/usr/local/bin/``
4. Register with MultiFlexi using the JSON definition
5. Configure environment variables through MultiFlexi UI

For AI Assistants
=================

When creating MultiFlexi applications:

1. **Always validate JSON** - Use ``multiflexi-cli application validate-json --file``
2. **Follow the structure** - Use the standard directory layout
3. **Check existing applications** - Look at examples in ~/Projects for patterns
4. **Generate unique UUIDs** - Never reuse UUIDs from other applications
5. **Include both languages** - Provide "en" and "cs" translations for all user-facing text
6. **Mark sensitive data** - Use ``"type": "password"`` for credentials
7. **Generate compliant reports** - All applications must produce reports following the schema at https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/schema/report.json
8. **Include required report fields** - Always include producer, status, and timestamp in reports
9. **Validate before finishing** - Always confirm JSON validity as the final step
10. **Update documentation** - Include README.md with usage instructions

Report Generation Checklist for AI
-----------------------------------

When generating reports in MultiFlexi applications:

* Include ``'producer' => 'ApplicationName'`` with the application name
* Set ``'status'`` to "success", "error", or "warning" based on execution result
* Use ``'timestamp' => date('c')`` for ISO8601 format
* Add a human-readable ``'message'`` describing what happened
* Include ``'metrics'`` object with relevant counters and the exit_code
* Optionally add ``'artifacts'`` for produced files/URLs
* Write report using ``json_encode($report, \JSON_PRETTY_PRINT)``
* Exit with appropriate exit code: 0 for success, non-zero for errors

Troubleshooting
===============

JSON Validation Fails
---------------------

* Check that all required fields are present
* Verify JSON syntax (commas, brackets, quotes)
* Ensure schemaVersion is "3.2.1"
* Confirm UUID is unique and properly formatted

Application Doesn't Execute
---------------------------

* Verify executable permissions (``chmod +x``)
* Check shebang line in script (``#!/bin/bash``)
* Ensure PHP file path is correct
* Verify all dependencies are installed

Environment Variables Not Loaded
---------------------------------

* Check ``.env`` file exists and is readable
* Verify variable names match JSON definition
* Ensure ``Shared::init()`` includes all required variables
* Check for typos in variable names

Resources
=========

* Application Schema: https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json
* Report Schema: https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/schema/report.json
* Example applications: ~/Projects/VitexSoftware/, ~/Projects/SpojeNet/
* MultiFlexi CLI: Required version 2.2.0 or newer
