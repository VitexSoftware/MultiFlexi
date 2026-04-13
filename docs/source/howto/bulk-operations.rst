Bulk Operations
================

**Target Audience:** Administrators
**Difficulty:** Intermediate
**Prerequisites:** ``multiflexi-cli`` installed; administrator access

.. contents::
   :local:
   :depth: 2

Overview
--------

The ``multiflexi-cli`` tool supports bulk management of companies, applications, RunTemplates, and credentials. This is useful for initial setup, migrations, mass configuration changes, and scripted deployments.

Bulk Company Management
------------------------

.. code-block:: bash

   # Export all companies to JSON
   multiflexi-cli company list --format=json > companies.json

   # Create multiple companies from a CSV
   while IFS=, read -r name code email; do
     multiflexi-cli company create --name="$name" --code="$code" --email="$email"
   done < companies.csv

   # Deactivate all companies matching a pattern
   multiflexi-cli company list --format=json | \
     jq -r '.[] | select(.code | startswith("TEST")) | .code' | \
     xargs -I{} multiflexi-cli company update --code={} --status=inactive

Bulk Application Assignment
-----------------------------

Assign one application to multiple companies:

.. code-block:: bash

   # Assign multiflexi-probe to all active companies
   multiflexi-cli company list --format=json | \
     jq -r '.[] | select(.status == "active") | .code' | \
     xargs -I{} multiflexi-cli company assign-app --company={} --app=multiflexi-probe

Bulk RunTemplate Creation
--------------------------

Create the same RunTemplate configuration across multiple companies:

.. code-block:: bash

   #!/bin/bash
   # deploy-runtemplates.sh
   # Creates a daily health-check RunTemplate for every company that has multiflexi-probe

   for COMPANY in $(multiflexi-cli company list --format=csv | tail -n+2 | cut -d, -f1); do
     multiflexi-cli runtemplate create \
       --company="$COMPANY" \
       --app=multiflexi-probe \
       --name="Daily health check" \
       --interval=daily \
       --executor=Native \
       --active=1
     echo "Created RunTemplate for $COMPANY"
   done

Bulk Credential Deployment
---------------------------

Create the same CredentialType for a list of companies from a configuration file:

.. code-block:: bash

   #!/bin/bash
   # deploy-credentials.sh

   while IFS=',' read -r company url user password; do
     multiflexi-cli credtype create \
       --company="$company" \
       --prototype=ABRAFLEXI \
       --label="AbraFlexi Production" \
       --ABRAFLEXI_URL="$url" \
       --ABRAFLEXI_USER="$user" \
       --ABRAFLEXI_PASSWORD="$password"
     echo "Credential created for $company"
   done < credentials.csv

Ansible Automation
-------------------

For large deployments, the ``multiflexi-ansible-collection`` provides Ansible roles for managing companies, applications, and credentials as code:

.. code-block:: bash

   # Install the collection
   ansible-galaxy collection install vitexsoftware.multiflexi

See :doc:`../integrations/ansible` for full documentation of the Ansible collection.

Bulk Job Management
--------------------

.. code-block:: bash

   # Cancel all pending jobs for a company
   multiflexi-cli job list --status=pending --company=ACME --format=json | \
     jq -r '.[].id' | \
     xargs -I{} multiflexi-cli job cancel --id={}

   # Re-run all failed jobs from today
   multiflexi-cli job list --status=failed --since=today --format=json | \
     jq -r '.[].runtemplate_id' | sort -u | \
     xargs -I{} multiflexi-cli runtemplate run --id={}

   # Clean up jobs older than 60 days
   multiflexi-cli job cleanup --older-than=60

Exporting Configuration
------------------------

.. code-block:: bash

   # Export all RunTemplates (for backup or migration)
   multiflexi-cli runtemplate list --format=json > runtemplates_backup.json

   # Export company environment variables
   multiflexi-cli company env export --company=ACME > acme_env.json

Using the REST API for Bulk Operations
---------------------------------------

For more complex bulk operations, use the REST API directly:

.. code-block:: bash

   # Get all companies
   curl -u admin:password \
     https://multiflexi.example.com/api/VitexSoftware/MultiFlexi/1.0.0/companies.json

   # Create a RunTemplate via API
   curl -u admin:password -X POST \
     -H "Content-Type: application/json" \
     -d '{"name":"Daily import","interval":"daily","app_id":3,"company_id":1}' \
     https://multiflexi.example.com/api/VitexSoftware/MultiFlexi/1.0.0/runtemplates.json

See :doc:`../reference/api` for the complete API reference.

See Also
--------

- :doc:`../reference/cli` — Full CLI command reference
- :doc:`../reference/api` — REST API reference
- :doc:`../integrations/ansible` — Ansible collection for infrastructure-as-code deployments
