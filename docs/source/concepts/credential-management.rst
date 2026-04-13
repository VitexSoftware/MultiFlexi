Credential Management
=====================

**Target Audience:** Users, Administrators
**Difficulty:** Intermediate
**Prerequisites:** Understanding of :doc:`data-model`

.. contents::
   :local:
   :depth: 2

Overview
--------

MultiFlexi uses a **three-tier credential architecture** that separates the *definition* of a credential type from its *configuration* at the company level, and from its *actual use* within individual jobs.

.. code-block:: text

    CredentialPrototype  ──►  CredentialType  ──►  Credential
    (JSON template /           (company-level        (assigned to
     system definition)         instance with         a RunTemplate)
                                actual values)

This design allows the same credential type (e.g. "AbraFlexi ERP connection") to be defined once and then configured separately for each company that uses it, with full isolation between companies.

Tier 1: CredentialPrototype
----------------------------

A **CredentialPrototype** is a JSON-based template that describes a category of credentials: what fields it requires, their types and validation rules, and how it should be presented in the UI.

Prototypes are shipped as Debian packages (e.g. ``multiflexi-abraflexi``, ``multiflexi-mail``, ``multiflexi-vaultwarden``) or imported manually via the CLI.

**Standard credential prototype packages:**

+----------------------------------+------------------------------------------+
| Package                          | Description                              |
+==================================+==========================================+
| ``multiflexi-abraflexi``         | AbraFlexi ERP connection                 |
+----------------------------------+------------------------------------------+
| ``multiflexi-csas``              | Česká Spořitelna / ČSAS / Erste API      |
+----------------------------------+------------------------------------------+
| ``multiflexi-raiffeisenbank``    | Raiffeisenbank Premium API               |
+----------------------------------+------------------------------------------+
| ``multiflexi-mail``              | SMTP / e-mail (Symfony Mailer)           |
+----------------------------------+------------------------------------------+
| ``multiflexi-database-connection``| PDO database connection                 |
+----------------------------------+------------------------------------------+
| ``multiflexi-vaultwarden``       | VaultWarden / Bitwarden secrets          |
+----------------------------------+------------------------------------------+
| ``multiflexi-mtr``               | MTR network diagnostics                  |
+----------------------------------+------------------------------------------+

**Installing a prototype package:**

.. code-block:: bash

   sudo apt install multiflexi-abraflexi

After installation, the prototype is automatically registered in MultiFlexi.

**Importing a custom prototype from JSON:**

.. code-block:: bash

   multiflexi-cli crprototype import --file my-integration.json

**Listing registered prototypes:**

.. code-block:: bash

   multiflexi-cli crprototype list

Prototype JSON Schema
~~~~~~~~~~~~~~~~~~~~~

All prototype JSON files must conform to the credential type schema. The schema enforces:

- ``code``: 2–64 alphanumeric characters (unique identifier)
- ``name``, ``description``, ``version``, ``logo``, ``url``
- ``fields``: array of field definitions with types and validation rules

**Supported field types:** ``string``, ``password``, ``url``, ``email``, ``integer``, ``boolean``, ``select``

**Example prototype JSON:**

.. code-block:: json

   {
     "code": "MYERP",
     "name": "My ERP Connection",
     "description": "Credentials for My ERP REST API",
     "version": "1.0.0",
     "fields": [
       {
         "code": "MYERP_URL",
         "name": "Server URL",
         "type": "url",
         "required": true
       },
       {
         "code": "MYERP_USER",
         "name": "Username",
         "type": "string",
         "required": true
       },
       {
         "code": "MYERP_PASSWORD",
         "name": "Password",
         "type": "password",
         "required": true
       }
     ]
   }

Tier 2: CredentialType
-----------------------

A **CredentialType** is a company-level *instance* of a CredentialPrototype. When a company needs to use a particular integration, an administrator creates a CredentialType for that company, filling in the actual connection values (URL, username, password, API key, etc.).

A single prototype can have multiple CredentialType instances per company — for example, a company may have separate AbraFlexi connections for production and staging environments.

**Creating a CredentialType via the web interface:**

1. Navigate to **Companies** → select your company → **Credentials**
2. Click **"+ Add Credential Type"**
3. Select the desired **Prototype** from the dropdown
4. Fill in the connection values (URL, API key, etc.)
5. Optionally give it a descriptive **Label** (e.g. "Production AbraFlexi")
6. Click **Save**

**Creating a CredentialType via CLI:**

.. code-block:: bash

   multiflexi-cli credtype create \
     --company=1 \
     --prototype=ABRAFLEXI \
     --label="Production AbraFlexi" \
     --ABRAFLEXI_URL=https://erp.example.com \
     --ABRAFLEXI_USER=admin \
     --ABRAFLEXI_PASSWORD=secret

**Listing CredentialTypes for a company:**

.. code-block:: bash

   multiflexi-cli credtype list --company=1

Tier 3: Credential (Assignment)
---------------------------------

A **Credential** is the assignment of a CredentialType to a specific RunTemplate. This is what actually injects the credential environment variables into the job when it runs.

One RunTemplate can have multiple credentials assigned — for example, a job that reads from AbraFlexi and sends email needs both an AbraFlexi CredentialType and a Mail CredentialType.

**Assigning a credential via the web interface:**

1. Open the RunTemplate detail page
2. Click **"Credentials"** tab
3. Click **"+ Assign Credential"**
4. Select the CredentialType from the list
5. Save

See :doc:`../howto/assigning-credentials` for a detailed step-by-step guide.

**Assigning a credential via CLI:**

.. code-block:: bash

   multiflexi-cli runtemplate assign-credential \
     --runtemplate=42 \
     --credentialtype=7

How Credentials Are Injected into Jobs
----------------------------------------

When a job runs, MultiFlexi merges all assigned CredentialType fields into the job's environment. Field codes become environment variable names.

For example, if a CredentialType has a field ``ABRAFLEXI_URL`` with value ``https://erp.example.com``, the job process will have:

.. code-block:: bash

   ABRAFLEXI_URL=https://erp.example.com

This means any application that respects these environment variables will automatically connect to the correct endpoint for the correct company — without storing credentials in the application itself.

Security Considerations
------------------------

- Credential values are stored encrypted in the database (AES-256) when ``DATA_ENCRYPTION_ENABLED=true``
- Password-type fields are masked in the web UI
- Credentials are company-scoped: users can only see credentials for their assigned company
- VaultWarden integration (``multiflexi-vaultwarden``) can be used to store secrets externally instead of in the MultiFlexi database

See Also
--------

- :doc:`data-model` — How credentials relate to other entities
- :doc:`../howto/assigning-credentials` — Practical step-by-step credential assignment
- :doc:`../reference/application-schema` — Application credential requirements
- :doc:`../reference/configuration` — Encryption settings
