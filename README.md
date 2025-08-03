MultiFlexi
===========

![MFB](multiflexi-social-preview.svg?raw=true)

[![ReadTheDocs](https://readthedocs.org/projects/multiflexi/badge/)](https://multiflexi.readthedocs.io/)
[![wakatime](https://wakatime.com/badge/user/5abba9ca-813e-43ac-9b5f-b1cfdf3dc1c7/project/28a38241-3585-4ce7-b365-d7341c69e635.svg)](https://wakatime.com/badge/user/5abba9ca-813e-43ac-9b5f-b1cfdf3dc1c7/project/28a38241-3585-4ce7-b365-d7341c69e635)
[![CodeFactor](https://www.codefactor.io/repository/github/vitexsoftware/multiflexi/badge)](https://www.codefactor.io/repository/github/vitexsoftware/multiflexi)
[![GitHub license](https://img.shields.io/github/license/VitexSoftware/MultiFlexi)](https://opensource.org/licenses/MIT)
[![GitHub release](https://img.shields.io/github/v/release/VitexSoftware/MultiFlexi)](https://github.com/VitexSoftware/MultiFlexi/releases)

MultiFlexi is a comprehensive PHP-based task scheduling and automation framework designed for accounting and business system integrations. It enables scheduled execution of applications and tools across multiple companies and platforms, with primary focus on systems like AbraFlexi and Pohoda.

## Key Features

- **Multi-Platform Integration**: Native support for AbraFlexi, Pohoda, and other business systems
- **Flexible Job Scheduling**: Automated task execution with customizable intervals and dependencies
- **Advanced Credential Management**: Secure handling of authentication credentials with extensible credential types
- **Multiple Execution Environments**: Support for native execution, containerized deployments, and cloud environments
- **Comprehensive Logging**: Detailed execution logs with system integration and monitoring capabilities
- **Multi-Interface Support**:
  - Responsive Bootstrap 4 web interface with real-time monitoring
  - Rich command-line interface with extensive management commands
  - RESTful API with OAuth2 authentication and multiple output formats
- **Enterprise-Ready**: User authentication, API tokens, data isolation, and Zabbix monitoring integration

## Architecture

MultiFlexi features a layered architecture with:

- **Database Layer**: ORM with migrations managing applications, companies, run templates, and jobs
- **Application Management**: External app definitions, metadata validation, and lifecycle management
- **Job Execution System**: Multi-environment execution with environment variable injection
- **Credential Framework**: Extensible credential types for various system integrations
- **Configuration Management**: Environment-based configuration with type-safe field definitions
- **Security Layer**: Authentication, authorization, and secure credential handling

# Member Projects

- <https://github.com/VitexSoftware/multiflexi-scheduler>
- <https://github.com/VitexSoftware/multiflexi-executor>
- <https://github.com/VitexSoftware/php-vitexsoftware-multiflexi-core>
- <https://github.com/VitexSoftware/multiflexi-cli>
- <https://github.com/VitexSoftware/multiflexi-database>
- <https://github.com/VitexSoftware/multiflexi-ansible-collection>

## Environment Variables

MultiFlexi automatically configures environment variables for executed applications:

**AbraFlexi Integration:**
- `ABRAFLEXI_URL`
- `ABRAFLEXI_LOGIN`
- `ABRAFLEXI_PASSWORD`
- `ABRAFLEXI_COMPANY`

**Pohoda Integration:**
- `POHODA_ICO`
- `POHODA_URL`
- `POHODA_USERNAME`
- `POHODA_PASSWORD`

**Custom Variables:** Individual module configurations per company with extensible variable definitions.

See the <https://multiflexi.readthedocs.io/> for complete documentation


Demo
----

A [demo instance](https://demo.multiflexi.eu/?login=demo&password=demo) is available

![demo screenshot](doc/index-1.10.4.314.png?raw=true)

Installation
------------

Debian packages are available. For more information about installation, see the [installation documentation](INSTALL.md)

Command Line Usage
==================

In the `bin` directory, you will find the following launchers for various functions:

- `multiflexi-app2json` - exports the application definition to a file
- `multiflexi-executor` - periodic application launcher
- `multiflexi-job2script` - generates a script with environment settings and a command to run a job by its number
- `multiflexi-json-app-remover` - removes an application from MultiFlexi based on a JSON definition
- `multiflexi-json2app` - loads application definitions from a file
- `multiflexi-probe` - helper tool for testing application functionality

multiflexi-cli
--------------

Usage: multiflexi-cli <command> [argument] [id]

Commands: version, list, remove

Example:

```
$ multiflexi-cli remove app 15
02/20/2024 23:48:51 🌼 ❲MultiFlexi cli⦒(15)AbraFlexi send@MultiFlexi\Application❳ Unassigned from 3 companies
02/20/2024 23:48:53 🌼 ❲MultiFlexi cli⦒(15)AbraFlexi send@MultiFlexi\Application❳ 2 RunTemplate removal
02/20/2024 23:48:56 🌼 ❲MultiFlexi cli⦒(15)AbraFlexi send@MultiFlexi\Application❳ 2 Config fields removed
02/20/2024 23:48:57 🌼 ❲MultiFlexi cli⦒(15)AbraFlexi send@MultiFlexi\Application❳ 881 Jobs removed
Done.
```

Plugins
-------

Any executable script or binary can be used as a plugin.

![App Listing page](docs/source/applisting.png?raw=true)

You can find the complete list on the [project page](https://www.multiflexi.eu/apps.php).

See the full list of ready-to-run applications within the MultiFlexi platform on the [application list page](https://www.multiflexi.eu/apps.php).

[![MultiFlexi App](https://github.com/VitexSoftware/MultiFlexi/blob/main/doc/multiflexi-app.svg)](https://www.multiflexi.eu/apps.php)
