Systemd Services
================

**Target Audience:** Administrators
**Difficulty:** Beginner
**Prerequisites:** Basic Linux systemd knowledge

.. contents::
   :local:
   :depth: 2

Overview
--------

A standard MultiFlexi installation runs three background services managed by systemd:

+-----------------------------------+----------------------------------------------+
| Service unit                      | Purpose                                      |
+===================================+==============================================+
| ``multiflexi-scheduler.service``  | Enqueues jobs based on RunTemplate schedules |
+-----------------------------------+----------------------------------------------+
| ``multiflexi-executor.service``   | Executes queued jobs                         |
+-----------------------------------+----------------------------------------------+
| ``multiflexi-eventor.service``    | Triggers jobs in response to external events |
+-----------------------------------+----------------------------------------------+

All three run as the ``multiflexi`` system user and load their configuration from ``/etc/multiflexi/multiflexi.env``.

Checking Service Status
------------------------

.. code-block:: bash

   # Status of all three services at a glance
   systemctl status multiflexi-scheduler multiflexi-executor multiflexi-eventor

   # Detailed status with recent log lines
   systemctl status multiflexi-executor -l

Starting and Stopping Services
--------------------------------

.. code-block:: bash

   # Start
   sudo systemctl start multiflexi-scheduler
   sudo systemctl start multiflexi-executor
   sudo systemctl start multiflexi-eventor

   # Stop
   sudo systemctl stop multiflexi-executor

   # Restart (e.g. after configuration change)
   sudo systemctl restart multiflexi-executor

   # Reload (re-reads the env file without interrupting running jobs — executor only)
   sudo systemctl reload multiflexi-executor

Enabling / Disabling on Boot
-----------------------------

Services are enabled at boot by default after installation. To manage this:

.. code-block:: bash

   # Enable (start at boot)
   sudo systemctl enable multiflexi-executor

   # Disable
   sudo systemctl disable multiflexi-eventor

Viewing Logs
-------------

All services log to the systemd journal.

.. code-block:: bash

   # Live log tail
   sudo journalctl -u multiflexi-executor -f

   # All three services together
   sudo journalctl -u multiflexi-scheduler -u multiflexi-executor -u multiflexi-eventor -f

   # Last 100 lines
   sudo journalctl -u multiflexi-executor -n 100

   # Logs since last boot
   sudo journalctl -u multiflexi-executor -b

   # Logs between timestamps
   sudo journalctl -u multiflexi-executor --since "2025-01-01 08:00" --until "2025-01-01 10:00"

Service Details
---------------

multiflexi-scheduler
~~~~~~~~~~~~~~~~~~~~~

Scans RunTemplates in the database and creates Job records when a scheduled run time is due. Runs continuously as a simple PHP daemon.

- **Binary**: ``/usr/lib/multiflexi-scheduler/daemon.php``
- **Restarts automatically**: yes (``Restart=always``, 10 s delay)
- **No memory ceiling** (scheduler is lightweight)

multiflexi-executor
~~~~~~~~~~~~~~~~~~~~

Picks up pending Job records, resolves environment variables, runs the job via the configured executor module, and stores results + artifacts.

- **Binary**: ``/usr/share/multiflexi-executor/daemon.php``
- **Memory ceiling**: 2 GB (``MemoryMax=2G``) — restarts automatically if exceeded
- **Soft memory warning**: 1800 MB (``MULTIFLEXI_MEMORY_LIMIT_MB=1800``)
- **Restarts automatically**: yes (``Restart=always``, 10 s delay)

multiflexi-eventor
~~~~~~~~~~~~~~~~~~~

Monitors configured event sources and enqueues jobs in response to external triggers (files, webhooks, queue messages).

- **Binary**: ``/usr/lib/multiflexi-eventor/daemon.php``
- **Memory ceiling**: 1 GB (``MemoryMax=1G``)
- **Restarts automatically**: yes (``Restart=always``, 10 s delay)

.. note::

   If you do not use event-driven job triggering, ``multiflexi-eventor`` can be disabled:
   ``sudo systemctl disable --now multiflexi-eventor``

Configuration File
-------------------

All services share ``/etc/multiflexi/multiflexi.env``. After editing this file, restart the affected services:

.. code-block:: bash

   sudo systemctl restart multiflexi-scheduler multiflexi-executor multiflexi-eventor

See :doc:`../reference/configuration` for the full list of configuration variables.

Troubleshooting Service Issues
--------------------------------

Service fails to start
~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Check the last start attempt
   sudo journalctl -u multiflexi-executor -n 50

   # Verify the env file is readable
   sudo -u multiflexi cat /etc/multiflexi/multiflexi.env

   # Check PHP syntax
   php -l /usr/share/multiflexi-executor/daemon.php

Service keeps restarting
~~~~~~~~~~~~~~~~~~~~~~~~~

Usually caused by a database connection failure or missing PHP extension.

.. code-block:: bash

   # Watch restart loop
   sudo journalctl -u multiflexi-executor -f

   # Test database connectivity
   php -r "new PDO('mysql:host=127.0.0.1;dbname=multiflexi', 'user', 'pass');"

Jobs are not being executed
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

1. Verify the executor is running: ``systemctl status multiflexi-executor``
2. Verify the scheduler is running: ``systemctl status multiflexi-scheduler``
3. Check for pending jobs in the database:

   .. code-block:: bash

      multiflexi-cli job list --status=pending

4. Check executor logs for errors: ``journalctl -u multiflexi-executor -n 200``

See Also
--------

- :doc:`../concepts/execution-architecture` — How the daemons interact
- :doc:`../reference/configuration` — Environment variables
- :doc:`../troubleshooting` — General troubleshooting guide
- :doc:`docker` — Running services in Docker Compose
