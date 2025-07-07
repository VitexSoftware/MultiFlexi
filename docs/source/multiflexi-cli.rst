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
- **company** - Manage companies and their settings
- **job** - Manage job execution and monitoring
- **runtemplate** - Manage run templates and scheduling
- **user** - User account management
- **token** - API token management
- **queue** - Job queue operations
- **appstatus** - System status information
- **describe** - List all available commands and their parameters
- **prune** - Prune logs and jobs, keeping only the latest N records (default: 1000)
- **completion** - Dump the shell completion script

Detailed Command Reference
-------------------------

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
- import-json: Import application from JSON file (requires --json).
- export-json: Export application to JSON file (requires --id, --json).
- remove-json: Remove application from JSON file (requires --json).
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
  --json         Path to JSON file for import/export/remove
  --appversion   Application Version
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    multiflexi-cli application list
    multiflexi-cli application get --id=1
    multiflexi-cli application create --name="App1" --uuid="uuid-123"
    multiflexi-cli application update --id=1 --name="App1 Updated"
    multiflexi-cli application delete --id=1
    multiflexi-cli application import-json --json=app.json
    multiflexi-cli application export-json --id=1 --json=app.json
    multiflexi-cli application showconfig --id=1

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
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

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
  --company_id   Company ID
  --interv       Interval code
  --active       Active
  --config       Application config key=value (repeatable)
  --schedule_time Schedule time for launch (Y-m-d H:i:s or "now")
  --executor     Executor to use for launch
  --env          Environment override key=value (repeatable)
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
  --password     Password
  --enabled      Enabled (true/false)
  -f, --format   Output format: text or json (default: text)

Examples:

.. code-block:: bash

    multiflexi-cli user list
    multiflexi-cli user get --id=1
    multiflexi-cli user create --login="jsmith" --firstname="John" --lastname="Smith" --email="jsmith@example.com" --password="secret"
    multiflexi-cli user update --id=1 --email="john.smith@example.com"
    multiflexi-cli user delete --id=1

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

completion
----------

Dump the shell completion script for bash, zsh, or fish.

.. code-block:: bash

    multiflexi-cli completion [shell]

Options:
  --debug        Tail the completion debug log

Examples:

.. code-block:: bash

    multiflexi-cli completion bash
    multiflexi-cli completion zsh
    multiflexi-cli completion fish

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
