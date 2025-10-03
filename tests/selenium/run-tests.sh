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

# Function to run database setup
setup_database() {
    print_status "Setting up test database..."
    node scripts/setupDatabase.js
    print_success "Database setup completed"
}

# Function to cleanup database  
cleanup_database() {
    print_status "Cleaning up test database..."
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
        "runtemplate")
            print_status "Running RunTemplate tests..."
            npm test tests/runtemplate.test.js
            ;;
        "smoke")
            print_status "Running smoke tests..."
            export HEADLESS=true
            npm test tests/multiflexi.e2e.test.js -- --grep "Smoke Tests"
            ;;
        *)
            print_error "Unknown test type: $test_type"
            print_status "Available test types: all, e2e, auth, runtemplate, smoke"
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
        setup_database
        ;;
    "db-cleanup")
        cleanup_database
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
    "help"|*)
        echo "MultiFlexi Selenium Test Runner"
        echo ""
        echo "Usage: $0 <command> [options]"
        echo ""
        echo "Commands:"
        echo "  check                    - Check dependencies"
        echo "  setup                    - Setup test environment"
        echo "  db-setup                 - Setup test database only"
        echo "  db-cleanup               - Cleanup test database only"
        echo "  test <type> [headless]   - Run specific tests"
        echo "  full                     - Run complete test suite with DB setup/cleanup"
        echo "  ci                       - Run tests in CI mode (headless)"
        echo "  help                     - Show this help"
        echo ""
        echo "Test types:"
        echo "  all                      - All tests"
        echo "  e2e                      - End-to-end tests"
        echo "  auth                     - Authentication tests"
        echo "  runtemplate              - RunTemplate tests"
        echo "  smoke                    - Smoke tests"
        echo ""
        echo "Examples:"
        echo "  $0 setup                 - Setup environment"
        echo "  $0 test auth             - Run auth tests with visible browser"
        echo "  $0 test all true         - Run all tests headlessly"
        echo "  $0 full                  - Complete test run with database"
        echo "  $0 ci                    - CI/CD pipeline run"
        ;;
esac