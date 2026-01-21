.. title:: MultiFlexi Documentation

Welcome to MultiFlexi's documentation!
======================================

.. image:: ../../src/images/project-logo.svg
   :alt: MultiFlexi Logo
   :align: center

**MultiFlexi** is a comprehensive PHP-based task scheduling and automation framework designed for accounting and business system integrations. It enables scheduled execution of applications and tools across multiple companies and platforms, with primary focus on systems like `AbraFlexi <https://www.abra.eu/flexi/>`_ and `Stormware Pohoda <https://www.stormware.cz/>`_.

**Version 1.29.0** brings enhanced monitoring capabilities with the new MultiFlexi Probe application for system health checks and monitoring.

Key Features
------------

- **Multi-Platform Integration**: Native support for AbraFlexi, Pohoda, and other business systems with extensible credential types
- **Flexible Job Scheduling**: Automated task execution with customizable intervals (hourly, daily, weekly, monthly, yearly) and dependencies
- **Three-Tier Credential Management**: Sophisticated credential system with CredentialPrototype → CredentialType → Credential architecture for secure, reusable authentication across companies and applications. Includes VaultWarden integration, banking APIs (FioBank, RaiffeisenBank, CSAS), and Office365 support
- **Multiple Execution Environments**: Support for native execution, Docker containers, Kubernetes pods, Azure cloud, and Podman
- **Comprehensive Logging**: Detailed execution logs with Zabbix and OpenTelemetry monitoring integration and real-time tracking
- **Multi-Interface Support**:
  
  - Responsive Bootstrap 4 web interface with real-time monitoring and charts
  - Rich command-line interface with extensive management commands
  - RESTful API with OAuth2 authentication and multiple output formats (JSON, XML, HTML, YAML)

- **Enterprise-Ready**: User authentication, API tokens, multi-tenant data isolation, and comprehensive security features

Getting Started
---------------

New to MultiFlexi? Start here:

1. **:doc:`quickstart`** - Get up and running in 15 minutes
2. **:doc:`install`** - Detailed installation instructions
3. **:doc:`tutorial-first-job`** - Complete end-to-end tutorial

Once you're comfortable with the basics, explore :doc:`concepts/system-overview` to understand MultiFlexi's architecture.

.. note::

   MultiFlexi is actively developed and regularly updated with new features and improvements.


Documentation Structure
-----------------------

This documentation is organized into several sections:

- **Getting Started**: Quickstart and installation guides for new users
- **Core Concepts**: Understanding MultiFlexi's architecture and design
- **How-To Guides**: Task-oriented instructions for common operations
- **Integration Guides**: Connect MultiFlexi with external systems
- **Reference**: Technical specifications, API docs, and CLI commands
- **System Administration**: Deployment, maintenance, and operations
- **Development**: Contributing to MultiFlexi and building applications

Contents
--------

.. toctree::
   :maxdepth: 2
   :caption: Getting Started

   quickstart
   install
   firstrun
   tutorial-first-job

.. toctree::
   :maxdepth: 2
   :caption: Core Concepts

   concepts/system-overview
   concepts/data-model
   concepts/job-lifecycle
   concepts/credential-management
   concepts/execution-architecture

.. toctree::
   :maxdepth: 2
   :caption: How-To Guides

   howto/adding-company
   howto/installing-applications
   howto/creating-runtemplates
   howto/scheduling-jobs
   howto/assigning-credentials
   howto/debugging-failed-jobs
   howto/bulk-operations

.. toctree::
   :maxdepth: 2
   :caption: Integration Guides

   integrations/zabbix
   integrations/opentelemetry
   integrations/abraflexi
   integrations/pohoda
   integrations/ansible

.. toctree::
   :maxdepth: 2
   :caption: Reference

   reference/api
   reference/cli
   reference/configuration
   reference/application-schema
   reference/executors
   reference/actions

.. toctree::
   :maxdepth: 2
   :caption: System Administration

   administration/docker
   administration/systemd-services
   administration/database-maintenance
   administration/backup-recovery
   administration/upgrading

.. toctree::
   :maxdepth: 2
   :caption: Development & Contributing

   development/architecture
   development/project-structure
   development/application-development
   development/testing
   development/contributing

.. toctree::
   :maxdepth: 2
   :caption: Additional Topics

   apps_overview
   gdpr-compliance
   troubleshooting
   usage

.. toctree::
   :maxdepth: 1
   :caption: Legacy Pages (Being Migrated)

   application
   company
   job
   runtemplate
   credential-type
   selenium-testing
   apps_development
   confienv

.. note::

   **Documentation Restructuring:** This documentation has been reorganized following industry best practices. See ``docs/DOCUMENTATION_RESTRUCTURING.md`` for details.
