Applications
============

.. toctree::
   :maxdepth: 2

.. contents::

MultiFlexi Application Requirements
===================================

MultiFlexi can execute applications written in any programming language. The application must read its configuration from environment variables. The output can be directed to stdout/stderr and stored. Additionally, it is possible to generate a JSON file and pass it to Zabbix.

Application definition
----------------------

Application is defined by a JSON file. The file must contain the following fields:

- ``name``: Name of the application
- ``description``: Description of the application
- ``homepage``: URL to the application's homepage
- ``executable``: Name of the executable file
- ``setup``: Command launched before first run of the application runtemplate
- ``deploy``: Command to deploy the application. Mostly apt install or git clone
- ``cmdparams``: Command line parameters for the application  - you can use {VARIABLE_NAME} to reference other environment variables
- ``ociimage``: Docker image name. Only for applications running in Docker, Podman or Kubernetes
- ``requirements``: Requirements for the application - e.g. AbraFlexi or Pohoda or RaiffeisenBank
- ``uuid``: Unique identifier for the application
- ``topics``: Keywords related to the application
- ``multiflexi``: Version of MultiFlexi required by the application
- ``environment``: Environment variables required by the application

The ``environment`` field contains a list of environment variables required by the application. Each variable must have the following fields:

- ``type``: Type of the variable (text, password, string)
- ``description``: Description of the variable
- ``defval``: Default value of the variable - you can use {VARIABLE_NAME} to reference other environment variables
- ``required``: Whether the variable is required or not

.. note::

    See more about configuration fields on :ref:`configuration` page.

The application icon is svg file stored in the same directory as json application definition. The icon must be named using the application's UUID.

Special variables
-----------------

The Zabbix post job action can be used to send the application's output to Zabbix. The following environment variables are used for this purpose:

- ``RESULT_FILE``: filename where the application stores its output
- ``ZABBIX_KEY``: Zabbix key name for the application.

Example JSON Definition
-----------------------

Here is an example of a JSON file defining an application:

.. code-block:: json

  {
      "image": "",
      "name": "RB transaction report",
      "description": "Raiffeisenbank transaction report",
      "executable": "raiffeisenbank-transaction-report",
      "setup": "",
      "cmdparams": "",
      "deploy": "apt install raiffeisenbank-statement-tools",
      "homepage": "https://github.com/Spoje-NET/raiffeisenbank-statement-tools",
      "requirements": "RaiffeisenBank",
      "ociimage": "docker.io/spojenet/raiffeisenbank-statement-tools",
      "uuid": "97f30cf9-2d9e-4d91-ad65-9bdd8b4663cd",
      "topics": "Bank,RaiffeisenBank,Transactions,Check,Json,Report",
      "environment": {
          "ACCOUNT_NUMBER": {
              "type": "text",
              "description": "Bank Account Number",
              "defval": "",
              "required": true
          },
          "CERT_PASS": {
              "type": "password",
              "description": "Certificate Password",
              "defval": "",
              "required": true
          }
      }
  }

.. note::

    Examples for App developers can be found at:
    
    - `MultiFlexi-Python-App-example <https://github.com/VitexSoftware/MultiFlexi-Python-App-example>`_

    - `MultiFlexi-Java-App-Example <https://github.com/VitexSoftware/MultiFlexi-Java-App-Example>`_

    - `MultiFlexi-Rust-App-Example <https://github.com/VitexSoftware/MultiFlexi-Rust-App-Example>`_

    Examples for other languages coming soon.


JSON Schema for Application Definitions
=======================================

This schema can be used to verify the correctness of the structure of JSON files with application definitions.

It defines the required fields, their types, and any constraints on their values.

.. code-block:: json

    {
      "$schema": "http://json-schema.org/draft-07/schema#",
      "title": "MultiFlexi App Definition",
      "type": "object",
      "properties": {
       "image": { "type": "string" },
       "name": { "type": "string" },
       "description": { "type": "string" },
       "executable": { "type": "string" },
       "setup": { "type": "string" },
       "cmdparams": { "type": "string" },
       "deploy": { "type": "string" },
       "homepage": { "type": "string" },
       "ociimage": { "type": "string" },
       "uuid": { "type": "string" },
       "topics": { "type": "string" },
       "requirements": { "type": "string" },
       "version": { "type": "string" },
       "multiflexi": { "type": "string" },
       "environment": {
        "type": "object",
        "patternProperties": {
          "^[A-Z0-9_]+$": {
           "type": "object",
           "properties": {
            "type": { "type": "string" },
            "description": { "type": "string" },
            "defval": {},
            "required": { "type": "boolean" },
            "hint": { "type": "string" },
            "options": {
              "type": ["object", "array"]
            }
           },
           "additionalProperties": true
          }
        }
       }
      },
      "required": [
       "name",
       "description",
       "executable",
       "environment"
      ],
      "additionalProperties": true
    }
  
.. note::

    The JSON Schema for validating MultiFlexi application definitions is available online:
    `multiflexi.app.schema.json <https://github.com/VitexSoftware/php-vitexsoftware-multiflexi-core/blob/main/multiflexi.app.schema.json>`_

Report JSON Schema
==================

Applications can optionally emit structured execution reports consumed by MultiFlexi (and e.g. exported to monitoring/analysis systems). These reports are validated by a separate JSON Schema.

Purpose:

* Provide a consistent machine-readable structure for application output summaries
* Allow validation before ingestion (fail fast on malformed data)
* Enable tooling (dashboards, exporters) to rely on stable field names

Key Concepts (as defined in the report schema):

* Metadata about the producing application (UUID, name, version)
* Timing information (start/end timestamps, duration)
* Result classification (status / severity / exit code)
* Produced artifacts (paths, checksums, sizes) when relevant
* Metrics (numeric values with units or context)
* Messages / log excerpts (structured list)
* Optional links (URLs to external resources or dashboards)

Validation Schema:

`multiflexi.report.schema.json <https://github.com/VitexSoftware/php-vitexsoftware-multiflexi-core/blob/main/multiflexi.report.schema.json>`_

Basic Report Example:

.. code-block:: json

   {
     "app_uuid": "97f30cf9-2d9e-4d91-ad65-9bdd8b4663cd",
     "app_name": "RB transaction report",
     "generated_at": "2025-09-24T12:34:56Z",
     "status": "success",
     "duration_ms": 8421,
     "metrics": [
       { "name": "transactions_processed", "value": 128, "unit": "count" },
       { "name": "total_amount", "value": 51234.77, "unit": "CZK" }
     ],
     "artifacts": [
       { "path": "output/report-2025-09-24.json", "size": 20480 }
     ],
     "messages": [
       { "level": "info", "text": "Processing completed" }
     ]
   }

Implementation Notes:

* If your application already writes a domain-specific JSON output, you can wrap or transform it into the report schema just before exit.
* Keep timestamps in ISO 8601 (UTC) for portability.
* Use stable metric names—prefer lowercase with underscores.
* Omit sections (e.g. artifacts, metrics) rather than sending empty arrays if not applicable (schema usually allows absence).
* Validate locally during development with any JSON Schema validator before integrating.

