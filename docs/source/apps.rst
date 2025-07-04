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

.. note::

    There is a JSON Schema available for validating MultiFlexi application definitions:
    `multiflexi.app.schema.json <https://github.com/VitexSoftware/MultiFlexi/blob/master/lib/multiflexi.app.schema.json>`_
    This schema can be used to verify the correctness of the structure of JSON files with application definitions.

Applications Overview
=====================

MultiFlexi offers a variety of applications to enhance your accounting and business processes. Below is a brief overview of the available applications:

.. list-table:: Available Applications
    :header-rows: 1

    * - Name
      - Description
      - HomePage
    * - AbraFlexi Checker
      - Check AbraFlexi availability
      - https://github.com/VitexSoftware/php-abraflexi-config
    * - Daily Digest
      - Digest for your Accounting every day
      - https://github.com/VitexSoftware/AbraFlexi-Digest/
    * - Weekly Digest
      - Digest for your Accounting every week
      - https://github.com/VitexSoftware/AbraFlexi-Digest/
    * - Monthly Digest
      - Digest for your Accounting every month
      - https://github.com/VitexSoftware/AbraFlexi-Digest/
    * - Email Sender
      - Send unsent documents with attachments
      - https://github.com/VitexSoftware/abraflexi-mailer/
    * - Kimai2AbraFlexi
      - Convert your Kiami timesheets into AbraFlexi invoices
      - https://github.com/VitexSoftware/Kimai2AbraFlexi
    * - Email Importer
      - Načítá doklady z mailboxu do FlexiBee
      - https://github.com/VitexSoftware/AbraFlexi-email-importer
    * - Show Unsent
      - Show Unsent documents
      - https://github.com/VitexSoftware/abraflexi-mailer/
    * - AbraFlexi send
      - Simple trigger AbraFlexi to send all unsent documents in Issued Invoices Agenda
      - https://github.com/VitexSoftware/abraflexi-mailer/
    * - Redmine2AbraFlexi
      - Redmine WorkHours to AbraFlexi invoice
      - https://github.com/VitexSoftware/Redmine2AbraFlexi/
    * - Yearly Digest
      - Generate AbraFlexi report every year
      - https://github.com/VitexSoftware/AbraFlexi-Digest/
    * - MultiFlexi Probe
      - Task launcher testing tool
      - https://github.com/VitexSoftware/MultiFlexi
    * - AllTime Digest
      - Digest for your Accounting from begin to now
      - https://github.com/VitexSoftware/AbraFlexi-Digest/
    * - AbraFlexi Bulk Mailer
      - By Query select recipients from Addressbook and send mail based on template
      - https://github.com/VitexSoftware/abraflexi-mailer/
    * - Subreg to AbraFlexi
      - Import Subreg Pricelist into AbraFlexi
      - https://github.com/Spoje-NET/subreg2abraflexi/
    * - Realpad to Mailkit
      - Synchronize Realpad Contacts into Mailkit
      - https://github.com/Spoje-NET/realpad2mailkit/
    * - AbraFlexi Revolut statements import
      - Import Revolut bank statements into AbraFlexi
      - https://github.com/VitexSoftware/AbraFlexi-Revolut
    * - AbraFlexi transaction report
      - Obtain AbraFlexi bank transaction report
      - https://github.com/VitexSoftware/abraflexi-matcher/
    * - AbraFlexi Bank statements puller
      - Pull bank statements into AbraFlexi
      - https://github.com/VitexSoftware/abraflexi-matcher/
    * - AbraFlexi Issued invoices Matcher
      - Not even Invoice matcher
      - https://github.com/VitexSoftware/abraflexi-matcher/
    * - Recieved invoices Matcher
      - Match received invoices with outcoming payments
      - https://github.com/VitexSoftware/abraflexi-matcher/
    * - Contract to Invoices
      - Trigger AbraFlexi contracts to generate invoices
      - https://github.com/VitexSoftware/abraflexi-contract-invoices
    * - Clear Reminder Labels
      - Clear Debtor's labels
      - https://github.com/VitexSoftware/abraflexi-reminder
    * - Debts overview
      - Gather unsettled invoices
      - https://github.com/VitexSoftware/abraflexi-reminder
    * - Notify Customers
      - Send inventarization
      - https://github.com/VitexSoftware/abraflexi-reminder
    * - SMS input
      - E5180s-22 SMS to Json receiver
      - https://github.com/Spoje-NET/Sms-Input
    * - Pohoda Checker
      - Check Stormware mServer availability
      - https://github.com/Spoje-NET/pohoda-client-checker
    * - AbraFlexi Benchmark
      - AbraFlexi Server Benchmark
      - https://github.com/VitexSoftware/AbraFlexi-Tools
    * - AbraFlexi Copy
      - Copy Company data between two AbraFlexi servers
      - https://github.com/VitexSoftware/AbraFlexi-Tools
    * - Price Fixer
      - Bundle product price updater for AbraFlexi
      - https://github.com/VitexSoftware/AbraFlexi-pricefixer
    * - Subreg credit Check
      - Check Subreg Credit for Customers in AbraFlexi
      - https://github.com/Spoje-NET/subreg2abraflexi/
    * - SubReg for AbraFlexi
      - Import Subreg Pricelist into AbraFlexi
      - https://github.com/Spoje-NET/subreg2abraflexi/
    * - Raiffeisenbank Transactions importer for AbraFlexi
      - Raiffeisen Bank Transaction puller
      - https://github.com/VitexSoftware/abraflexi-raiffeisenbank
    * - Raiffeisenbank Statement importer for AbraFlexi
      - Raiffeisen Bank Statements puller
      - https://github.com/VitexSoftware/abraflexi-raiffeisenbank
    * - discomp2abraflexi
      - Import Pricelist from Discomp to AbraFlexi
      - https://github.com/Spoje-NET/discomp2abraflexi
    * - Pohoda Transaction Report
      - Check Bank Transactions in Stormware Pohoda
      - https://github.com/Spoje-NET/pohoda-client-checker
    * - Reminder
      - Remind unsettled invoices
      - https://github.com/VitexSoftware/abraflexi-reminder
    * - RB Balance
      - Raiffeisenbank Balance check
      - https://github.com/Spoje-NET/raiffeisenbank-statement-tools
    * - Raiffeisenbank statements for PohodaSQL
      - Import Raiffeisenbank statements into Pohoda
      - https://github.com/Spoje-NET/pohoda-raiffeisenbank
    * - RB statement downloader
      - Download Raiffeisenbank statements in given format
      - https://github.com/Spoje-NET/raiffeisenbank-statement-tools
    * - Raiffeisenbank statements for Pohoda
      - Import Raiffeisenbank statements into Pohoda
      - https://github.com/Spoje-NET/pohoda-raiffeisenbank
    * - Raiffeisenbank statements for PohodaSQL+Sharepoint
      - Import Raiffeisenbank statements into PohodaSQL and store in Sharepoint
      - https://github.com/Spoje-NET/pohoda-raiffeisenbank
    * - Fio Statement Downloader
      - Download FioBank statements to disk
      - https://github.com/Spoje-NET/fiobank-statement-tools
    * - RB statement mailer
      - Download Raiffeisenbank statements in given format and send it via email
      - https://github.com/Spoje-NET/raiffeisenbank-statement-tools
    * - Fio Statement Mailer
      - Send FioBank statements to mail recipient
      - https://github.com/Spoje-NET/fiobank-statement-tools
    * - Fio transaction report
      - FioBank transaction report
      - https://github.com/Spoje-NET/fiobank-statement-tools
    * - RB transaction report
      - Raiffeisenbank transaction report
      - https://github.com/Spoje-NET/raiffeisenbank-statement-tools
    * - Import Raiffeisen bank Statements to AbraFlexi Events
      - Download Raiffeisenbank PDF Statements and import them to AbraFlexi events
      - https://github.com/VitexSoftware/abraflexi-raiffeisenbank


