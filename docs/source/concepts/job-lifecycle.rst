Job Lifecycle
=============

Every MultiFlexi job follows a well-defined lifecycle ensuring reliable execution, comprehensive logging, and automatic artifact preservation.

**Target Audience:** Users, Developers  
**Difficulty:** Intermediate  
**Prerequisites:** Understanding of :doc:`data-model`

.. contents::
   :local:
   :depth: 2

Lifecycle Overview
------------------

A job progresses through distinct phases from creation to completion:

.. code-block:: text

    ┌──────────────┐
    │  Created     │  Scheduler creates job record
    └──────┬───────┘
           │
    ┌──────▼───────┐
    │  Scheduled   │  Entry added to schedule table
    └──────┬───────┘
           │
    ┌──────▼───────┐
    │   Queued     │  Executor detects ready job
    └──────┬───────┘
           │
    ┌──────▼───────┐
    │   Running    │  Application executes
    └──────┬───────┘
           │
    ┌──────▼───────┐
    │  Completed   │  Results recorded
    └──────┬───────┘
           │
    ┌──────▼───────┐
    │  Artifacts   │  Outputs preserved
    │  Preserved   │
    └──────────────┘

Phase 1: Job Creation
----------------------

**Trigger Points:**

- Scheduler daemon determines RunTemplate is due
- User clicks "Execute Now" in web interface
- API call: ``POST /api/.../job/``
- CLI command: ``multiflexi-cli job:create --template <id>``

**What Happens:**

1. New ``job`` record inserted with:
   
   - ``runtemplate_id``: Parent template reference
   - ``company_id``, ``app_id``: Company and application
   - ``executor``: Execution environment (Native, Docker, etc.)
   - ``env``: Serialized environment variables
   - ``launched_by``: User ID who triggered execution

2. Environment assembly from multiple sources:
   
   - Application default configuration
   - Company credential values
   - RunTemplate custom overrides

3. Job status set to "pending"

**Database State:**

.. code-block:: sql

    INSERT INTO job (runtemplate_id, company_id, app_id, executor, env, launched_by)
    VALUES (42, 15, 8, 'Native', '{"API_KEY":"..."}', 1);

Phase 2: Job Scheduling
------------------------

**Triggered By:** Job creation

**What Happens:**

1. Entry added to ``schedule`` table:
   
   - ``job_id``: Reference to job
   - ``after``: Execution timestamp (NOW for adhoc, calculated for scheduled)

2. RunTemplate updated:
   
   - ``next_schedule`` set to NULL (prevents duplicate scheduling)
   - ``last_schedule`` updated to current timestamp

**Database State:**

.. code-block:: sql

    INSERT INTO schedule (job_id, after)
    VALUES (12345, '2026-01-21 18:00:00');
    
    UPDATE runtemplate
    SET next_schedule = NULL, last_schedule = NOW()
    WHERE id = 42;

**Monitoring:**

- Zabbix receives "prepared" phase event (if enabled)
- OpenTelemetry span created for job lifecycle (if enabled)

Phase 3: Job Queuing
---------------------

**Triggered By:** Executor daemon polling cycle

**Executor Daemon Behavior:**

- Polls ``schedule`` table every N seconds (configurable via ``MULTIFLEXI_CYCLE_PAUSE``)
- Query: ``SELECT * FROM schedule WHERE after < NOW() ORDER BY after ASC``
- Respects parallel execution limit (``MULTIFLEXI_MAX_PARALLEL``)

**What Happens:**

1. Executor loads job record from database
2. Deserializes environment variables
3. Determines execution method based on ``executor`` field
4. Job state changes to "queued" (internal state)

**Executor Types:**

- **Native:** Symfony Process component on host
- **Podman:** Container execution with ``podman run``
- **Docker:** Container execution with ``docker run``
- **Kubernetes:** Pod deployment
- **Azure:** Cloud function invocation

Phase 4: Job Execution
-----------------------

**What Happens:**

1. **Pre-execution (runBegin):**
   
   - Job ``begin`` timestamp recorded
   - Process ID (PID) captured
   - Status set to "running"
   - Zabbix receives "running" phase event

2. **Execution (launchJob):**
   
   - Command constructed: ``executable`` + ``cmdparams``
   - Environment variables injected
   - Process launched
   - Output streams captured in real-time:
     
     - stdout → buffered
     - stderr → buffered
   
   - Progress monitoring (for long-running jobs)

3. **Process Monitoring:**
   
   - Exit code tracked
   - Execution duration calculated
   - Resource usage monitored (memory, CPU)

**Example Native Execution:**

.. code-block:: php

    $process = new Process(
        ['/usr/bin/bank-importer', '--format=json'],
        null,  // working directory
        $environmentVars  // from job.env
    );
    $process->start();
    $exitCode = $process->wait();
    $stdout = $process->getOutput();
    $stderr = $process->getErrorOutput();

**Example Docker Execution:**

.. code-block:: bash

    docker run --rm \
      -e API_KEY=xxx \
      -e DATABASE_URL=yyy \
      vitexsoftware/bank-importer:latest \
      --format=json

Phase 5: Job Completion
------------------------

**Triggered By:** Process termination

**What Happens (runEnd):**

1. **Result Capture:**
   
   - ``exitcode``: Process exit status (0 = success, non-zero = failure)
   - ``stdout``: Complete standard output
   - ``stderr``: Complete standard error
   - ``end``: Completion timestamp
   - ``pid``: Process identifier

2. **Database Updates:**

.. code-block:: sql

    UPDATE job
    SET exitcode = 0,
        stdout = 'Bank import completed: 42 transactions',
        stderr = '',
        begin = '2026-01-21 18:00:05',
        end = '2026-01-21 18:00:23',
        pid = 12345
    WHERE id = 12345;

3. **RunTemplate Statistics:**

.. code-block:: sql

    UPDATE runtemplate
    SET successfull_jobs_count = successfull_jobs_count + 1
    WHERE id = 42;

4. **Schedule Cleanup:**

.. code-block:: sql

    DELETE FROM schedule WHERE job_id = 12345;

5. **Next Schedule Calculation:**

.. code-block:: sql

    UPDATE runtemplate
    SET next_schedule = '2026-01-22 18:00:00'  -- Next day for daily jobs
    WHERE id = 42;

**Monitoring:**

- Zabbix receives "completed" phase with exitcode
- OpenTelemetry span closed with final status
- SQL audit log entry created

Phase 6: Artifact Preservation
-------------------------------

**Automatically Executed:** After job completion

**What Gets Preserved:**

1. **Standard Output** (``stdout.txt``):
   
   - Content-Type: ``text/plain``
   - Contains complete stdout stream
   - Useful for debugging and result verification

2. **Standard Error** (``stderr.txt``):
   
   - Content-Type: ``text/plain``
   - Contains complete stderr stream
   - Essential for troubleshooting failures

3. **Application Result Files:**
   
   - Files created in job's output directory
   - MIME types auto-detected
   - Original filenames preserved

**Database Storage:**

.. code-block:: sql

    INSERT INTO artifacts (job_id, filename, content_type, content, note, created)
    VALUES
      (12345, 'stdout.txt', 'text/plain', 'Bank import completed...', 'Standard output', NOW()),
      (12345, 'stderr.txt', 'text/plain', '', 'Standard error', NOW()),
      (12345, 'import-results.json', 'application/json', '{...}', 'Import results', NOW());

**Access Methods:**

- **Web UI:** Job detail page → Artifacts section → Download button
- **API:** ``GET /api/.../artifact/<id>``
- **CLI:** ``multiflexi-cli artifact:get <id> --output file.txt``

Job Status Indicators
----------------------

Exit Code Interpretation
~~~~~~~~~~~~~~~~~~~~~~~~~

.. list-table::
   :header-rows: 1
   :widths: 15 20 65

   * - Exit Code
     - Status
     - Meaning
   * - 0
     - ✅ Success
     - Job completed successfully
   * - 1
     - ⚠️ Warning
     - Minor issues, check logs
   * - 127
     - 🔵 Not Found
     - Command/binary not found
   * - 255
     - ❌ Failure
     - Critical error, job failed
   * - -1
     - 🔄 Retry
     - Transient error, retry recommended

Visual Indicators
~~~~~~~~~~~~~~~~~

Web interface displays color-coded status:

- **Green (bg-success):** Exit code 0
- **Yellow (bg-warning):** Exit code 1
- **Red (bg-danger):** Exit code 255
- **Blue (bg-primary):** Exit code 127
- **Cyan (bg-info):** Exit code -1

Job Persistence
---------------

**Retention Policy:**

- Jobs are **never automatically deleted**
- Full execution history retained indefinitely (by default)
- Supports compliance and debugging requirements

**Manual Cleanup:**

.. code-block:: bash

    # Delete jobs older than 90 days
    multiflexi-cli job:cleanup --days=90

**GDPR Compliance:**

Data retention policies can be configured per application or company. See :doc:`../gdpr-compliance` for details.

Error Handling
--------------

Common Failure Scenarios
~~~~~~~~~~~~~~~~~~~~~~~~

**Application Binary Not Found (Exit 127):**

- **Cause:** Executable not installed or incorrect path
- **Solution:** Verify application package installed: ``apt list --installed | grep multiflexi-<app>``

**Permission Denied (Exit 1 or 126):**

- **Cause:** Insufficient file/directory permissions
- **Solution:** Check executor daemon runs as correct user (``multiflexi``)

**Timeout:**

- **Cause:** Application exceeds configured timeout
- **Solution:** Increase timeout in application configuration

**Database Connection Failed:**

- **Cause:** Credential invalid or database unreachable
- **Solution:** Verify credential values, test database connectivity

Retry Strategies
~~~~~~~~~~~~~~~~

**Manual Retry:**

.. code-block:: bash

    multiflexi-cli job:retry <job-id>

**Automatic Retry:**

Configure in RunTemplate:

- ``max_retries``: Number of retry attempts
- ``retry_delay``: Seconds between retries
- ``retry_on_exit_codes``: Only retry for specific exit codes

Monitoring and Observability
-----------------------------

Real-Time Monitoring
~~~~~~~~~~~~~~~~~~~~

Track job execution in real-time:

1. **Web UI Dashboard:** Shows currently running jobs
2. **Job Detail Page:** Real-time status updates (requires page refresh)
3. **System Logs:** ``journalctl -u multiflexi-executor -f``

Performance Metrics
~~~~~~~~~~~~~~~~~~~

Key metrics to monitor:

- **Execution Duration:** Time from begin to end
- **Queue Wait Time:** Time from scheduled to begin
- **Success Rate:** Percentage of jobs with exitcode 0
- **Failure Patterns:** Common exit codes for failures

Zabbix Integration
~~~~~~~~~~~~~~~~~~

Metrics sent to Zabbix:

- ``multiflexi.job.phase[<job_id>]``: Current phase (prepared, running, completed)
- ``multiflexi.job.exitcode[<job_id>]``: Final exit code
- ``multiflexi.job.duration[<job_id>]``: Execution time in seconds

See :doc:`../integrations/zabbix` for configuration.

OpenTelemetry Tracing
~~~~~~~~~~~~~~~~~~~~~

Distributed tracing spans:

- **Span Name:** ``job.execute``
- **Attributes:** job_id, runtemplate_id, company_id, app_id, executor
- **Events:** phase transitions (prepared → running → completed)

See :doc:`../integrations/opentelemetry` for configuration.

Best Practices
--------------

Application Design
~~~~~~~~~~~~~~~~~~

- **Idempotent Operations:** Jobs should be safely re-runnable
- **Clear Exit Codes:** Use 0 for success, non-zero for specific failures
- **Structured Output:** Emit JSON/XML for easier parsing
- **Progress Logging:** Log milestones to stdout for monitoring

Resource Management
~~~~~~~~~~~~~~~~~~~

- **Memory Limits:** Configure ``MULTIFLEXI_MEMORY_LIMIT_MB`` for executor
- **Parallel Execution:** Set ``MULTIFLEXI_MAX_PARALLEL`` to prevent overload
- **Cleanup:** Applications should clean up temporary files

Artifact Management
~~~~~~~~~~~~~~~~~~~

- **File Organization:** Use descriptive filenames
- **Size Awareness:** Monitor artifact storage growth
- **Retention:** Implement cleanup for old artifacts if needed

Troubleshooting
~~~~~~~~~~~~~~~

- **Review Artifacts:** Always check stdout.txt and stderr.txt first
- **Check Credentials:** Verify credential assignment and values
- **Test Locally:** Run application manually with same environment
- **Enable Debug Logging:** Set application debug flags in RunTemplate config

See Also
--------

- :doc:`data-model` - Job entity relationships
- :doc:`execution-architecture` - Scheduler and executor daemons
- :doc:`../howto/debugging-failed-jobs` - Troubleshooting guide
- :doc:`../reference/cli-job-commands` - CLI job management

.. tip::

   Understanding the job lifecycle helps diagnose issues and optimize application performance. Each phase has specific monitoring and debugging techniques.
