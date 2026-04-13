MultiFlexi
===========

![MFB](multiflexi-social-preview.svg?raw=true)

[![ReadTheDocs](https://readthedocs.org/projects/multiflexi/badge/)](https://multiflexi.readthedocs.io/)
[![GitHub license](https://img.shields.io/github/license/VitexSoftware/MultiFlexi)](https://opensource.org/licenses/MIT)

MultiFlexi je komplexní PHP framework pro plánování úloh a automatizaci, navržený pro integraci účetních a obchodních systémů (AbraFlexi, Stormware Pohoda, aj.). Umožňuje plánované spouštění aplikací napříč více firmami, disponuje bohatým systémem přihlašovacích údajů, REST API, webovým rozhraním a nástroji pro příkazovou řádku.

Projekt je rozdělen do několika specializovaných podprojektů:

## Členské projekty

### Jádro systému
- [multiflexi-common](https://github.com/VitexSoftware/multiflexi-common) - Společná dokumentace, sdílené prostředky a Zabbix LLD skripty (tento repozitář)
- [php-vitexsoftware-multiflexi-core](https://github.com/VitexSoftware/php-vitexsoftware-multiflexi-core) - Centrální knihovna s jádrem obchodní logiky (ORM, Job, RunTemplate, Credential, systém artefaktů)
- [multiflexi-database](https://github.com/VitexSoftware/multiflexi-database) - Schéma databáze a Phinx migrace (MySQL, PostgreSQL, SQLite)
- [multiflexi-database-connection](https://github.com/VitexSoftware/multiflexi-database-connection) - Podpora PDO databázového připojení jako typ přihlašovacích údajů
- [multiflexi-server](https://github.com/VitexSoftware/multiflexi-server) - REST API backend (PHP Slim 4)
- [multiflexi-api](https://github.com/VitexSoftware/multiflexi-api) - OpenAPI specifikace a generátor serverového kódu
- [MultiFlexi](https://github.com/VitexSoftware/MultiFlexi) - Hlavní webové rozhraní (přehled, správa firem a úloh)
- [multiflexi-web](https://github.com/VitexSoftware/multiflexi-web) - Webové prostředky a frontend balíček
- [multiflexi-ui](https://github.com/VitexSoftware/multiflexi-ui) - React/TypeScript/Vite UI komponenty
- [multiflexi-cli](https://github.com/VitexSoftware/multiflexi-cli) - Příkazová řádka pro správu aplikací, firem, přihlašovacích údajů a úloh

### Služby a spouštění
- [multiflexi-scheduler](https://github.com/VitexSoftware/multiflexi-scheduler) - Systemd démon pro plánování úloh (cron)
- [multiflexi-executor](https://github.com/VitexSoftware/multiflexi-executor) - Systemd démon pro vykonávání úloh
- [multiflexi-event-processor](https://github.com/VitexSoftware/multiflexi-event-processor) - Démon pro spouštění úloh na základě událostí

### Uživatelská rozhraní
- [multiflexi-tui](https://github.com/VitexSoftware/multiflexi-tui) - Terminálové rozhraní (TUI) postavené na Charmbracelet Bubbletea
- [multiflexi-probe](https://github.com/VitexSoftware/multiflexi-probe) - Testovací a ladicí nástroj pro spouštěč úloh MultiFlexi

### Pluginy přihlašovacích údajů
- [multiflexi-abraflexi](https://github.com/VitexSoftware/multiflexi-abraflexi) - Prototyp přihlašovacích údajů pro AbraFlexi ERP
- [multiflexi-csas](https://github.com/VitexSoftware/multiflexi-csas) - Prototyp přihlašovacích údajů pro API Česká Spořitelna / ČSAS / Erste
- [multiflexi-raiffeisenbank](https://github.com/VitexSoftware/multiflexi-raiffeisenbank) - Prototyp přihlašovacích údajů pro Raiffeisenbank Premium API
- [multiflexi-mail](https://github.com/VitexSoftware/multiflexi-mail) - Podpora SMTP/e-mail přihlašovacích údajů (Symfony Mailer)
- [multiflexi-vaultwarden](https://github.com/VitexSoftware/multiflexi-vaultwarden) - Podpora přihlašovacích údajů z VaultWarden/Bitwarden
- [multiflexi-mtr](https://github.com/VitexSoftware/multiflexi-mtr) - Integrace síťové diagnostiky MTR

### Monitoring a pozorovatelnost
- [multiflexi-zabbix](https://github.com/VitexSoftware/multiflexi-zabbix) - Integrace monitorování Zabbix (LLD discovery a šablony)
- [multiflexi-zabbix-selenium](https://github.com/VitexSoftware/multiflexi-zabbix-selenium) - Integrace výsledků Mocha/Selenium testů do Zabbix

### Integrace a nasazení
- [multiflexi-ansible-collection](https://github.com/VitexSoftware/multiflexi-ansible-collection) - Ansible kolekce pro automatizované nasazení
- [multiflexi-all](https://github.com/VitexSoftware/multiflexi-all) - Meta-balíček pro kompletní instalaci

### MCP integrace
- [multiflexi-mcp-server](https://github.com/VitexSoftware/multiflexi-mcp-server) - MCP server (Model Context Protocol) pro přístup AI agentů k MultiFlexi API

### Ukázkové aplikace
- [MultiFlexi-Golang-App-Example](https://github.com/VitexSoftware/MultiFlexi-Golang-App-Example) - Ukázková MultiFlexi aplikace v jazyce Go
- [MultiFlexi-Java-App-Example](https://github.com/VitexSoftware/MultiFlexi-Java-App-Example) - Ukázková MultiFlexi aplikace v jazyce Java
- [multiflexi-node-app](https://github.com/VitexSoftware/multiflexi-node-app) - Ukázková MultiFlexi aplikace v Node.js / Express

### Dokumentace a lokalizace
- [multiflexi-doc-en](https://github.com/VitexSoftware/multiflexi-doc-en) - Anglický dokumentační balíček
- [MultiFlexi-cz](https://github.com/VitexSoftware/MultiFlexi-cz) - Česká lokalizace dokumentace MultiFlexi

## Dokumentace

Kompletní dokumentaci, návody a tutoriály naleznete na [https://multiflexi.readthedocs.io/](https://multiflexi.readthedocs.io/).

## Demo

K dispozici je [ukázková instance](https://demo.multiflexi.eu/?login=demo&password=demo) pro testování.

![demo screenshot](doc/index-1.10.4.314.png?raw=true)
