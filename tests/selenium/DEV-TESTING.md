# MultiFlexi Development Testing

Testování pouze na development pr� Description: Source code in development📋 Description: Source code in developmentstředí `http://localhost/MultiFlexi/src/`

## 🖥️ Development Environment

**URL:** `http://localhost/MultiFlexi/src/`  
**Účel:** Testování rozpracovaných zdrojových kódů  
**Databáze:** `multiflexi_dev_test`  
**Debug:** Zapnutý pro detailní výstupy

## 🚀 Running tests

### Interaktivní menu
```bash
# Nejjednodušší způsob - menu s výběrem testů
./run-dev.sh
```

### NPM Scripts
```bash
# Základní testy
npm run dev:smoke          # Smoke test (2 min)
npm run dev:abraflexi      # AbraFlexi workflow (5 min) 
npm run dev:multicompany   # Multi-company setup (4 min)
npm run dev:errors         # Error recovery (5 min)

# Sady testů
npm run dev:scenarios      # All business scenarios (15 min)
npm run dev:all           # Kompletní test suite (20 min)
```

### Přímé spuštění s environment
```bash
# Set up environment and run
TEST_ENVIRONMENT=development npm run test:smoke
TEST_ENVIRONMENT=development npm run test:abraflexi
```

## ⚙️ Konfigurace

### .env soubor
```bash
TEST_ENVIRONMENT=development
BASE_URL=http://localhost/MultiFlexi/src/
DB_NAME=multiflexi_dev_test
DEBUG=true
HEADLESS=false
```

### Automatická konfigurace
Script `run-dev.sh` automaticky:
- Ověří konfiguraci pro development
- Nastaví správné environment variables
- Zkontroluje závislosti
- Nabídne menu s testy

## 📊 Výstupy testů

### Environment info
```
🖥️ MultiFlexi Development Environment Tests
==========================================
🌐 URL: http://localhost/MultiFlexi/src/
🌍 ENVIRONMENT INFO:
==================================================
📋 Environment: development
📝 Description: Rozpracované zdrojové kódy
🌐 Base URL: http://localhost/MultiFlexi/src/
🗄️ Database: multiflexi_dev_test @ localhost
🔧 Debug mode: ON
👤 Headless: NO
==================================================
```

### Detailní debug výstupy
Při `DEBUG=true` dostanete:
- Real-time kroky testování
- WebDriver akce
- Database operace  
- Browser logs
- Screenshots při chybách

## 🧪 Available test scenarios

### 🔥 Smoke Test (2 min)
Rychlá kontrola základní funkčnosti:
- Načtení stránky
- Registrace admin účtu
- Přihlášení
- Dashboard access
- Základní navigace

### ⭐⭐⭐ AbraFlexi Complete Workflow (5 min)
Kompletní business proces:
- Setup admin environment
- Vytvoření firmy "DEMO s.r.o."
- Konfigurace AbraFlexi credentials
- Přiřazení aplikací
- RunTemplate pro bankovní import
- Spuštění a monitoring jobu

### ⭐⭐ Multi-Company Setup (4 min)
Test více firem:
- Vytvoření 3 různých firem
- Izolované credentials
- Samostatné joby
- Datová separace

### ⭐⭐ Job Error Recovery (5 min)
Error handling:
- Různé typy chyb
- Diagnostika problémů
- Retry mechanismy
- Recovery workflow

## 🔧 Troubleshooting

### Zkontrolovat development setup
```bash
# Ověřit že development kódy existují
ls -la /var/www/html/MultiFlexi/src/

# Zkontrolovat Apache konfiguraci
curl -I http://localhost/MultiFlexi/src/
```

### Database problémy
```bash
# Zkontrolovat test databázi
mysql -e "SHOW DATABASES LIKE 'multiflexi_dev_test';"

# Vyčistit test databázi
npm run cleanup-db
```

### Debug mode
```bash
# Spustit s extra debug informacemi
DEBUG=true VERBOSE_LOGGING=true npm run dev:smoke

# Spustit bez headless (vidět browser)
HEADLESS=false npm run dev:smoke
```

## 🎯 Doporučené workflow

### Rychlá kontrola (daily)
```bash
./run-dev.sh
# Vybrat: 1 (Smoke Test)
```

### Před commitem (weekly)
```bash
npm run dev:abraflexi
```

### Kompletní validace (před release)
```bash
npm run dev:all
```

## 📝 Poznámky

- **Debug mode je zapnutý** - vidíte detailní výstupy
- **Headless je vypnutý** - vidíte browser akce  
- **Screenshots při chybách** - automatické ukládání
- **Browser logs** - zachycení JS chyb
- **Phinx migrace** - plné database schema