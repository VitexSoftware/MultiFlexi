Scheduling Jobs
===============

**Target Audience:** Users, Administrators
**Difficulty:** Beginner
**Prerequisites:** :doc:`creating-runtemplates`

.. contents::
   :local:
   :depth: 2

Overview
--------

In MultiFlexi, jobs are scheduled through **RunTemplates**. The ``multiflexi-scheduler`` daemon periodically checks all active RunTemplates and creates a Job record for each one whose scheduled run time has arrived.

You do not manage individual jobs directly — you configure the RunTemplate schedule and the system handles the rest.

How Scheduling Works
---------------------

1. The **scheduler daemon** wakes up every minute and scans active RunTemplates
2. For each RunTemplate where ``next_run <= now()``, it creates a new ``Job`` record in the database
3. It updates the RunTemplate's ``next_run`` to the next scheduled time
4. The **executor daemon** picks up pending Job records and executes them

This means there is a small delay (up to ~1 minute) between a scheduled time and actual execution start.

Interval Reference
-------------------

+------------------+-------------------------------------------+-------------------------------------------+
| Interval         | Next run calculation                      | Typical use case                          |
+==================+===========================================+===========================================+
| ``Manually``     | Never auto-scheduled                      | On-demand / ad-hoc runs                   |
+------------------+-------------------------------------------+-------------------------------------------+
| ``Hourly``       | Top of the next hour                      | Bank statement polling, health checks     |
+------------------+-------------------------------------------+-------------------------------------------+
| ``Daily``        | Same time the next day                    | Daily reports, nightly imports            |
+------------------+-------------------------------------------+-------------------------------------------+
| ``Weekly``       | Same day/time next week                   | Weekly summaries                          |
+------------------+-------------------------------------------+-------------------------------------------+
| ``Monthly``      | Same date next month                      | Monthly invoicing, period-end tasks       |
+------------------+-------------------------------------------+-------------------------------------------+
| ``Yearly``       | Same date next year                       | Annual reports, year-end closing          |
+------------------+-------------------------------------------+-------------------------------------------+

Viewing the Job Schedule
-------------------------

**Via the web interface:**

- The dashboard shows **Upcoming Schedule** — jobs planned for the near future
- Company pages show per-company upcoming jobs

**Via CLI:**

.. code-block:: bash

   # Show all pending (not yet executed) jobs
   multiflexi-cli job list --status=pending

   # Show next run times for all RunTemplates
   multiflexi-cli runtemplate list

Pausing Scheduling
-------------------

To stop a RunTemplate from generating new jobs without deleting it, set it to **Inactive**:

**Via web:** Open RunTemplate → click **"Deactivate"** (or toggle the Active switch)

**Via CLI:**

.. code-block:: bash

   multiflexi-cli runtemplate update --id=42 --active=0

Resuming Scheduling
--------------------

.. code-block:: bash

   multiflexi-cli runtemplate update --id=42 --active=1

The scheduler will calculate the next run time from now and resume scheduling.

Triggering a Job Immediately
-----------------------------

To run a job right now, outside of its normal schedule:

**Via web:** Open RunTemplate detail → click **"▶️ Execute Now"**

**Via CLI:**

.. code-block:: bash

   multiflexi-cli runtemplate run --id=42

This creates a Job with status ``pending`` which the executor picks up within seconds.

Event-Driven Scheduling
------------------------

In addition to time-based scheduling, MultiFlexi supports **event-driven** job triggering via the ``multiflexi-eventor`` (event processor) daemon. Jobs can be triggered by:

- File system events (new file in a watched directory)
- Webhook notifications
- Message queue events

Event-driven RunTemplates use the ``Manually`` interval and are triggered exclusively by the event processor. See the event processor documentation for configuration details.

Monitoring Job Execution
-------------------------

After a job runs, its status appears in:

- The **Jobs** view in the web interface (filter by company, application, status, date)
- The **Dashboard** recent jobs widget
- The **Zabbix** integration (if configured) — see :doc:`../integrations/zabbix`
- Job logs: ``journalctl -u multiflexi-executor -f``

See Also
--------

- :doc:`creating-runtemplates` — Creating and configuring RunTemplates
- :doc:`debugging-failed-jobs` — What to do when a job fails
- :doc:`../concepts/execution-architecture` — How scheduler and executor interact
- :doc:`../administration/systemd-services` — Managing the scheduler daemon
