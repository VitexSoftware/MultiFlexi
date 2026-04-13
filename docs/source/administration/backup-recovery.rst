Backup and Recovery
====================

**Target Audience:** Administrators
**Difficulty:** Intermediate
**Prerequisites:** Database access; root or sudo access

.. contents::
   :local:
   :depth: 2

What to Back Up
---------------

A complete MultiFlexi backup consists of:

1. **Database** — all configuration, jobs, credentials, artifacts, logs
2. **Configuration file** — ``/etc/multiflexi/multiflexi.env``
3. **Encryption master key** — ``ENCRYPTION_MASTER_KEY`` from the env file (critical!)
4. **Uploaded application JSON files** (if any are stored locally)

.. warning::

   The ``ENCRYPTION_MASTER_KEY`` is required to decrypt stored credentials. Without it,
   a restored database is useless. Store this key in a separate, secure location.

Database Backup
----------------

MySQL / MariaDB
~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Full database dump
   mysqldump -u root -p multiflexi > /backup/multiflexi_$(date +%Y%m%d_%H%M%S).sql

   # Compressed
   mysqldump -u root -p multiflexi | gzip > /backup/multiflexi_$(date +%Y%m%d_%H%M%S).sql.gz

   # Non-interactive (using credentials file or .my.cnf)
   mysqldump --defaults-file=/etc/mysql/multiflexi-backup.cnf multiflexi > /backup/multiflexi.sql

PostgreSQL
~~~~~~~~~~~

.. code-block:: bash

   # As postgres user
   sudo -u postgres pg_dump multiflexi > /backup/multiflexi_$(date +%Y%m%d_%H%M%S).sql

   # Compressed
   sudo -u postgres pg_dump -Fc multiflexi > /backup/multiflexi_$(date +%Y%m%d_%H%M%S).dump

SQLite
~~~~~~~

.. code-block:: bash

   # Simple file copy (while services are stopped for consistency)
   sudo systemctl stop multiflexi-scheduler multiflexi-executor multiflexi-eventor
   cp /var/lib/multiflexi/multiflexi.db /backup/multiflexi_$(date +%Y%m%d_%H%M%S).db
   sudo systemctl start multiflexi-scheduler multiflexi-executor multiflexi-eventor

Configuration Backup
---------------------

.. code-block:: bash

   sudo cp /etc/multiflexi/multiflexi.env /backup/multiflexi.env_$(date +%Y%m%d)

Automating Backups
-------------------

Example cron job (daily at 2:00 AM):

.. code-block:: bash

   # /etc/cron.d/multiflexi-backup
   0 2 * * * root mysqldump -u root multiflexi | gzip > /backup/multiflexi_$(date +\%Y\%m\%d).sql.gz && find /backup -name "multiflexi_*.sql.gz" -mtime +30 -delete

Adjust the retention period (``-mtime +30``) and backup path to match your policy.

Database Recovery
------------------

MySQL / MariaDB
~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Stop services first
   sudo systemctl stop multiflexi-scheduler multiflexi-executor multiflexi-eventor

   # Drop and recreate database
   mysql -u root -p -e "DROP DATABASE multiflexi; CREATE DATABASE multiflexi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

   # Restore
   mysql -u root -p multiflexi < /backup/multiflexi_20250101_020000.sql

   # Start services
   sudo systemctl start multiflexi-scheduler multiflexi-executor multiflexi-eventor

PostgreSQL
~~~~~~~~~~~

.. code-block:: bash

   sudo systemctl stop multiflexi-scheduler multiflexi-executor multiflexi-eventor

   sudo -u postgres dropdb multiflexi
   sudo -u postgres createdb multiflexi

   # Plain SQL restore
   sudo -u postgres psql multiflexi < /backup/multiflexi.sql

   # Custom format restore
   sudo -u postgres pg_restore -d multiflexi /backup/multiflexi.dump

   sudo systemctl start multiflexi-scheduler multiflexi-executor multiflexi-eventor

Post-Recovery Checks
---------------------

After restoring:

1. Verify the configuration file matches the restored database:

   .. code-block:: bash

      cat /etc/multiflexi/multiflexi.env

2. Run pending migrations (in case the backup predates the current schema):

   .. code-block:: bash

      sudo -u multiflexi php /usr/share/multiflexi/vendor/bin/phinx migrate -c /etc/multiflexi/phinx.php

3. Verify services start cleanly:

   .. code-block:: bash

      sudo systemctl restart multiflexi-scheduler multiflexi-executor multiflexi-eventor
      systemctl status multiflexi-scheduler multiflexi-executor multiflexi-eventor

4. Open the web interface and confirm companies, run templates, and credentials are present.

Disaster Recovery Checklist
-----------------------------

In the event of total system loss:

1. Install the operating system and add the MultiFlexi APT repository (see :doc:`../install`)
2. Install MultiFlexi: ``sudo apt install multiflexi-mysql`` (or your DB variant)
3. Restore ``/etc/multiflexi/multiflexi.env`` including the ``ENCRYPTION_MASTER_KEY``
4. Restore the database
5. Install the same credential plugin packages that were previously installed
6. Restart services

See Also
--------

- :doc:`database-maintenance` — Migration and maintenance commands
- :doc:`upgrading` — Pre-upgrade backup recommendations
- :doc:`../reference/configuration` — Encryption key configuration
