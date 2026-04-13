Installing Applications
========================

**Target Audience:** Administrators
**Difficulty:** Beginner
**Prerequisites:** At least one :doc:`Company <adding-company>` created

.. contents::
   :local:
   :depth: 2

Overview
--------

**Applications** are the tools MultiFlexi executes — importers, exporters, reporters, integrations. They must first be installed on the server as Debian packages, then registered in MultiFlexi, and finally assigned to a company.

Step 1: Install the Application Package
-----------------------------------------

All available applications are in the ``repo.multiflexi.eu`` APT repository.

.. code-block:: bash

   # Search for available applications
   apt search multiflexi

   # Install a specific application
   sudo apt install multiflexi-probe

   # Install all standard applications
   sudo apt install multiflexi-all

After installation the application's ``.app.json`` file is placed in the MultiFlexi applications directory and automatically imported into the database.

Step 2: Verify the Application is Registered
----------------------------------------------

.. code-block:: bash

   multiflexi-cli application list

The application should appear in the list. If it does not, import it manually:

.. code-block:: bash

   multiflexi-cli application import-json \
     --file /usr/share/multiflexi/apps/probe.app.json

Step 3: Assign the Application to a Company
--------------------------------------------

**Via the web interface:**

1. Open the company detail page (**Companies** → select company)
2. Click **"📦 Applications"** tab
3. Find the application in the "Available" list
4. Click **"✓ Assign"** or **"Install"**

The application now appears in the company's installed applications list and you can create RunTemplates for it.

**Via CLI:**

.. code-block:: bash

   multiflexi-cli company assign-app \
     --company=ACME \
     --app=multiflexi-probe

Configuring Application Parameters
------------------------------------

Many applications have configuration fields (API URLs, file paths, behaviour flags). These are set per-company on the assignment:

1. After assigning, click the application name in the company view
2. Fill in the configuration fields shown
3. Save

.. image:: ../_static/images/screenshots/appconfigfieldseditor.png
   :alt: Application configuration fields editor
   :align: center

Importing Custom Application JSON
-----------------------------------

Applications do not have to come from a Debian package. Any valid ``.app.json`` file can be imported:

.. code-block:: bash

   # Validate first
   multiflexi-cli application validate-json --file myapp.app.json

   # Import
   multiflexi-cli application import-json --file myapp.app.json

The JSON must conform to the `application schema <https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json>`_. See :doc:`../reference/application-schema` for full details.

Updating an Application
------------------------

.. code-block:: bash

   # Update package
   sudo apt install multiflexi-probe  # re-installs latest version

   # Re-import updated JSON
   multiflexi-cli application import-json \
     --file /usr/share/multiflexi/apps/probe.app.json \
     --update

Removing an Application from a Company
----------------------------------------

Removing an application from a company does not delete existing job history.

.. code-block:: bash

   multiflexi-cli company unassign-app \
     --company=ACME \
     --app=multiflexi-probe

See Also
--------

- :doc:`creating-runtemplates` — Scheduling the installed application
- :doc:`../reference/application-schema` — Application JSON format
- :doc:`../apps_overview` — Overview of available applications
