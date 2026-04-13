Upgrading MultiFlexi
====================

**Target Audience:** Administrators
**Difficulty:** Intermediate
**Prerequisites:** Root or sudo access; working backup

.. contents::
   :local:
   :depth: 2

Before You Upgrade
-------------------

1. **Read the release notes** for the target version on `GitHub Releases <https://github.com/VitexSoftware/MultiFlexi/releases>`_.
2. **Create a database backup** — see :doc:`backup-recovery`.
3. **Verify free disk space**: migrations can temporarily double index sizes.
4. **Schedule a maintenance window** if jobs are running in production.

Standard Upgrade (APT)
-----------------------

MultiFlexi is distributed via the ``repo.multiflexi.eu`` APT repository. A standard upgrade is:

.. code-block:: bash

   sudo apt update
   sudo apt upgrade multiflexi

This upgrades the main web package. To upgrade all ecosystem components at once:

.. code-block:: bash

   sudo apt update
   sudo apt upgrade

.. tip::

   To upgrade only MultiFlexi packages (not the whole system):

   .. code-block:: bash

      sudo apt upgrade $(apt list --installed 2>/dev/null | grep multiflexi | cut -d/ -f1 | tr '\n' ' ')

What Happens During Upgrade
-----------------------------

1. ``dpkg`` stops the affected services (scheduler, executor, eventor)
2. New package files are installed
3. The ``postinst`` script runs database migrations via Phinx automatically
4. Services are restarted

The migration step may take a few seconds to several minutes depending on database size.

Verifying the Upgrade
----------------------

.. code-block:: bash

   # Check installed version
   dpkg -l multiflexi | grep multiflexi

   # Check all MultiFlexi packages
   dpkg -l | grep multiflexi

   # Verify services are running
   systemctl status multiflexi-scheduler multiflexi-executor multiflexi-eventor

   # Verify web interface loads
   curl -s -o /dev/null -w "%{http_code}" http://localhost/multiflexi/

Upgrading Individual Components
---------------------------------

The MultiFlexi ecosystem is modular. Components can be upgraded independently as long as dependency version constraints are satisfied.

.. code-block:: bash

   # Upgrade only the executor
   sudo apt install multiflexi-executor

   # Upgrade only the core library
   sudo apt install php-vitexsoftware-multiflexi-core

   # Upgrade CLI tools
   sudo apt install multiflexi-cli

   # Upgrade a credential plugin
   sudo apt install multiflexi-abraflexi

After upgrading a core library, restart all services:

.. code-block:: bash

   sudo systemctl restart multiflexi-scheduler multiflexi-executor multiflexi-eventor

Database Migration Notes
-------------------------

Migrations run automatically during package install/upgrade. To run them manually:

.. code-block:: bash

   # Run pending migrations
   sudo -u multiflexi php /usr/share/multiflexi/vendor/bin/phinx migrate -c /etc/multiflexi/phinx.php

   # Check migration status
   sudo -u multiflexi php /usr/share/multiflexi/vendor/bin/phinx status -c /etc/multiflexi/phinx.php

.. warning::

   Never skip migrations when upgrading multiple versions. Always migrate sequentially through each intermediate version if skipping releases.

Rollback Procedure
-------------------

If an upgrade fails, restore from backup and downgrade:

.. code-block:: bash

   # Stop services
   sudo systemctl stop multiflexi-scheduler multiflexi-executor multiflexi-eventor

   # Restore database (see backup-recovery)
   sudo mysql multiflexi < /path/to/backup.sql

   # Downgrade to specific version (if still in APT cache)
   sudo apt install multiflexi=2.2.6

   # Restart services
   sudo systemctl start multiflexi-scheduler multiflexi-executor multiflexi-eventor

.. note::

   Downgrading is only safe when no forward-only migrations have been applied. Always test upgrades in a staging environment first.

Upgrading Docker Deployments
-----------------------------

.. code-block:: bash

   cd /path/to/multiflexi-docker/

   # Pull new images
   docker compose pull

   # Recreate containers with new images
   docker compose up -d --force-recreate

   # Check logs
   docker compose logs -f

See :doc:`docker` for the full Docker deployment guide.

See Also
--------

- :doc:`backup-recovery` — How to back up before upgrading
- :doc:`database-maintenance` — Migration details
- :doc:`../reference/configuration` — Configuration changes between versions
