.. _multiflexi-cli:

MultiFlexi CLI
==============

The MultiFlexi CLI is a Symfony Console-based command line interface for managing MultiFlexi jobs and companies.

Usage
-----

.. code-block:: bash

    multiflexi-cli <command> [action] [options]

Commands
--------

job
---

Manage jobs (list, get, create, update).

.. code-block:: bash

    multiflexi-cli job <action> [--id=ID] [--runtemplate_id=ID] [--scheduled=DATETIME] [--executor=EXECUTOR] [--schedule_type=TYPE] [--app_id=ID]

Actions:
- list:   List all jobs.
- get:    Get job details by ID.
- create: Create a new job (requires --runtemplate_id and --scheduled).
- update: Update an existing job (requires --id).

Examples:

.. code-block:: bash

    multiflexi-cli job list
    multiflexi-cli job get --id=123
    multiflexi-cli job create --runtemplate_id=5 --scheduled="2024-07-01 12:00"
    multiflexi-cli job update --id=123 --executor=Native

company
-------

Manage companies (list, get, create, update).

.. code-block:: bash

    multiflexi-cli company <action> [--id=ID] [--name=NAME] [--customer=CUSTOMER] [--server=SERVER]

Actions:
- list:   List all companies.
- get:    Get company details by ID.
- create: Create a new company (requires --name).
- update: Update an existing company (requires --id).

Examples:

.. code-block:: bash

    multiflexi-cli company list
    multiflexi-cli company get --id=1
    multiflexi-cli company create --name="Acme Corp" --customer="CustomerX"
    multiflexi-cli company update --id=1 --server="server.example.com"

companyapp
----------

Manage company applications (list, get, create, update).

Examples:
.. code-block:: bash

    multiflexi-cli companyapp list
    multiflexi-cli companyapp get --id=1
    multiflexi-cli companyapp create --company_id=1 --name="App1" --type="web"
    multiflexi-cli companyapp update --id=1 --name="Updated App"
    multiflexi-cli companyapp list  --company_id=1 --app_id=19 --format=json | jq '.[].id'

completion
----------

Dump the shell completion script for bash, zsh, or fish.

.. code-block:: bash

    multiflexi-cli completion [shell]

Options
-------

-h, --help
    Display help for a command.

-V, --version
    Display the application version.

Global Options
--------------

--ansi|--no-ansi
    Force (or disable) ANSI output.

-n, --no-interaction
    Do not ask any interactive question.

-v|vv|vvv, --verbose
    Increase the verbosity of messages.

Examples
--------

.. code-block:: bash

    multiflexi-cli job list
    multiflexi-cli company create --name="NewCo"
    multiflexi-cli completion bash

Author
------

MultiFlexi was written by Vítězslav Dvořák <info@vitexsoftware.cz>.

Copyright
---------

This is free software; see the source for copying conditions. There is NO warranty; not even for MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
