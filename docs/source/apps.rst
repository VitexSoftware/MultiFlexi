Applications
============

.. toctree::
   :maxdepth: 2

.. contents::

MultiFlexi Application Requirements
===================================

MultiFlexi can execute applications written in any programming language. The application must read its configuration from environment variables and can output results to files or monitoring systems.

How Applications Work
---------------------

MultiFlexi applications are defined using JSON files that describe:

- **Basic Information**: Name, description, author, and version
- **Execution Details**: How to run the application (command or container image)
- **Configuration Fields**: What settings users need to provide
- **Output Information**: What files or data the application produces

When you install an application, MultiFlexi reads this definition and creates configuration forms automatically. Users fill in the required information, and MultiFlexi handles running the application with the correct settings.

Application Categories
----------------------

MultiFlexi applications are organized into categories to help you find what you need:

- **Accounting**: Invoice processing, financial reporting, ERP integrations
- **Banking**: Transaction reports, bank statement processing
- **Communication**: Email reports, SMS notifications, messaging integrations
- **Database**: Data exports, backups, synchronization
- **Monitoring**: System health checks, performance metrics, alerts
- **Security**: Vulnerability scans, compliance checks, audit reports

Basic Application Setup
-----------------------

Setting up an application involves three main steps:

1. **Installation**: Install the application package (usually done by system administrator)
2. **Configuration**: Provide required settings like database connections, API keys, file paths
3. **Scheduling**: Set when and how often the application should run

.. image:: appconfigfieldseditor.png
    :alt: Configuration interface showing application settings

Configuration Field Types
--------------------------

Applications use different types of configuration fields:

- **Text Fields**: For entering names, descriptions, and simple values
- **Password Fields**: For API keys and sensitive information (hidden from view)  
- **File Paths**: For selecting files or directories on the server
- **Email Addresses**: For notification recipients and email configuration
- **URLs**: For API endpoints and web service connections
- **Numbers**: For timeouts, limits, and numeric settings
- **Checkboxes**: For yes/no options and feature toggles
- **Dropdown Lists**: For selecting from predefined options

Finding Applications
--------------------

You can find MultiFlexi applications in several ways:

**Package Manager** (for system administrators):

.. code-block:: bash

    apt search multiflexi

**Application Store**: Visit the `MultiFlexi apps page <https://www.multiflexi.eu/apps.php>`_ to browse available applications.

**GitHub Repositories**: Many applications are available as open source projects that can be packaged for your system.

.. tip::

    To install all available applications at once, ask your system administrator to install the `multiflexi-all` meta package.
Special Variables for Monitoring
---------------------------------

Some applications can send their results to monitoring systems like Zabbix. These applications use special environment variables:

- ``RESULT_FILE``: Where the application saves its output data
- ``ZABBIX_KEY``: The name used to identify this data in Zabbix monitoring

These are automatically configured by MultiFlexi when you set up monitoring integration.

MultiFlexi Probe Application
----------------------------

MultiFlexi includes a built-in test application called "Probe" that's useful for:

- **Testing MultiFlexi**: Verify that your installation is working correctly
- **System Monitoring**: Check system health and performance
- **Learning**: Understand how applications work before creating your own
- **Debugging**: Troubleshoot issues with application execution

The Probe application creates a simple JSON report showing system information and can be configured to test different types of fields and outputs.

Creating Your Own Applications
------------------------------

If you're interested in developing your own MultiFlexi applications, you'll find everything you need in the developer documentation:

- Technical application requirements
- JSON schema specifications  
- Detailed examples and templates
- Code samples in multiple languages

See the :doc:`development` section for complete technical documentation.

.. note::

    For end users, focus on configuring and using existing applications. Application development requires programming knowledge and is covered in the developer documentation.

