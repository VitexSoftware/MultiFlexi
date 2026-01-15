Usage Guide
===========

.. toctree::
   :maxdepth: 2

.. contents::
   :local:

MultiFlexi provides a unified platform for scheduling, executing, and monitoring automated tasks. This guide covers the primary interfaces and common workflows.

Accessing MultiFlexi
--------------------

MultiFlexi offers three distinct interfaces for valid interaction:

1.  **Web Interface**: The primary dashboard for management and monitoring.
2.  **Command Line Interface (CLI)**: For server-side management and scripting.
3.  **API**: For programmatic integration.

Web Interface
-------------

The web interface is the central hub for MultiFlexi.

**Login**

Navigate to ``http://<your-server>/multiflexi`` and log in with your credentials.

**Dashboard Overview**

Upon login, the dashboard presents:

- **System Status**: Real-time health metrics.
- **Recent Jobs**: Status of recently executed tasks.
- **Upcoming Schedule**: Timeline of planned executions.

**Common Actions**

- **Manage Companies**: Configure tenants (companies) that MultiFlexi will interact with.
- **Install Applications**: Browse and enable applications for specific companies.
- **Schedule Jobs**: Define when and how applications should run.
- **View Logs**: Inspect detailed execution history for debugging.

Command Line Interface (CLI)
----------------------------

The ``multiflexi-cli`` tool allows for efficient system management directly from the terminal.

**Basic Usage**

.. code-block:: bash

   multiflexi-cli [command] [options]

**Key Commands**

- ``multiflexi-cli list``: List all registered jobs.
- ``multiflexi-cli run <job_id>``: Manually trigger a specific job.
- ``multiflexi-cli status``: Check the health of the scheduler daemon.

For a complete command reference, see :doc:`multiflexi-cli` or :doc:`commandline`.

API Integration
---------------

MultiFlexi exposes a RESTful API for external integrations.

- **Endpoint**: ``/api/``
- **Authentication**: OAuth2 or API Tokens.
- **Format**: JSON, XML.

Developers should refer to the :doc:`api` documentation for endpoint details and usage examples.

Common Workflows
----------------

Creating a New Schedule
~~~~~~~~~~~~~~~~~~~~~~~

1.  **Select Company**: Choose the target company from the top menu.
2.  **Choose Application**: Navigate to "Applications" and select the tool to schedule.
3.  **Configure Job**:
    - Set parameters (dates, filters, etc.).
    - Define the **Interval** (e.g., Every Morning at 8:00 AM).
4.  **Save**: The job is now active and will run automatically.

Monitoring Execution
~~~~~~~~~~~~~~~~~~~~

1.  Go to **Job History**.
2.  Filter by Status (Success, Failed).
3.  Click **Log** on any entry to see the full output.

GDPR & Data Privacy
-------------------

MultiFlexi is designed with privacy in mind.

- **Data Retention**: Old transaction data is automatically pruned based on configured retention policies.
- **Right to Erasure**: Tools are available to anonymize or delete specific user data upon request.
- **Audit Trails**: All administrative actions are logged for compliance.

For detailed compliance procedures, consult :doc:`gdpr-compliance`.
