OpenTelemetry Integration
===========================

.. contents:: Table of Contents
   :depth: 3
   :local:

Overview
--------

MultiFlexi supports exporting metrics to **OpenTelemetry (OTel)** compatible backends such as Prometheus, Grafana, Jaeger, and the OpenTelemetry Collector.

OpenTelemetry is a vendor-neutral, open-source observability framework that provides APIs, libraries, agents, and instrumentation to enable the collection and export of telemetry data (metrics, logs, and traces).

Why OpenTelemetry?
~~~~~~~~~~~~~~~~~~

* **Vendor-agnostic**: Works with any OpenTelemetry-compatible backend
* **Standardized**: Industry-standard protocol (OTLP)
* **Comprehensive**: Metrics, traces, and logs in one framework
* **Cloud-native**: Built for modern distributed systems
* **Flexible**: Export to multiple backends simultaneously

Coexistence with Zabbix
~~~~~~~~~~~~~~~~~~~~~~~~

MultiFlexi's OpenTelemetry integration runs **alongside** the existing Zabbix integration. Both can be enabled simultaneously:

* **Zabbix**: Provides infrastructure monitoring and alerting
* **OpenTelemetry**: Provides application performance metrics and distributed tracing

Available Metrics
-----------------

MultiFlexi exports the following metrics to OpenTelemetry:

Counters
~~~~~~~~

.. list-table::
   :header-rows: 1
   :widths: 40 60

   * - Metric Name
     - Description
   * - ``multiflexi.jobs.total``
     - Total number of jobs executed
   * - ``multiflexi.jobs.success``
     - Number of successful jobs (exitcode=0)
   * - ``multiflexi.jobs.failed``
     - Number of failed jobs (exitcode≠0)

Histograms
~~~~~~~~~~

.. list-table::
   :header-rows: 1
   :widths: 40 60

   * - Metric Name
     - Description
   * - ``multiflexi.job.duration``
     - Job execution duration in seconds

Observable Gauges
~~~~~~~~~~~~~~~~~

These metrics are collected in real-time when the OTLP endpoint polls them:

.. list-table::
   :header-rows: 1
   :widths: 40 60

   * - Metric Name
     - Description
   * - ``multiflexi.jobs.running``
     - Currently running jobs
   * - ``multiflexi.applications.total``
     - Total number of applications
   * - ``multiflexi.applications.enabled``
     - Number of enabled applications
   * - ``multiflexi.companies.total``
     - Total number of companies
   * - ``multiflexi.runtemplates.total``
     - Total number of run templates

Metric Attributes
~~~~~~~~~~~~~~~~~

All metrics include the following attributes (where applicable):

* ``job_id``: Unique job identifier
* ``app_id``: Application ID
* ``app_name``: Application name
* ``company_id``: Company ID
* ``company_name``: Company name
* ``runtemplate_id``: RunTemplate ID
* ``runtemplate_name``: RunTemplate name
* ``executor``: Executor type (Native, Docker, etc.)
* ``exitcode``: Job exit code

Installation
------------

Step 1: Install OpenTelemetry Dependencies
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Install the required PHP packages using Composer:

.. code-block:: bash

   cd /path/to/multiflexi
   composer require open-telemetry/sdk open-telemetry/exporter-otlp

Step 2: Configure Environment Variables
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Add the following environment variables to your configuration:

**For Docker deployments** (``docker/.env``):

.. code-block:: bash

   # OpenTelemetry Configuration
   OTEL_ENABLED=true
   OTEL_SERVICE_NAME=multiflexi
   OTEL_EXPORTER_OTLP_ENDPOINT=http://otel-collector:4318
   OTEL_EXPORTER_OTLP_PROTOCOL=http/json

**For systemd deployments** (``/etc/multiflexi/multiflexi.conf``):

.. code-block:: bash

   export OTEL_ENABLED=true
   export OTEL_SERVICE_NAME=multiflexi
   export OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4318
   export OTEL_EXPORTER_OTLP_PROTOCOL=http/json

**For Apache/PHP-FPM** (``/etc/multiflexi/apache.conf`` or ``.htaccess``):

.. code-block:: apache

   SetEnv OTEL_ENABLED true
   SetEnv OTEL_SERVICE_NAME multiflexi
   SetEnv OTEL_EXPORTER_OTLP_ENDPOINT http://localhost:4318
   SetEnv OTEL_EXPORTER_OTLP_PROTOCOL http/json

Configuration Options
~~~~~~~~~~~~~~~~~~~~~

.. list-table::
   :header-rows: 1
   :widths: 30 50 20

   * - Variable
     - Description
     - Default
   * - ``OTEL_ENABLED``
     - Enable/disable OpenTelemetry export
     - ``false``
   * - ``OTEL_SERVICE_NAME``
     - Service identifier in OTLP
     - ``multiflexi``
   * - ``OTEL_EXPORTER_OTLP_ENDPOINT``
     - OTLP collector endpoint URL
     - ``http://localhost:4318``
   * - ``OTEL_EXPORTER_OTLP_PROTOCOL``
     - Protocol (``http/json`` or ``grpc``)
     - ``http/json``

Protocol Selection
~~~~~~~~~~~~~~~~~~

MultiFlexi supports two OTLP protocols:

**HTTP/JSON** (recommended):

.. code-block:: bash

   OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4318
   OTEL_EXPORTER_OTLP_PROTOCOL=http/json

**gRPC** (higher performance):

.. code-block:: bash

   OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4317
   OTEL_EXPORTER_OTLP_PROTOCOL=grpc

Testing
-------

Use the built-in CLI command to test your OpenTelemetry configuration:

.. code-block:: bash

   multiflexi-cli telemetry:test

This command will:

1. Check if OpenTelemetry is enabled
2. Display current configuration
3. Send test metrics to the configured endpoint
4. Verify that the connection works

Example output:

.. code-block:: text

   Testing OpenTelemetry Metrics Export

   Configuration:
     Service Name: multiflexi
     Endpoint: http://localhost:4318
     Protocol: http/json

   Initializing OTel Metrics Exporter...
   ✓ Exporter initialized successfully

   Testing job start metric...
   ✓ Job start metric recorded

   Testing job end metrics...
     ✓ Success metric (exitcode=0, duration=5.5s)
     ✓ Failure metric (exitcode=1, duration=2.3s)

   Testing observable gauges (real-time metrics)...
     ✓ multiflexi.jobs.running
     ✓ multiflexi.applications.total
     ✓ multiflexi.companies.total

   Flushing metrics to OTLP endpoint...
   ✓ Metrics flushed successfully

   Test completed successfully!

Deployment Architectures
-------------------------

Architecture 1: Direct to Prometheus
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

MultiFlexi → OpenTelemetry Collector → Prometheus → Grafana

**Docker Compose Example:**

.. code-block:: yaml

   version: '3.8'
   services:
     multiflexi:
       image: vitexsoftware/multiflexi:latest
       environment:
         OTEL_ENABLED: "true"
         OTEL_EXPORTER_OTLP_ENDPOINT: "http://otel-collector:4318"
       depends_on:
         - otel-collector

     otel-collector:
       image: otel/opentelemetry-collector:latest
       command: ["--config=/etc/otel-collector-config.yaml"]
       volumes:
         - ./otel-collector-config.yaml:/etc/otel-collector-config.yaml
       ports:
         - "4318:4318"   # OTLP HTTP
         - "8889:8889"   # Prometheus metrics

     prometheus:
       image: prom/prometheus:latest
       volumes:
         - ./prometheus.yml:/etc/prometheus/prometheus.yml
       ports:
         - "9090:9090"

     grafana:
       image: grafana/grafana:latest
       ports:
         - "3000:3000"
       depends_on:
         - prometheus

**OpenTelemetry Collector Configuration** (``otel-collector-config.yaml``):

.. code-block:: yaml

   receivers:
     otlp:
       protocols:
         http:
           endpoint: 0.0.0.0:4318
         grpc:
           endpoint: 0.0.0.0:4317

   processors:
     batch:

   exporters:
     prometheus:
       endpoint: "0.0.0.0:8889"
     logging:
       loglevel: debug

   service:
     pipelines:
       metrics:
         receivers: [otlp]
         processors: [batch]
         exporters: [prometheus, logging]

**Prometheus Configuration** (``prometheus.yml``):

.. code-block:: yaml

   global:
     scrape_interval: 15s

   scrape_configs:
     - job_name: 'otel-collector'
       static_configs:
         - targets: ['otel-collector:8889']

Architecture 2: Cloud-Native Stack
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

MultiFlexi → OpenTelemetry Collector → Tempo (traces) + Prometheus (metrics) → Grafana

Architecture 3: Kubernetes Deployment
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Use the OpenTelemetry Operator for Kubernetes to automatically instrument MultiFlexi pods.

Grafana Dashboard
-----------------

Import the MultiFlexi OpenTelemetry dashboard to visualize metrics.

Example PromQL Queries
~~~~~~~~~~~~~~~~~~~~~~

**Job Success Rate**:

.. code-block:: promql

   rate(multiflexi_jobs_success_total[5m]) / 
   rate(multiflexi_jobs_total[5m]) * 100

**Average Job Duration**:

.. code-block:: promql

   rate(multiflexi_job_duration_sum[5m]) / 
   rate(multiflexi_job_duration_count[5m])

**Failed Jobs by Application**:

.. code-block:: promql

   sum by (app_name) (
     rate(multiflexi_jobs_failed_total[5m])
   )

**Running Jobs**:

.. code-block:: promql

   multiflexi_jobs_running

Dashboard Panels
~~~~~~~~~~~~~~~~

Create the following panels in Grafana:

1. **Job Execution Rate** (Graph): ``rate(multiflexi_jobs_total[5m])``
2. **Success vs Failure** (Pie Chart): Compare success and failed counters
3. **Job Duration Heatmap**: Use histogram buckets
4. **Top Applications** (Bar Gauge): Jobs by ``app_name``
5. **Active Resources** (Stat): Show gauges for jobs, apps, companies

Troubleshooting
---------------

OpenTelemetry Not Exporting Metrics
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

**Check 1: Verify Configuration**

.. code-block:: bash

   multiflexi-cli telemetry:test

**Check 2: Verify Endpoint Connectivity**

.. code-block:: bash

   curl -v http://localhost:4318/v1/metrics

**Check 3: Check PHP Error Logs**

.. code-block:: bash

   tail -f /var/log/apache2/error.log

**Check 4: Enable Debug Logging**

Set ``MULTIFLEXI_DEBUG=true`` to see detailed OTel export messages.

Metrics Not Appearing in Prometheus
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

1. Check that OpenTelemetry Collector is receiving metrics:

   .. code-block:: bash

      curl http://localhost:8889/metrics

2. Verify Prometheus is scraping the collector:

   Check Prometheus UI → Targets → ``otel-collector``

3. Check for metric name transformations:

   OTel metrics may be renamed by the Prometheus exporter (dots → underscores)

Performance Impact
------------------

OpenTelemetry metrics export has minimal performance overhead:

* **CPU overhead**: < 1% per job
* **Memory overhead**: ~2MB for exporter initialization
* **Network**: Batched exports every 10 seconds

Best Practices
--------------

1. **Use batching**: The default configuration batches metrics for efficiency
2. **Monitor collector health**: Set up alerts for OTLP collector downtime
3. **Set retention policies**: Configure Prometheus retention (default: 15 days)
4. **Use labels wisely**: Avoid high-cardinality labels (e.g., job_id in queries)
5. **Start with HTTP/JSON**: Simpler to debug than gRPC

Security
--------

Securing OTLP Endpoint
~~~~~~~~~~~~~~~~~~~~~~~

Use TLS and authentication for production:

.. code-block:: bash

   OTEL_EXPORTER_OTLP_ENDPOINT=https://otel-collector.example.com:4318
   OTEL_EXPORTER_OTLP_HEADERS="Authorization=Bearer YOUR_TOKEN"

Network Segmentation
~~~~~~~~~~~~~~~~~~~~

Run the OpenTelemetry Collector in a DMZ and restrict access:

* MultiFlexi → Collector: Allow port 4318/4317
* Collector → Prometheus: Allow port 9090
* Collector → Internet: Deny (unless using cloud backends)

Further Reading
---------------

* `OpenTelemetry Official Documentation <https://opentelemetry.io/docs/>`_
* `OpenTelemetry PHP Documentation <https://opentelemetry.io/docs/languages/php/>`_
* `OTLP Specification <https://opentelemetry.io/docs/specs/otlp/>`_
* `Prometheus Documentation <https://prometheus.io/docs/>`_
* `Grafana Documentation <https://grafana.com/docs/>`_

See Also
--------

* :doc:`configuration` - General MultiFlexi configuration
* :doc:`commandline` - CLI commands reference
* :doc:`docker` - Docker deployment guide
* :doc:`ansible` - Ansible deployment automation
