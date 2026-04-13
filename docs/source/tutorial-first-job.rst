Your First Automated Job (Tutorial)
====================================

**Target Audience:** New Users
**Difficulty:** Beginner
**Time Required:** ~20 minutes
**Prerequisites:** MultiFlexi installed (:doc:`install`); administrator account created (:doc:`firstrun`)

.. contents::
   :local:
   :depth: 2

Overview
--------

This tutorial walks you through the complete workflow for setting up an automated job from scratch. We will use **MultiFlexi Probe** — a built-in health-check application — as a concrete example.

By the end you will have:

- A company configured in MultiFlexi
- An application installed and assigned to the company
- A RunTemplate set to run hourly
- A successfully executed job with preserved artifacts

Step 1: Install the Probe Application
---------------------------------------

MultiFlexi Probe is a simple diagnostic tool that checks system health and is ideal for testing.

.. code-block:: bash

   sudo apt install multiflexi-probe

Verify it is registered:

.. code-block:: bash

   multiflexi-cli application list | grep probe

You should see ``multiflexi-probe`` in the output.

Step 2: Create a Company
-------------------------

A company is the tenant for whom jobs run. We will create a demo company.

**Via web interface:**

1. Navigate to **Companies** → click **"➕ New Company"**
2. Enter:

   - **Name:** ``Demo Company``
   - **Code:** ``DEMO``
   - **Status:** Active

3. Click **Save**

**Via CLI:**

.. code-block:: bash

   multiflexi-cli company create \
     --name="Demo Company" \
     --code=DEMO

Step 3: Assign the Application to the Company
----------------------------------------------

Now we tell MultiFlexi that Demo Company will use the Probe application.

**Via web interface:**

1. Open the **Demo Company** detail page
2. Click the **"Applications"** tab
3. Find **MultiFlexi Probe** in the available list
4. Click **"✓ Assign"**

**Via CLI:**

.. code-block:: bash

   multiflexi-cli company assign-app \
     --company=DEMO \
     --app=multiflexi-probe

Step 4: Create a RunTemplate
------------------------------

A RunTemplate defines *how* and *when* the application runs for this company.

**Via web interface:**

1. On the Demo Company page, click **MultiFlexi Probe**
2. Click **"⚙️ Create RunTemplate"**
3. Fill in:

   - **Name:** ``Hourly health check``
   - **Interval:** ``Hourly``
   - **Executor:** ``Native``
   - **Status:** Active

4. Click **Save**

**Via CLI:**

.. code-block:: bash

   multiflexi-cli runtemplate create \
     --company=DEMO \
     --app=multiflexi-probe \
     --name="Hourly health check" \
     --interval=hourly \
     --executor=Native \
     --active=1

Note the RunTemplate ID from the output (e.g. ``ID: 1``).

Step 5: Execute the Job Manually
----------------------------------

Before waiting for the hourly schedule, run the job immediately to verify it works.

**Via web interface:**

1. Open the RunTemplate detail page
2. Click **"▶️ Execute Now"**
3. Wait ~5 seconds and refresh

**Via CLI:**

.. code-block:: bash

   multiflexi-cli runtemplate run --id=1

Step 6: Inspect the Results
-----------------------------

**Via web interface:**

1. Click **"Jobs"** in the top navigation (or open the RunTemplate's **Jobs** tab)
2. The most recent job should show **Status: ✅ Success** and **Exit Code: 0**
3. Click the job to open its detail page
4. Click the **"Artifacts"** tab — you will see:

   - ``stdout.txt`` — the probe's output (system metrics)
   - ``stderr.txt`` — any error messages (should be empty on success)

**Via CLI:**

.. code-block:: bash

   # Show recent jobs for the RunTemplate
   multiflexi-cli job list --runtemplate=1

   # Show stdout of the last job
   multiflexi-cli job artifacts --id=<JOB_ID> --file=stdout.txt

Step 7: Verify Automated Scheduling
-------------------------------------

The hourly schedule will now fire automatically. To confirm:

.. code-block:: bash

   # Check that the scheduler daemon is running
   systemctl status multiflexi-scheduler

   # Watch the executor log for the next scheduled run
   sudo journalctl -u multiflexi-executor -f

At the top of the next hour, you will see the job start and complete in the logs.

What You Have Built
--------------------

.. code-block:: text

   Demo Company
     └── MultiFlexi Probe (application)
           └── "Hourly health check" (RunTemplate, interval=hourly)
                     ↓ (fires every hour)
               Job → stdout/stderr artifacts stored in DB

Next Steps
----------

**Add real applications:**

.. code-block:: bash

   sudo apt search multiflexi   # see all available applications
   sudo apt install multiflexi-abraflexi  # example: AbraFlexi connector

**Set up credentials:**

If an application connects to an external system, install the credential prototype package and configure it for your company — see :doc:`howto/assigning-credentials`.

**Add post-job actions:**

Configure the RunTemplate to send a notification on failure, trigger another job on success, or post results to Zabbix — see :doc:`reference/actions`.

**Monitor with Zabbix:**

Integrate MultiFlexi with Zabbix for automatic alerting on job failures — see :doc:`integrations/zabbix`.

Troubleshooting
----------------

**Job status is "Failed":**
  Open the job artifacts and read ``stderr.txt``. See :doc:`howto/debugging-failed-jobs`.

**Job never runs (stays "Pending"):**
  Check the executor: ``systemctl status multiflexi-executor``

**Application not found after apt install:**
  Run: ``multiflexi-cli application import-json --file /usr/share/multiflexi/apps/probe.app.json``

See Also
--------

- :doc:`quickstart` — Condensed 15-minute version of this tutorial
- :doc:`howto/adding-company` — Company management details
- :doc:`howto/creating-runtemplates` — RunTemplate options reference
- :doc:`howto/assigning-credentials` — Connecting to external systems
- :doc:`concepts/data-model` — Understanding the entity relationships
