#!/bin/bash

# MultiFlexi Local Environment Test Runner
# Tests http://localhost/multiflexi/ (installed package)

echo "📦 MultiFlexi Local Environment Tests (Package)"
echo "==============================================="
echo "🌐 URL: http://localhost/multiflexi/"
echo "⚙️ Config: /etc/multiflexi/multiflexi.env"
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

# Check .env file for local
if [ ! -f ".env" ]; then
    echo "⚠️ .env file does not exist, creating for local..."
    cp .env.example .env
    sed -i 's/TEST_ENVIRONMENT=local/TEST_ENVIRONMENT=local/' .env
fi

# Verify that we are testing the correct URL
echo "🔍 Verifying configuration for LOCAL environment..."
if grep -q "TEST_ENVIRONMENT=local" .env; then
    echo "✅ Configuration set for local environment"
else
    echo "⚙️ Setting up configuration for local..."
    sed -i 's/TEST_ENVIRONMENT=.*/TEST_ENVIRONMENT=local/' .env
    echo "✅ Configuration updated"
fi

# Check package config availability
if [ -f "/etc/multiflexi/multiflexi.env" ]; then
    echo "✅ Package config found: /etc/multiflexi/multiflexi.env"
else
    echo "⚠️ Package config not found: /etc/multiflexi/multiflexi.env"
    echo "   Using test configuration only"
fi
echo ""

# Menu for test selection
echo "Select test to run on http://localhost/multiflexi/:"
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

# Export environment pro jistotu
export TEST_ENVIRONMENT=local

case $choice in
    1)
        echo "⚡ Running Simple Smoke Test on local environment..."
        npm run simple-smoke
        ;;
    2)
        echo "🔥 Running Full Smoke Test on local environment..."
        npm run test:smoke
        ;;
    3)
        echo "⭐⭐⭐ Running AbraFlexi Complete Workflow on local environment..."
        npm run test:abraflexi
        ;;
    4)
        echo "⭐⭐ Running Multi-Company Setup on local environment..."
        npm run test:multicompany
        ;;
    5)
        echo "⭐⭐ Running Job Error Recovery on local environment..."
        npm run test:errors
        ;;
    6)
        echo "📋 Running all page tests on local environment..."
        npm run test:pages
        ;;
    7)
        echo "🎯 Running all business scenarios on local environment..."
        npm run test:scenarios
        ;;
    8)
        echo "🚀 Running complete test suite on local environment..."
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
echo "🌐 Tested on: http://localhost/multiflexi/"
echo "⚙️ Package config: /etc/multiflexi/multiflexi.env"
echo "📝 For more information see SCENARIOS.md"