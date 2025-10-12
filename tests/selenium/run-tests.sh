#!/bin/bash

# MultiFlexi Selenium Test Runner Script
# This script provides easy commands to run different test scenarios

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "package.json" ]; then
    print_error "Please run this script from the tests/selenium directory"
    exit 1
fi

# Function to check dependencies
check_dependencies() {
    print_status "Checking dependencies..."
    
    # Check Node.js
    if ! command -v node &> /dev/null; then
        print_error "Node.js is not installed"
        exit 1
    fi
    
    # Check npm
    if ! command -v npm &> /dev/null; then
        print_error "npm is not installed"
        exit 1
    fi
    
    # Check Chrome
    if ! command -v google-chrome &> /dev/null && ! command -v chromium-browser &> /dev/null; then
        print_warning "Google Chrome or Chromium not found in PATH"
    fi
    
    # Check Phinx
    if ! command -v phinx &> /dev/null; then
        print_error "Phinx is not installed or not in PATH"
        exit 1
    fi
    
    # Check database configuration
    if [ ! -f "/etc/multiflexi/database.env" ]; then
        print_error "Database configuration file /etc/multiflexi/database.env not found"
        exit 1
    fi
    
    # Check migrations directory
    if [ ! -d "/usr/lib/multiflexi-database/migrations" ]; then
        print_error "Migrations directory /usr/lib/multiflexi-database/migrations not found"
        exit 1
    fi
    
    print_success "Dependencies check completed"
}

# Function to setup environment
setup_environment() {
    print_status "Setting up test environment..."
    
    # Install npm dependencies if needed
    if [ ! -d "node_modules" ]; then
        print_status "Installing npm dependencies..."
        npm install
    fi
    
    # Check for .env file
    if [ ! -f ".env" ]; then
        if [ -f ".env.example" ]; then
            print_warning ".env file not found, copying from .env.example"
            cp .env.example .env
            print_warning "Please edit .env file with your configuration before running tests"
        else
            print_error ".env.example file not found"
            exit 1
        fi
    fi
    
    print_success "Environment setup completed"
}

# Function to run database setup with proper Phinx migrations
setup_database() {
    print_status "Setting up test database with Phinx migrations..."
    print_status "This will:"
    print_status "  1. Drop all existing database tables"
    print_status "  2. Run Phinx migrations from /usr/lib/multiflexi-database/migrations/"
    print_status "  3. Insert basic test data"
    
    node scripts/setupDatabase.js
    
    print_status "Verifying database setup..."
    node scripts/setupDatabase.js verify
    
    print_success "Database setup completed successfully"
}

# Function to cleanup database  
cleanup_database() {
    print_status "Cleaning up test database..."
    print_status "This will drop all database tables"
    
    node scripts/setupDatabase.js cleanup
    print_success "Database cleanup completed"
}

# Function to run tests
run_tests() {
    local test_type=$1
    local headless=${2:-false}
    
    if [ "$headless" = "true" ]; then
        export HEADLESS=true
        print_status "Running tests in headless mode"
    else
        export HEADLESS=false
        print_status "Running tests with visible browser"
    fi
    
    case $test_type in
        "all")
            print_status "Running all tests..."
            npm test
            ;;
        "e2e")
            print_status "Running end-to-end tests..."
            npm test tests/multiflexi.e2e.test.js
            ;;
        "auth")
            print_status "Running authentication tests..."
            npm test tests/auth.test.js
            ;;
        "companies")
            print_status "Running companies tests..."
            npm test tests/companies.test.js
            ;;
        "applications")
            print_status "Running applications tests..."
            npm test tests/applications.test.js
            ;;
        "credentials")
            print_status "Running credentials tests..."
            npm test tests/credentials.test.js
            ;;
        "jobs")
            print_status "Running jobs tests..."
            npm test tests/jobs.test.js
            ;;
        "runtemplate")
            print_status "Running RunTemplate tests..."
            npm test tests/runtemplate.test.js
            ;;
        "smoke")
            print_status "Running smoke tests..."
            export HEADLESS=true
            npm test tests/smoke-test.test.js
            ;;
        "scenarios")
            print_status "Running scenario tests..."
            npm test tests/scenario-*.test.js
            ;;
        *)
            print_error "Unknown test type: $test_type"
            print_status "Available test types: all, e2e, auth, companies, applications, credentials, jobs, runtemplate, smoke, scenarios"
            exit 1
            ;;
    esac
}

# Main execution
case "${1:-help}" in
    "check")
        check_dependencies
        ;;
    "setup")
        check_dependencies
        setup_environment
        ;;
    "db-setup")
        check_dependencies
        setup_database
        ;;
    "db-cleanup")
        cleanup_database
        ;;
    "db-verify")
        print_status "Verifying database setup..."
        node scripts/setupDatabase.js verify
        ;;
    "test")
        test_type=${2:-all}
        headless=${3:-false}
        check_dependencies
        setup_environment
        run_tests $test_type $headless
        ;;
    "full")
        print_status "Running full test suite with database setup and cleanup..."
        check_dependencies
        setup_environment
        setup_database
        run_tests "all" "false"
        cleanup_database
        ;;
    "ci")
        print_status "Running tests in CI mode (headless with database setup)..."
        check_dependencies
        setup_environment
        setup_database
        run_tests "all" "true"
        cleanup_database
        ;;
    "fresh")
        print_status "Running fresh test with complete database rebuild..."
        check_dependencies
        setup_environment
        setup_database
        run_tests "${2:-all}" "${3:-false}"
        ;;
    "help"|*)
        echo "MultiFlexi Selenium Test Runner"
        echo ""
        echo "Usage: $0 <command> [options]"
        echo ""
        echo "Commands:"
        echo "  check                    - Check dependencies (including Phinx and database config)"
        echo "  setup                    - Setup test environment"
        echo "  db-setup                 - Setup test database (drop tables + run Phinx migrations)"
        echo "  db-cleanup               - Cleanup test database (drop all tables)"
        echo "  db-verify                - Verify database setup"
        echo "  test <type> [headless]   - Run specific tests"
        echo "  full                     - Run complete test suite with DB setup/cleanup"
        echo "  fresh [type] [headless]  - Fresh database setup + run tests (no cleanup)"
        echo "  ci                       - Run tests in CI mode (headless)"
        echo "  help                     - Show this help"
        echo ""
        echo "Test types:"
        echo "  all                      - All tests"
        echo "  e2e                      - End-to-end tests"
        echo "  auth                     - Authentication tests"
        echo "  companies                - Companies management tests"
        echo "  applications             - Applications tests"
        echo "  credentials              - Credentials tests"
        echo "  jobs                     - Jobs tests"
        echo "  runtemplate              - RunTemplate tests"
        echo "  smoke                    - Smoke tests"
        echo "  scenarios                - Scenario tests"
        echo ""
        echo "Database Setup Process:"
        echo "  1. Drops all existing database tables"
        echo "  2. Runs Phinx migrations from /usr/lib/multiflexi-database/migrations/"
        echo "  3. Inserts basic test data"
        echo ""
        echo "Examples:"
        echo "  $0 check                 - Check if everything is ready"
        echo "  $0 setup                 - Setup environment"
        echo "  $0 db-setup              - Prepare database for testing"
        echo "  $0 test auth             - Run auth tests with visible browser"
        echo "  $0 test all true         - Run all tests headlessly"
        echo "  $0 fresh smoke true      - Fresh DB + run smoke tests headlessly"
        echo "  $0 full                  - Complete test run with database"
        echo "  $0 ci                    - CI/CD pipeline run"
        ;;
esac
