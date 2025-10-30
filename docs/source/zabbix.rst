Zabbix Integration
==================

MultiFlexi provides comprehensive integration with Zabbix for infrastructure monitoring, job execution tracking, and automated alerting. This integration enables real-time visibility into MultiFlexi operations and allows proactive incident management.

.. toctree::
   :maxdepth: 2

.. contents::
   :local:
   :depth: 3

Overview
--------

The MultiFlexi-Zabbix integration provides:

- **Job Execution Monitoring**: Track job success/failure rates, execution times, and status
- **Application Metrics**: Monitor application performance and availability
- **Company-Level Monitoring**: Track metrics per company/tenant
- **Low-Level Discovery (LLD)**: Automatic discovery of companies, applications, and run templates
- **Custom Metrics**: Send application-specific metrics from job output
- **Alerting**: Configure alerts based on job failures, execution times, or custom metrics

Architecture
------------

MultiFlexi communicates with Zabbix using two methods:

**1. Zabbix Sender Protocol** (recommended)
   - Native PHP implementation of Zabbix sender protocol
   - Direct TCP socket communication to Zabbix server (port 10051)
   - No external dependencies required
   - Real-time metric transmission

**2. Zabbix Sender Binary** (optional)
   - Uses system ``zabbix_sender`` command
   - Requires ``zabbix-sender`` package installation
   - Enabled with ``USE_ZABBIX_SENDER=true``

Data Flow:

.. code-block:: text

   MultiFlexi Job → Action Handler → ZabbixSender → Zabbix Server → Zabbix Database
                                                           ↓
                                                    Zabbix Frontend
                                                           ↓
                                                      Alerts/Graphs

Configuration
-------------

Environment Variables
~~~~~~~~~~~~~~~~~~~~

Configure Zabbix integration using environment variables in ``/etc/multiflexi/multiflexi.env`` or ``.env``:

.. code-block:: bash

   # Zabbix Server Configuration
   ZABBIX_SERVER=zabbix.example.com      # Zabbix server hostname or IP
   ZABBIX_HOST=multiflexi-server          # This MultiFlexi instance hostname in Zabbix
   
   # Optional: Use system zabbix_sender binary instead of native PHP sender
   USE_ZABBIX_SENDER=false                # Set to 'true' to use /usr/bin/zabbix_sender

**Variable Descriptions:**

- ``ZABBIX_SERVER``: The hostname or IP address of your Zabbix server/proxy. If not set, Zabbix integration is disabled.
- ``ZABBIX_HOST``: The monitored host name as registered in Zabbix. Defaults to system hostname if not specified. Can be overridden per-company.
- ``USE_ZABBIX_SENDER``: When ``true``, uses the system ``zabbix_sender`` binary instead of native PHP implementation.

Company-Specific Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Each company can override the Zabbix hostname using the ``zabbix_host`` field in the company configuration:

.. code-block:: bash

   multiflexi-cli company update --id=1 --zabbix_host=customer-server

This allows multi-tenant deployments where different companies report to different Zabbix hosts.

Zabbix Server Setup
~~~~~~~~~~~~~~~~~~~

**1. Create Host in Zabbix**

Create a host in Zabbix matching your ``ZABBIX_HOST`` value:

- Host name: ``multiflexi-server`` (or your configured value)
- Monitored by: Zabbix server or proxy
- Interfaces: Not required (using passive items)

**2. Create Zabbix Trapper Items**

MultiFlexi sends metrics as Zabbix trapper items. For each metric, create a trapper item:

- Type: ``Zabbix trapper``
- Key: ``zabbix_action[{key}]`` (see Metric Keys section)
- Type of information: Text, Numeric, or Log depending on metric

**3. Import MultiFlexi Template** (recommended)

Import the provided Zabbix template with pre-configured items, triggers, and graphs:

.. code-block:: bash

   # Template location in MultiFlexi package
   /usr/share/multiflexi/zabbix-templates/multiflexi-template.xml

The template includes:

- **System Status Items**: Database configuration, service status, entity counts
- **Job Monitoring**: Job execution status and statistics
- **Company Discovery**: Automatic discovery of companies/tenants
- **RunTemplate Discovery**: Application and company-specific job monitoring
- **Action Discovery**: Monitoring of Zabbix actions configured in RunTemplates
- **Pre-configured Triggers**: Job failures, low success rates, service down alerts
- **Performance Graphs**: Entity statistics and job execution metrics
- **HTTP Tests**: Web interface availability monitoring

**Template Features:**

- Compatible with Zabbix 6.0+
- Low-Level Discovery (LLD) for dynamic monitoring
- Dependent items using JSONPath for efficient data extraction
- Value mapping for human-readable status
- Customizable macros for thresholds

**Zabbix Agent Configuration:**

The Zabbix agent configuration is automatically installed at ``/etc/zabbix/zabbix_agent2.d/multiflexi.conf`` with the following UserParameters:

- ``multiflexi.company.lld`` - Company discovery
- ``multiflexi.job.lld`` - Job/task discovery
- ``multiflexi.runtemplate.lld[*]`` - RunTemplate discovery
- ``multiflexi.action.lld`` - Action discovery
- ``multiflexi.appstatus`` - System status (JSON format)
- ``multiflexi.jobstatus`` - Job status summary (JSON format)

Restart Zabbix agent after package installation:

.. code-block:: bash

   systemctl restart zabbix-agent2

Usage
-----

Zabbix Action Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Zabbix integration is configured as a success or failure action in RunTemplates.

**Web Interface:**

1. Navigate to RunTemplate details
2. Click "Configure Actions"
3. Enable "Zabbix" for Success and/or Fail actions
4. Configure:
   - **Zabbix key**: Item key in Zabbix (supports macros: ``{COMPANY_CODE}``, ``{APP_CODE}``, ``{RUNTEMPLATE_ID}``)
   - **Metrics file**: Path to JSON file with metrics (optional, uses stdout if empty)

**CLI Configuration:**

.. code-block:: bash

   # Create RunTemplate with Zabbix action
   multiflexi-cli runtemplate create \
     --name="Daily Backup" \
     --app_id=5 \
     --company_id=1 \
     --interv="@daily"
   
   # Configure actions via web interface or database

Metric Keys
~~~~~~~~~~~

Metric keys follow this pattern:

.. code-block:: text

   zabbix_action[{COMPANY_CODE}-{APP_CODE}-{RUNTEMPLATE_ID}-data]

**Examples:**

- ``zabbix_action[ACME-backup-42-data]`` - Company ACME, backup app, runtemplate 42
- ``zabbix_action[DEMO-invoice-sync-15-data]`` - Company DEMO, invoice sync app, runtemplate 15

**Custom Keys:**

You can override the default key in the Zabbix action configuration:

.. code-block:: text

   zabbix_action[custom-metric-name]

Sending Metrics
~~~~~~~~~~~~~~~

**Method 1: Standard Output**

By default, the Zabbix action sends job stdout to Zabbix:

.. code-block:: bash

   #!/bin/bash
   # Your application
   echo "Jobs processed: 150"
   echo "Errors: 0"
   echo "Duration: 5.2s"

This output is sent to Zabbix when the Zabbix action executes.

**Method 2: Metrics File**

For structured data, write a JSON file and specify it in the Zabbix action:

.. code-block:: json

   {
     "jobs_processed": 150,
     "errors": 0,
     "duration_seconds": 5.2,
     "status": "success"
   }

Configure the metrics file path:

- **Web UI**: Set "Metrics file" field to ``/tmp/metrics.json``
- **Application**: Define ``RESULT_FILE`` environment variable

**Method 3: Application Environment Variable**

Applications can define ``RESULT_FILE`` and ``ZABBIX_KEY`` in their JSON definition:

.. code-block:: json

   {
     "environment": {
       "RESULT_FILE": {
         "type": "file-path",
         "description": "Output metrics file",
         "defval": "/tmp/app-metrics.json",
         "required": false
       },
       "ZABBIX_KEY": {
         "type": "string",
         "description": "Zabbix item key",
         "defval": "app-custom-metric",
         "required": false
       }
     }
   }

Low-Level Discovery (LLD)
--------------------------

MultiFlexi provides LLD scripts for automatic discovery of monitoring entities in Zabbix.

Available LLD Scripts
~~~~~~~~~~~~~~~~~~~~~

**1. multiflexi-zabbix-lld**

Discovers companies:

.. code-block:: bash

   multiflexi-zabbix-lld

Output:

.. code-block:: json

   [
     {
       "{#COMPANY_NAME}": "Acme Corporation",
       "{#COMPANY_CODE}": "ACME",
       "{#COMPANY_SERVER}": "multiflexi-server"
     },
     {
       "{#COMPANY_NAME}": "Beta Industries",
       "{#COMPANY_CODE}": "BETA",
       "{#COMPANY_SERVER}": "multiflexi-server"
     }
   ]

With ``-a`` flag, discovers applications per company:

.. code-block:: bash

   multiflexi-zabbix-lld -a

Output includes:

- ``{#APPNAME}`` - Application name
- ``{#INTERVAL}`` - Execution interval (e.g., "hourly", "daily")
- ``{#COMPANY_NAME}`` - Company name
- ``{#COMPANY_CODE}`` - Company code/slug
- ``{#COMPANY_SERVER}`` - Zabbix host name

**2. multiflexi-zabbix-lld-company**

Discovers run templates for a specific company:

.. code-block:: bash

   multiflexi-zabbix-lld-company SERVER.COMPANY_CODE

Example:

.. code-block:: bash

   multiflexi-zabbix-lld-company multiflexi-server.ACME

Output:

.. code-block:: json

   [
     {
       "{#APPNAME}": "Invoice Sync",
       "{#APPNAME_CODE}": "invoice-sync",
       "{#APPNAME_UUID}": "a1b2c3d4-...",
       "{#INTERVAL}": "hourly",
       "{#INTERVAL_SECONDS}": "3600",
       "{#RUNTEMPLATE}": "15",
       "{#RUNTEMPLATE_NAME}": "Daily Invoice Sync",
       "{#COMPANY_NAME}": "Acme Corporation",
       "{#COMPANY_CODE}": "ACME",
       "{#COMPANY_SERVER}": "multiflexi-server"
     }
   ]

**3. multiflexi-zabbix-lld-actions**

Discovers run templates with Zabbix actions configured:

.. code-block:: bash

   multiflexi-zabbix-lld-actions

Output includes:

- ``{#RUN_TEMPLATE_ID}`` - RunTemplate ID
- ``{#RUN_TEMPLATE_NAME}`` - RunTemplate name
- ``{#COMPANY_ID}`` - Company ID
- ``{#COMPANY_NAME}`` - Company name
- ``{#APP_ID}`` - Application ID
- ``{#APP_NAME}`` - Application name
- ``{#SUCCESS_ACTIONS}`` - Serialized success actions
- ``{#FAIL_ACTIONS}`` - Serialized fail actions
- ``{#ZABBIX_KEY_SUCCESS}`` - Zabbix key for success
- ``{#ZABBIX_KEY_FAIL}`` - Zabbix key for failure

**4. multiflexi-zabbix-lld-tasks**

Discovers scheduled tasks/jobs.

Zabbix LLD Configuration
~~~~~~~~~~~~~~~~~~~~~~~~

In Zabbix, create a discovery rule:

**Item Configuration:**

- Name: ``MultiFlexi Companies Discovery``
- Type: ``External check``
- Key: ``multiflexi-zabbix-lld``
- Type of information: ``Text``
- Update interval: ``1h``

**Item Prototypes:**

Create item prototypes using discovered macros:

.. code-block:: text

   # Company status item
   Key: zabbix_action[{#COMPANY_CODE}-status]
   Name: Company {#COMPANY_NAME} Status
   
   # Application metrics
   Key: zabbix_action[{#COMPANY_CODE}-{#APPNAME_CODE}-data]
   Name: {#COMPANY_NAME} - {#APPNAME} Metrics

**Trigger Prototypes:**

.. code-block:: text

   # Alert on job failure
   Expression: {MultiFlexi:zabbix_action[{#COMPANY_CODE}-{#APPNAME_CODE}-status].str("failed")}=1
   Severity: High
   Name: Job failed for {#COMPANY_NAME} - {#APPNAME}

Monitoring Examples
-------------------

Basic Job Monitoring
~~~~~~~~~~~~~~~~~~~~

Monitor job execution with exit code tracking:

**Application Script:**

.. code-block:: bash

   #!/bin/bash
   # Your job logic
   if [ $? -eq 0 ]; then
     echo "success"
     exit 0
   else
     echo "failed"
     exit 1
   fi

**Zabbix Configuration:**

- Enable Zabbix action for both Success and Fail
- Success key: ``zabbix_action[{COMPANY_CODE}-{APP_CODE}-{RUNTEMPLATE_ID}-success]``
- Fail key: ``zabbix_action[{COMPANY_CODE}-{APP_CODE}-{RUNTEMPLATE_ID}-fail]``

Advanced Metrics
~~~~~~~~~~~~~~~~

Send detailed performance metrics:

**Application Output (metrics.json):**

.. code-block:: json

   {
     "timestamp": "2025-01-30T12:00:00Z",
     "records_processed": 1523,
     "processing_time_ms": 4521,
     "memory_peak_mb": 128.5,
     "errors": 0,
     "warnings": 3,
     "status": "completed"
   }

**Zabbix Items:**

Create dependent items to extract specific fields:

.. code-block:: text

   Master item: zabbix_action[company-app-metrics]
   
   Dependent item: Records Processed
   Preprocessing: JSONPath: $.records_processed
   
   Dependent item: Processing Time
   Preprocessing: JSONPath: $.processing_time_ms
   Units: ms
   
   Dependent item: Memory Usage
   Preprocessing: JSONPath: $.memory_peak_mb
   Units: MB

Multi-Company Monitoring
~~~~~~~~~~~~~~~~~~~~~~~~

Monitor multiple companies with separate Zabbix hosts:

**Company A Configuration:**

.. code-block:: bash

   multiflexi-cli company update --id=1 --zabbix_host=customer-a-server

**Company B Configuration:**

.. code-block:: bash

   multiflexi-cli company update --id=2 --zabbix_host=customer-b-server

Each company's metrics are sent to their respective Zabbix host.

Troubleshooting
---------------

Checking Zabbix Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Verify Zabbix configuration:

.. code-block:: bash

   multiflexi-cli status

Output shows:

.. code-block:: text

   zabbix: multiflexi-server => zabbix.example.com

Or if disabled:

.. code-block:: text

   zabbix: disabled

Testing Connectivity
~~~~~~~~~~~~~~~~~~~~

Test Zabbix server connectivity:

.. code-block:: bash

   # Test with system zabbix_sender
   zabbix_sender -z zabbix.example.com -s multiflexi-server -k test.key -o "test value"
   
   # Check Zabbix server port
   telnet zabbix.example.com 10051
   nc -zv zabbix.example.com 10051

Debugging Failed Sends
~~~~~~~~~~~~~~~~~~~~~~

**Enable Debug Logging:**

.. code-block:: bash

   # In /etc/multiflexi/multiflexi.env
   MULTIFLEXI_DEBUG=true
   EASE_LOGGER="syslog|\MultiFlexi\LogToSQL"

**Check Logs:**

.. code-block:: bash

   # System logs
   tail -f /var/log/syslog | grep -i zabbix
   
   # MultiFlexi database logs
   multiflexi-cli job get --id=JOBID

**Common Issues:**

1. **"No Zabbix server defined"**
   - ``ZABBIX_SERVER`` not set in environment
   - Solution: Configure ``ZABBIX_SERVER`` in ``.env``

2. **"can't connect to zabbix.example.com:10051"**
   - Network connectivity issue
   - Firewall blocking port 10051
   - Incorrect server address
   - Solution: Check network, firewall rules, verify server address

3. **"Required metrics file not found"**
   - Metrics file path doesn't exist
   - Application didn't create the file
   - Solution: Verify file path, check application logs

4. **"zabbix server returned non-successful response"**
   - Zabbix host doesn't exist
   - Item key doesn't exist or wrong type
   - Solution: Create host/item in Zabbix, verify key names

Verifying Data in Zabbix
~~~~~~~~~~~~~~~~~~~~~~~~~

**Check Latest Data:**

1. Zabbix Frontend → Monitoring → Latest data
2. Select host (e.g., ``multiflexi-server``)
3. Filter by application or item name
4. Verify data is arriving

**Check Item History:**

1. Click on item name in Latest data
2. View → History
3. Verify timestamps and values

**Zabbix Server Logs:**

.. code-block:: bash

   tail -f /var/log/zabbix/zabbix_server.log | grep -i trapper

Best Practices
--------------

Metric Naming
~~~~~~~~~~~~~

- Use consistent naming: ``{COMPANY_CODE}-{APP_CODE}-{METRIC_TYPE}``
- Avoid special characters in keys
- Use descriptive names: ``backup-success`` instead of ``bs``
- Document custom keys in application JSON

Data Format
~~~~~~~~~~~

- Use JSON for structured metrics
- Include timestamp in ISO 8601 format
- Include status/severity field
- Keep metrics focused and relevant
- Don't send overly verbose output

Performance
~~~~~~~~~~~

- Batch metrics when possible
- Use metrics files for large datasets
- Avoid sending binary data
- Consider data retention in Zabbix
- Monitor Zabbix server load

Security
~~~~~~~~

- Use Zabbix PSK encryption for sensitive data
- Restrict Zabbix server port (10051) access
- Validate metric data before sending
- Don't include passwords in metrics
- Use separate Zabbix hosts for multi-tenant deployments

Maintenance
~~~~~~~~~~~

- Regularly review Zabbix triggers
- Archive old metrics
- Update LLD rules when adding companies/apps
- Test monitoring after MultiFlexi upgrades
- Document custom Zabbix configurations

Comparison with OpenTelemetry
------------------------------

MultiFlexi supports both Zabbix and OpenTelemetry for monitoring. Choose based on your needs:

**Zabbix:**

- ✅ Mature, proven monitoring solution
- ✅ Comprehensive alerting and escalation
- ✅ Built-in frontend and dashboards
- ✅ LLD for automatic discovery
- ✅ Better for infrastructure monitoring
- ❌ More complex setup
- ❌ Less modern observability features

**OpenTelemetry:**

- ✅ Modern, vendor-neutral standard
- ✅ Cloud-native and microservices-friendly
- ✅ Better for metrics, traces, logs (3 pillars)
- ✅ Integration with Prometheus, Grafana, etc.
- ✅ Simpler metric export
- ❌ Requires separate components (collector, backend)
- ❌ Less mature alerting (depends on backend)

**Recommendation:**

- Use **Zabbix** if you already have Zabbix infrastructure and need comprehensive alerting
- Use **OpenTelemetry** for cloud-native deployments or Prometheus/Grafana stacks
- Use **both** for comprehensive observability (infrastructure + application metrics)

See Also
--------

- :doc:`opentelemetry` - OpenTelemetry integration documentation
- :doc:`configuration` - General configuration options
- :doc:`apps` - Application development guide (including metrics)
- :doc:`multiflexi-cli` - CLI commands including status checking
- `Zabbix Documentation <https://www.zabbix.com/documentation/current/>`_
- `Zabbix Sender Protocol <https://www.zabbix.com/documentation/current/en/manual/appendix/protocols/header_datalen>`_

Reference Implementation
------------------------

The MultiFlexi Zabbix integration source code:

- **Action Handler**: ``php-vitexsoftware-multiflexi-core/src/MultiFlexi/Action/Zabbix.php``
- **Zabbix Sender**: ``php-vitexsoftware-multiflexi-core/src/MultiFlexi/ZabbixSender.php``
- **LLD Scripts**: ``MultiFlexi/lib/zabbix*.php``
- **Protocol Implementation**: ``php-vitexsoftware-multiflexi-core/src/MultiFlexi/Zabbix/``

For development and customization examples, refer to the source code repository.

