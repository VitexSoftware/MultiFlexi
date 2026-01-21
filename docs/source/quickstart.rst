Quickstart
==========

Get MultiFlexi running and execute your first automated job in under 15 minutes.

**Target Audience:** New Users  
**Difficulty:** Beginner  
**Prerequisites:**

- Debian 12 or Ubuntu 24.04 LTS
- Root or sudo access
- Internet connection

.. contents::
   :local:
   :depth: 2

Installation (5 minutes)
------------------------

**1. Add MultiFlexi Repository**

.. code-block:: bash

   curl -sSLo /tmp/multiflexi-archive-keyring.deb https://repo.multiflexi.eu/multiflexi-archive-keyring.deb
   sudo dpkg -i /tmp/multiflexi-archive-keyring.deb
   echo "deb [signed-by=/usr/share/keyrings/repo.multiflexi.eu.gpg] https://repo.multiflexi.eu/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/multiflexi.list
   sudo apt update

**2. Install MultiFlexi with MySQL**

.. code-block:: bash

   sudo apt install multiflexi-mysql

When prompted:

- "Configure database for multiflexi?" → **Yes**
- "Password for the new user" → **Leave blank** (auto-generated)

**3. Install Probe Application (for testing)**

.. code-block:: bash

   sudo apt install multiflexi-all

Configuration (2 minutes)
--------------------------

**1. Access Web Interface**

Open your browser: ``http://<your-server-ip>/multiflexi``

**2. Create Administrator Account**

On first visit, you'll see the initial setup screen:

- **Username:** Choose your admin username
- **Password:** Create a secure password
- **Email:** Your email address
- Click **"Create Administrator"**

Your First Job (8 minutes)
---------------------------

**1. Add a Company**

Companies represent the organizations/tenants you're automating tasks for.

- Navigate to **"Companies"** in the top menu
- Click **"➕ New Company"**
- Fill in:

  - **Name:** "Demo Company"
  - **Code:** "DEMO" (unique identifier)
  - **Status:** Active

- Click **"Save"**

**2. Install an Application**

Applications are the tools MultiFlexi executes (reports, importers, integrations).

- From the company page, click **"📦 Applications"**
- Find **"MultiFlexi Probe"** (system health check tool)
- Click **"✓ Install"**

**3. Create a RunTemplate**

RunTemplates define *how* and *when* an application runs for a company.

- Click **"⚙️ Create RunTemplate"** on the Probe application
- Configure:

  - **Name:** "Hourly System Check"
  - **Interval:** "Hourly" (runs every hour at :00)
  - **Status:** Active

- Click **"Save"**

**4. Execute Your First Job**

- On the RunTemplate detail page, click **"▶️ Execute Now"**
- After ~5 seconds, you'll see:

  - **Status:** ✅ Success (green badge)
  - **Exit Code:** 0
  - **Stdout:** System health metrics

**Congratulations!** You've executed your first automated job.

What's Next?
------------

- **Learn the Data Model:** Understand Applications, Companies, RunTemplates, and Jobs (:doc:`concepts/data-model`)
- **Follow the Full Tutorial:** Build a complete integration from scratch (:doc:`tutorial-first-job`)
- **Explore Applications:** Install real-world applications (:doc:`apps_overview`)
- **Set Up Monitoring:** Integrate Zabbix or OpenTelemetry (:doc:`zabbix`)

Troubleshooting
---------------

**Web interface not loading?**
  Check Apache status: ``sudo systemctl status apache2``

**Database errors during installation?**
  Ensure MySQL is running: ``sudo systemctl status mysql``

**Probe execution failed?**
  Check logs: ``sudo journalctl -u multiflexi-executor -f``

**Need help?** See the full :doc:`troubleshooting` guide.

.. seealso::

   - :doc:`install` - Detailed installation instructions
   - :doc:`firstrun` - Initial configuration guide
   - :doc:`usage` - Complete usage documentation
