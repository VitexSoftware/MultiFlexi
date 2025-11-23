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
- **Advanced Credential Management**: Secure handling of authentication credentials including VaultWarden integration, banking APIs (FioBank, RaiffeisenBank, CSAS), and Office365
- **Multiple Execution Environments**: Support for native execution, Docker containers, Kubernetes pods, Azure cloud, and Podman
- **Comprehensive Logging**: Detailed execution logs with Zabbix and OpenTelemetry monitoring integration and real-time tracking
- **Multi-Interface Support**:
  
  - Responsive Bootstrap 4 web interface with real-time monitoring and charts
  - Rich command-line interface with extensive management commands
  - RESTful API with OAuth2 authentication and multiple output formats (JSON, XML, HTML, YAML)

- **Enterprise-Ready**: User authentication, API tokens, multi-tenant data isolation, and comprehensive security features

Getting Started
---------------

To get started with MultiFlexi:

1. Follow the :doc:`install` guide for installation instructions
2. Complete the :doc:`firstrun` configuration
3. Explore the :doc:`usage` section for operational guidance
4. Check the :doc:`api` documentation for programmatic integration
5. Use the :doc:`multiflexi-cli` for command-line management
6. See :doc:`ansible` for Infrastructure as Code automation

For developers and contributors, see the :doc:`development` section for technical details, architecture information, and development guidelines.

.. note::

   MultiFlexi is actively developed and regularly updated with new features and improvements.


Check out the :doc:`usage` section for further information, including
how to :ref:`installation` the project.

.. note::

   This project is under active development.

Contents
--------

.. toctree::
   :maxdepth: 2
   :caption: Contents:

   install
   docker
   firstrun
   usage
   troubleshooting
   api
   ansible
   application
   configuration
   company
   job
   runtemplate
   apps
   actions
   executors
   commandline
   zabbix
   opentelemetry
   development
   selenium-testing
   architecture
   apps_overview
   multiflexi-cli
   credential-type
   gdpr-compliance
