# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Architecture

MultiFlexi is a comprehensive PHP-based task scheduling and automation framework designed for accounting and business system integrations. The system follows a multi-component architecture:

### Core Components

- **multiflexi-common**: (This repository) Documentation, common assets, and Zabbix integration
- **php-vitexsoftware-multiflexi-core**: (External) The central library containing core functionality
- **multiflexi-web**: (External) Bootstrap 4 web interface with real-time monitoring
- **multiflexi-cli**: (External) Command-line interface for management operations
- **multiflexi-scheduler**: (External) Systemd service daemon that continuously schedules jobs
- **multiflexi-executor**: (External) Systemd service daemon that continuously executes scheduled jobs
- **multiflexi-database**: (External) Database schema and migration management
- **multiflexi-server**: (External) Optional server component for advanced deployments
- **multiflexi-zabbix**: (External) Zabbix integration components (moved from core)
- **ansible-collection**: (External) Ansible automation collection for infrastructure deployment

### Directory Structure

- `bin/`: Common executables and Zabbix LLD launchers
- `debian/`: Debian package build configuration for documentation and common assets
- `docs/`: Sphinx documentation source
- `lib/`: PHP scripts for Zabbix LLD and other common utilities
- `zabbix/`: Zabbix templates and configuration files
- `gdpr/`: GDPR compliance policies and documentation
- `tools/`: Miscellaneous helper scripts

### Application Architecture

MultiFlexi uses a layered architecture:

1. **Database Layer**: ORM with Phinx migrations managing applications, companies, run templates, and jobs
2. **Application Management**: External app definitions (.app.json files) with metadata validation
3. **Job Execution System**: Multi-environment execution with environment variable injection
4. **Credential Framework**: Extensible credential types for various system integrations
5. **Configuration Management**: Environment-based configuration with type-safe field definitions
6. **Security Layer**: Authentication, authorization, and secure credential handling
7. **Audit Trail**: Job scheduling tracked by user (web or CLI) via `job.launched_by` foreign key

### Application Definition Schema

Applications are defined using `.app.json` files that must conform to the schema at:
https://raw.githubusercontent.com/VitexSoftware/php-vitexsoftware-multiflexi-core/refs/heads/main/multiflexi.app.schema.json

Key elements:
- `executable`: Command to run
- `environment`: Configuration fields with types (string, bool, number, password, file-path)
- `requirements`: System dependencies
- `ociimage`: Docker container image for containerized execution
- `artifacts`: Output files produced by the application

### Job Audit Trail

MultiFlexi tracks which user scheduled each job for accountability:

**Database Schema:**
- `job.launched_by` (integer, NOT NULL, FK to `user.id`): User who scheduled the job
- Migration: `20251121091900_job_launched_by_integer.php`

**User Types:**
- **Web Users** (`user.enabled = 1`): Users authenticated through web interface
- **CLI/OS Users** (`user.enabled = 0`): System users created automatically by `UnixUser` class

**How it Works:**
1. When jobs are scheduled via web interface, `\Ease\Shared::user()` returns the authenticated web user
2. When jobs are scheduled via `multiflexi-cli`, the `UnixUser` class:
   - Detects the OS username via `get_current_user()`
   - Checks if user exists in database
   - Creates new user record with `enabled = 0` if not found
3. All jobs store user ID in `launched_by` column (never NULL)

**UI Display:**
- 👤 Web users shown with badge and link to user profile
- 🖥️ CLI users shown with secondary badge and link to auto-created profile
- User full name displayed if available, otherwise username

### Queue Position Display

Scheduled jobs display their current position in the execution queue:

**Visual Indicators:**
- **Scheduled Jobs**: Blue badge showing position (#3/10 means 3rd out of 10 jobs)
- **Orphaned Jobs**: Warning badge (⚠️ orphaned) for jobs without schedule entry

**How it Works:**
1. `CompanyJobLister` and `DashboardRecentJobsTable` query all scheduled jobs ordered by `schedule.after ASC`
2. Build position map: `$scheduledCounts[$jobId] = $position`
3. Display format: "💣 in 5 minutes #3/10" (scheduled time + queue position)

**Code Locations:**
- `CompanyJobLister::addSelectizeValues()`: Queue position calculation
- `CompanyJobLister::completeDataRow()`: Display logic with badges
- `DashboardRecentJobsTable`: Status badge with queue info

**Code Locations:**
- Core: `https://github.com/VitexSoftware/php-vitexsoftware-multiflexi-core`
- Web UI: `https://github.com/VitexSoftware/multiflexi-web`
- CLI: `https://github.com/VitexSoftware/multiflexi-cli`
- Zabbix Integration: `https://github.com/VitexSoftware/multiflexi-zabbix`

## Database Access

### MySQL Database Connection

**IMPORTANT:** Always use `sudo mysql` for direct database access to avoid repeated authentication attempts that waste AI credits.

```bash
# Access MultiFlexi database
sudo mysql

# Direct query execution
sudo mysql -e "USE multiflexi; DESCRIBE tablename;"
sudo mysql -e "USE multiflexi; SELECT * FROM user LIMIT 10;"

# Export database structure
sudo mysql multiflexi -e "SHOW CREATE TABLE runtemplate\G"
```

**Why sudo mysql:**
- No password required (uses Unix socket authentication)
- Faster and more reliable than password-based authentication
- Avoids wasting resources on repeated connection attempts
- Standard approach for local MySQL administration on Debian/Ubuntu

## API Access and Authentication

### API Authentication Requirements

The MultiFlexi REST API **requires authentication** for all endpoints (except `/ping` and `/status`).

**Authentication Methods:**
1. **HTTP Basic Authentication** with username and password
2. **Token-based authentication** (OAuth-style tokens)
3. **Session cookie authentication** (for logged-in users)

**Note:** The API accepts session cookies from logged-in users, so JavaScript code on MultiFlexi pages does not require any special authentication mechanism - it automatically uses the user's existing session.

### User Management

**Create API user via multiflexi-cli:**

```bash
# Create new user interactively
multiflexi-cli user:create

# Create user with parameters
multiflexi-cli user:create --login=apiuser --password=secret --email=api@example.com

# List existing users
multiflexi-cli user:list

# Generate API token for user
multiflexi-cli user:token --login=apiuser
```

### Testing API Endpoints

**Using HTTP Basic Authentication:**

```bash
# Test with basic auth
curl -u username:password http://localhost/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/apps.json

# Test specific endpoint
curl -u username:password http://localhost/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/runtemplate/15.json

# With pretty-printed JSON output
curl -u username:password http://localhost/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/companies.json | python3 -m json.tool
```

**Using Token Authentication:**

```bash
# Get token
TOKEN=$(curl -s -u username:password http://localhost/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/login.json | jq -r .token)

# Use token in subsequent requests
curl -H "Authorization: Bearer $TOKEN" http://localhost/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/apps.json
```

**API Pagination Parameters:**

All list endpoints support pagination:
- `limit` - Maximum number of results (default: 20, max: 100)
- `offset` - Number of records to skip (default: 0)
- `order` - Field to sort by, use `-` prefix for descending (e.g., `-id`)

```bash
# Get first 10 jobs
curl -u username:password "http://localhost/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/jobs.json?limit=10"

# Get next 10 jobs (pagination)
curl -u username:password "http://localhost/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/jobs.json?limit=10&offset=10"

# Get jobs ordered by ID descending
curl -u username:password "http://localhost/MultiFlexi/src/api/VitexSoftware/MultiFlexi/1.0.0/jobs.json?limit=10&order=-id"
```

**Using Session Cookies (JavaScript):**

For JavaScript code running on MultiFlexi web pages, session cookie authentication is used automatically by the browser.

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
multiflexi-cli application import-json --file path/to/app.json

# Validate application JSON schema
multiflexi-cli application validate-json --file multiflexi/app.app.json

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
multiflexi-cli application validate-json --file multiflexi/retention-policy.json
```
```

### Systemd Service Architecture (v2.x+)

MultiFlexi 2.x uses dedicated systemd services instead of cron:

```bash
# Control scheduler service
sudo systemctl start multiflexi-scheduler
sudo systemctl stop multiflexi-scheduler
sudo systemctl status multiflexi-scheduler
sudo systemctl restart multiflexi-scheduler

# Control executor service
sudo systemctl start multiflexi-executor
sudo systemctl stop multiflexi-executor
sudo systemctl status multiflexi-executor
sudo systemctl restart multiflexi-executor

# View service logs
sudo journalctl -u multiflexi-scheduler -f
sudo journalctl -u multiflexi-executor -f

# Development: Run daemons manually
make daemon  # Run local daemon for testing
```

**Service Configuration** (``/etc/multiflexi/multiflexi.env``):

- ``MULTIFLEXI_DAEMONIZE=true``: Run continuously (default for services)
- ``MULTIFLEXI_CYCLE_PAUSE=10``: Seconds between executor polling cycles
- ``MULTIFLEXI_MEMORY_LIMIT_MB=1800``: Soft memory limit for executor graceful shutdown
- ``MULTIFLEXI_MAX_PARALLEL=4``: Maximum concurrent jobs (executor; requires pcntl)

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

## Code Migration Notice

Most of the logic previously contained in this repository has been moved to specialized repositories:

1. **Web Interface & Widgets**: Now in `multiflexi-web`
2. **CLI Commands**: Now in `multiflexi-cli`
3. **Zabbix Integration**: Now in `multiflexi-zabbix`
4. **Database Migrations**: Now in `multiflexi-database`

This repository (`multiflexi-common`) now serves as the central point for documentation and common assets that are shared across the ecosystem.

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
multiflexi-cli application validate-json --file tests/multiflexi_probe.multiflexi.app.json
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
- [multiflexi-scheduler](https://github.com/VitexSoftware/multiflexi-scheduler)
- [multiflexi-executor](https://github.com/VitexSoftware/multiflexi-executor)
- [php-vitexsoftware-multiflexi-core](https://github.com/VitexSoftware/php-vitexsoftware-multiflexi-core)
- [multiflexi-cli](https://github.com/VitexSoftware/multiflexi-cli)
- [multiflexi-database](https://github.com/VitexSoftware/multiflexi-database)
- [multiflexi-ansible-collection](https://github.com/VitexSoftware/multiflexi-ansible-collection)
- [multiflexi-web](https://github.com/VitexSoftware/multiflexi-web)
- [multiflexi-zabbix](https://github.com/VitexSoftware/multiflexi-zabbix)

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
