Creating RunTemplates
=====================

**Target Audience:** Users, Administrators
**Difficulty:** Beginner
**Prerequisites:** :doc:`installing-applications`; understanding of :doc:`../concepts/data-model`

.. contents::
   :local:
   :depth: 2

Overview
--------

A **RunTemplate** ties an Application to a Company and answers the questions: *how* should the application run, *when*, and *with what parameters*? Each RunTemplate produces one job per scheduled run.

Creating a RunTemplate via the Web Interface
---------------------------------------------

1. Open the company detail page (**Companies** → select company)
2. Click the application you want to schedule
3. Click **"⚙️ Create RunTemplate"** (or **"New RunTemplate"**)
4. Fill in the form:

   .. list-table::
      :widths: 20 15 65
      :header-rows: 1

      * - Field
        - Required
        - Description
      * - **Name**
        - Yes
        - Descriptive label (e.g. "Daily bank import")
      * - **Description**
        - No
        - Free-text note for documentation
      * - **Schedule / Interval**
        - Yes
        - How often to run (see intervals below)
      * - **Executor**
        - Yes
        - Where to run: Native, Docker, Podman, Kubernetes, Azure
      * - **Status**
        - Yes
        - Active (scheduled) or Inactive (paused)
      * - **Delay**
        - No
        - Seconds to delay execution after the scheduled time

5. Fill in any application-specific **configuration fields** shown below the main form
6. Click **"Save"**

Schedule Intervals
~~~~~~~~~~~~~~~~~~

+------------------+-----------------------------------------------+
| Interval         | Description                                   |
+==================+===============================================+
| ``Manually``     | Never auto-scheduled; only run on demand      |
+------------------+-----------------------------------------------+
| ``Hourly``       | Once per hour, at the top of the hour         |
+------------------+-----------------------------------------------+
| ``Daily``        | Once per day                                  |
+------------------+-----------------------------------------------+
| ``Weekly``       | Once per week                                 |
+------------------+-----------------------------------------------+
| ``Monthly``      | Once per month                                |
+------------------+-----------------------------------------------+
| ``Yearly``       | Once per year                                 |
+------------------+-----------------------------------------------+

Choosing an Executor
~~~~~~~~~~~~~~~~~~~~

The executor determines *where* the job process runs:

- **Native** — directly on the server (default; simplest)
- **Docker** — inside a Docker container (requires ``ociimage`` in the app definition)
- **Podman** — inside a rootless Podman container
- **Kubernetes** — as a one-shot pod in a Kubernetes cluster
- **Azure** — in Azure Container Instances (experimental)

See :doc:`../reference/executors` for setup requirements.

Creating a RunTemplate via CLI
-------------------------------

.. code-block:: bash

   multiflexi-cli runtemplate create \
     --company=ACME \
     --app=multiflexi-probe \
     --name="Hourly health check" \
     --interval=hourly \
     --executor=Native \
     --active=1

Listing RunTemplates
---------------------

.. code-block:: bash

   # All RunTemplates
   multiflexi-cli runtemplate list

   # For a specific company
   multiflexi-cli runtemplate list --company=ACME

Updating a RunTemplate
-----------------------

.. code-block:: bash

   # Pause scheduling
   multiflexi-cli runtemplate update --id=42 --active=0

   # Change interval
   multiflexi-cli runtemplate update --id=42 --interval=daily

   # Change executor
   multiflexi-cli runtemplate update --id=42 --executor=Docker

Running a RunTemplate Immediately
-----------------------------------

To trigger a one-off execution outside the normal schedule:

**Via the web interface:** Open the RunTemplate detail page → click **"▶️ Execute Now"**

**Via CLI:**

.. code-block:: bash

   multiflexi-cli runtemplate run --id=42

Configuring Post-Job Actions
------------------------------

Actions fire after a job completes. They can send notifications, trigger further jobs, or post data to external systems.

1. Open the RunTemplate detail page
2. Click **"Actions"** tab
3. Click **"+ Add Action"**
4. Select the action type and configure it
5. Set the **trigger condition**: on success, on failure, or always

See :doc:`../reference/actions` for all available actions.

Deleting a RunTemplate
-----------------------

.. warning::

   Deleting a RunTemplate does not delete existing job history. Use **Inactive** status to pause without deleting.

.. code-block:: bash

   multiflexi-cli runtemplate delete --id=42

See Also
--------

- :doc:`scheduling-jobs` — Understanding the scheduling mechanism
- :doc:`assigning-credentials` — Adding credentials to a RunTemplate
- :doc:`../reference/executors` — Executor module reference
- :doc:`../reference/actions` — Post-job actions reference
- :doc:`../concepts/job-lifecycle` — What happens when a RunTemplate fires
