# MultiFlexi Selenium Test Suite - Scenarios

Complete Selenium test suite with business scenarios for MultiFlexi web interface.

## 📋 Obsah

### 🔥 Smoke Test
Rychlé ověření základní funkčnosti systému před spuštěním hlavních testů.

### 🎯 Main Scenarios

#### 1. **AbraFlexi Complete Workflow** (`scenario-abraflexi-workflow.test.js`)
**Priorita: ⭐⭐⭐ VYSOKÁ**

Nejčastější use case - kompletní setup firmy s AbraFlexi integrací.

**Kroky:**
- Setup admin účtu
- Vytvoření firmy "DEMO s.r.o."
- Konfigurace AbraFlexi credentials
- Přiřazení AbraFlexi aplikací
- Vytvoření RunTemplate pro import bankovních výpisů
- Test spuštění jobu
- Monitoring a kontrola výsledků

**Očekávaný výsledek:** Plně funkční firma připravená pro produkci s automatickým importem bankovních výpisů každých 60 minut.

#### 2. **Multi-Company Setup** (`scenario-multi-company.test.js`) 
**Priorita: ⭐⭐ STŘEDNÍ**

Testuje správu více firem v jednom MultiFlexi.

**Kroky:**
- Vytvoření 3 firem s různými profily
- Rozdělení credentials podle firem
- Konfigurace isolovaných jobů
- Test přepínání mezi firmami
- Ověření datové izolace

**Očekávaný výsledek:** Doložení, že MultiFlexi umí spravovat více nezávislých firem současně.

#### 3. **Job Error Recovery** (`scenario-error-recovery.test.js`)
**Priorita: ⭐⭐ STŘEDNÍ**

Testuje robustnost systému při chybách.

**Kroky:**
- Vytvoření job s nesprávnou konfigurací
- Monitorování chybových stavů
- Diagnostika a řešení problémů
- Test retry mechanismů
- Ověření alertů a notifikací

**Očekávaný výsledek:** Ověření, že systém správně zvládá chybové stavy a poskytuje užitečné informace pro diagnostiku.

## 🚀 Spuštění Testů

### Prerekvizity

```bash
# Nainstalovat závislosti
cd /home/vitex/Projects/Multi/MultiFlexi/tests/selenium
npm install

# Set up environment
cp .env.example .env
# Editovat .env soubor s vašimi nastaveními
```

### Základní spuštění

```bash
# Smoke test - rychlá kontrola funkčnosti
npm run test:smoke

# Všechny page testy
npm run test:pages

# All business scenarios
npm run test:scenarios

# Kompletní test suite
npm run test:all
```

### Individual scenarios

```bash
# AbraFlexi Complete Workflow (⭐⭐⭐)
npm run test:abraflexi

# Multi-Company Setup (⭐⭐)
npm run test:multicompany

# Job Error Recovery (⭐⭐)
npm run test:errors
```

### CI/CD

```bash
# Pro CI pipeline (pouze smoke test)
npm run test:ci
```

## 📊 Výstupy Testů

### Console Output
Testy poskytují detailní real-time výstup s:
- ✅ Úspěšné kroky
- ❌ Selhání s detaily
- 📊 Průběžné statistiky
- 🎯 Souhrnné zprávy

### Očekávané Chování

#### AbraFlexi Workflow
```
🚀 Starting AbraFlexi Complete Workflow...
👤 Vytváření admin účtu...
✅ Admin účet připraven
🏢 Vytváření firmy DEMO s.r.o...
✅ Firma DEMO s.r.o. vytvořena
🔐 Vytváření AbraFlexi credentials...
✅ AbraFlexi credentials vytvořeny
📱 Příprava AbraFlexi aplikace...
✅ AbraFlexi aplikace připravena
📋 Vytváření RunTemplate pro bankovní import...
✅ RunTemplate pro bankovní import vytvořen
▶️ Starting bank import...
✅ Job spuštěn
👀 Monitoring průběhu jobu...
📊 Nejnovější job ID: 1, Status: running
📄 Kontrola výstupu a logů...
🎯 Finální ověření workflow...
🎉 AbraFlexi Complete Workflow úspěšně dokončen!
```

#### Multi-Company Setup
```
🏢 Vytváření ABC Trading s.r.o...
✅ ABC Trading s.r.o. vytvořena
🏢 Vytváření XYZ Services a.s...
✅ XYZ Services a.s. vytvořena
🏢 Vytváření DEF Manufacturing spol. s r.o...
✅ DEF Manufacturing spol. s r.o. vytvořena
📊 MULTI-COMPANY SETUP SUMMARY
🏢 Firma 1: ABC Trading s.r.o.
   📋 IČO: 11111111
   🏷️ Typ: obchodní společnost
   🔐 Credentials: 1
   📋 Templates: 1
   ▶️ Jobs: 1
```

#### Error Recovery
```
🛠️ Demonstrace workflow řešení chyb...
🔍 ANALÝZA CHYB:
📋 Job 1:
   📊 Status: failed
   🔍 Má chybu: ANO
   📄 Output: Connection refused to mysql://invalid:invalid@nonexistent:3306
📊 KATEGORIE CHYB:
🔌 Connection errors: 1
⏰ Timeout errors: 1
📦 Dependency errors: 1
```

## ⚙️ Konfigurace

### Environment Variables (.env)

```bash
# MultiFlexi URL
BASE_URL=http://localhost/multiflexi

# Test Database
TEST_DB_HOST=localhost
TEST_DB_PORT=3306
TEST_DB_NAME=multiflexi_test
TEST_DB_USER=multiflexi_test
TEST_DB_PASS=test_password

# Admin credentials for testing
ADMIN_USERNAME=admin
ADMIN_PASSWORD=admin123
ADMIN_EMAIL=admin@multiflexi.test

# Browser settings
HEADLESS=false
BROWSER_TIMEOUT=30000
```

### Customizace Testů

Scenarios can be customized by modifying test data in individual files:

```javascript
// scenario-abraflexi-workflow.test.js
const testCompany = {
    name: 'VAŠE FIRMA s.r.o.',
    ico: 'VAŠE_IČO',
    enabled: true
};

const abraFlexiCredentials = {
    url: 'https://vaše-abraflexi.url',
    login: 'váš_login',
    password: 'vaše_heslo',
    company_code: 'váš_kód_firmy'
};
```

## 🛠️ Troubleshooting

### Časté Problémy

1. **Testy selžou na timeout**
   ```bash
   # Zvýšit timeout v package.json nebo přidat do testu:
   this.timeout(600000); // 10 minut
   ```

2. **ChromeDriver problémy**
   ```bash
   # Aktualizovat chromedriver
   npm install chromedriver@latest
   ```

3. **Database connection chyby**
   ```bash
   # Zkontrolovat .env soubor
   # Ověřit, že test databáze existuje
   mysql -u root -p -e "CREATE DATABASE multiflexi_test;"
   ```

4. **Port už je používán**
   ```bash
   # Změnit BASE_URL v .env
   BASE_URL=http://localhost:8080/multiflexi
   ```

### Debug Mode

```bash
# Spustit s debug výstupem
DEBUG=true npm run test:abraflexi

# Spustit bez headless mode (vidět browser)
HEADLESS=false npm run test:abraflexi
```

## 📈 Metriky a KPI

### Úspěšnost Testů
- **Smoke Test:** Měl by projít vždy (< 2 minuty)
- **AbraFlexi Workflow:** Měl by projít v 90% případů (< 5 minut)
- **Multi-Company:** Měl by projít v 85% případů (< 4 minuty)
- **Error Recovery:** Měl by projít vždy (testuje chyby) (< 5 minut)

### Pokrytí
- **Základní funkce:** 100% (auth, navigation, CRUD)
- **Business procesy:** 80% (nejčastější use cases)
- **Error handling:** 60% (hlavní chybové stavy)

## 🎯 Roadmap

### Phase 2 - Extended Scenarios (future)
4. **Scheduled Jobs Management** - Test plánování a řízení opakovaných úloh
5. **Multi-User Collaboration** - Test pro více uživatelů současně
6. **Performance Under Load** - Zátěžové testování
7. **API Integration Testing** - Test API endpointů
8. **Mobile Responsiveness** - Test mobilních zařízení
9. **Security Penetration** - Základní security testy
10. **Backup & Restore** - Test zálohování a obnovy
11. **Upgrade Scenarios** - Test upgradu systému
12. **Third-party Integrations** - Test externích integrací

### Fáze 3 - Pokročilé Funkce
- Visual regression testing
- Accessibility (a11y) testing  
- Cross-browser compatibility
- Automated performance benchmarking
- Integration s monitoring systémy

## 👥 Přispívání

To add new scenarios:

1. Vytvořte nový soubor `scenario-název.test.js`
2. Použijte existující Page Objects ze `src/` adresáře
3. Follow naming convention and structure of existing scenarios
4. Přidejte script do `package.json`
5. Aktualizujte tuto dokumentaci

## 📞 Podpora

- **Issues:** https://github.com/VitexSoftware/MultiFlexi/issues
- **Documentation:** https://multiflexi.eu/docs
- **Email:** info@vitexsoftware.cz