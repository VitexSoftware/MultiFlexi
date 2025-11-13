MultiFlexi
===========

![MFB](multiflexi-social-preview.svg?raw=true)

[![ReadTheDocs](https://readthedocs.org/projects/multiflexi/badge/)](https://multiflexi.readthedocs.io/)
[![wakatime](https://wakatime.com/badge/user/5abba9ca-813e-43ac-9b5f-b1cfdf3dc1c7/project/28a38241-3585-4ce7-b365-d7341c69e635.svg)](https://wakatime.com/badge/user/5abba9ca-813e-43ac-9b5f-b1cfdf3dc1c7/project/28a38241-3585-4ce7-b365-d7341c69e635)
[![CodeFactor](https://www.codefactor.io/repository/github/vitexsoftware/multiflexi/badge)](https://www.codefactor.io/repository/github/vitexsoftware/multiflexi)
[![GitHub license](https://img.shields.io/github/license/VitexSoftware/MultiFlexi)](https://opensource.org/licenses/MIT)
[![GitHub release](https://img.shields.io/github/v/release/VitexSoftware/MultiFlexi)](https://github.com/VitexSoftware/MultiFlexi/releases)

MultiFlexi is a comprehensive PHP-based task scheduling and automation framework designed for accounting and business system integrations. It enables scheduled execution of applications and tools across multiple companies and platforms, with primary focus on systems like AbraFlexi and Pohoda.

**Version 1.32.0** introduces comprehensive security enhancements as part of GDPR Phase 3 compliance, including security audit logging, data encryption, API rate limiting, and IP whitelisting.

## Key Features

- **Multi-Platform Integration**: Native support for AbraFlexi, Pohoda, and other business systems
- **Flexible Job Scheduling**: Automated task execution with customizable intervals and dependencies
- **Advanced Credential Management**: Secure handling of authentication credentials with extensible credential types
- **Multiple Execution Environments**: Support for native execution, containerized deployments, and cloud environments
- **Comprehensive Logging**: Detailed execution logs with Zabbix and OpenTelemetry monitoring integration
- **Multi-Interface Support**:
  - Responsive Bootstrap 4 web interface with real-time monitoring
  - Rich command-line interface with extensive management commands
  - RESTful API with OAuth2 authentication and multiple output formats
- **Enterprise-Ready**: User authentication, API tokens, data isolation, and comprehensive monitoring (Zabbix + OpenTelemetry)
- **Privacy & GDPR Compliance**: Comprehensive consent management, self-hosted analytics support, and European privacy standards
- **Security Features**: AES-256 data encryption, comprehensive audit logging, API rate limiting, IP whitelisting, and advanced security monitoring

## Architecture

MultiFlexi features a layered architecture with:

- **Database Layer**: ORM with migrations managing applications, companies, run templates, and jobs
- **Application Management**: External app definitions, metadata validation, and lifecycle management
- **Job Execution System**: Multi-environment execution with environment variable injection
- **Credential Framework**: Extensible credential types for various system integrations
- **Configuration Management**: Environment-based configuration with type-safe field definitions
- **Security Layer**: Authentication, authorization, secure credential handling, data encryption, audit logging, rate limiting, and IP access control

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

**Privacy & Analytics:**
- `ENABLE_GOOGLE_ANALYTICS` - Enable/disable Google Analytics (default: false for European self-hosting)

**Security Configuration (GDPR Phase 3):**
- `SECURITY_AUDIT_ENABLED` - Enable comprehensive security event logging (default: true)
- `DATA_ENCRYPTION_ENABLED` - Enable AES-256 data encryption for sensitive information (default: true)
- `RATE_LIMITING_ENABLED` - Enable API rate limiting protection (default: true)
- `IP_WHITELIST_ENABLED` - Enable IP whitelisting for administrative access (default: false)
- `ENCRYPTION_MASTER_KEY` - Master key for data encryption at rest (automatically generated during installation)

**Encryption Configuration:**

MultiFlexi uses AES-256-GCM encryption to protect sensitive credentials and personal data. The encryption system requires a master key configured via `ENCRYPTION_MASTER_KEY` in `/etc/multiflexi/multiflexi.env`.

- **Automatic Setup**: Master key is automatically generated during package installation
- **Manual Configuration**: `openssl rand -base64 32` to generate a new key
- **Status Check**: Use `multiflexi-cli appstatus` or `multiflexi-cli encryption status`
- **Key Management**: Encryption keys are wrapped with the master key and stored in the database

See [INSTALL.md](INSTALL.md) for detailed encryption configuration and security notes.

**Monitoring Configuration:**

- `ZABBIX_SERVER` - Zabbix server address for infrastructure monitoring
- `ZABBIX_HOST` - Hostname for Zabbix metrics
- `OTEL_ENABLED` - Enable OpenTelemetry metrics export (default: false)
- `OTEL_SERVICE_NAME` - Service name for OpenTelemetry (default: multiflexi)
- `OTEL_EXPORTER_OTLP_ENDPOINT` - OTLP collector endpoint (default: http://localhost:4318)
- `OTEL_EXPORTER_OTLP_PROTOCOL` - Protocol for OTLP (http/json or grpc)

**Custom Variables:** Individual module configurations per company with extensible variable definitions.

## Monitoring & Observability

MultiFlexi provides comprehensive monitoring through two complementary systems:

### Zabbix Integration

Real-time infrastructure monitoring with:
- Job execution tracking and phase monitoring
- Application and company metrics
- Automatic alert generation
- LLD (Low-Level Discovery) support for dynamic monitoring

Configuration:
```bash
ZABBIX_SERVER=zabbix.example.com
ZABBIX_HOST=multiflexi-server
```

See [Zabbix documentation](https://multiflexi.readthedocs.io/en/latest/zabbix.html) for detailed setup.

### OpenTelemetry Integration

Modern observability with vendor-neutral metrics export to:
- Prometheus + Grafana
- OpenTelemetry Collector
- Cloud-native monitoring stacks

**Available Metrics:**
- `multiflexi.jobs.total` - Total job executions
- `multiflexi.jobs.success` - Successful jobs
- `multiflexi.jobs.failed` - Failed jobs
- `multiflexi.job.duration` - Job execution time
- `multiflexi.jobs.running` - Currently running jobs
- `multiflexi.applications.total` - Application count
- `multiflexi.companies.total` - Company count

Configuration:
```bash
OTEL_ENABLED=true
OTEL_SERVICE_NAME=multiflexi
OTEL_EXPORTER_OTLP_ENDPOINT=http://otel-collector:4318
OTEL_EXPORTER_OTLP_PROTOCOL=http/json
```

Test your configuration:
```bash
multiflexi-cli telemetry:test
```

See [OpenTelemetry documentation](https://multiflexi.readthedocs.io/en/latest/opentelemetry.html) for deployment examples, Grafana dashboards, and complete integration guide.

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
- `multiflexi-probe` - system monitoring and health check probe for MultiFlexi functionality testing

multiflexi-cli
--------------

The MultiFlexi CLI provides comprehensive management capabilities with support for applications, companies, jobs, run templates, and more.

Usage: multiflexi-cli <command> [action] [options]

Key commands: application, company, job, runtemplate, version

**Application management:**
- `multiflexi-cli application validate-json --file app.json` - validate application JSON against schema
- `multiflexi-cli application import-json --file app.json` - import application from JSON
- `multiflexi-cli application list` - list all applications

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
