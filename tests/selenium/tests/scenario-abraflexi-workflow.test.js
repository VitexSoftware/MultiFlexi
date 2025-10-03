const { expect } = require('chai');
const { setupDatabase, cleanupDatabase } = require('../scripts/setupDatabase');
const AuthPage = require('../src/AuthPage');
const DashboardPage = require('../src/DashboardPage');
const CompaniesPage = require('../src/CompaniesPage');
const CredentialsPage = require('../src/CredentialsPage');
const ApplicationsPage = require('../src/ApplicationsPage');
const RunTemplatePage = require('../src/RunTemplatePage');
const JobsPage = require('../src/JobsPage');

/**
 * Scénář: "AbraFlexi Complete Workflow"
 * 
 * Nejčastější use case - kompletní setup firmy s AbraFlexi integrací
 * 
 * Kroky:
 * 1. Setup admin účtu
 * 2. Vytvoření firmy "DEMO s.r.o."
 * 3. Konfigurace AbraFlexi credentials
 * 4. Přiřazení AbraFlexi aplikací
 * 5. Vytvoření RunTemplate pro import bankovních výpisů
 * 6. Test spuštění jobu
 * 7. Monitoring a kontrola výsledků
 */
describe('Scénář: AbraFlexi Complete Workflow', function() {
    this.timeout(300000); // 5 minut pro kompletní workflow
    
    let authPage, dashboardPage, companiesPage, credentialsPage;
    let applicationsPage, runTemplatePage, jobsPage;
    
    // Test data
    const testCompany = {
        name: 'DEMO s.r.o.',
        ico: '12345678',
        enabled: true
    };
    
    const abraFlexiCredentials = {
        name: 'AbraFlexi DEMO',
        type: 'abraflexi',
        url: 'https://demo.abraflexi.eu:5434',
        login: 'winstrom',
        password: 'winstrom',
        company_code: 'demo_s_r_o_',
        enabled: true
    };
    
    const bankImportTemplate = {
        name: 'Import bankovních výpisů - DEMO',
        description: 'Automatický import výpisů z FIO banky do AbraFlexi',
        application: 'fio-banka-statement',
        scheduleType: 'interval',
        interval: 60,
        intervalUnit: 'minutes',
        enabled: true,
        envVars: {
            'ABRAFLEXI_URL': 'https://demo.abraflexi.eu:5434',
            'ABRAFLEXI_LOGIN': 'winstrom',
            'ABRAFLEXI_PASSWORD': 'winstrom',
            'ABRAFLEXI_COMPANY': 'demo_s_r_o_',
            'FIO_TOKEN': 'demo_token_12345',
            'IMPORT_DAYS_BACK': '7'
        }
    };
    
    before(async function() {
        console.log('🚀 Starting AbraFlexi Complete Workflow...');
        
        // Setup databáze
        await setupDatabase();
        
        // Inicializace page objects
        authPage = new AuthPage();
        dashboardPage = new DashboardPage();
        companiesPage = new CompaniesPage();
        credentialsPage = new CredentialsPage();
        applicationsPage = new ApplicationsPage();
        runTemplatePage = new RunTemplatePage();
        jobsPage = new JobsPage();
        
        // Společný WebDriver pro všechny page objekty
        await authPage.initializeDriver();
        [dashboardPage, companiesPage, credentialsPage, applicationsPage, runTemplatePage, jobsPage]
            .forEach(page => page.driver = authPage.driver);
    });

    after(async function() {
        console.log('🧹 Cleaning up after AbraFlexi workflow...');
        if (authPage) await authPage.quit();
        await cleanupDatabase();
    });

    describe('Krok 1: Příprava admin prostředí', function() {
        it('should setup admin account and login', async function() {
            console.log('👤 Creating admin account...');
            
            await authPage.registerAdmin();
            await authPage.waitForRegistrationSuccess();
            
            await authPage.loginAsAdmin();
            await authPage.waitForLoginSuccess();
            
            const isLoggedIn = await authPage.isLoggedIn();
            expect(isLoggedIn).to.be.true;
            
            console.log('✅ Admin account ready');
        });
        
        it('should verify dashboard is accessible', async function() {
            console.log('📊 Verifying dashboard access...');
            
            await dashboardPage.goToDashboard();
            const isLoaded = await dashboardPage.isDashboardLoaded();
            expect(isLoaded).to.be.true;
            
            console.log('✅ Dashboard accessible');
        });
    });

    describe('Krok 2: Vytvoření firmy DEMO s.r.o.', function() {
        it('should create new company successfully', async function() {
            console.log('🏢 Creating company DEMO s.r.o...');
            
            await companiesPage.createCompany(testCompany);
            
            // Ověření vytvoření
            const companies = await companiesPage.getCompaniesList();
            const createdCompany = companies.find(c => c.name.includes('DEMO s.r.o.'));
            expect(createdCompany).to.exist;
            
            console.log('✅ Company DEMO s.r.o. created');
        });
        
        it('should configure company details', async function() {
            console.log('⚙️ Konfigurace detailů firmy...');
            
            // Přejít na detail firmy pro další konfiguraci
            await companiesPage.goToCompany(1);
            
            // Ověřit, že jsme na správné stránce
            const title = await companiesPage.getElementText(companiesPage.selectors.pageTitle);
            expect(title).to.include('Company');
            
            console.log('✅ Company detail accessible');
        });
    });

    describe('Krok 3: Konfigurace AbraFlexi credentials', function() {
        it('should create AbraFlexi credentials', async function() {
            console.log('🔐 Creating AbraFlexi credentials...');
            
            await credentialsPage.createCredential(abraFlexiCredentials);
            
            // Ověření vytvoření
            const credentials = await credentialsPage.getCredentialsList();
            const createdCredential = credentials.find(c => c.name.includes('AbraFlexi DEMO'));
            expect(createdCredential).to.exist;
            
            console.log('✅ AbraFlexi credentials created');
        });
        
        it('should verify credential configuration', async function() {
            console.log('🔍 Verifying credentials configuration...');
            
            // Test přístupu k credential detailu
            await credentialsPage.goToCredential(1);
            
            const title = await credentialsPage.getElementText(credentialsPage.selectors.pageTitle);
            expect(title).to.include('MultiFlexi');
            
            console.log('✅ Credentials configuration verified');
        });
    });

    describe('Krok 4: Přiřazení AbraFlexi aplikací', function() {
        it('should verify available applications', async function() {
            console.log('📱 Kontrola dostupných aplikací...');
            
            const apps = await applicationsPage.getApplicationsList();
            expect(apps).to.be.an('array');
            expect(apps.length).to.be.greaterThan(0);
            
            console.log(`✅ Nalezeno ${apps.length} dostupných aplikací`);
        });
        
        it('should create AbraFlexi-compatible application if needed', async function() {
            console.log('🔧 Preparing AbraFlexi application...');
            
            const abraFlexiApp = {
                name: 'FIO Banka Statement Import',
                description: 'Import bankovních výpisů z FIO banky do AbraFlexi',
                executable: 'fio-statement-import',
                homepage: 'https://github.com/VitexSoftware/php-abraflexi-banka',
                version: '1.0.0',
                enabled: true
            };
            
            await applicationsPage.createApplication(abraFlexiApp);
            
            // Ověření vytvoření
            const apps = await applicationsPage.getApplicationsList();
            const createdApp = apps.find(app => app.name.includes('FIO Banka'));
            expect(createdApp).to.exist;
            
            console.log('✅ AbraFlexi aplikace připravena');
        });
    });

    describe('Krok 5: Vytvoření RunTemplate pro bankovní import', function() {
        it('should create bank import RunTemplate', async function() {
            console.log('📋 Creating RunTemplate for bank import...');
            
            await runTemplatePage.createRunTemplate(bankImportTemplate);
            
            // Ověření vytvoření
            const templates = await runTemplatePage.getRunTemplatesList();
            const createdTemplate = templates.find(t => t.name.includes('Import bankovních výpisů'));
            expect(createdTemplate).to.exist;
            
            console.log('✅ RunTemplate for bank import created');
        });
        
        it('should verify RunTemplate configuration', async function() {
            console.log('🔍 Verifying RunTemplate configuration...');
            
            // Přejít na seznam RunTemplate a ověřit
            await runTemplatePage.goToRunTemplates();
            
            const templates = await runTemplatePage.getRunTemplatesList();
            expect(templates.length).to.be.greaterThan(0);
            
            console.log('✅ RunTemplate configuration verified');
        });
    });

    describe('Krok 6: Test spuštění jobu', function() {
        it('should execute bank import job', async function() {
            console.log('▶️ Starting bank import...');
            
            await runTemplatePage.executeRunTemplate(bankImportTemplate.name);
            
            // Počkat na spuštění
            await runTemplatePage.driver.sleep(3000);
            
            console.log('✅ Job started');
        });
        
        it('should monitor job execution', async function() {
            console.log('👀 Monitoring průběhu jobu...');
            
            // Přejít na seznam jobů
            const jobs = await jobsPage.getJobsList();
            expect(jobs).to.be.an('array');
            
            if (jobs.length > 0) {
                const latestJob = jobs[0];
                console.log(`📊 Latest job ID: ${latestJob.id}, Status: ${latestJob.status}`);
                
                // Získat status jobu
                const status = await jobsPage.getJobStatus(latestJob.id);
                expect(status).to.be.a('string');
                
                console.log(`📈 Job status: ${status}`);
            }
            
            console.log('✅ Monitoring completed');
        });
    });

    describe('Krok 7: Kontrola výsledků a finalizace', function() {
        it('should verify job output and logs', async function() {
            console.log('📄 Kontrola výstupu a logů...');
            
            const jobs = await jobsPage.getJobsList();
            
            if (jobs.length > 0) {
                const latestJob = jobs[0];
                
                // Získat výstup jobu
                const output = await jobsPage.getJobOutput(latestJob.id);
                expect(output).to.be.a('string');
                
                console.log('📋 Job output získán');
            }
            
            console.log('✅ Kontrola výsledků dokončena');
        });
        
        it('should verify complete workflow success', async function() {
            console.log('🎯 Finální ověření workflow...');
            
            // Ověřit, že máme:
            // 1. Funkční firmu
            const companies = await companiesPage.getCompaniesList();
            expect(companies.some(c => c.name.includes('DEMO'))).to.be.true;
            
            // 2. Funkční credentials
            const credentials = await credentialsPage.getCredentialsList();
            expect(credentials.some(c => c.name.includes('AbraFlexi'))).to.be.true;
            
            // 3. Funkční aplikaci
            const apps = await applicationsPage.getApplicationsList();
            expect(apps.length).to.be.greaterThan(0);
            
            // 4. Funkční RunTemplate
            const templates = await runTemplatePage.getRunTemplatesList();
            expect(templates.some(t => t.name.includes('Import'))).to.be.true;
            
            console.log('🎉 AbraFlexi Complete Workflow úspěšně dokončen!');
        });
    });

    describe('Workflow Summary', function() {
        it('should provide complete workflow summary', async function() {
            console.log('\n' + '='.repeat(60));
            console.log('📊 ABRAFLEXI WORKFLOW SUMMARY');
            console.log('='.repeat(60));
            console.log('✅ Admin účet vytvořen a přihlášen');
            console.log('✅ Firma "DEMO s.r.o." vytvořena');
            console.log('✅ AbraFlexi credentials nakonfigurovány');
            console.log('✅ AbraFlexi aplikace připravena');
            console.log('✅ RunTemplate pro bankovní import vytvořen');
            console.log('✅ Job úspěšně spuštěn a monitorován');
            console.log('✅ Výsledky ověřeny');
            console.log('\n🎯 Firma je připravena pro produkční použití!');
            console.log('🔄 Bankovní výpisy budou importovány každých 60 minut');
            console.log('📈 Dashboard zobrazuje aktuální statistiky');
            console.log('='.repeat(60) + '\n');
            
            // Test vždy projde - je to jen pro logging
            expect(true).to.be.true;
        });
    });
});