.. title:: MultiFlexi Documentation

Welcome to MultiFlexi's documentation!
======================================

.. image:: _static/project-logo.svg
   :alt: MultiFlexi Logo
   :align: center

**MultiFlexi** is a comprehensive PHP-based task scheduling and automation framework designed for accounting and business system integrations. It enables scheduled execution of applications and tools across multiple companies and platforms, with primary focus on systems like `AbraFlexi <https://www.abra.eu/flexi/>`_ and `Stormware Pohoda <https://www.stormware.cz/>`_.

**Version 2.3.0** introduces event-driven job triggering, an expanded credential plugin ecosystem, automatic artifact preservation for all jobs, and the new MultiFlexi TUI terminal interface.

Key Features
------------

- **Multi-Platform Integration**: Native support for AbraFlexi, Pohoda, ČSAS, Raiffeisenbank, and other business systems via an extensible credential plugin ecosystem
- **Flexible Job Scheduling**: Time-based scheduling (hourly, daily, weekly, monthly, yearly) plus event-driven triggering via the event processor daemon
- **Three-Tier Credential Management**: ``CredentialPrototype → CredentialType → Credential`` architecture for secure, company-scoped authentication. Plugins available for ERP, banking APIs, SMTP, VaultWarden/Bitwarden, and more
- **Universal Artifact Preservation**: Every job automatically stores its stdout, stderr, and result files as searchable artifacts — regardless of which actions are configured
- **Multiple Execution Environments**: Native, Docker, Podman, Kubernetes pods, and Azure Container Instances
- **Comprehensive Observability**: Zabbix LLD integration, OpenTelemetry support, live job output via WebSocket, structured logging
- **Multiple Interfaces**:

  - Web UI (`MultiFlexi <https://github.com/VitexSoftware/MultiFlexi/>`_) — Bootstrap 5, real-time dashboard
  - CLI (`multiflexi-cli <https://github.com/VitexSoftware/multiflexi-cli/>`_) — full management from the terminal
  - TUI (`multiflexi-tui <https://github.com/VitexSoftware/multiflexi-tui/>`_) — interactive terminal UI (Bubbletea/Go)
  - REST API — JSON/XML/YAML/HTML output formats, HTTP Basic + token auth
  - MCP Server (`multiflexi-mcp-server <https://github.com/VitexSoftware/multiflexi-mcp-server/>`_) — AI agent integration

- **Enterprise-Ready**: Multi-tenant data isolation, RBAC, AES-256 credential encryption, GDPR compliance tools, Ansible deployment collection

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
   project-components

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
   integrations/kubernetes

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
