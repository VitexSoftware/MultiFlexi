# MultiFlexi Selenium Tests - Complete Page Coverage

## 📋 Přehled testů pro všechny stránky s `new PageTop`

Vytvořil jsem kompletní sadu Selenium testů pokrývající **VŠECHNY** stránky MultiFlexi obsahující `new PageTop`. Celkem **50+ stránek** rozdělených do logických skupin.

## 🗂️ Struktura testů

### 1. **Hlavní funkční testy**
- `auth.test.js` - Registrace, přihlášení, odhlášení
- `dashboard.test.js` - Dashboard, statistiky, rychlé akce
- `applications.test.js` - Správa aplikací, vytváření, editace
- `companies.test.js` - Správa firem, konfigurace
- `jobs.test.js` - Plánování jobů, monitoring, historie
- `credentials.test.js` - Správa přihlašovacích údajů
- `runtemplate.test.js` - Vytváření a správa RunTemplate

### 2. **Komprehenzivní testy**
- `all-pages.test.js` - Test všech 50+ stránek s PageTop
- `complete-integration.test.js` - Komplexní workflow test
- `multiflexi.e2e.test.js` - End-to-end scenáře

## 📄 Pokryté stránky

### Hlavní stránky
- ✅ `/index.php` - Úvodní stránka  
- ✅ `/main.php` - Hlavní stránka
- ✅ `/dashboard.php` - Dashboard
- ✅ `/about.php` - O aplikaci

### Správa uživatelů
- ✅ `/users.php` - Seznam uživatelů
- ✅ `/user.php` - Detail uživatele  
- ✅ `/createaccount.php` - Vytvoření účtu
- ✅ `/login.php` - Přihlášení
- ✅ `/logout.php` - Odhlášení
- ✅ `/passwordrecovery.php` - Obnovení hesla

### Aplikace a služby
- ✅ `/apps.php` - Seznam aplikací
- ✅ `/app.php` - Detail aplikace
- ✅ `/launch.php` - Spuštění aplikace
- ✅ `/actions.php` - Akce

### Firmy a zákazníci  
- ✅ `/companies.php` - Seznam firem
- ✅ `/company.php` - Detail firmy
- ✅ `/companysetup.php` - Nastavení firmy
- ✅ `/companyapp.php` - Aplikace firmy
- ✅ `/companyapps.php` - Seznam aplikací firmy
- ✅ `/companycreds.php` - Přihlašovací údaje firmy
- ✅ `/companydelete.php` - Smazání firmy
- ✅ `/customers.php` - Seznam zákazníků
- ✅ `/customer.php` - Detail zákazníka

### Joby a šablony
- ✅ `/joblist.php` - Historie jobů  
- ✅ `/job.php` - Detail jobu
- ✅ `/newjob.php` - Naplánování jobu
- ✅ `/queue.php` - Fronta jobů
- ✅ `/runtemplate.php` - RunTemplate
- ✅ `/runtemplateclone.php` - Klonování šablony
- ✅ `/schedule.php` - Plánování
- ✅ `/periodical.php` - Periodické úlohy

### Přihlašovací údaje
- ✅ `/credentials.php` - Seznam credentials
- ✅ `/credential.php` - Detail credential
- ✅ `/credentialclone.php` - Klonování credential
- ✅ `/credentialtypes.php` - Typy credentials
- ✅ `/credentialtype.php` - Detail typu credential
- ✅ `/credtypes.php` - Pomocníci typů

### Konfigurace
- ✅ `/conffield.php` - Konfigurační pole
- ✅ `/intervals.php` - Nastavení intervalů  
- ✅ `/servers.php` - Seznam serverů
- ✅ `/server.php` - Detail serveru
- ✅ `/custserviceconfig.php` - Vlastní konfigurace

### Systémové stránky
- ✅ `/status.php` - Stav systému
- ✅ `/requirements.php` - Systémové požadavky
- ✅ `/wizard.php` - Průvodce nastavením
- ✅ `/logs.php` - Logy
- ✅ `/search.php` - Vyhledávání
- ✅ `/template.php` - Šablony

### Moduly a rozšíření
- ✅ `/executors.php` - Executor moduly
- ✅ `/actionmodules.php` - Action moduly  
- ✅ `/envmods.php` - Environment moduly

### Další stránky
- ✅ `/adhoc.php` - Ad-hoc úlohy
- ✅ `/credup.php` - Vytvoření credentials

## 🚀 Running tests

### Jednotlivé skupiny testů
```bash
npm run test:auth          # Autentifikace
npm run test:dashboard     # Dashboard  
npm run test:applications  # Aplikace
npm run test:companies     # Firmy
npm run test:jobs          # Joby
npm run test:credentials   # Přihlašovací údaje
npm run test:runtemplate   # RunTemplates
npm run test:all-pages     # Všechny stránky (50+)
npm run test:integration   # Komplexní workflow
```

### Kompletní test suite
```bash
./run-tests.sh full        # Všechny testy s DB setup/cleanup
./run-tests.sh ci          # CI/CD režim (headless)
```

## 🎯 Co testy ověřují

### Pro každou stránku:
1. **Načtení stránky** bez chyb
2. **Struktura HTML** (body, title, headers)
3. **Navigace** a menu
4. **Ochrana** před neoprávněným přístupem
5. **Handling chyb** (neplatné ID, atd.)

### Funkční testy:
1. **CRUD operace** (Create, Read, Update, Delete)
2. **Formuláře** a validace
3. **Filtrování** a vyhledávání  
4. **Workflow** mezi stránkami
5. **Session management**

### Integrační testy:
1. **Komplexní workflow** napříč všemi stránkami
2. **Data consistency** mezi stránkami
3. **Cross-page navigation**
4. **End-to-end scenáře**

## 📊 Výsledky

Testy poskytují:
- ✅ **100% pokrytí** všech stránek s PageTop
- ✅ **Automatické screenshots** při chybách
- ✅ **Detailní reporty** výsledků
- ✅ **CI/CD ready** s GitHub Actions
- ✅ **Database cleanup** po testech

## 🔧 Konfigurace

Všechny testy jsou **plně konfigurovatelné** přes `.env`:
- URL aplikace
- Databázové připojení  
- Uživatelské účty
- Timeout hodnoty
- Debug módy

**Result: Complete test coverage of all 50+ MultiFlexi pages! 🎉**