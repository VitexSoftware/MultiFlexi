# Database Setup Update for Selenium Tests

## Overview

The Selenium test scripts have been updated to properly prepare the database before running tests by:
1. Dropping all existing database tables
2. Running Phinx migrations from `/usr/lib/multiflexi-database/migrations/`
3. Using the current database credentials from `/etc/multiflexi/database.env`

## What Changed

### Updated Files

1. **`scripts/setupDatabase.js`** - Completely rewritten to:
   - Use the main MultiFlexi database (not separate test databases)
   - Read configuration from `/etc/multiflexi/database.env`
   - Drop all existing tables before migrations
   - Run Phinx migrations using the official adapter
   - Insert basic test data after migration
   - Provide database verification functionality

2. **`run-tests.sh`** - Enhanced with:
   - Dependency checking (including Phinx and database config)
   - Database setup commands (`db-setup`, `db-cleanup`, `db-verify`)
   - Better error handling and user guidance
   - Fresh database setup option (`fresh`)

3. **`scripts/database-setup.sh`** - New shell script library with:
   - Reusable database setup functions
   - Both Node.js and shell-based approaches
   - Proper error handling and logging
   - Can be sourced by other scripts

### Database Setup Process

The database setup now follows this process:

1. **Prerequisites Check**:
   - Verify Phinx is installed
   - Check `/etc/multiflexi/database.env` exists
   - Confirm migrations directory `/usr/lib/multiflexi-database/migrations/` exists

2. **Database Preparation**:
   - Drop all existing tables (including foreign key constraints handling)
   - Run: `cd /usr/lib/multiflexi-database && phinx migrate -c phinx-adapter.php`
   - Insert basic test data (admin user, test company, test application)

3. **Verification**:
   - Check all required tables exist
   - Verify migration tracking table (`phinxlog`)
   - Report number of tables and applied migrations

## Usage

### Command Line Usage

```bash
# Check all dependencies
./run-tests.sh check

# Setup database only
./run-tests.sh db-setup

# Verify database is ready
./run-tests.sh db-verify

# Clean database (drop all tables)
./run-tests.sh db-cleanup

# Run tests with fresh database setup
./run-tests.sh fresh smoke true

# Full test suite with database setup and cleanup
./run-tests.sh full
```

### Node.js Script Usage

```bash
# Setup database
node scripts/setupDatabase.js

# Verify database
node scripts/setupDatabase.js verify

# Cleanup database
node scripts/setupDatabase.js cleanup
```

### Shell Library Usage

```bash
# Source the functions
source scripts/database-setup.sh

# Use the functions
setup_multiflexi_database
verify_database_setup
cleanup_multiflexi_database
```

## Benefits

1. **Consistent State**: Every test run starts with a clean, known database state
2. **Real Schema**: Uses actual production migrations instead of test schemas
3. **Current Credentials**: Automatically uses the configured database credentials
4. **Reliable**: Proper error handling and verification steps
5. **Flexible**: Can be used standalone or integrated into test workflows

## Requirements

- Phinx must be installed and available in PATH
- Database configuration file `/etc/multiflexi/database.env` must exist
- Migrations directory `/usr/lib/multiflexi-database/migrations/` must be accessible
- Database user must have sufficient privileges (CREATE, DROP, INSERT, etc.)

## Migration from Previous Approach

The previous approach used separate test databases (`multiflexi_dev_test`, etc.) with basic schema setup. The new approach:

- Uses the main MultiFlexi database directly
- Applies all production migrations for complete schema
- Provides better test coverage by using real database structure
- Eliminates schema drift between test and production environments

## Troubleshooting

### Common Issues

1. **"Phinx not found"**: Install Phinx or add to PATH
2. **"Database config not found"**: Ensure `/etc/multiflexi/database.env` exists
3. **"Permission denied"**: Database user needs sufficient privileges
4. **"Migration failed"**: Check database connectivity and migration files

### Debug Mode

Set `DEBUG=true` in your environment or `.env` file for verbose logging.

