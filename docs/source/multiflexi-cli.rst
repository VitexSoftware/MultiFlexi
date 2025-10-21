.. _multiflexi-cli:

MultiFlexi CLI
==============

The MultiFlexi CLI is a powerful Symfony Console-based command line interface for comprehensive management of MultiFlexi resources. It provides full CRUD operations for all system entities and supports both text and JSON output formats for automation and scripting.

Installation
------------

The CLI is included with MultiFlexi and available as:

.. code-block:: bash

    # System-wide installation
    multiflexi-cli <command> [action] [options]
    
    # Local installation
    ./cli/multiflexi-cli <command> [action] [options]

General Usage
-------------

.. code-block:: bash

    multiflexi-cli <command> [action] [options]

**Global Options:**

- ``-f, --format`` - Output format: text or json (default: text)
- ``-v, --verbose`` - Increase verbosity (use -vv or -vvv for more detail)
- ``--no-ansi`` - Disable colored output
- ``-h, --help`` - Display help for the command
- ``-V, --version`` - Display application version

**Environment Configuration:**

Use the ``-e`` or ``--environment`` option to specify a custom .env file:

.. code-block:: bash

    multiflexi-cli -e /path/to/custom/.env command action

Commands Overview
-----------------

The MultiFlexi CLI provides the following main commands:

- **application** - Manage applications (import, export, configuration)
- **artifact** - Manage job artifacts (list, get, save)
- **company** - Manage companies and their settings
- **job** - Manage job execution and monitoring
- **runtemplate** - Manage run templates and scheduling
- **user** - User account management
- **token** - API token management
- **credtype** - Credential type operations
- **companyapp** - Manage company-application relations
- **queue** - Job queue operations
- **appstatus** - System status information
- **describe** - List all available commands and their parameters
- **prune** - Prune logs and jobs, keeping only the latest N records (default: 1000)
- **retention** - GDPR data retention management (calculate, cleanup, reporting)
- **completion** - Dump the shell completion script

Detailed Command Reference
--------------------------

.. contents::
   :local:
   :depth: 2

application
-----------

Manage applications (list, get, create, update, delete, import/export/remove JSON, show configuration fields).

.. code-block:: bash

    multiflexi-cli application <action> [options]

Actions:
- list:   List all applications.
- get:    Get application details by ID or UUID.
- create: Create a new application (requires --name, --uuid).
- update: Update an existing application (requires --id or --uuid).
- delete: Delete an application (requires --id).
- import-json: Import application from JSON file (requires --file).
- export-json: Export application to JSON file (requires --id, --file).
- remove-json: Remove application from JSON file (requires --file).
- validate-json: Validate application JSON file (requires --file).
- showconfig: Show defined configuration fields for application (requires --id or --uuid).

Options:
  --id           Application ID
  --uuid         Application UUID
  --name         Name
  --description  Description
  --topics       Topics
  --executable   Executable
  --ociimage     OCI Image
  --requirements Requirements
  --homepage     Homepage URL
  --file         Path to JSON file for import/export/remove/validate
  --appversion   Application Version
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    multiflexi-cli application list
    multiflexi-cli application get --id=1
    multiflexi-cli application create --name="App1" --uuid="uuid-123"
    multiflexi-cli application update --id=1 --name="App1 Updated"
    multiflexi-cli application delete --id=1
    multiflexi-cli application import-json --file=app.json
    multiflexi-cli application export-json --id=1 --file=app.json
    multiflexi-cli application showconfig --id=1
    multiflexi-cli application validate-json --file=app.json

artifact
--------

Manage job artifacts (list, get, save).

.. code-block:: bash

    multiflexi-cli artifact <action> [options]

Actions:
- list: List all artifacts or artifacts for a specific job.
- get:  Get artifact details by ID.
- save: Save artifact content to a file.

Options:
  --id           Artifact ID
  --job_id       Job ID to filter artifacts
  --file         File path to save artifact content to
  --fields       Comma-separated list of fields to display
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    multiflexi-cli artifact list
    multiflexi-cli artifact list --job_id=123
    multiflexi-cli artifact get --id=456
    multiflexi-cli artifact save --id=456 --file=output.txt
    multiflexi-cli artifact list --fields=id,name,size --format=json

company
-------

Manage companies (list, get, create, update, remove).

.. code-block:: bash

    multiflexi-cli company <action> [options]

Actions:
- list:   List all companies.
- get:    Get company details by ID.
- create: Create a new company (requires --name).
- update: Update an existing company (requires --id).
- remove: Remove a company (requires --id).

Options:
  --id           Company ID
  --name         Company name
  --customer     Customer
  --enabled      Enabled (true/false)
  --settings     Settings
  --logo         Logo
  --ic           IC
  --DatCreate    Created date (date-time)
  --DatUpdate    Updated date (date-time)
  --email        Email
  --slug         Company Slug
  --fields       Comma-separated list of fields to display
  --zabbix_host  Zabbix Host
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    multiflexi-cli company list
    multiflexi-cli company get --id=1
    multiflexi-cli company create --name="Acme Corp" --customer="CustomerX"
    multiflexi-cli company remove --id=1

job
---

Manage jobs (list, get, create, update, delete).

.. code-block:: bash

    multiflexi-cli job <action> [options]

Actions:
- status: Show job status aggregation.
- list:   List all jobs.
- get:    Get job details by ID.
- create: Create a new job (requires --runtemplate_id and --scheduled).
- update: Update an existing job (requires --id).
- delete: Delete a job by its ID.

Options:
  --id           Job ID
  --runtemplate_id RunTemplate ID
  --scheduled    Scheduled datetime
  --executor     Executor
  --schedule_type Schedule type
  --app_id       App ID
  --fields       Comma-separated list of fields to display
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    multiflexi-cli job status
    multiflexi-cli job list
    multiflexi-cli job get --id=123
    multiflexi-cli job create --runtemplate_id=5 --scheduled="2024-07-01 12:00"
    multiflexi-cli job update --id=123 --executor=Native
    multiflexi-cli job delete --id=123

runtemplate
-----------

Manage runtemplates (list, get, create, update, delete, schedule).

.. code-block:: bash

    multiflexi-cli runtemplate <action> [options]

Actions:
- list:   List all runtemplates.
- get:    Get runtemplate details by ID.
- create: Create a new runtemplate (requires --name, --app_id, --company_id).
- update: Update an existing runtemplate (requires --id).
- delete: Delete a runtemplate (requires --id).
- schedule: Schedule a runtemplate launch as a job (requires --id).

Options:
  --id           RunTemplate ID
  --name         Name
  --app_id       App ID
  --app_uuid     App UUID
  --company_id   Company ID
  --company      Company slug (string) or ID (integer)
  --interv       Interval code
  --cron         Crontab expression for scheduling
  --active       Active
  --config       Application config key=value (repeatable)
  --schedule_time Schedule time for launch (Y-m-d H:i:s or "now")
  --executor     Executor to use for launch
  --env          Environment override key=value (repeatable)
  --fields       Comma-separated list of fields to display
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    multiflexi-cli runtemplate create --name="Import Yesterday" --app_id=19 --company_id=1 --config=IMPORT_SCOPE=yesterday --config=ANOTHER_KEY=foo
    multiflexi-cli runtemplate update --id=230 --config=IMPORT_SCOPE=yesterday --config=ANOTHER_KEY=foo
    multiflexi-cli runtemplate get --id=230 --format=json
    multiflexi-cli runtemplate create --name="Import" --app_id=6e2b2c2e-7c2a-4b1a-8e2d-123456789abc --company_id=1
    multiflexi-cli runtemplate schedule --id=123 --schedule_time="2025-07-01 10:00:00" --executor=Native --env=FOO=bar --env=BAZ=qux

user
----

Manage users (list, get, create, update, delete).

.. code-block:: bash

    multiflexi-cli user <action> [options]

Actions:
- list:   List all users.
- get:    Get user details by ID.
- create: Create a new user (requires --login, --firstname, --lastname, --email, --password).
- update: Update an existing user (requires --id).
- delete: Delete a user (requires --id).

Options:
  --id           User ID
  --login        Login
  --firstname    First name
  --lastname     Last name
  --email        Email
  --password     Password (hashed)
  --plaintext    Plaintext password
  --enabled      Enabled (true/false)
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    multiflexi-cli user list
    multiflexi-cli user get --id=1
    multiflexi-cli user create --login="jsmith" --firstname="John" --lastname="Smith" --email="jsmith@example.com" --password="secret"
    multiflexi-cli user update --id=1 --email="john.smith@example.com"
    multiflexi-cli user delete --id=1

credtype
--------

Credential type operations (list, get, update, import, import-json, export-json, remove-json, validate-json).

.. code-block:: bash

    multiflexi-cli credtype <action> [options]

Actions:
- list: List all credential types.
- get: Get credential type details by ID or UUID.
- update: Update an existing credential type (requires --id or --uuid).
- import: Import credential type from file.
- import-json: Import credential type from JSON file (requires --file).
- export-json: Export credential type to JSON file (requires --id or --uuid, --file).
- remove-json: Remove credential type from JSON file (requires --file).
- validate-json: Validate credential type JSON file (requires --file).

Options:
  --id           Credential Type ID
  --uuid         Credential Type UUID
  --name         Name
  --file         Path to JSON file for import/export/remove/validate
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    multiflexi-cli credtype list
    multiflexi-cli credtype get --id=1
    multiflexi-cli credtype import-json --file=credtype.json
    multiflexi-cli credtype export-json --id=1 --file=credtype.json
    multiflexi-cli credtype validate-json --file=credtype.json

token
-----

Manage tokens (list, get, create, generate, update).

.. code-block:: bash

    multiflexi-cli token <action> [options]

Actions:
- list:   List all tokens.
- get:    Get token details by ID.
- create: Create a new token (requires --user).
- generate: Generate a new token value (requires --user).
- update: Update an existing token (requires --id).

Options:
  --id           Token ID
  --user         User ID
  --token        Token value
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    multiflexi-cli token list
    multiflexi-cli token get --id=1
    multiflexi-cli token create --user=2
    multiflexi-cli token generate --user=2
    multiflexi-cli token update --id=1 --token=NEWVALUE

companyapp
----------

Manage company-application relations (list, get, create, update, delete).

.. code-block:: bash

    multiflexi-cli companyapp <action> [options]

Actions:
- list: List all company-application relations.
- get: Get company-application relation details by ID.
- create: Create a new company-application relation (requires --company_id and --app_id or --app_uuid).
- update: Update an existing company-application relation (requires --id).
- delete: Delete a company-application relation (requires --id).

Options:
  --id           Relation ID
  --company_id   Company ID
  --app_id       Application ID
  --app_uuid     Application UUID
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    multiflexi-cli companyapp list
    multiflexi-cli companyapp get --id=1
    multiflexi-cli companyapp create --company_id=1 --app_id=5
    multiflexi-cli companyapp create --company_id=1 --app_uuid=6e2b2c2e-7c2a-4b1a-8e2d-123456789abc
    multiflexi-cli companyapp delete --id=1

queue
-----

Queue operations (list, truncate).

.. code-block:: bash

    multiflexi-cli queue <action> [options]

Actions:
- list:     Show all scheduled jobs in the queue.
- truncate: Remove all scheduled jobs from the queue.

Options:
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    multiflexi-cli queue list -f json
    multiflexi-cli queue truncate -f json

prune
-----

Prune logs and jobs, keeping only the latest N records (default: 1000).

.. code-block:: bash

    multiflexi-cli prune [--logs] [--jobs] [--keep=N]

Options:
  --logs         Prune logs table
  --jobs         Prune jobs table
  --keep         Number of records to keep (default: 1000)

Examples:

.. code-block:: bash

    multiflexi-cli prune --logs
    multiflexi-cli prune --jobs --keep=500
    multiflexi-cli prune --logs --jobs --keep=2000

retention
---------

GDPR data retention management commands for automated data lifecycle management.

.. code-block:: bash

    multiflexi-cli retention:cleanup <action> [options]

Actions:
- **calculate**: Calculate retention expiration dates for all data types
- **cleanup**: Execute scheduled cleanup (with optional --dry-run)
- **grace-period**: Process grace period cleanup (final deletions)
- **archive-cleanup**: Clean up expired archives (requires --days)
- **report**: Generate compliance reports (supports --format and --output)
- **status**: Show current retention status

Options:
  --dry-run      Execute cleanup in dry-run mode (show what would be deleted)
  --days         Number of days for archive cleanup
  --format       Output format: json, csv, html (default: text)
  --output       Output file path for reports
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    # Calculate retention expiration dates
    multiflexi-cli retention:cleanup calculate
    
    # Run cleanup in dry-run mode to preview actions
    multiflexi-cli retention:cleanup cleanup --dry-run
    
    # Execute actual cleanup
    multiflexi-cli retention:cleanup cleanup
    
    # Process grace period deletions
    multiflexi-cli retention:cleanup grace-period
    
    # Clean archives older than 7 years (2555 days)
    multiflexi-cli retention:cleanup archive-cleanup --days=2555
    
    # Generate compliance report in JSON format
    multiflexi-cli retention:cleanup report --format=json --output=compliance-report.json
    
    # Check retention status
    multiflexi-cli retention:cleanup status
    
    # Validate application JSON against GDPR schema
    multiflexi-cli application validate-json --file multiflexi/app.json

**GDPR Compliance Integration**

The retention commands integrate with MultiFlexi's comprehensive GDPR compliance framework:

- **Automated Scheduling**: Set up cron jobs for regular cleanup execution
- **Audit Trails**: All retention actions are logged for compliance evidence
- **Grace Periods**: Configurable grace periods before final data deletion
- **Archive Management**: Secure archival with integrity verification
- **Compliance Reporting**: Generate reports for regulatory requirements

For complete GDPR compliance documentation, see :doc:`gdpr-compliance`.

completion
----------

Dump the shell completion script for bash, zsh, or fish.

.. code-block:: bash

    multiflexi-cli completion [shell]

Arguments:
  shell          The shell type (e.g. "bash"), the value of the "$SHELL" env var will be used if this is not given

Options:
  --debug        Tail the completion debug log

Examples:

.. code-block:: bash

    multiflexi-cli completion bash
    multiflexi-cli completion zsh
    multiflexi-cli completion fish
    multiflexi-cli completion --debug

describe
--------

List all available commands and their parameters.

.. code-block:: bash

    multiflexi-cli describe

appstatus
---------

Prints App Status.

.. code-block:: bash

    multiflexi-cli appstatus

Credential Type Import
----------------------

MultiFlexi supports importing credential type definitions via the CLI. This allows administrators to define new credential types in JSON format and load them into the system for use in app and integration configurations.

.. code-block:: bash

    multiflexi-cli credtype import --file example.credential-type.json

- The command reads the credential type from the specified file and imports it into MultiFlexi.
- The JSON file must conform to the :ref:`credential-type-schema`.
- Imported credential types are available for assignment to apps and integrations.

See :doc:`credential-type` for schema details and examples.
