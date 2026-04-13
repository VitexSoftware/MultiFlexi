Troubleshooting
===============

.. contents::
   :local:
   :depth: 2

This page covers common issues and their solutions. For job-specific debugging, see :doc:`howto/debugging-failed-jobs`.

Quick Diagnostics
-----------------

Run these checks first when something is wrong:

.. code-block:: bash

   # Service status
   systemctl status multiflexi-scheduler multiflexi-executor multiflexi-eventor

   # Recent executor errors
   sudo journalctl -u multiflexi-executor -n 50 --no-pager

   # Recent scheduler errors
   sudo journalctl -u multiflexi-scheduler -n 50 --no-pager

   # Web server errors
   sudo tail -20 /var/log/apache2/error.log

   # Application log
   sudo tail -20 /var/log/multiflexi/multiflexi.log

Authentication Issues
---------------------

Invalid Security Token
~~~~~~~~~~~~~~~~~~~~~~

**Symptom:** "Invalid security token." error when logging in.

**Cause:** CSRF token mismatch — usually a stale session or browser cache issue.

**Solutions:**

1. Clear browser cookies and cache for the MultiFlexi domain
2. Try an incognito/private browser window
3. Verify PHP session storage is writable:

   .. code-block:: bash

      ls -la $(php -r "echo session_save_path();")
      # Should be writable by the web server user (www-data)

4. Restart PHP-FPM and web server:

   .. code-block:: bash

      sudo systemctl restart php8.2-fpm apache2

5. Check server time synchronization:

   .. code-block:: bash

      timedatectl status

Cannot Log In (Wrong Password)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you forget the administrator password, reset it via CLI:

.. code-block:: bash

   multiflexi-cli user update --login=admin --password=newpassword

Jobs Not Running
-----------------

Jobs Stay in "Pending" State
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Cause:** Executor daemon is not running.

.. code-block:: bash

   systemctl status multiflexi-executor
   sudo systemctl start multiflexi-executor

If it keeps failing to start:

.. code-block:: bash

   sudo journalctl -u multiflexi-executor -n 100
   # Look for "PHP Fatal error" or "Connection refused"

No New Jobs Being Created
~~~~~~~~~~~~~~~~~~~~~~~~~~

**Cause:** Scheduler daemon is not running, or all RunTemplates are inactive.

.. code-block:: bash

   systemctl status multiflexi-scheduler
   multiflexi-cli runtemplate list  # check Active column

Job Fails Immediately (exit code non-zero)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

1. Open the job in the web interface → **Artifacts** → read ``stderr.txt``
2. Check that the application package is installed:

   .. code-block:: bash

      dpkg -l | grep <app-name>

3. Run the application manually with the same environment:

   .. code-block:: bash

      multiflexi-cli runtemplate env --id=<ID> --export | bash -c 'eval "$(cat)" && <executable>'

See :doc:`howto/debugging-failed-jobs` for a complete walkthrough.

Job Runs Forever / Never Finishes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Cause:** Usually the application hangs waiting for input, or the executor hit its memory ceiling (2 GB) and restarted.

.. code-block:: bash

   # Check for zombie processes
   ps aux | grep multiflexi

   # Check memory usage
   sudo journalctl -u multiflexi-executor | grep -i memory

   # Check if executor restarted
   sudo journalctl -u multiflexi-executor | grep "Started\|Stopped"

Credential Issues
-----------------

Credential Fields Not Passed to Job
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Cause:** The CredentialType is not assigned to the RunTemplate.

.. code-block:: bash

   multiflexi-cli runtemplate list-credentials --runtemplate=<ID>
   # If empty, assign the credential:
   multiflexi-cli runtemplate assign-credential --runtemplate=<ID> --credentialtype=<ID>

Wrong Values in Credentials
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Show current values
   multiflexi-cli credtype show --id=<ID>

   # Update
   multiflexi-cli credtype update --id=<ID> --FIELD_NAME=newvalue

Installation Issues
--------------------

APT Repository Not Found
~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Re-add the repository
   curl -sSLo /tmp/multiflexi-archive-keyring.deb \
     https://repo.multiflexi.eu/multiflexi-archive-keyring.deb
   sudo dpkg -i /tmp/multiflexi-archive-keyring.deb
   echo "deb [signed-by=/usr/share/keyrings/repo.multiflexi.eu.gpg] \
     https://repo.multiflexi.eu/ $(lsb_release -sc) main" | \
     sudo tee /etc/apt/sources.list.d/multiflexi.list
   sudo apt update

Database Migration Fails During Install
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Check database connectivity
   mysql -u multiflexi -p -h 127.0.0.1 multiflexi -e "SHOW TABLES;"

   # Run migrations manually
   sudo -u multiflexi php /usr/share/multiflexi/vendor/bin/phinx migrate \
     -c /etc/multiflexi/phinx.php

Web Interface Issues
---------------------

White Screen / HTTP 500
~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   sudo tail -50 /var/log/apache2/error.log
   sudo tail -50 /var/log/multiflexi/multiflexi.log

   # Enable debug mode temporarily
   echo "APP_DEBUG=true" | sudo tee -a /etc/multiflexi/multiflexi.env
   sudo systemctl reload apache2

   # Revert after fixing
   sudo sed -i '/APP_DEBUG=true/d' /etc/multiflexi/multiflexi.env

Page Not Found (404) for /multiflexi
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Ensure mod_rewrite is enabled
   sudo a2enmod rewrite
   sudo systemctl restart apache2

   # Check Apache site configuration
   sudo cat /etc/apache2/conf-available/multiflexi.conf

Slow Performance
~~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Check database query performance
   mysql -u root -p multiflexi -e "SHOW PROCESSLIST;"

   # Optimize heavy tables
   mysql -u root -p multiflexi -e "OPTIMIZE TABLE job, artifacts, logger;"

   # Purge old job data
   multiflexi-cli job cleanup --older-than=90

Docker Deployment Issues
-------------------------

Container Cannot Reach Database
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   # Check service health in Docker Compose
   docker compose ps

   # Test database connectivity from web container
   docker compose exec web mysql -u multiflexi -p -h db multiflexi -e "SHOW TABLES;"

Container Keeps Restarting
~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

   docker compose logs executor
   docker compose logs scheduler

See :doc:`administration/docker` for full Docker troubleshooting.

Zabbix Integration Issues
--------------------------

Metrics Not Appearing in Zabbix
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

1. Verify the Zabbix sender configuration:

   .. code-block:: bash

      grep ZABBIX /etc/multiflexi/multiflexi.env

2. Test connectivity to the Zabbix server:

   .. code-block:: bash

      nc -zv <ZABBIX_SERVER> 10051

3. Check that the MultiFlexi host exists in Zabbix and has the template applied.

See :doc:`integrations/zabbix` for setup details.

Getting Help
-------------

If none of the above resolves your issue:

1. Check the `GitHub Issues <https://github.com/VitexSoftware/MultiFlexi/issues>`_ for known bugs
2. Review the full documentation at `<https://multiflexi.readthedocs.io/>`_
3. Open a new GitHub issue with:
   - MultiFlexi version: ``dpkg -l multiflexi``
   - OS and PHP version: ``lsb_release -a && php -v``
   - Relevant log output
   - Steps to reproduce

See Also
--------

- :doc:`howto/debugging-failed-jobs` — Debugging individual job failures
- :doc:`administration/systemd-services` — Service management
- :doc:`reference/configuration` — Configuration reference
