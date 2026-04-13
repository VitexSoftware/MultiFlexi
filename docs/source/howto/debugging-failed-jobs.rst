Debugging Failed Jobs
=====================

**Target Audience:** Users, Administrators
**Difficulty:** Intermediate
**Prerequisites:** Access to the web interface and/or SSH to the server

.. contents::
   :local:
   :depth: 2

Overview
--------

When a job fails, MultiFlexi automatically preserves its full output — stdout, stderr, and any result files — as **artifacts**. This makes debugging straightforward: start with the artifacts, then work backwards through logs and configuration.

Step 1: Find the Failed Job
----------------------------

**Via the web interface:**

1. Go to **Jobs** in the top navigation
2. Filter by **Status = Failed** (or by company/application)
3. Click the job to open its detail page

**Via CLI:**

.. code-block:: bash

   # List recent failed jobs
   multiflexi-cli job list --status=failed --limit=20

   # List failed jobs for a specific company
   multiflexi-cli job list --status=failed --company=ACME

Step 2: Inspect the Artifacts
-------------------------------

Every job stores:

- **stdout.txt** — standard output of the process
- **stderr.txt** — standard error (most errors appear here)
- **result files** — any output files produced by the application

**Via web:** On the job detail page, click the **"Artifacts"** tab to view or download each file.

**Via CLI:**

.. code-block:: bash

   # List artifacts for a job
   multiflexi-cli job artifacts --id=1234

   # Show stderr of a job
   multiflexi-cli job artifacts --id=1234 --file=stderr.txt

Typical error patterns in stderr:

- ``Connection refused`` / ``CURL error`` → network or URL issue, check credentials
- ``Access denied`` / ``Unauthorized`` → wrong username/password in CredentialType
- ``PHP Fatal error`` → application bug or missing dependency
- ``command not found`` → application not installed on the host
- ``No such file or directory`` → wrong path in configuration

Step 3: Check the Executor Daemon Log
---------------------------------------

The executor daemon logs job start/stop events and any internal errors:

.. code-block:: bash

   # Recent executor log
   sudo journalctl -u multiflexi-executor -n 200

   # Filter by job ID (if the application logged it)
   sudo journalctl -u multiflexi-executor -g "job.*1234"

   # Live log while re-running the job
   sudo journalctl -u multiflexi-executor -f &
   multiflexi-cli runtemplate run --id=42

Step 4: Verify the Environment
--------------------------------

Most failures are caused by missing or wrong environment variables (credentials, URLs, API keys).

**Print the resolved environment for a RunTemplate:**

.. code-block:: bash

   multiflexi-cli runtemplate env --id=42

This shows exactly what environment variables the job process will receive. Verify URLs, check for typos, confirm passwords are set.

**Common credential issues:**

.. code-block:: bash

   # List credentials assigned to the RunTemplate
   multiflexi-cli runtemplate list-credentials --runtemplate=42

   # Show CredentialType values
   multiflexi-cli credtype show --id=7

Step 5: Run the Application Manually
--------------------------------------

Once you have the resolved environment, you can reproduce the failure manually by running the application directly:

.. code-block:: bash

   # Export the environment and run the executable
   multiflexi-cli runtemplate env --id=42 --export | bash -c 'eval "$(cat)" && /usr/bin/my-application'

   # Or manually set key variables
   export ABRAFLEXI_URL=https://erp.example.com
   export ABRAFLEXI_USER=admin
   export ABRAFLEXI_PASSWORD=secret
   /usr/bin/multiflexi-probe

Step 6: Check PHP and Web Server Logs
--------------------------------------

For issues in the web interface itself:

.. code-block:: bash

   # Apache error log
   sudo tail -f /var/log/apache2/error.log

   # MultiFlexi application log
   sudo tail -f /var/log/multiflexi/multiflexi.log

   # PHP error log
   sudo tail -f /var/log/php*.log

Step 7: Re-run the Job
-----------------------

After fixing the root cause:

**Via web:** Open the RunTemplate → click **"▶️ Execute Now"**

**Via CLI:**

.. code-block:: bash

   multiflexi-cli runtemplate run --id=42

Common Issues Quick Reference
-------------------------------

.. list-table::
   :widths: 35 65
   :header-rows: 1

   * - Symptom
     - Likely cause and fix
   * - Exit code non-zero, stderr empty
     - Application returned an error without output. Check the application's own log files.
   * - ``Connection refused`` in stderr
     - The URL or port in the CredentialType is wrong, or the remote service is down.
   * - ``No such file or directory``
     - A path in the configuration or credential is wrong, or the application binary is not installed.
   * - ``command not found``
     - The application Debian package is not installed: ``sudo apt install <app-package>``
   * - Job never runs (status stays pending)
     - Executor daemon is not running: ``systemctl status multiflexi-executor``
   * - Job shows as running but never finishes
     - Executor hit the memory ceiling (2 GB) and restarted mid-job. Check for memory leaks in the application.
   * - Credential fields empty in environment
     - CredentialType not assigned to the RunTemplate — see :doc:`assigning-credentials`

See Also
--------

- :doc:`../administration/systemd-services` — Checking daemon status and logs
- :doc:`assigning-credentials` — Fixing credential assignment issues
- :doc:`../reference/configuration` — Logging configuration
- :doc:`../troubleshooting` — General troubleshooting
