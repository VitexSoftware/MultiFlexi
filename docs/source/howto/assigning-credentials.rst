Assigning Credentials
======================

**Target Audience:** Users, Administrators
**Difficulty:** Intermediate
**Prerequisites:** :doc:`creating-runtemplates`; understanding of :doc:`../concepts/credential-management`

.. contents::
   :local:
   :depth: 2

Overview
--------

Credentials let a job authenticate to external systems (ERP, bank API, mail server, etc.) without hard-coding any secrets in the application. The process has two stages:

1. **Create a CredentialType** — configure the actual connection values for a company
2. **Assign the CredentialType to a RunTemplate** — inject those values as environment variables when the job runs

Step 1: Install the Credential Prototype Package
--------------------------------------------------

Credential types are defined by prototype packages. Install the one you need:

.. code-block:: bash

   sudo apt install multiflexi-abraflexi   # AbraFlexi ERP
   sudo apt install multiflexi-mail        # SMTP email
   sudo apt install multiflexi-csas        # Česká Spořitelna API
   sudo apt install multiflexi-raiffeisenbank  # Raiffeisenbank API
   sudo apt install multiflexi-vaultwarden # Bitwarden/VaultWarden

After installation the prototype is automatically registered in MultiFlexi.

Step 2: Create a CredentialType for the Company
-------------------------------------------------

**Via the web interface:**

1. Open the company detail page (**Companies** → select company)
2. Click **"Credentials"** tab
3. Click **"+ Add Credential Type"**
4. Select the **Prototype** (e.g. "AbraFlexi ERP connection")
5. Fill in the connection values:

   .. code-block:: text

      Label:              Production AbraFlexi
      ABRAFLEXI_URL:      https://erp.acme.example.com:5434
      ABRAFLEXI_USER:     admin
      ABRAFLEXI_PASSWORD: ●●●●●●●●
      ABRAFLEXI_COMPANY:  ACME_s.r.o.

6. Click **"Save"**

**Via CLI:**

.. code-block:: bash

   multiflexi-cli credtype create \
     --company=ACME \
     --prototype=ABRAFLEXI \
     --label="Production AbraFlexi" \
     --ABRAFLEXI_URL=https://erp.acme.example.com:5434 \
     --ABRAFLEXI_USER=admin \
     --ABRAFLEXI_PASSWORD=secret

   # List created CredentialTypes
   multiflexi-cli credtype list --company=ACME

.. tip::

   You can create multiple CredentialType instances from the same prototype for different environments (e.g. "Production AbraFlexi" and "Test AbraFlexi").

Step 3: Assign the CredentialType to a RunTemplate
----------------------------------------------------

**Via the web interface:**

1. Open the RunTemplate detail page
2. Click the **"Credentials"** tab
3. Click **"+ Assign Credential"**
4. Select the CredentialType from the dropdown (e.g. "Production AbraFlexi")
5. Click **"Save"**

**Via CLI:**

.. code-block:: bash

   multiflexi-cli runtemplate assign-credential \
     --runtemplate=42 \
     --credentialtype=7

   # Verify the assignment
   multiflexi-cli runtemplate list-credentials --runtemplate=42

How Credentials Are Used
-------------------------

When the executor runs the job, it merges the credential field values into the process environment. For example, if the CredentialType has:

.. code-block:: text

   ABRAFLEXI_URL = https://erp.acme.example.com:5434
   ABRAFLEXI_USER = admin
   ABRAFLEXI_PASSWORD = secret

The job process will receive those as environment variables and the application connects automatically.

Multiple Credentials per RunTemplate
--------------------------------------

A RunTemplate can have multiple credentials assigned — for example, a job that reads from AbraFlexi and sends the result by email needs both:

- An **AbraFlexi** CredentialType
- A **Mail** CredentialType

Repeat Step 3 for each required credential.

Removing a Credential Assignment
----------------------------------

.. code-block:: bash

   multiflexi-cli runtemplate remove-credential \
     --runtemplate=42 \
     --credentialtype=7

Removing an assignment does not delete the CredentialType itself — it remains available to be assigned to other RunTemplates.

Updating Credential Values
---------------------------

If a password or API key changes, update the CredentialType (not the RunTemplate):

**Via web:** **Companies** → company → **Credentials** → click the CredentialType → edit values → Save

**Via CLI:**

.. code-block:: bash

   multiflexi-cli credtype update \
     --id=7 \
     --ABRAFLEXI_PASSWORD=newpassword

All RunTemplates that use this CredentialType will automatically use the new values on the next job run.

See Also
--------

- :doc:`../concepts/credential-management` — Three-tier credential architecture
- :doc:`creating-runtemplates` — RunTemplate creation
- :doc:`../reference/configuration` — Encryption settings for stored credentials
