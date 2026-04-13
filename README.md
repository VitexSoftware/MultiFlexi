MultiFlexi
===========

![MFB](multiflexi-social-preview.svg?raw=true)

[![ReadTheDocs](https://readthedocs.org/projects/multiflexi/badge/)](https://multiflexi.readthedocs.io/)
[![GitHub license](https://img.shields.io/github/license/VitexSoftware/MultiFlexi)](https://opensource.org/licenses/MIT)

MultiFlexi is a comprehensive PHP-based task scheduling and automation framework designed for accounting and business system integrations (AbraFlexi, Stormware Pohoda, etc.). It enables scheduled execution of applications across multiple companies, with a rich credential system, REST API, web UI, and CLI tools.

The MultiFlexi project is divided into several specialized subprojects:

## Member Projects

### Core Ecosystem
- [multiflexi-common](https://github.com/VitexSoftware/multiflexi-common) - Common documentation, shared assets, Zabbix LLD scripts (this repository)
- [php-vitexsoftware-multiflexi-core](https://github.com/VitexSoftware/php-vitexsoftware-multiflexi-core) - Central library with core business logic (ORM, Job, RunTemplate, Credential, Artifact system)
- [multiflexi-database](https://github.com/VitexSoftware/multiflexi-database) - Database schema and Phinx migrations (MySQL, PostgreSQL, SQLite)
- [multiflexi-database-connection](https://github.com/VitexSoftware/multiflexi-database-connection) - PDO database connection credential support
- [multiflexi-server](https://github.com/VitexSoftware/multiflexi-server) - REST API backend (PHP Slim 4)
- [multiflexi-api](https://github.com/VitexSoftware/multiflexi-api) - OpenAPI specification and server code generator
- [MultiFlexi](https://github.com/VitexSoftware/MultiFlexi) - Main web interface (dashboard, company and job management)
- [multiflexi-web](https://github.com/VitexSoftware/multiflexi-web) - Web assets and frontend package
- [multiflexi-ui](https://github.com/VitexSoftware/multiflexi-ui) - React/TypeScript/Vite UI components
- [multiflexi-cli](https://github.com/VitexSoftware/multiflexi-cli) - Command-line interface for managing apps, companies, credentials, and jobs

### Services & Execution
- [multiflexi-scheduler](https://github.com/VitexSoftware/multiflexi-scheduler) - Systemd daemon for cron-based job scheduling
- [multiflexi-executor](https://github.com/VitexSoftware/multiflexi-executor) - Systemd daemon for job execution
- [multiflexi-event-processor](https://github.com/VitexSoftware/multiflexi-event-processor) - Event-driven job triggering daemon

### User Interfaces
- [multiflexi-tui](https://github.com/VitexSoftware/multiflexi-tui) - Terminal UI (TUI) frontend built with Charmbracelet Bubbletea
- [multiflexi-probe](https://github.com/VitexSoftware/multiflexi-probe) - Testing and debugging tool for the MultiFlexi task launcher

### Credential Plugins
- [multiflexi-abraflexi](https://github.com/VitexSoftware/multiflexi-abraflexi) - AbraFlexi ERP credential prototype
- [multiflexi-csas](https://github.com/VitexSoftware/multiflexi-csas) - Česká Spořitelna / ČSAS / Erste API credential prototype
- [multiflexi-raiffeisenbank](https://github.com/VitexSoftware/multiflexi-raiffeisenbank) - Raiffeisenbank Premium API credential prototype
- [multiflexi-mail](https://github.com/VitexSoftware/multiflexi-mail) - SMTP/Mail credential support (Symfony Mailer)
- [multiflexi-vaultwarden](https://github.com/VitexSoftware/multiflexi-vaultwarden) - VaultWarden/Bitwarden secrets credential support
- [multiflexi-mtr](https://github.com/VitexSoftware/multiflexi-mtr) - MTR network diagnostics integration

### Monitoring & Observability
- [multiflexi-zabbix](https://github.com/VitexSoftware/multiflexi-zabbix) - Zabbix monitoring integration (LLD discovery & templates)
- [multiflexi-zabbix-selenium](https://github.com/VitexSoftware/multiflexi-zabbix-selenium) - Mocha/Selenium test results integration into Zabbix

### Integration & Deployment
- [multiflexi-ansible-collection](https://github.com/VitexSoftware/multiflexi-ansible-collection) - Ansible collection for automated deployment
- [multiflexi-all](https://github.com/VitexSoftware/multiflexi-all) - Meta-package for full-stack installation

### MCP Integration
- [multiflexi-mcp-server](https://github.com/VitexSoftware/multiflexi-mcp-server) - Model Context Protocol (MCP) server for AI agent access to MultiFlexi API

### Example Applications
- [MultiFlexi-Golang-App-Example](https://github.com/VitexSoftware/MultiFlexi-Golang-App-Example) - Example MultiFlexi application in Go
- [MultiFlexi-Java-App-Example](https://github.com/VitexSoftware/MultiFlexi-Java-App-Example) - Example MultiFlexi application in Java
- [multiflexi-node-app](https://github.com/VitexSoftware/multiflexi-node-app) - Example MultiFlexi application in Node.js / Express

### Documentation & Localization
- [multiflexi-doc-en](https://github.com/VitexSoftware/multiflexi-doc-en) - English documentation package
- [MultiFlexi-cz](https://github.com/VitexSoftware/MultiFlexi-cz) - Czech localization for MultiFlexi documentation

## Documentation

See [https://multiflexi.readthedocs.io/](https://multiflexi.readthedocs.io/) for complete documentation, integration guides, and tutorials.

## Demo

A [demo instance](https://demo.multiflexi.eu/?login=demo&password=demo) is available.

![demo screenshot](doc/index-1.10.4.314.png?raw=true)
