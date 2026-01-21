Data Model
==========

MultiFlexi's architecture revolves around four core entities that work together to enable automated task execution across multiple organizations.

**Target Audience:** All Users  
**Difficulty:** Beginner  
**Prerequisites:** Basic understanding of task scheduling concepts

.. contents::
   :local:
   :depth: 2

Entity Relationships
--------------------

.. code-block:: text

    Application (1) ───────── (∞) RunTemplate (∞) ───────── (1) Company
         │                            │
         │                            │
         └─────────── (∞) Job ────────┘

**Conceptual Flow:**

1. **Applications** define *what* can be executed (e.g., "Bank Statement Importer")
2. **Companies** represent *who* the work is for (e.g., "Acme Corp", "Beta Industries")
3. **RunTemplates** configure *how* an Application runs for a specific Company (e.g., "Import Acme's bank statements daily at 2 AM")
4. **Jobs** are individual *executions* created from RunTemplates (e.g., "Import job for 2025-01-21 02:00")

Core Entities
-------------

Application
~~~~~~~~~~~

**Definition:** A packaged executable that performs a specific automation task.

**Examples:**

- Bank statement importer (fetches transactions from APIs)
- Invoice generator (creates PDFs from database data)
- Email sender (dispatches notifications)
- System health checker (monitors server metrics)

**Key Properties:**

- ``executable``: Command to run (e.g., ``/usr/bin/bank-importer``, ``docker run myimage``)
- ``environment``: Configuration fields required (API keys, URLs, file paths)
- ``requirements``: Dependencies (PHP extensions, system packages)
- ``ociimage``: Docker/Podman container image (optional)

**Storage:** Applications are defined in JSON files conforming to the `Application Schema <https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json>`_.

**Lifecycle:**

1. Packaged as Debian ``.deb`` files (e.g., ``apt install multiflexi-probe``)
2. Installed via ``multiflexi-cli application import-json``
3. Registered in MultiFlexi database (``application`` table)
4. Activated per-company through the web interface

Company
~~~~~~~

**Definition:** A tenant/organization for which MultiFlexi automates tasks.

**Purpose:** Provides multi-tenant data isolation. Each company has its own:

- Credentials (stored separately)
- RunTemplates (configured independently)
- Job history (isolated logs)

**Examples:**

- "Acme Corporation" (accounting client)
- "Internal IT" (your own infrastructure)
- "Beta Industries" (another client)

**Key Properties:**

- ``name``: Human-readable label
- ``code``: Unique identifier (used in logs, APIs)
- ``ic`` / ``dic``: Tax identifiers (for accounting systems)
- ``enabled``: Active/inactive status

**Common Use Cases:**

- **Accounting Firms:** One company per client
- **MSPs:** One company per managed customer
- **Internal Use:** Single company for your organization

RunTemplate
~~~~~~~~~~~

**Definition:** A configured instance of an Application for a specific Company, including scheduling and execution parameters.

**Analogy:** A "recipe" that defines:

- *Which* application to run
- *For which* company
- *When* to run it (interval)
- *How* to run it (configuration values)
- *Where* to run it (executor: native, Docker, Podman)

**Key Properties:**

- ``app_id``: Reference to Application
- ``company_id``: Reference to Company
- ``interv``: Scheduling interval (``h``=hourly, ``d``=daily, ``w``=weekly, ``m``=monthly, ``c``=custom cron)
- ``executor``: Execution environment (Native, Podman, Docker, Azure, Kubernetes)
- ``configuration``: Key-value pairs for environment variables (database URLs, API keys, file paths)

**Example RunTemplate:**

.. list-table::
   :header-rows: 1
   :widths: 20 80

   * - Field
     - Value
   * - Name
     - "Acme Daily Bank Import"
   * - Application
     - "Bank Statement Importer"
   * - Company
     - "Acme Corporation"
   * - Interval
     - Daily (``d``) at 02:00
   * - Configuration
     - ``BANK_URL=https://api.bank.com``, ``ACCOUNT_ID=12345``

**Lifecycle:**

1. Created via web interface ("Activation Wizard") or CLI
2. Credentials assigned (see :doc:`credential-management`)
3. Configuration values set
4. Activated (``active=true``)
5. Scheduler creates Jobs automatically based on interval

Job
~~~

**Definition:** A single execution instance of a RunTemplate.

**Created When:**

- Scheduler daemon determines a RunTemplate is due (based on interval)
- User clicks "Execute Now" in web interface
- API call triggers adhoc execution
- CLI command: ``multiflexi-cli job:create --template <id>``

**Lifecycle:**

1. **Created:** Job record inserted into database with status "pending"
2. **Scheduled:** Entry added to ``schedule`` table with execution timestamp
3. **Queued:** Executor daemon detects job is ready (``schedule.after < NOW()``)
4. **Running:** Executor launches process, captures output
5. **Completed:** Exit code, stdout, stderr recorded
6. **Artifacts Preserved:** Output files saved to ``artifacts`` table

**Key Properties:**

- ``runtemplate_id``: Parent template
- ``begin`` / ``end``: Execution timestamps
- ``exitcode``: Process exit status (0=success)
- ``stdout`` / ``stderr``: Captured output
- ``pid``: Process ID (for monitoring)
- ``launched_by``: User who triggered execution (audit trail)

**Persistence:** Jobs are never automatically deleted. Full execution history is retained for compliance and debugging.

Relationship Examples
---------------------

**Scenario 1: Multi-Company Accounting Firm**

- **Application:** "AbraFlexi Invoice Exporter"
- **Companies:** "Client A", "Client B", "Client C"
- **RunTemplates:**

  - "Client A - Weekly Invoice Export" (runs Mondays)
  - "Client B - Daily Invoice Export" (runs daily)
  - "Client C - Monthly Invoice Export" (runs 1st of month)

- **Jobs:** Each RunTemplate generates jobs automatically (e.g., "Client A - 2025-01-20", "Client A - 2025-01-27", ...)

**Scenario 2: Internal IT Automation**

- **Application:** "System Health Probe"
- **Company:** "Internal IT"
- **RunTemplate:** "Hourly Health Check"
- **Jobs:** One job per hour (e.g., "2025-01-21 14:00", "2025-01-21 15:00", ...)

Configuration Inheritance
-------------------------

Configuration values for Jobs are assembled from multiple sources (priority order):

1. **Application Defaults:** Base configuration from ``.app.json``
2. **Company Credentials:** Values from assigned credentials (e.g., database URLs)
3. **RunTemplate Overrides:** Custom values specific to this template

**Example:**

.. code-block:: text

    Application defines:     DATABASE_HOST = localhost
    Company credential has:  DATABASE_HOST = mysql.acme.com
    RunTemplate overrides:   DATABASE_HOST = mysql-replica.acme.com
    
    Final Job receives:      DATABASE_HOST = mysql-replica.acme.com

This allows flexible configuration while maintaining sensible defaults.

Database Schema Highlights
---------------------------

.. code-block:: sql

    -- Core tables
    CREATE TABLE application (id, uuid, name, enabled, executable);
    CREATE TABLE company (id, code, name, enabled);
    CREATE TABLE runtemplate (id, app_id, company_id, interv, active);
    CREATE TABLE job (id, runtemplate_id, schedule, begin, end, exitcode);
    CREATE TABLE schedule (id, job_id, after);  -- Scheduling queue
    CREATE TABLE artifacts (id, job_id, filename, content);
    
    -- Foreign keys enforce referential integrity
    runtemplate.app_id → application.id
    runtemplate.company_id → company.id
    job.runtemplate_id → runtemplate.id

See Also
--------

- :doc:`job-lifecycle` - Detailed job execution phases
- :doc:`credential-management` - How credentials integrate
- :doc:`../reference/application-schema` - Full Application JSON specification
- :doc:`../howto/creating-runtemplates` - Practical RunTemplate creation

.. note::

   Understanding the data model is essential for effectively using MultiFlexi. These four entities form the foundation of all automation workflows.
