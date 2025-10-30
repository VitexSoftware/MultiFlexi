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

### Data Retention Management (GDPR Phase 4)

```bash
# Calculate retention expiration dates
multiflexi-cli retention:cleanup calculate

# Run scheduled cleanup (with dry-run option)
multiflexi-cli retention:cleanup cleanup --dry-run

# Process grace period cleanup (final deletions)
multiflexi-cli retention:cleanup grace-period

# Clean up expired archives
multiflexi-cli retention:cleanup archive-cleanup --days=2555

# Generate compliance reports
multiflexi-cli retention:cleanup report --format=json --output=report.json

# Show retention status
multiflexi-cli retention:cleanup status

# Validate retention policies
multiflexi-cli application validate-json --json multiflexi/retention-policy.json
```
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

### GDPR Implementation Phases

MultiFlexi implements GDPR compliance in phases:

**Phase 1**: User consent and privacy management
**Phase 2**: Data export and user rights (Article 15, 20)
**Phase 3**: Security enhancements and access controls
**Phase 4**: Data retention and automated deletion policies (Article 5(1)(e), 17)

### Security Configuration (GDPR Phase 3)
- `SECURITY_AUDIT_ENABLED`: Enable comprehensive security event logging (default: true)
- `DATA_ENCRYPTION_ENABLED`: Enable AES-256 data encryption (default: true)
- `RATE_LIMITING_ENABLED`: Enable API rate limiting (default: true)
- `IP_WHITELIST_ENABLED`: Enable IP whitelisting for admin access (default: false)
- `ENCRYPTION_MASTER_KEY`: Master encryption key (required for data encryption)

### Data Retention Configuration (GDPR Phase 4)
- `DATA_RETENTION_ENABLED`: Enable automated data retention and cleanup (default: true)
- `RETENTION_GRACE_PERIOD_DAYS`: Default grace period before final deletion (default: 30)
- `RETENTION_ARCHIVE_PATH`: Path for archived data storage (default: /var/lib/multiflexi/archives)
- `RETENTION_CLEANUP_SCHEDULE`: Cron expression for automated cleanup (default: 0 2 * * *)

## User Interface Design

### Dashboard Widgets

The dashboard (`src/dashboard.php`) is built using modular, reusable widget components located in `src/MultiFlexi/Ui/`:

**Widget Components:**
- `DashboardMetricsCards` - Main metrics cards (Total Jobs, Active Applications, Active Companies, Run Templates)
- `DashboardStatusCards` - Job status cards (Successful, Failed, Running, Today's Jobs)
- `DashboardJobsByAppChart` - Bar chart showing top 10 applications by job count
- `DashboardJobsByCompanyChart` - Pie chart showing top 10 companies by job count
- `DashboardTimelineChart` - Multi-line graph of job execution timeline (last 7 days)
- `DashboardIntervalChart` - Bar chart showing run templates by interval (hourly, daily, etc.)
- `DashboardRecentJobsTable` - Table of last 20 jobs with clickable links and emoticons
- `DashboardStyles` - Static CSS styles for dashboard components

**Design Pattern:**
Each widget is a self-contained class that:
1. Extends appropriate Ease Framework base class (`\Ease\TWB4\Row`, `\Ease\Html\DivTag`, etc.)
2. Fetches its own data in the constructor
3. Handles its own rendering and error states
4. Can be instantiated with a simple `new WidgetName()` call

**Recent Jobs Table Features:**
- Clickable links with emoticons:
  - 📦 Applications → `app.php?id=X`
  - 🏢 Companies → `company.php?id=X`
  - ⚙️ RunTemplates → `runtemplate.php?id=X`
- Status badges with visual indicators (Running, Pending, Success, Failed)
- Formatted timestamps

### Activation Wizard

The Activation Wizard (`src/activation-wizard.php`) provides a guided 7-step process for activating applications in companies:

**Step 1: Choose Company** - Select the company where the application will be activated
**Step 2: Choose Application** - Select an enabled application from the catalog
**Step 3: Create RunTemplate** - Define the RunTemplate name and execution interval
**Step 4: Assign Credentials** - Bind required credentials (mServer, SQLServer, etc.) to the RunTemplate
**Step 5: Configure** - Set application-specific configuration parameters
  - Fields populated from credentials are displayed as disabled with credential type logos
  - Values are automatically inherited from assigned credentials
**Step 6: Actions** - Configure success and failure actions (Zabbix, WebHook, Custom Commands, etc.)
**Step 7: Summary** - Review complete configuration with action buttons:
  - ⚗️ View RunTemplate - Navigate to the RunTemplate detail page
  - 📅 Schedule - Launch the scheduling interface for this RunTemplate
  - 📋 All RunTemplates - View the complete list of RunTemplates
  - 🌟 New Activation - Start a new activation wizard

**Key Features:**
- Session-based wizard state management
- Credential-aware configuration with visual indicators
- Real-time validation and error handling
- Support for credential binding and credential type logos
- Prevents duplicate credential bindings
- Automatic cleanup of wizard session data on completion

**Navigation:**
- Access from main menu: "🧙 Activation Wizard"
- Access from RunTemplates page: "Activation Wizard" button
- Access from Company panel: "🧙🏽‍♂️ Launch wizard" button
- Direct URL: `activation-wizard.php?reset=1` (starts fresh)

**Implementation:**
- Component: `src/MultiFlexi/Ui/ActivationWizard.php`
- Handler: `src/activation-wizard.php`
- Related: Credential assignment via `MultiFlexi\RunTplCreds`

### Responsive Menu Implementation

The main navigation menu (`src/MultiFlexi/Ui/MainMenu.php`) implements a responsive Bootstrap 4 navbar with the following design:

**Desktop Layout:**
- Full horizontal menu bar with dropdown menus
- Search form visible inline on the right side
- Language selector on far right

**Mobile Layout:**
- Hamburger toggle button for collapsible menu
- Search form remains visible outside the collapsible section
- Compact layout with 100px search input width
- All menu items collapse into hamburger menu

**Search Form Placement:**
The search form is intentionally placed outside the navbar collapse div using `$nav->addItem()` rather than `$nav->addMenuItem()`. This ensures the search functionality remains accessible on all screen sizes without being hidden in the collapsed mobile menu.

**Styling Considerations:**
- Form uses `flex-wrap: nowrap` to prevent element wrapping
- Compact spacing (`mr-1`, `ml-1`) for tight mobile layout
- Fixed input width (100px) optimized for mobile screens

## Code Quality Standards

The project follows PSR-12 coding standards and includes:

- English comments and messages
- PHPStan static analysis (level 7)
- PHP-CS-Fixer for code formatting
- PHPUnit for testing
- Internationalization support using `_()` functions

### PHP 8.2+ Compatibility Requirements

**Property Declaration (Critical)**

PHP 8.2+ requires all class properties to be explicitly declared before use. Dynamic property creation is deprecated and will cause warnings.

**Common ORM Properties:**

When extending classes that use `\Ease\SQL\Orm` trait, always declare these properties at the class level:

```php
class MyClass extends \MultiFlexi\Engine
{
    use \Ease\SQL\Orm;

    /**
     * Creation timestamp column name.
     */
    public ?string $createColumn = null;

    /**
     * Last modified timestamp column name.
     */
    public ?string $lastModifiedColumn = null;

    public function __construct($identifier = null)
    {
        $this->myTable = 'my_table';
        $this->createColumn = 'created_at';
        $this->lastModifiedColumn = 'updated_at';
        parent::__construct($identifier);
    }
}
```

**Why This Matters:**
- PHP's `__sleep()` method (used for serialization) expects declared properties
- Session storage and caching trigger object serialization
- Undeclared properties cause deprecation warnings in PHP 8.2+
- Will become fatal errors in PHP 9.0

**Best Practices:**
1. Declare all properties at the class level with appropriate types
2. Initialize nullable properties to `null`
3. Set actual values in the constructor
4. Never rely on dynamic property creation
5. Run code on PHP 8.2+ during development to catch issues early

## Testing

Test applications are located in the `tests/` directory:
- `multiflexi_probe.multiflexi.app.json`: Probe testing application
- `sleep.multiflexi.app.json`: Simple sleep test application

To validate application definitions against the schema, use:
```bash
multiflexi-cli application validate-json --json tests/multiflexi_probe.multiflexi.app.json
```

### Security Testing

Test security components with these commands:

```bash
# Test encryption functionality
php -r "echo MultiFlexi\Security\EncryptionHelpers::testEncryption() ? 'PASS' : 'FAIL'; echo PHP_EOL;"

# Check security audit logging
php -r "if(isset(\$GLOBALS['securityAuditLogger'])) { echo 'Security audit logger available'; } else { echo 'Not initialized'; }"

# Test rate limiting
php -r "echo MultiFlexi\Security\RateLimitHelpers::isRateLimitingAvailable() ? 'Available' : 'Disabled'; echo PHP_EOL;"

### Data Retention Testing (GDPR Phase 4)

Test data retention components with these commands:

```bash
# Test retention service initialization
php -r "use MultiFlexi\DataRetention\RetentionService; \$rs = new RetentionService(); echo 'Retention service: OK'; echo PHP_EOL;"

# Test policy validation
php -r "use MultiFlexi\DataRetention\RetentionPolicyManager; \$pm = new RetentionPolicyManager(); echo 'Policy manager: OK'; echo PHP_EOL;"

# Test data archiver functionality
php -r "use MultiFlexi\DataRetention\DataArchiver; \$da = new DataArchiver(); echo 'Data archiver: OK'; echo PHP_EOL;"

# Run retention system unit tests
vendor/bin/phpunit tests/DataRetention/RetentionServiceTest.php
```
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

## GDPR Phase 4: Data Retention Administration

### Web Interface

Access the data retention administration interface at:
```
http://your-multiflexi-domain/data-retention-admin.php
```

**Features:**
- Policy management dashboard with real-time statistics
- Create, edit, delete, and toggle retention policies
- Quick actions for cleanup and retention calculations
- Visual overview of expired records awaiting cleanup
- Manual cleanup execution with dry-run capability

### Default Retention Policies

| Data Type | Retention Period | Action | Legal Basis |
|-----------|------------------|--------|-------------|
| User accounts (inactive) | 3 years | Anonymize | GDPR Art. 5(1)(e) |
| Session data | 30 days | Hard delete | Data minimization |
| Audit logs | 7 years | Archive | Legal requirements |
| Job execution logs | 1 year | Soft delete | Business operations |
| Application logs | 1 year | Hard delete | Troubleshooting |
| Company data | 5 years | Anonymize | Business relationships |
| Login attempts | 90 days | Hard delete | Security monitoring |

### Automated Scheduling

Add to crontab for automated cleanup:
```bash
# Daily cleanup at 2:00 AM
0 2 * * * /path/to/multiflexi-cli retention:cleanup cleanup

# Weekly grace period cleanup
0 3 * * 0 /path/to/multiflexi-cli retention:cleanup grace-period

# Monthly compliance reporting
0 9 1 * * /path/to/multiflexi-cli retention:cleanup report --format=json --output=/var/log/multiflexi/retention-report.json
```

### Data Archival and Recovery

**Archive Management:**
- Pre-deletion archives for 7 years (configurable)
- Legal hold capability for litigation/investigation
- Integrity verification with SHA-256 hashes
- Export capabilities (JSON, CSV formats)

**Recovery Process:**
- Grace periods before final deletion (default 30 days)
- Archive search and retrieval via CLI and web interface
- Verification of archived data integrity
- Legal hold extensions for compliance requirements
