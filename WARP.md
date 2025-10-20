# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Architecture

MultiFlexi is a comprehensive PHP-based task scheduling and automation framework designed for accounting and business system integrations. The system follows a multi-component architecture:

### Core Components

- **multiflexi-core** (external library): The central library containing core functionality
- **multiflexi-web**: Bootstrap 4 web interface with real-time monitoring
- **multiflexi-cli**: Command-line interface for management operations
- **multiflexi-executor**: Periodic application launcher
- **multiflexi-database**: Database schema and migration management
- **multiflexi-server**: Optional server component for advanced deployments
- **ansible-collection**: Ansible automation collection for infrastructure deployment

### Directory Structure

- `bin/`: Command-line executables and launchers
- `src/MultiFlexi/`: Core application source code organized by functionality:
  - `Action/`: Action handlers (GitHub, RedmineIssue)
  - `Command/`: CLI command implementations
  - `CredentialType/`: Credential type definitions (CSAS, Office365, VaultWarden)
  - `Ui/`: Web interface components and widgets
- `debian/`: Debian package build configuration
- `tests/`: Test files including application definitions
- `.devcontainer/`: Development container configuration
- `.vscode/`: VS Code workspace settings

### Application Architecture

MultiFlexi uses a layered architecture:

1. **Database Layer**: ORM with Phinx migrations managing applications, companies, run templates, and jobs
2. **Application Management**: External app definitions (.app.json files) with metadata validation
3. **Job Execution System**: Multi-environment execution with environment variable injection
4. **Credential Framework**: Extensible credential types for various system integrations
5. **Configuration Management**: Environment-based configuration with type-safe field definitions
6. **Security Layer**: Authentication, authorization, and secure credential handling

### Application Definition Schema

Applications are defined using `.app.json` files that must conform to the schema at:
https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json

Key elements:
- `executable`: Command to run
- `environment`: Configuration fields with types (string, bool, number, password, file-path)
- `requirements`: System dependencies
- `ociimage`: Docker container image for containerized execution
- `artifacts`: Output files produced by the application

## Development Commands

### Build and Test Commands

```bash
# Install dependencies
composer install

# Run all tests
make tests
vendor/bin/phpunit tests

# Run specific test configuration
vendor/bin/phpunit -c tests/configuration.xml tests/

# Static code analysis
make static-code-analysis
vendor/bin/phpstan analyse --configuration=phpstan-default.neon.dist --memory-limit=-1

# Generate static code analysis baseline
make static-code-analysis-baseline

# Code style fixing
make cs
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --diff --verbose
```

### Application Management

```bash
# Import application from JSON definition
multiflexi-cli application import-json --json path/to/app.json

# Validate application JSON schema
multiflexi-cli application validate-json --json multiflexi/app.app.json

# Export application to JSON
multiflexi-app2json

# Remove application
multiflexi-json-app-remover

# Generate job script with environment
multiflexi-job2script

# Import multiple applications
multiflexi-json2apps
```

### Scheduled Job Execution

```bash
# Run hourly jobs
make hourly

# Run daily jobs  
make daily

# Run monthly jobs
make monthly

# Run daemon
make daemon
```

### Development Environment

```bash
# Clean build artifacts
make clean

# Update autoloader
make autoload

# Install probe application for testing
make probeapp
make instprobe
```

### Package Building

```bash
# Build Debian packages
make debs

# Build and install locally
make redeb

# Create distribution packages
make debs2deb

# Build Docker images
make dimage        # Main image
make demoimage     # Demo image  
make probeimage    # Probe image

# Build multi-architecture probe image
make probeimagex
```

### Development Containers

```bash
# Run demo Docker container
make demorun

# Run main Docker container
make drun
```

## Environment Variables

MultiFlexi automatically configures environment variables for executed applications:

### AbraFlexi Integration
- `ABRAFLEXI_URL`: Server URL
- `ABRAFLEXI_LOGIN`: Username
- `ABRAFLEXI_PASSWORD`: Password
- `ABRAFLEXI_COMPANY`: Company identifier

### Pohoda Integration
- `POHODA_ICO`: Company ICO number
- `POHODA_URL`: Server URL
- `POHODA_USERNAME`: Username
- `POHODA_PASSWORD`: Password

## Code Quality Standards

The project follows PSR-12 coding standards and includes:

- English comments and messages
- PHPStan static analysis (level 7)
- PHP-CS-Fixer for code formatting
- PHPUnit for testing
- Internationalization support using `_()` functions

## Testing

Test applications are located in the `tests/` directory:
- `multiflexi_probe.multiflexi.app.json`: Probe testing application
- `sleep.multiflexi.app.json`: Simple sleep test application

To validate application definitions against the schema, use:
```bash
multiflexi-cli application validate-json --json tests/multiflexi_probe.multiflexi.app.json
```

### Application Import Behavior

MultiFlexi supports repeated application imports without database constraint violations. The import system:

1. **Identifies existing applications by UUID first, then by name as fallback**
2. **Cleanly replaces environment configurations** by deleting existing configs before inserting new ones
3. **Updates application metadata** instead of creating duplicates
4. **Handles translations** using `ON DUPLICATE KEY UPDATE`

This design allows packages to be reinstalled and application definitions to be updated seamlessly.

## Member Projects

MultiFlexi is part of a larger ecosystem of related projects:
- multiflexi-scheduler
- multiflexi-executor  
- php-vitexsoftware-multiflexi-core
- multiflexi-cli
- multiflexi-database
- multiflexi-ansible-collection

When making changes, consider impacts on these related components and ensure compatibility across the ecosystem.