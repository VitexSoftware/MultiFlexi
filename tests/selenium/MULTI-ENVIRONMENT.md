# MultiFlexi Multi-Environment Testing

Testování na třech různých prostředích MultiFlexi současně.

## 🌍 Dostupná Prostředí

### 1📋 Description: Source code in development **Development** - Source code in development
- **URL:** `http://localhost/MultiFlexi/src/`
- **Účel:** Testování aktuálního vývoje
- **Databáze:** `multiflexi_dev_test`
- **Migrace:** Automatické přes Phinx

### 2. **Local** - Nainstalováno z Debian balíčku  
- **URL:** `http://localhost/multiflexi/`
- **Účel:** Testování produkční verze lokálně
- **Databáze:** `multiflexi_local_test`
- **Migrace:** Automatické přes Phinx

### 3. **Staging** - Testing server
- **URL:** `https://vyvojar.spoje.net/multiflexi/`
- **Účel:** Testování na vzdáleném serveru
- **Databáze:** `multiflexi_staging_test` 
- **Migrace:** Základní schema (bez Phinx)

## 🚀 Running Multi-Environment Tests

### Interaktivní Menu
```bash
# Spustit interaktivní výběr prostředí a testů
./run-multi-env.sh
```

### NPM Scripts pro konkrétní prostředí

```bash
# Smoke testy na jednotlivých prostředích
npm run test:dev:smoke          # Development prostředí
npm run test:local:smoke        # Local prostředí  
npm run test:staging:smoke      # Staging prostředí

# AbraFlexi workflow na jednotlivých prostředích
npm run test:dev:abraflexi      # Development prostředí
npm run test:local:abraflexi    # Local prostředí
npm run test:staging:abraflexi  # Staging prostředí

# Všechna prostředí postupně
npm run test:all-envs:smoke     # Smoke test na všech prostředích
npm run test:all-envs:scenarios # Všechny scénáře na všech prostředích
```

### Environment Variables

```bash
# Přepnout prostředí přes environment variable
TEST_ENVIRONMENT=development npm run test:smoke
TEST_ENVIRONMENT=local npm run test:abraflexi  
TEST_ENVIRONMENT=staging npm run test:scenarios
```

## ⚙️ Konfigurace Prostředí

### .env konfigurace
Každé prostředí má vlastní sadu proměnných v `.env` souboru:

```bash
# Development Environment
DEVELOPMENT_BASE_URL=http://localhost/MultiFlexi/src/
DEVELOPMENT_DB_NAME=multiflexi_dev_test
DEVELOPMENT_DB_HOST=localhost

# Local Environment  
LOCAL_BASE_URL=http://localhost/multiflexi/
LOCAL_DB_NAME=multiflexi_local_test
LOCAL_DB_HOST=localhost

# Staging Environment
STAGING_BASE_URL=https://vyvojar.spoje.net/multiflexi/
STAGING_DB_NAME=multiflexi_staging_test
STAGING_DB_HOST=vyvojar.spoje.net
```

### Automatická konfigurace
- **SSL certifikáty:** Automaticky ignorovány pro staging HTTPS
- **Timeouts:** Delší pro vzdálené servery
- **Databáze:** Environment-specific setup
- **Browser:** Headless mode pro CI

## 📊 Environment Manager

### Automatické rozpoznání prostředí
```javascript
const EnvironmentManager = require('./src/EnvironmentManager');
const envManager = EnvironmentManager.getInstance();

console.log(envManager.getEnvironment());        // 'development', 'local', 'staging'
console.log(envManager.getConfig().baseUrl);     // URL pro aktuální prostředí
console.log(envManager.isLocalEnvironment());    // true/false
```

### Přepínání prostředí za běhu
```javascript
envManager.switchEnvironment('staging');
// Automaticky přenačte konfiguraci
```

## 🧪 Test Scenarios by Environment

### Development - Plné testování
- ✅ Všechny scénáře
- ✅ Database migrations
- ✅ Debug mode dostupný
- ✅ Real-time development testing

### Local - Produkční simulace
- ✅ Všechny scénáře
- ✅ Package testing (debian .deb)
- ✅ Production-like environment
- ✅ Performance baseline

### Staging - Vzdálené testování
- ✅ Smoke test (vždy)
- ✅ Critical scenarios pouze
- ⚠️ Omezené database operace
- ⚠️ Síťová latence considerations

## 📈 Výstupy Multi-Environment Testů

### Jednotlivé prostředí
```bash
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

### Všechna prostředí
```bash
📊 SOUHRN TESTOVÁNÍ VŠECH PROSTŘEDÍ
====================================
✅ Úspěšných prostředí: 2/3
❌ Selhalo prostředí: 1/3
🚨 Selhala prostředí: staging
```

## 🛠️ Troubleshooting

### Development prostředí
```bash
# Check that source code exists
ls -la /var/www/html/MultiFlexi/src/

# Zkontrolovat Apache konfiguraci
sudo a2ensite multiflexi-dev
sudo systemctl reload apache2
```

### Local prostředí  
```bash
# Zkontrolovat instalaci balíčku
dpkg -l | grep multiflexi

# Restartovat služby
sudo systemctl restart multiflexi
sudo systemctl status multiflexi
```

### Staging prostředí
```bash
# Zkontrolovat síťové připojení
curl -I https://vyvojar.spoje.net/multiflexi/

# Test SSL certifikátu
openssl s_client -connect vyvojar.spoje.net:443 -servername vyvojar.spoje.net
```

### Database problémy
```bash
# Zkontrolovat databáze pro všechna prostředí
mysql -e "SHOW DATABASES LIKE 'multiflexi_%_test';"

# Vyčistit všechny test databáze
npm run cleanup-db
```

## 📋 Best Practices

### Pořadí testování
1. **Development first** - Rychlé feedback pro vývoj
2. **Local second** - Ověření package integrity  
3. **Staging last** - Finální validace

### Selective testing
```bash
# Smoke test na všech prostředích (rychlé)
npm run test:all-envs:smoke

# Full scenarios pouze na development
TEST_ENVIRONMENT=development npm run test:scenarios

# Critical scenarios na staging
TEST_ENVIRONMENT=staging npm run test:abraflexi
```

### CI/CD Integration
```yaml
# GitHub Actions example
test-multi-env:
  runs-on: ubuntu-latest
  strategy:
    matrix:
      environment: [development, local]
  steps:
    - name: Run tests
      run: TEST_ENVIRONMENT=${{ matrix.environment }} npm run test:smoke
```

## 🎯 Použití

### Rychlá validace
```bash
# Jen smoke test všude (~ 6 minut)
./run-multi-env.sh
# Vybrat: 4 (ALL) -> A (Smoke)
```

### Kompletní validace
```bash  
# AbraFlexi workflow všude (~ 15 minut)
./run-multi-env.sh
# Vybrat: 4 (ALL) -> B (AbraFlexi)
```

### Targeted testing
```bash
# Jen staging server kritický test (~ 5 minut)
TEST_ENVIRONMENT=staging npm run test:abraflexi
```

## 🎉 Výhody Multi-Environment Testingu

- **🔍 Detekce environment-specific bugů**
- **📦 Validace packaging a deployment**  
- **🌐 Test síťové stability a latence**
- **🔒 HTTPS/SSL problém detection**
- **📊 Performance comparison across environments**
- **🚀 Confidence v production deployment**