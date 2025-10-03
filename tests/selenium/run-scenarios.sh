#!/bin/bash

# MultiFlexi Business Scenarios Test Runner
# Runs business scenarios in correct order

echo "🚀 MultiFlexi Business Scenarios Test Suite"
echo "=========================================="
echo ""

# Zkontrolovat, že jsme v správném adresáři
if [ ! -f "package.json" ]; then
    echo "❌ Chyba: Nejste v adresáři tests/selenium!"
    echo "   Přejděte do: cd /home/vitex/Projects/Multi/MultiFlexi/tests/selenium"
    exit 1
fi

# Zkontrolovat závislosti
if [ ! -d "node_modules" ]; then
    echo "📦 Instalace závislostí..."
    npm install
    echo ""
fi

# Zkontrolovat .env soubor
if [ ! -f ".env" ]; then
    echo "⚙️ Vytváření .env souboru..."
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo "✅ .env soubor vytvořen z .env.example"
        echo "   Zkontrolujte nastavení před spuštěním testů"
    else
        echo "⚠️ .env soubor neexistuje, bude použito výchozí nastavení"
    fi
    echo ""
fi

# Menu pro výběr testů
echo "Select test to run:"
echo "1) 🔥 Smoke Test (2 min) - Rychlá kontrola funkčnosti"
echo "2) ⭐⭐⭐ AbraFlexi Complete Workflow (5 min) - Kompletní setup firmy"
echo "3) ⭐⭐ Multi-Company Setup (4 min) - Test více firem"
echo "4) ⭐⭐ Job Error Recovery (5 min) - Test chybových stavů"
echo "5) 📋 Všechny stránkové testy (10 min)"
echo "6) 🎯 All scenarios (15 min)"
echo "7) 🚀 Kompletní test suite (20 min)"
echo "0) Exit"
echo ""

read -p "Zadejte číslo (0-7): " choice

case $choice in
    1)
        echo "🔥 Running Smoke Test..."
        npm run test:smoke
        ;;
    2)
        echo "⭐⭐⭐ Running AbraFlexi Complete Workflow..."
        npm run test:abraflexi
        ;;
    3)
        echo "⭐⭐ Running Multi-Company Setup..."
        npm run test:multicompany
        ;;
    4)
        echo "⭐⭐ Running Job Error Recovery..."
        npm run test:errors
        ;;
    5)
        echo "📋 Running all page tests..."
        npm run test:pages
        ;;
    6)
        echo "🎯 Running all scenarios..."
        npm run test:scenarios
        ;;
    7)
        echo "🚀 Running complete test suite..."
        npm run test:all
        ;;
    0)
        echo "👋 Ukončuji..."
        exit 0
        ;;
    *)
        echo "❌ Neplatná volba!"
        exit 1
        ;;
esac

echo ""
echo "✅ Test dokončen!"
echo ""
echo "📊 Pro detailní výsledky zkontrolujte výstup výše."
echo "📝 Pro více informací viz SCENARIOS.md"
echo "🐛 Problémy hlaste na: https://github.com/VitexSoftware/MultiFlexi/issues"