MultiFlexi Architecture
=======================

The MultiFlexi ecosystem is composed of several interrelated projects, each fulfilling a specific role in the overall architecture. Below is an overview of the main member projects and their relationships:

.. toctree::
  :maxdepth: 2
  :caption: Architecture Topics


Core Components
---------------

- **php-vitexsoftware-multiflexi-core**
  (https://github.com/VitexSoftware/php-vitexsoftware-multiflexi-core)

  The core PHP library providing the main business logic, data models, and shared utilities for the MultiFlexi platform. All other components depend on this package for core functionality.

- **multiflexi-database**
  (https://github.com/VitexSoftware/multiflexi-database)

  Contains database schema definitions and migration scripts for MultiFlexi. Used by the server and other components to initialize and update the database structure.

- **multiflexi-cli**
  (https://github.com/VitexSoftware/multiflexi-cli)

  Command-line interface for managing MultiFlexi resources (applications, companies, users, jobs, etc.). Relies on the core library and interacts with the database.

- **multiflexi-server**
  (https://github.com/VitexSoftware/multiflexi-server)

  The main backend server providing REST API and web UI for MultiFlexi. It orchestrates job scheduling, user management, and integrates with the core library and database.

- **multiflexi-executor**
  (https://github.com/VitexSoftware/multiflexi-executor)

  A dedicated service or agent responsible for executing jobs and tasks as scheduled by the server. Communicates with the server and may run in isolated environments.

- **multiflexi-ansible-collection**
  (https://github.com/VitexSoftware/multiflexi-ansible-collection)

  An Ansible collection providing playbooks and roles for deploying and managing MultiFlexi components in various environments.

- **multiflexi-all**
  (https://github.com/VitexSoftware/multiflexi-all)

  A meta-repository that aggregates all the above projects, providing a unified source for development, deployment, and integration.

Project Relationships
---------------------

- The **core** library is a dependency for the CLI, server, and executor.
- The **database** project provides schema and migrations for all components that require persistent storage.
- The **CLI** and **server** both interact with the database and core library, but serve different user interfaces (command-line vs. web/API).
- The **executor** is managed by the server and is responsible for running jobs in a secure and isolated manner.
- The **ansible-collection** is used to automate deployment and configuration of all components.
- The **all** meta-repo is used for orchestration, CI/CD, and as a reference for the complete MultiFlexi stack.

This modular architecture allows for flexible deployment, scaling, and maintenance of the MultiFlexi platform.

.. figure:: multiflexi-components.svg
   :align: center
   :width: 800px
   :alt: MultiFlexi Components Relationship Diagram

   MultiFlexi project relationships schema

