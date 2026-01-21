System Overview
===============

MultiFlexi is a comprehensive PHP-based task scheduling and automation framework designed for business system integrations, with primary focus on accounting and ERP systems.

**Target Audience:** All Users  
**Difficulty:** Beginner  
**Prerequisites:** None

.. contents::
   :local:
   :depth: 2

What is MultiFlexi?
-------------------

MultiFlexi enables organizations to automate repetitive business tasks across multiple companies and platforms. It provides:

- **Centralized Scheduling:** Define when tasks run (hourly, daily, weekly, custom)
- **Multi-Tenant Architecture:** Isolate data and configurations per company/client
- **Flexible Execution:** Run tasks natively, in Docker containers, or in the cloud
- **Comprehensive Monitoring:** Track execution history, logs, and artifacts
- **Secure Credential Management:** Store and reuse authentication credentials safely

Core Use Cases
--------------

Accounting Firms
~~~~~~~~~~~~~~~~

Automate client data processing:

- Import bank statements from multiple banks
- Generate invoices and reports
- Sync data between accounting systems
- Export tax reports on schedule

**Example:** An accounting firm manages 50 clients, each requiring daily bank imports. MultiFlexi runs 50 jobs automatically, one per client, with isolated credentials.

Managed Service Providers (MSPs)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Automate infrastructure monitoring and maintenance:

- System health checks across customer environments
- Backup verification and reporting
- Log aggregation and analysis
- Compliance reporting

**Example:** An MSP monitors 100 customer servers hourly for disk space, memory usage, and service health using the MultiFlexi Probe application.

Internal IT Automation
~~~~~~~~~~~~~~~~~~~~~~

Streamline internal business processes:

- Data synchronization between systems
- Scheduled report generation
- Email notification dispatch
- Database maintenance tasks

**Example:** A company synchronizes employee data from HR system to email server nightly, automatically creating/updating accounts.

System Architecture
-------------------

Component Overview
~~~~~~~~~~~~~~~~~~

.. code-block:: text

    ┌─────────────────────────────────────────────────────────────┐
    │                      Web Interface                          │
    │              (Bootstrap 4 UI, Dashboard, Forms)             │
    └────────────────────┬────────────────────────────────────────┘
                         │
    ┌────────────────────┴────────────────────────────────────────┐
    │                       REST API                              │
    │           (JSON/XML endpoints, OAuth2 auth)                 │
    └────────────────────┬────────────────────────────────────────┘
                         │
    ┌────────────────────┴────────────────────────────────────────┐
    │                   Core Application                          │
    │          (PHP business logic, ORM, validation)              │
    └───┬────────────────┬────────────────────┬────────────────┬──┘
        │                │                    │                │
        ▼                ▼                    ▼                ▼
    ┌────────┐    ┌──────────┐      ┌─────────────┐    ┌──────────┐
    │Database│    │Scheduler │      │  Executor   │    │   CLI    │
    │(MySQL) │    │ Daemon   │      │   Daemon    │    │  Tools   │
    └────────┘    └──────────┘      └─────────────┘    └──────────┘

System Services (v2.x+)
~~~~~~~~~~~~~~~~~~~~~~~~

MultiFlexi 2.x uses dedicated systemd services:

**multiflexi-scheduler.service**
  - Continuously evaluates RunTemplates
  - Creates Job records when schedules are due
  - Calculates next execution times
  - Runs as daemon under ``multiflexi`` user

**multiflexi-executor.service**
  - Polls for scheduled jobs
  - Launches applications with proper environment
  - Captures output and exit codes
  - Preserves artifacts automatically
  - Runs as daemon under ``multiflexi`` user

Data Flow
~~~~~~~~~

1. **User configures RunTemplate** (via Web UI or CLI)
2. **Scheduler daemon evaluates schedule** (every cycle)
3. **Job record created** when due time arrives
4. **Executor daemon detects job** (polling schedule table)
5. **Application executes** with injected environment
6. **Output captured** (stdout, stderr, exit code)
7. **Artifacts preserved** (result files, logs)
8. **Monitoring updated** (Zabbix, OpenTelemetry)

Interfaces
----------

Web Interface
~~~~~~~~~~~~~

The primary management interface:

- **Dashboard:** Real-time metrics, recent jobs, charts
- **Companies:** Manage tenants/organizations
- **Applications:** Browse and install available apps
- **RunTemplates:** Configure scheduling and execution
- **Jobs:** View execution history and artifacts
- **Credentials:** Manage authentication

**Access:** ``http://<server>/multiflexi``

Command-Line Interface (CLI)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Server-side management tool (``multiflexi-cli``):

- User and company management
- Application import/export
- Job execution and monitoring
- Credential operations
- System maintenance

**Example:**

.. code-block:: bash

   multiflexi-cli job:list --status=failed
   multiflexi-cli company:create --name "Acme Corp"
   multiflexi-cli runtemplate:show 42

REST API
~~~~~~~~

Programmatic integration:

- **Authentication:** OAuth2, API tokens
- **Formats:** JSON, XML, YAML, HTML
- **Endpoints:** Companies, Applications, RunTemplates, Jobs, Users
- **Methods:** GET, POST, PUT, DELETE

**Example:**

.. code-block:: bash

   curl -u user:pass http://server/api/VitexSoftware/MultiFlexi/1.0.0/jobs.json

Key Features
------------

Multi-Platform Integration
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Native support for:

- **AbraFlexi** (Czech ERP/accounting system)
- **Pohoda** (Stormware accounting software)
- **Banking APIs** (FioBank, RaiffeisenBank, CSAS)
- **Office365** (email, calendar)
- **VaultWarden** (password management)

Three-Tier Credential Management
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Sophisticated credential architecture:

1. **CredentialPrototype** (JSON templates)
2. **CredentialType** (company-specific instances)
3. **Credential** (actual values for RunTemplates)

See :doc:`credential-management` for details.

Multiple Execution Environments
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Applications can run in:

- **Native:** Direct execution on host
- **Docker:** Containerized execution
- **Podman:** Rootless containers
- **Kubernetes:** Cluster orchestration
- **Azure:** Cloud-based execution

Job Artifact Preservation
~~~~~~~~~~~~~~~~~~~~~~~~~~

Every job automatically preserves:

- **stdout.txt:** Standard output stream
- **stderr.txt:** Standard error stream
- **Result files:** Application-generated outputs

Artifacts stored in database, accessible via Web UI or API.

Comprehensive Monitoring
~~~~~~~~~~~~~~~~~~~~~~~~~

Integration with:

- **Zabbix:** Metrics and alerting
- **OpenTelemetry:** Distributed tracing
- **Built-in Logging:** SQL-based audit trail

Security & Compliance
---------------------

Authentication & Authorization
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

- User accounts with password hashing
- API token generation
- Session management
- Role-based access control

Multi-Tenant Data Isolation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

- Company-scoped credentials
- Separate job histories
- Isolated configurations
- Foreign key constraints

GDPR Compliance
~~~~~~~~~~~~~~~

- Right to erasure tools
- Data retention policies
- Audit trail logging
- Consent management

See :doc:`../gdpr-compliance` for details.

Deployment Options
------------------

Standalone Server
~~~~~~~~~~~~~~~~~

Single-server deployment with local database:

- Ideal for: Small teams, single organization
- Requirements: Debian/Ubuntu, MySQL, Apache
- Package: ``multiflexi-mysql``

Docker Deployment
~~~~~~~~~~~~~~~~~

Containerized deployment:

- Ideal for: Development, testing, portable setups
- Images available on Docker Hub
- Docker Compose configurations provided

See :doc:`../administration/docker` for details.

Ansible Automation
~~~~~~~~~~~~~~~~~~

Infrastructure-as-Code deployment:

- Ideal for: Multi-server, production environments
- Collection: ``vitexsoftware.multiflexi``
- Idempotent playbooks

See :doc:`../integrations/ansible` for details.

Scaling Considerations
----------------------

Small Deployments (< 100 jobs/day)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

- Single server
- SQLite or MySQL
- Native executor

Medium Deployments (100-1000 jobs/day)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

- Dedicated database server
- MySQL/PostgreSQL
- Multiple executor daemons

Large Deployments (> 1000 jobs/day)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

- Load-balanced web servers
- High-availability database
- Distributed executor fleet
- Redis caching
- CDN for static assets

See Also
--------

- :doc:`data-model` - Core entities and relationships
- :doc:`execution-architecture` - How jobs are scheduled and executed
- :doc:`credential-management` - Secure authentication handling
- :doc:`job-lifecycle` - Detailed job execution phases

.. note::

   This overview provides a high-level understanding of MultiFlexi. Explore the linked pages for deeper technical details.
