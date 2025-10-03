#!/bin/bash

# MultiFlexi Development Environment Test Runner
# Tests only http://localhost/MultiFlexi/src/

echo "🖥️  MultiFlexi Development Environment Tests"
echo "=========================================="
echo "🌐 URL: http://localhost/MultiFlexi/src/"
echo ""

# Check that we are in the correct directory
if [ ! -f "package.json" ]; then
    echo "❌ Error: You are not in tests/selenium directory!"
    echo "   Navigate to: cd /home/vitex/Projects/Multi/MultiFlexi/tests/selenium"
    exit 1
fi

# Check dependencies
if [ ! -d "node_modules" ]; then
    echo "📦 Installing dependencies..."
    npm install
    echo ""
fi

# Check .env file
if [ ! -f ".env" ]; then
    echo "⚠️ .env file does not exist, creating for development..."
    cp .env.example .env
    sed -i 's/TEST_ENVIRONMENT=local/TEST_ENVIRONMENT=development/' .env
    sed -i 's|LOCAL_BASE_URL|http://localhost/MultiFlexi/src/|' .env
fi

# Verify that we are testing the correct URL
echo "🔍 Verifying configuration..."
if grep -q "development" .env && grep -q "MultiFlexi/src" .env; then
    echo "✅ Configuration set for development environment"
else
    echo "⚙️ Setting up configuration for development..."
    echo "TEST_ENVIRONMENT=development" > .env.temp
    echo "BASE_URL=http://localhost/MultiFlexi/src/" >> .env.temp
    echo "DB_NAME=multiflexi_dev_test" >> .env.temp
    cat .env.example | grep -v "TEST_ENVIRONMENT\|BASE_URL\|DB_NAME" >> .env.temp
    mv .env.temp .env
    echo "✅ Configuration updated"
fi
echo ""

# Menu for test selection
echo "Select test to run on http://localhost/MultiFlexi/src/:"
echo "1) ⚡ Simple Smoke Test (1 min) - No database, frontend only"
echo "2) 🔥 Full Smoke Test (2 min) - With database, complete check"
echo "3) ⭐⭐⭐ AbraFlexi Complete Workflow (5 min) - Complete company setup"
echo "4) ⭐⭐ Multi-Company Setup (4 min) - Test multiple companies"
echo "5) ⭐⭐ Job Error Recovery (5 min) - Test error states"
echo "6) 📋 All page tests (10 min)"
echo "7) 🎯 All business scenarios (15 min)"
echo "8) 🚀 Complete test suite (20 min)"
echo "0) Exit"
echo ""

read -p "Enter number (0-8): " choice

# Export environment for safety
export TEST_ENVIRONMENT=development

case $choice in
    1)
        echo "⚡ Running Simple Smoke Test on development environment..."
        npm run simple-smoke
        ;;
    2)
        echo "🔥 Running Full Smoke Test on development environment..."
        npm run test:smoke
        ;;
    3)
        echo "⭐⭐⭐ Running AbraFlexi Complete Workflow on development environment..."
        npm run test:abraflexi
        ;;
    4)
        echo "⭐⭐ Running Multi-Company Setup on development environment..."
        npm run test:multicompany
        ;;
    5)
        echo "⭐⭐ Running Job Error Recovery on development environment..."
        npm run test:errors
        ;;
    6)
        echo "📋 Running all page tests on development environment..."
        npm run test:pages
        ;;
    7)
        echo "🎯 Running all business scenarios on development environment..."
        npm run test:scenarios
        ;;
    8)
        echo "🚀 Running complete test suite on development environment..."
        npm run test:all
        ;;
    0)
        echo "👋 Exiting..."
        exit 0
        ;;
    *)
        echo "❌ Invalid choice!"
        exit 1
        ;;
esac

echo ""
echo "✅ Test completed!"
echo ""
echo "📊 Check the output above for detailed results."
echo "🌐 Tested on: http://localhost/MultiFlexi/src/"
echo "📝 For more information see SCENARIOS.md"