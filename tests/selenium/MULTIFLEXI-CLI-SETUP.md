# MultiFlexi CLI Database Setup for Selenium Tests

## Overview

The Selenium test suite has been updated to use `multiflexi-cli` commands for creating test users and data, making it database-agnostic and ensuring consistency across different database backends (MySQL, PostgreSQL, SQLite, etc.).

## Key Features

✅ **Database Agnostic**: Uses `multiflexi-cli` which works with any PDO-supported database
✅ **Proper User Creation**: Creates users with proper password hashing using CLI
✅ **Clean Migrations**: Uses official Phinx migrations from `/usr/lib/multiflexi-database/migrations/`
✅ **Verification**: Validates setup using CLI commands
✅ **Fallback Support**: Graceful error handling and verification

## Database Setup Process

### 1. Drop All Tables
- Removes all existing database tables
- Disables foreign key constraints during cleanup
- Database-specific implementation (currently MySQL)

### 2. Run Phinx Migrations
```bash
cd /usr/lib/multiflexi-database && phinx migrate -c phinx-adapter.php
```
- Applies all 98 migrations
- Creates complete MultiFlexi schema
- Uses official migration files

### 3. Create Test Data with multiflexi-cli

#### Create Test Users
```bash
# Admin user
multiflexi-cli user create --login=admin --firstname=Test --lastname=Admin --email=admin@test.com --plaintext=admin123 --enabled=true

# Regular test user  
multiflexi-cli user create --login=testuser --firstname=Test --lastname=User --email=testuser@test.com --plaintext=testpass123 --enabled=true
```

#### Create Test Company
```bash
multiflexi-cli company create --name="TestCorp" --enabled=true --ic=12345678 --email=test@testcorp.com --slug=testcorp
```

#### Create Test Application
```bash
multiflexi-cli application create --name="TestApp" --description="Test Application for Selenium tests" --executable="echo test" --homepage="http://example.com" --appversion="1.0.0" --uuid="testapp"
```

## Test Credentials

The following test accounts are created for Selenium tests:

### Admin User
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@test.com`
- **Role**: Administrator

### Test User
- **Username**: `testuser`
- **Password**: `testpass123`
- **Email**: `testuser@test.com`
- **Role**: Regular user

### Test Company
- **Name**: `TestCorp`
- **IC**: `12345678`
- **Email**: `test@testcorp.com`
- **Slug**: `testcorp`

### Test Application
- **Name**: `TestApp`
- **UUID**: `testapp`
- **Version**: `1.0.0`
- **Description**: Test Application for Selenium tests

## Usage Commands

### Setup Database
```bash
# Full setup with verification
./run-tests.sh db-setup

# Or directly
node scripts/setupDatabase.js
```

### Verify Setup
```bash
# Verify database state
./run-tests.sh db-verify

# Or directly
node scripts/setupDatabase.js verify
```

### Clean Database
```bash
# Drop all tables
./run-tests.sh db-cleanup

# Or directly
node scripts/setupDatabase.js cleanup
```

### Fresh Test Run
```bash
# Setup database and run tests
./run-tests.sh fresh smoke true
```

## AuthPage Integration

The `AuthPage` class has been updated with new methods for test user authentication:

```javascript
// Login methods
await authPage.loginAsAdmin();     // Uses admin/admin123
await authPage.loginAsTestUser();  // Uses testuser/testpass123
await authPage.loginAsCustomer();  // Uses testcustomer/testpass123

// Credential getters
const adminCreds = authPage.getAdminCredentials();
const testUserCreds = authPage.getTestUserCredentials();
const customerCreds = authPage.getCustomerCredentials();
```

## Environment Configuration

Test credentials are configurable via environment variables in `.env`:

```bash
# Admin credentials (created by multiflexi-cli)
ADMIN_USERNAME=admin
ADMIN_PASSWORD=admin123
ADMIN_EMAIL=admin@test.com

# Test user credentials (created by multiflexi-cli)
TEST_USER_USERNAME=testuser
TEST_USER_PASSWORD=testpass123
TEST_USER_EMAIL=testuser@test.com

# Customer credentials (for completeness)
CUSTOMER_USERNAME=testcustomer
CUSTOMER_PASSWORD=testpass123
CUSTOMER_EMAIL=customer@test.com

# Test company data
TEST_COMPANY_NAME=TestCorp
TEST_COMPANY_CODE=TEST001
TEST_COMPANY_IC=12345678
TEST_COMPANY_EMAIL=test@testcorp.com
```

## Database Verification

The verification process uses `multiflexi-cli` commands to ensure database-agnostic checking:

```bash
# Check users
multiflexi-cli user list --format=json

# Check companies
multiflexi-cli company list --format=json

# Check applications
multiflexi-cli application list --format=json
```

Expected verification results:
- ✅ 28 tables created
- ✅ 98 migrations applied
- ✅ 2 test users created (admin, testuser)
- ✅ 1 test company created (TestCorp)
- ✅ 1 test application created (TestApp)

## Benefits

### Database Agnostic
- Works with MySQL, PostgreSQL, SQLite, and other PDO databases
- No hardcoded SQL queries for data creation
- Consistent behavior across database backends

### Proper User Management
- Uses MultiFlexi's built-in user creation logic
- Proper password hashing and validation
- Respects application business rules

### Real Schema
- Uses production migration files
- No schema drift between test and production
- Complete database structure

### Reliable Testing
- Clean state for every test run
- Proper test data setup
- Comprehensive verification

## Troubleshooting

### Common Issues

1. **"multiflexi-cli command not found"**
   - Ensure MultiFlexi is installed
   - Check PATH includes `/usr/bin`

2. **"Database config not found"**
   - Verify `/etc/multiflexi/database.env` exists
   - Check database connection settings

3. **"User creation failed"**
   - Check database connectivity
   - Verify user has sufficient privileges
   - Check for existing users with same login

4. **"Company creation failed"**
   - Slug must be ≤ 10 characters
   - Company name should be reasonably short

5. **"Application creation failed"**
   - UUID must be unique and not too long
   - Use `--appversion` not `--version`

### Debug Commands

```bash
# Test CLI connectivity
multiflexi-cli user list

# Test database connection
multiflexi-cli company list

# Check migration status
cd /usr/lib/multiflexi-database && phinx status -c phinx-adapter.php
```

## Migration from Previous Approach

The previous approach used direct SQL queries and separate test databases. The new approach:

- ✅ Uses `multiflexi-cli` exclusively for data creation
- ✅ Works with any database backend
- ✅ Uses main database instead of separate test databases
- ✅ Applies complete production migrations
- ✅ Creates users with proper password hashing
- ✅ Follows MultiFlexi business rules

This ensures tests run against a realistic database structure and user accounts that match production behavior.
