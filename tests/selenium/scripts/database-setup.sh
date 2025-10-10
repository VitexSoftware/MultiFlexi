#!/bin/bash

# Database setup functions for MultiFlexi Selenium tests
# These functions handle proper database preparation using Phinx migrations

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Print colored output
print_status() {
    echo -e "${BLUE}[DB-SETUP]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[DB-SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[DB-WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[DB-ERROR]${NC} $1"
}

# Function to check database setup prerequisites
check_db_prerequisites() {
    print_status "Checking database setup prerequisites..."
    
    # Check Phinx
    if ! command -v phinx &> /dev/null; then
        print_error "Phinx is not installed or not in PATH"
        return 1
    fi
    
    # Check database configuration
    if [ ! -f "/etc/multiflexi/database.env" ]; then
        print_error "Database configuration file /etc/multiflexi/database.env not found"
        return 1
    fi
    
    # Check migrations directory
    if [ ! -d "/usr/lib/multiflexi-database/migrations" ]; then
        print_error "Migrations directory /usr/lib/multiflexi-database/migrations not found"
        return 1
    fi
    
    # Check phinx adapter
    if [ ! -f "/usr/lib/multiflexi-database/phinx-adapter.php" ]; then
        print_error "Phinx adapter /usr/lib/multiflexi-database/phinx-adapter.php not found"
        return 1
    fi
    
    print_success "Database prerequisites check completed"
    return 0
}

# Function to setup database using the Node.js script (recommended)
setup_database_nodejs() {
    print_status "Setting up database using Node.js script..."
    
    if [ -f "scripts/setupDatabase.js" ]; then
        node scripts/setupDatabase.js
        if [ $? -eq 0 ]; then
            print_success "Database setup completed successfully"
            return 0
        else
            print_error "Database setup failed"
            return 1
        fi
    else
        print_error "setupDatabase.js script not found"
        return 1
    fi
}

# Function to setup database directly using shell commands (fallback)
setup_database_shell() {
    print_status "Setting up database using shell commands (fallback method)..."
    
    # Source database configuration
    if [ -f "/etc/multiflexi/database.env" ]; then
        # Extract database connection details
        DB_HOST=$(grep "^DB_HOST=" /etc/multiflexi/database.env | cut -d'=' -f2)
        DB_PORT=$(grep "^DB_PORT=" /etc/multiflexi/database.env | cut -d'=' -f2)
        DB_DATABASE=$(grep "^DB_DATABASE=" /etc/multiflexi/database.env | cut -d'=' -f2)
        DB_USERNAME=$(grep "^DB_USERNAME=" /etc/multiflexi/database.env | cut -d'=' -f2)
        DB_PASSWORD=$(grep "^DB_PASSWORD=" /etc/multiflexi/database.env | cut -d'=' -f2)
        
        print_status "Dropping all existing tables..."
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -e "
            SET FOREIGN_KEY_CHECKS = 0;
            SET GROUP_CONCAT_MAX_LEN=32768;
            SET @tables = (SELECT GROUP_CONCAT(table_name) FROM information_schema.tables WHERE table_schema = '$DB_DATABASE');
            PREPARE stmt FROM CONCAT('DROP TABLE IF EXISTS ', @tables);
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
            SET FOREIGN_KEY_CHECKS = 1;
        " 2>/dev/null
        
        if [ $? -eq 0 ]; then
            print_success "All existing tables dropped successfully"
        else
            print_error "Failed to drop existing tables"
            return 1
        fi
        
        print_status "Running Phinx migrations..."
        cd /usr/lib/multiflexi-database && phinx migrate -c phinx-adapter.php 2>/dev/null
        
        if [ $? -eq 0 ]; then
            print_success "Database migrations completed successfully"
            return 0
        else
            print_error "Database migrations failed"
            return 1
        fi
    else
        print_error "Database configuration not found"
        return 1
    fi
}

# Function to verify database setup
verify_database_setup() {
    print_status "Verifying database setup..."
    
    if [ -f "scripts/setupDatabase.js" ]; then
        node scripts/setupDatabase.js verify
        if [ $? -eq 0 ]; then
            print_success "Database verification completed successfully"
            return 0
        else
            print_error "Database verification failed"
            return 1
        fi
    else
        print_warning "Cannot verify database - setupDatabase.js script not found"
        return 0
    fi
}

# Main function to setup database with proper error handling
setup_multiflexi_database() {
    local use_nodejs=${1:-true}
    
    print_status "Starting MultiFlexi database setup..."
    print_status "This will:"
    print_status "  1. Drop all existing database tables"
    print_status "  2. Run Phinx migrations from /usr/lib/multiflexi-database/migrations/"
    print_status "  3. Insert basic test data"
    print_status ""
    
    # Check prerequisites
    if ! check_db_prerequisites; then
        print_error "Prerequisites check failed"
        return 1
    fi
    
    # Setup database
    if [ "$use_nodejs" = "true" ]; then
        if setup_database_nodejs; then
            verify_database_setup
            return $?
        else
            print_warning "Node.js setup failed, trying shell fallback..."
            if setup_database_shell; then
                print_success "Database setup completed using shell fallback"
                return 0
            else
                print_error "Both Node.js and shell setup methods failed"
                return 1
            fi
        fi
    else
        setup_database_shell
        return $?
    fi
}

# Function to cleanup database
cleanup_multiflexi_database() {
    print_status "Cleaning up MultiFlexi database..."
    
    if [ -f "scripts/setupDatabase.js" ]; then
        node scripts/setupDatabase.js cleanup
        if [ $? -eq 0 ]; then
            print_success "Database cleanup completed successfully"
            return 0
        else
            print_error "Database cleanup failed"
            return 1
        fi
    else
        print_error "setupDatabase.js script not found for cleanup"
        return 1
    fi
}

# Export functions for use in other scripts
export -f print_status print_success print_warning print_error
export -f check_db_prerequisites setup_database_nodejs setup_database_shell
export -f verify_database_setup setup_multiflexi_database cleanup_multiflexi_database
