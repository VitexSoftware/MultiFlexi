# MultiFlexi Selenium Tests - Quick Start Guide

## 🚀 Quick Setup

1. **Install dependencies:**
```bash
cd tests/selenium
npm install
```

2. **Configure environment:**
```bash
cp .env.example .env
# Edit .env with your database and MultiFlexi URL settings
```

3. **Run complete test suite:**
```bash
./run-tests.sh full
```

## 📋 What the tests do:

### 1. **Database Setup**
- Creates clean `multiflexi_test` database
- Applies all migrations to reach initial state
- Inserts basic test data

### 2. **Admin Registration** 
- Opens MultiFlexi web interface
- Registers admin account via registration form
- Verifies successful registration

### 3. **RunTemplate Creation**
- Logs in as admin
- Creates new RunTemplate with:
  - Basic configuration (name, description)
  - Application selection  
  - Scheduling options (interval, timeout)
  - Environment variables
  - Enable/disable settings

### 4. **Job Execution**
- Schedules RunTemplate execution
- Monitors execution status
- Verifies completion
- Checks for errors

### 5. **Cleanup**
- Logs out user
- Drops test database
- Closes browser

## 🛠️ Available Commands

```bash
# Setup environment only
./run-tests.sh setup

# Run specific test types
./run-tests.sh test auth          # Authentication tests
./run-tests.sh test runtemplate   # RunTemplate tests  
./run-tests.sh test e2e           # End-to-end tests

# Run headless (for CI)
./run-tests.sh test all true

# Database operations
./run-tests.sh db-setup           # Setup test DB only
./run-tests.sh db-cleanup         # Cleanup test DB only
```

## 🔧 Configuration

### Required `.env` settings:
```env
BASE_URL=http://localhost/MultiFlexi
DB_HOST=localhost
DB_NAME=multiflexi_test  
DB_USER=multiflexi_test
DB_PASSWORD=test_password
ADMIN_USERNAME=admin
ADMIN_PASSWORD=admin123
```

## 🐛 Troubleshooting

**Database connection issues:**
- Verify MySQL is running
- Check credentials in `.env`
- Ensure test user has CREATE/DROP privileges

**MultiFlexi not loading:**
- Verify BASE_URL is correct
- Check Apache/PHP is running
- Ensure MultiFlexi is properly installed

**Chrome/WebDriver issues:**  
- Install Google Chrome
- Run `npm install` to get chromedriver
- For headless: set `HEADLESS=true`

## 📸 Screenshots

Failed tests automatically save screenshots to `screenshots/` directory for debugging.

## 🔄 CI/CD Integration

GitHub Actions workflow included at `.github/workflows/selenium-tests.yml` for automated testing on push/PR.