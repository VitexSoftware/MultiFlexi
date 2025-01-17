.. _multiflexi-cli:

MultiFlexi CLI
==============

The MultiFlexi CLI is a command line interface for managing MultiFlexi.

Usage
-----

.. code-block:: bash

    multiflexi-cli <command> [argument] [identifier] [property] [option]

Commands
--------

version
-------

Display the application name and version.

.. code-block:: bash

    multiflexi-cli version

remove
------

Remove a user, app, company, runtemplate, or job.

.. code-block:: bash

    multiflexi-cli remove <type> <identifier>

Types:
- user: Remove a user by identifier.
- app: Remove an app by identifier.
- company: Remove a company by identifier.
- runtemplate: Remove a runtemplate by identifier.
- job: Remove a job by identifier.

status
------

Display the status of the application.

.. code-block:: bash

    multiflexi-cli status [format] [jobs]

Arguments:
- format: Optional. Can be 'plaintext' (default) or 'json'.
- jobs: Optional. Include job-related status information.

Options
-------

--help
------

Display this help message.

Examples
--------

.. code-block:: bash

    multiflexi-cli version
    multiflexi-cli remove user 123
    multiflexi-cli status
    multiflexi-cli status json
    multiflexi-cli status jobs

Author
------

MultiFlexi was written by Vítězslav Dvořák <info@vitexsoftware.cz>.

Copyright
---------

This is free software; see the source for copying conditions. There is NO warranty; not even for MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.