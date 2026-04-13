Database Maintenance
=====================

**Target Audience:** Administrators
**Difficulty:** Intermediate
**Prerequisites:** Database access; basic SQL knowledge

.. contents::
   :local:
   :depth: 2

Overview
--------

MultiFlexi uses `Phinx <https://phinx.org/>`_ for database schema management. Migrations run automatically during package install/upgrade via the ``postinst`` script. This page covers manual maintenance tasks.

Supported Databases
--------------------

+-------------+----------------------------------+-----------------+
| Backend     | Package                          | Recommended for |
+=============+==================================+=================+
| MySQL 8.0+  | ``multiflexi-mysql``             | Production      |
+-------------+----------------------------------+-----------------+
| MariaDB 10+ | ``multiflexi-mysql``             | Production      |
+-------------+----------------------------------+-----------------+
| PostgreSQL  | ``multiflexi-postgresql``        | Production      |
+-------------+----------------------------------+-----------------+
| SQLite 3    | ``multiflexi-sqlite``            | Dev / Testing   |
+-------------+----------------------------------+-----------------+

Checking Migration Status
--------------------------

.. code-block:: bash

   sudo -u multiflexi php /usr/share/multiflexi/vendor/bin/phinx status \
     -c /etc/multiflexi/phinx.php

Output shows each migration with its status (``up`` = applied, ``down`` = pending).

Running Pending Migrations
---------------------------

Migrations run automatically on upgrade, but can be triggered manually:

.. code-block:: bash

   sudo -u multiflexi php /usr/share/multiflexi/vendor/bin/phinx migrate \
     -c /etc/multiflexi/phinx.php

Rolling Back a Migration
-------------------------

.. warning::

   Only roll back if the last migration was applied accidentally or in error. Rolling back in production data is destructive.

.. code-block:: bash

   # Roll back the last applied migration
   sudo -u multiflexi php /usr/share/multiflexi/vendor/bin/phinx rollback \
     -c /etc/multiflexi/phinx.php

   # Roll back to a specific version
   sudo -u multiflexi php /usr/share/multiflexi/vendor/bin/phinx rollback \
     -c /etc/multiflexi/phinx.php -t 20251130113650

Core Database Tables
---------------------

Understanding the schema helps with maintenance and debugging.

+----------------------------+----------------------------------------------+
| Table                      | Contents                                     |
+============================+==============================================+
| ``user``                   | Administrator and user accounts              |
+----------------------------+----------------------------------------------+
| ``company``                | Multi-tenant company records                 |
+----------------------------+----------------------------------------------+
| ``applications``           | Registered application definitions          |
+----------------------------+----------------------------------------------+
| ``app_to_company``         | Which apps are assigned to which companies   |
+----------------------------+----------------------------------------------+
| ``run_template``           | Job execution templates                      |
+----------------------------+----------------------------------------------+
| ``job``                    | Individual job execution records             |
+----------------------------+----------------------------------------------+
| ``artifacts``              | Job output files (stdout, stderr, results)   |
+----------------------------+----------------------------------------------+
| ``config_registry``        | RunTemplate configuration field values       |
+----------------------------+----------------------------------------------+
| ``company_env``            | Company-level environment variables          |
+----------------------------+----------------------------------------------+
| ``credential_prototype``   | Credential type definitions (JSON-based)     |
+----------------------------+----------------------------------------------+
| ``credential_type``        | Company-level credential instances           |
+----------------------------+----------------------------------------------+
| ``logger``                 | Job execution log entries                    |
+----------------------------+----------------------------------------------+
| ``token``                  | API authentication tokens                    |
+----------------------------+----------------------------------------------+

Cleaning Up Old Data
---------------------

Job and artifact records accumulate over time. Remove old data to keep the database lean:

.. code-block:: bash

   # Via CLI — remove jobs older than 90 days (adjust as needed)
   multiflexi-cli job cleanup --older-than=90

   # Via SQL (MySQL/MariaDB) — remove jobs and cascade-delete their artifacts
   mysql -u root -p multiflexi -e "
     DELETE FROM job
     WHERE DatCreate < NOW() - INTERVAL 90 DAY
       AND status IN ('ok', 'failed');
   "

.. note::

   The ``artifacts`` table has a ``CASCADE DELETE`` constraint on ``job_id``.
   Deleting a job record automatically removes all its associated artifacts.

Performance Maintenance
------------------------

MySQL / MariaDB
~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Rebuild table indexes and reclaim space
   mysql -u root -p multiflexi -e "OPTIMIZE TABLE job, artifacts, logger;"

   # Analyze table statistics for query planner
   mysql -u root -p multiflexi -e "ANALYZE TABLE job, run_template, applications;"

PostgreSQL
~~~~~~~~~~~

.. code-block:: bash

   sudo -u postgres psql multiflexi -c "VACUUM ANALYZE;"

   # Full vacuum (reclaims disk space, requires exclusive lock)
   sudo -u postgres psql multiflexi -c "VACUUM FULL ANALYZE job;"

Checking Database Size
-----------------------

MySQL / MariaDB
~~~~~~~~~~~~~~~~

.. code-block:: bash

   mysql -u root -p multiflexi -e "
     SELECT table_name,
            ROUND(data_length/1024/1024, 2) AS data_MB,
            ROUND(index_length/1024/1024, 2) AS index_MB
     FROM information_schema.tables
     WHERE table_schema = 'multiflexi'
     ORDER BY data_length + index_length DESC;
   "

PostgreSQL
~~~~~~~~~~~

.. code-block:: bash

   sudo -u postgres psql multiflexi -c "
     SELECT relname, pg_size_pretty(pg_total_relation_size(oid))
     FROM pg_class WHERE relkind = 'r' ORDER BY pg_total_relation_size(oid) DESC;
   "

Changing the Database Backend
-------------------------------

To migrate from SQLite (development) to MySQL (production):

1. Back up all data: ``multiflexi-cli export > backup.json`` (if available) or use a SQL dump
2. Install the new database backend: ``sudo apt install multiflexi-mysql``
3. Restore data to the new database
4. Update ``/etc/multiflexi/multiflexi.env`` with new ``DB_*`` settings
5. Run migrations: ``phinx migrate``

See Also
--------

- :doc:`backup-recovery` — Database backup procedures
- :doc:`upgrading` — Migration during upgrades
- :doc:`../reference/configuration` — Database connection settings
