#!/bin/bash

# MultiFlexi Multi-Environment Test Runner
# Allows running tests on all three environments

echo "🌍 MultiFlexi Multi-Environment Test Runner"
echo "==========================================="
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

# Function to run test on specific environment
run_environment_test() {
    local env=$1
    local test_type=$2
    
    echo "🚀 Running $test_type on environment: $env"
    echo "----------------------------------------"
    
    # Export environment variable
    export TEST_ENVIRONMENT=$env
    
    case $test_type in
        "smoke")
            npm run test:smoke
            ;;
        "abraflexi")
            npm run test:abraflexi
            ;;
        "multicompany")
            npm run test:multicompany
            ;;
        "errors")
            npm run test:errors
            ;;
        "scenarios")
            npm run test:scenarios
            ;;
        "all")
            npm run test:all
            ;;
    esac
    
    local exit_code=$?
    echo ""
    if [ $exit_code -eq 0 ]; then
        echo "✅ Test on environment '$env' was successful!"
    else
        echo "❌ Test on environment '$env' failed (exit code: $exit_code)"
    fi
    echo ""
    
    return $exit_code
}

# Funkce pro spuštění testu na všech prostředích
run_all_environments() {
    local test_type=$1
    
    echo "🌍 Running '$test_type' on ALL environments"
    echo "============================================="
    echo ""
    
    local environments=("development" "local" "staging")
    local env_descriptions=("Source code in development" "Debian package" "Testing server")
    local failed_envs=()
    local success_count=0
    
    for i in "${!environments[@]}"; do
        local env="${environments[$i]}"
        local desc="${env_descriptions[$i]}"
        
        echo "🎯 Prostředí ${i+1}/3: $env ($desc)"
        
        run_environment_test "$env" "$test_type"
        
        if [ $? -eq 0 ]; then
            ((success_count++))
        else
            failed_envs+=("$env")
        fi
        
        echo "⏳ Pauza mezi prostředími (5 sekund)..."
        sleep 5
    done
    
    # Souhrnná zpráva
    echo "📊 SOUHRN TESTOVÁNÍ VŠECH PROSTŘEDÍ"
    echo "===================================="
    echo "✅ Úspěšných prostředí: $success_count/3"
    echo "❌ Selhalo prostředí: ${#failed_envs[@]}/3"
    
    if [ ${#failed_envs[@]} -gt 0 ]; then
        echo "🚨 Selhala prostředí: ${failed_envs[*]}"
        return 1
    else
        echo "🎉 Všechna prostředí prošla úspěšně!"
        return 0
    fi
}

# Menu pro výběr
echo "Dostupná prostředí:"
echo "1) 🖥️  development  - http://localhost/MultiFlexi/src/ (source code)"
echo "2) 📦 local        - http://localhost/multiflexi/ (debian balíček)"
echo "3) 🌐 staging      - https://vyvojar.spoje.net/multiflexi/ (testing server)"
echo "4) 🌍 ALL          - Všechna prostředí postupně"
echo ""

echo "Dostupné testy:"
echo "A) 🔥 Smoke Test (2 min)"
echo "B) ⭐⭐⭐ AbraFlexi Complete Workflow (5 min)"
echo "C) ⭐⭐ Multi-Company Setup (4 min)"
echo "D) ⭐⭐ Job Error Recovery (5 min)"
echo "E) 🎯 Všechny scénáře (15 min)"
echo "F) 🚀 Kompletní test suite (20 min)"
echo ""

read -p "Select environment (1-4): " env_choice
echo ""

# Určit prostředí
case $env_choice in
    1)
        selected_env="development"
        env_desc="Source code"
        ;;
    2)
        selected_env="local"
        env_desc="Debian balíček"
        ;;
    3)
        selected_env="staging"
        env_desc="Testing server"
        ;;
    4)
        selected_env="all"
        env_desc="Všechna prostředí"
        ;;
    *)
        echo "❌ Neplatná volba prostředí!"
        exit 1
        ;;
esac

read -p "Select test (A-F): " test_choice
echo ""

# Určit test
case $test_choice in
    [Aa])
        selected_test="smoke"
        test_desc="Smoke Test"
        ;;
    [Bb])
        selected_test="abraflexi"
        test_desc="AbraFlexi Complete Workflow"
        ;;
    [Cc])
        selected_test="multicompany"
        test_desc="Multi-Company Setup"
        ;;
    [Dd])
        selected_test="errors"
        test_desc="Job Error Recovery"
        ;;
    [Ee])
        selected_test="scenarios"
        test_desc="Všechny scénáře"
        ;;
    [Ff])
        selected_test="all"
        test_desc="Kompletní test suite"
        ;;
    *)
        echo "❌ Neplatná volba testu!"
        exit 1
        ;;
esac

echo "echo "🎯 Selected configuration:""
echo "   🌍 Prostředí: $env_desc"
echo "   🧪 Test: $test_desc"
echo ""

read -p "Pokračovat? (y/N): " confirm
if [[ ! $confirm =~ ^[Yy]$ ]]; then
    echo "👋 Zrušeno uživatelem"
    exit 0
fi

echo ""
echo "🚀 Running tests..."
echo ""

# Spustit test
if [ "$selected_env" = "all" ]; then
    run_all_environments "$selected_test"
    exit_code=$?
else
    run_environment_test "$selected_env" "$selected_test"
    exit_code=$?
fi

echo ""
echo "✅ Testování dokončeno!"
echo ""
echo "📊 Pro detailní výsledky zkontrolujte výstup výše."
echo "📝 Pro více informací viz SCENARIOS.md"
echo "🐛 Problémy hlaste na: https://github.com/VitexSoftware/MultiFlexi/issues"

exit $exit_code