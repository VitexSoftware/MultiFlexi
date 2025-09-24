Manage MultiFlexi using Ansible
===============================

The **vitexus.multiflexi** Ansible Collection provides comprehensive automation capabilities for managing MultiFlexi servers and their components. This collection enables Infrastructure as Code (IaC) practices for MultiFlexi deployments, allowing you to manage users, companies, applications, jobs, and configurations programmatically.

Installation
------------

Install the collection from Ansible Galaxy:

.. code-block:: bash

   ansible-galaxy collection install vitexus.multiflexi

Or add it to your ``requirements.yml``:

.. code-block:: yaml

   collections:
     - name: vitexus.multiflexi
       version: ">=1.0.0"

Then install with:

.. code-block:: bash

   ansible-galaxy collection install -r requirements.yml

Prerequisites
-------------

Before using the Ansible collection, ensure:

1. **MultiFlexi Server**: A running MultiFlexi installation (see :doc:`install`)
2. **multiflexi-cli**: The command-line interface must be available in PATH (see :doc:`multiflexi-cli`)
3. **Dependencies**: Install ``python3-mysql.connector`` on target machines
4. **Permissions**: Appropriate access rights for the Ansible user

multiflexi-cli Installation
---------------------------

The ``multiflexi-cli`` tool is the backbone of all Ansible module operations in this collection. It must be installed on target hosts (typically the MultiFlexi server) and available in ``PATH``.

You can install it from either:

* **Stable (production) repository**: ``https://repo.multiflexi.eu/`` – recommended for production deployments.
* **Testing (nightly) repository**: ``https://repo.vitexsoftware.com/`` – provides the latest features and fixes; may be unstable.

Manual Repository Setup (Debian / Ubuntu)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Add the stable repository:

.. code-block:: bash

  echo 'deb [trusted=yes] https://repo.multiflexi.eu/ stable main' | sudo tee /etc/apt/sources.list.d/multiflexi.list
  sudo apt update
  sudo apt install multiflexi-cli

Add (or switch to) the testing/nightly repository:

.. code-block:: bash

  echo 'deb [trusted=yes] https://repo.vitexsoftware.com/ testing main' | sudo tee /etc/apt/sources.list.d/multiflexi-testing.list
  sudo apt update
  sudo apt install multiflexi-cli

(If both are present, pin versions using APT preferences as needed.)

Automated Repository Setup via Ansible Role
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

You can automate adding the testing repository using the Galaxy role ``Vitexus.vitex_software_repo_role``:

.. code-block:: bash

  ansible-galaxy role install Vitexus.vitex_software_repo_role

Example playbook to enable testing repo and install the CLI:

.. code-block:: yaml

  - name: Prepare MultiFlexi host with testing repo
    hosts: multiflexi_servers
    become: true
    roles:
     - role: Vitexus.vitex_software_repo_role
      vars:
        vitex_repo_channel: testing   # or 'stable'
    tasks:
     - name: Install multiflexi-cli
      apt:
        name: multiflexi-cli
        state: present
        update_cache: true

Validation:

.. code-block:: bash

  multiflexi-cli --version

Ensure the reported version meets the requirements of this collection (typically MultiFlexi >= 2.0.0).

Architecture Overview
---------------------

The collection follows standard Ansible practices with:

- **Roles**: Server installation and configuration management
- **Modules**: Entity management (users, companies, applications, etc.)
- **CLI Integration**: All operations use :doc:`multiflexi-cli` for consistency
- **Idempotency**: Safe, repeatable automation without side effects

Available Components
--------------------

Roles
^^^^^

**multiflexi_server**
  Complete MultiFlexi server installation and configuration for Debian/Ubuntu systems.

**run**
  Execution and runtime management role.

**demo**  
  Creates sample data and configurations for testing and demonstration purposes.

Modules
^^^^^^^

**Core Entity Management:**

- ``user`` - Manage MultiFlexi users
- ``company`` - Manage companies and organizations  
- ``company_info`` - Retrieve company information
- ``application`` - Manage applications and tools
- ``job`` - Schedule and manage execution jobs
- ``runtemplate`` - Configure execution templates
- ``topic`` - Manage categorization topics

**Authentication & Security:**

- ``credential`` - Manage authentication credentials
- ``credential_type`` - Define credential types

**System Information:**

- ``multiflexi_info`` - Gather system status and facts

Quick Start Guide
------------------

1. **Install MultiFlexi Server**

.. code-block:: yaml

   - hosts: multiflexi_servers
     become: true
     roles:
       - vitexus.multiflexi.multiflexi_server
     vars:
       multiflexi_server_db_type: mysql
       multiflexi_server_webserver_type: apache

2. **Create a Company**

.. code-block:: yaml

   - name: Create demo company
     vitexus.multiflexi.company:
       name: "Demo Company Ltd."
       slug: "DEMO"
       ic: "12345678"
       email: "demo@example.com"
       state: present

3. **Create a User**

.. code-block:: yaml

   - name: Create MultiFlexi user
     vitexus.multiflexi.user:
       state: present
       login: "admin"
       email: "admin@example.com"
       firstname: "System"
       lastname: "Administrator"
       password: "secure_password"
       enabled: true

4. **Register an Application**

.. code-block:: yaml

   - name: Register application
     vitexus.multiflexi.application:
       state: present
       name: "System Monitor"
       executable: "/usr/bin/htop"
       description: "System monitoring tool"
       uuid: "78fa718c-7ca2-4a38-840e-8e5f0db06432"

5. **Create Execution Template**

.. code-block:: yaml

   - name: Create run template
     vitexus.multiflexi.runtemplate:
       state: present
       name: "Daily System Check"
       app_uuid: "78fa718c-7ca2-4a38-840e-8e5f0db06432"
       company: "DEMO"
       active: true
       interv: "@daily"

Example Playbooks
-----------------

**Complete MultiFlexi Setup**

.. code-block:: yaml

   ---
   - name: Deploy MultiFlexi Infrastructure
     hosts: multiflexi_servers
     become: true
     vars:
       companies:
         - name: "Acme Corporation"
           slug: "ACME"
           ic: "87654321"
         - name: "Beta Industries"  
           slug: "BETA"
           ic: "11223344"
       
       users:
         - login: "john.doe"
           email: "john@acme.corp"
           firstname: "John"
           lastname: "Doe"
         - login: "jane.smith"
           email: "jane@beta.industries" 
           firstname: "Jane"
           lastname: "Smith"

     tasks:
       - name: Install MultiFlexi server
         include_role:
           name: vitexus.multiflexi.multiflexi_server

       - name: Create companies
         vitexus.multiflexi.company:
           name: "{{ item.name }}"
           slug: "{{ item.slug }}"
           ic: "{{ item.ic }}"
           state: present
         loop: "{{ companies }}"

       - name: Create users
         vitexus.multiflexi.user:
           login: "{{ item.login }}"
           email: "{{ item.email }}"
           firstname: "{{ item.firstname }}"
           lastname: "{{ item.lastname }}"
           password: "{{ default_password | default('change_me') }}"
           enabled: true
           state: present
         loop: "{{ users }}"

**Application Deployment**

.. code-block:: yaml

   - name: Deploy business applications
     hosts: multiflexi_servers
     tasks:
       - name: Register AbraFlexi connector
         vitexus.multiflexi.application:
           name: "AbraFlexi Sync"
           executable: "/opt/abraflexi/sync"
           description: "Synchronizes data with AbraFlexi"
           uuid: "a1b2c3d4-e5f6-7890-abcd-ef1234567890"
           state: present

       - name: Create sync template
         vitexus.multiflexi.runtemplate:
           name: "Hourly AbraFlexi Sync"
           app_uuid: "a1b2c3d4-e5f6-7890-abcd-ef1234567890"
           company: "ACME"
           active: true
           interv: "0 * * * *"  # Every hour
           state: present

       - name: Schedule immediate sync job
         vitexus.multiflexi.job:
           runtemplate_id: "{{ sync_template.runtemplate.id }}"
           scheduled: "now"
           executor: "native"
           state: present

Best Practices
--------------

**Security**
- Use Ansible Vault for sensitive data like passwords
- Implement proper user access controls
- Regular credential rotation

**Configuration Management**
- Use group_vars and host_vars for environment-specific settings
- Template configuration files for different environments
- Version control your playbooks and inventories

**Monitoring and Maintenance**
- Regularly check system status with ``multiflexi_info`` module
- Implement automated backup procedures
- Monitor job execution and failure rates

**Example Vault Usage:**

.. code-block:: yaml

   # group_vars/multiflexi_servers/vault.yml (encrypted)
   vault_multiflexi_admin_password: "super_secure_password"
   vault_database_password: "db_secure_password"

   # group_vars/multiflexi_servers/main.yml
   multiflexi_admin_password: "{{ vault_multiflexi_admin_password }}"
   database_password: "{{ vault_database_password }}"

Advanced Usage
--------------

**Custom Credential Types**

.. code-block:: yaml

   - name: Create custom credential type
     vitexus.multiflexi.credential_type:
       name: "Banking API"
       description: "Credentials for banking system integration"
       fields:
         - name: "api_key"
           type: "string"
           required: true
         - name: "endpoint_url"
           type: "url"
           required: true
       state: present

**Dynamic Inventory Integration**

Use the collection modules within dynamic inventory scripts to automatically discover MultiFlexi infrastructure.

**CI/CD Pipeline Integration**

.. code-block:: yaml

   - name: Validate MultiFlexi configuration
     vitexus.multiflexi.multiflexi_info:
     register: system_info

   - name: Check system health
     assert:
       that:
         - system_info.multiflexi_status == "running"
         - system_info.multiflexi_companies | int > 0
       msg: "MultiFlexi system not healthy"

Troubleshooting
---------------

**Common Issues:**

1. **Authentication Errors**: Verify multiflexi-cli access and permissions
2. **Module Not Found**: Ensure collection is properly installed
3. **CLI Command Failures**: Check multiflexi-cli version compatibility
4. **Permission Denied**: Verify sudo access and file permissions

**Debug Mode:**
Enable verbose output for troubleshooting:

.. code-block:: bash

   ansible-playbook -vv your-playbook.yml

This will show all CLI commands being executed and their output.

**Version Compatibility:**
- Ansible: >= 2.15.0  
- MultiFlexi: >= 2.0.0
- Python: >= 3.9

Contributing
------------

The Ansible collection is open source and welcomes contributions:

- **Repository**: https://github.com/VitexSoftware/multiflexi-ansible-collection
- **Issues**: https://github.com/VitexSoftware/multiflexi-ansible-collection/issues
- **Galaxy**: https://galaxy.ansible.com/vitexus/multiflexi

Support
-------

For support and questions:

- MultiFlexi Documentation: https://multiflexi.eu/docs/
- Ansible Collection Issues: GitHub repository
- Community: MultiFlexi user forums and discussions

License
-------

The Ansible collection is licensed under MIT License, same as MultiFlexi core.