Execution Architecture
======================

**Target Audience:** Administrators, Developers
**Difficulty:** Intermediate
**Prerequisites:** :doc:`data-model`, :doc:`job-lifecycle`

.. contents::
   :local:
   :depth: 2

Overview
--------

MultiFlexi's execution layer is made up of three independent daemons that run as systemd services. Each has a distinct responsibility:

.. code-block:: text

    ┌─────────────────────┐   schedules   ┌─────────────────────┐
    │  multiflexi-        │──────────────►│  Job queue          │
    │  scheduler          │               │  (database)         │
    └─────────────────────┘               └──────────┬──────────┘
                                                     │ picks up
    ┌─────────────────────┐   triggers    ┌──────────▼──────────┐
    │  multiflexi-        │──────────────►│  multiflexi-        │
    │  event-processor    │               │  executor           │
    └─────────────────────┘               └─────────────────────┘

- **Scheduler** (`multiflexi-scheduler`) — scans RunTemplates and enqueues jobs at the configured interval
- **Event Processor** (`multiflexi-eventor`) — watches for external events and triggers jobs in response
- **Executor** (`multiflexi-executor`) — picks jobs from the queue, resolves their environment, and runs them

The three daemons are fully decoupled and communicate only through the shared database. All three can be scaled horizontally if needed.

The Scheduler Daemon
---------------------

The scheduler runs as ``multiflexi-scheduler.service``. It wakes up periodically, iterates over all enabled RunTemplates, and creates a ``Job`` record for any RunTemplate whose next scheduled run time has passed.

**Scheduling intervals available in RunTemplates:**

- Manually (on-demand only)
- Hourly (at the start of each hour)
- Daily (once per day)
- Weekly (once per week)
- Monthly (once per month)
- Yearly (once per year)

The scheduler writes the next scheduled time back to the RunTemplate after enqueuing, preventing duplicate enqueuing.

**systemd service unit:**

.. code-block:: ini

   [Unit]
   Description=Run MultiFlexi scheduled jobs
   After=multi-user.target

   [Service]
   User=multiflexi
   Group=multiflexi
   EnvironmentFile=/etc/multiflexi/multiflexi.env
   ExecStart=/usr/bin/php /usr/lib/multiflexi-scheduler/daemon.php
   LimitNOFILE=8192:16384
   Type=simple
   Restart=always
   RestartSec=10

The Event Processor Daemon
---------------------------

The event processor runs as ``multiflexi-eventor.service``. It monitors configured event sources and fires jobs in response to external triggers rather than time-based schedules.

Typical use cases:

- Trigger a job when a new file appears in a directory
- React to a webhook notification
- Start processing when a message arrives on a queue

**systemd service unit:**

.. code-block:: ini

   [Unit]
   Description=MultiFlexi Event Processor - event-driven job triggering
   After=multi-user.target

   [Service]
   User=multiflexi
   Group=multiflexi
   EnvironmentFile=/etc/multiflexi/multiflexi.env
   ExecStart=/usr/bin/php /usr/lib/multiflexi-eventor/daemon.php
   LimitNOFILE=8192:16384
   Type=simple
   MemoryMax=1G
   Environment=MULTIFLEXI_MEMORY_LIMIT_MB=900
   Restart=always
   RestartSec=10

The Executor Daemon
--------------------

The executor runs as ``multiflexi-executor.service``. It is the core of job execution. Its main loop:

1. Queries the database for the next pending ``Job`` record
2. Resolves the full environment for that job (config fields + assigned credentials)
3. Selects and invokes the configured **executor module** (see below)
4. Captures stdout, stderr, and any output files
5. Stores all output as **artifacts** in the ``artifacts`` table
6. Records the exit code and updates the job status (success / failed)
7. Fires any configured **post-job actions**

The executor has a configurable memory ceiling (default: 2 GB) and restarts automatically if it crashes or exceeds the memory limit.

**systemd service unit:**

.. code-block:: ini

   [Unit]
   Description=Run MultiFlexi scheduled jobs
   After=multi-user.target

   [Service]
   User=multiflexi
   Group=multiflexi
   EnvironmentFile=/etc/multiflexi/multiflexi.env
   ExecStart=/usr/bin/php /usr/share/multiflexi-executor/daemon.php
   LimitNOFILE=8192:16384
   Type=simple
   MemoryMax=2G
   Environment=MULTIFLEXI_MEMORY_LIMIT_MB=1800
   Restart=always
   RestartSec=10

Executor Modules
-----------------

Each RunTemplate selects an **executor module** that determines *where* the job process runs.

+------------------+------------------------------------------------------+---------------------+
| Module           | Description                                          | Requirement         |
+==================+======================================================+=====================+
| ``Native``       | Runs directly on the host as the ``multiflexi`` user | Default; no extras  |
+------------------+------------------------------------------------------+---------------------+
| ``Docker``       | Runs inside a Docker container                       | Docker installed    |
+------------------+------------------------------------------------------+---------------------+
| ``Podman``       | Runs inside a Podman container (rootless)            | Podman installed    |
+------------------+------------------------------------------------------+---------------------+
| ``Kubernetes``   | Runs as a one-shot Kubernetes pod via kubectl+Helm   | kubectl + helm      |
+------------------+------------------------------------------------------+---------------------+
| ``Azure``        | Runs in Azure Container Instances (experimental)     | Azure credentials   |
+------------------+------------------------------------------------------+---------------------+

The Native executor is the default and simplest option. The Docker and Podman executors provide process isolation. Kubernetes is suitable for cloud-native deployments.

See :doc:`../reference/executors` for complete configuration details for each module.

Environment Variable Resolution
---------------------------------

Before any job is launched, the executor builds the complete environment by merging (in order of increasing precedence):

1. System-level defaults from ``/etc/multiflexi/multiflexi.env``
2. Company-level environment variables (``company_env`` table)
3. RunTemplate configuration field values
4. Assigned credential field values

This means credential values can override RunTemplate values, which can override company defaults. The final merged set of variables is passed to the child process.

Artifact Preservation
----------------------

Every job — regardless of which post-job actions are enabled — automatically preserves its outputs in the ``artifacts`` database table:

- **stdout.txt** — standard output of the job process
- **stderr.txt** — standard error of the job process
- **result files** — any output files produced by the application (MIME type auto-detected)

Artifacts are accessible from the Job detail page in the web UI, or via the REST API. They are cascade-deleted when the Job record is deleted.

Post-Job Actions
-----------------

After a job completes, the executor can fire a configured set of **Actions**. Actions are evaluated against the job's exit code and can perform notifications, trigger further jobs, or integrate with external systems.

See :doc:`../reference/actions` for the complete list of available actions.

See Also
--------

- :doc:`job-lifecycle` — Detailed state transitions of a Job
- :doc:`../reference/executors` — Executor module configuration
- :doc:`../reference/actions` — Post-job actions
- :doc:`../administration/systemd-services` — Managing the daemons
