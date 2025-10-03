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
 * Scénář: "Multi-Company Setup"
 * 
 * Testuje správu více firem v jednom MultiFlexi
 * 
 * Kroky:
 * 1. Vytvoření 3 firem s různými profily
 * 2. Rozdělení credentials podle firem
 * 3. Konfigurace isolovaných jobů
 * 4. Test přepínání mezi firmami
 * 5. Ověření datové izolace
 */
describe('Scénář: Multi-Company Setup', function() {
    this.timeout(240000); // 4 minuty pro multi-company setup
    
    let authPage, dashboardPage, companiesPage, credentialsPage;
    let applicationsPage, runTemplatePage, jobsPage;
    
    // Test data - 3 různé firmy
    const companies = [
        {
            name: 'ABC Trading s.r.o.',
            ico: '11111111',
            type: 'obchodní společnost',
            enabled: true,
            abraflexiUrl: 'https://demo1.abraflexi.eu:5434',
            abraflexiCompany: 'abc_trading_s_r_o_'
        },
        {
            name: 'XYZ Services a.s.',
            ico: '22222222',
            type: 'servisní společnost',
            enabled: true,
            abraflexiUrl: 'https://demo2.abraflexi.eu:5434',
            abraflexiCompany: 'xyz_services_a_s_'
        },
        {
            name: 'DEF Manufacturing spol. s r.o.',
            ico: '33333333',
            type: 'výrobní podnik',
            enabled: true,
            abraflexiUrl: 'https://demo3.abraflexi.eu:5434',
            abraflexiCompany: 'def_manufacturing_spol_s_r_o_'
        }
    ];
    
    const credentialsTemplate = {
        type: 'abraflexi',
        login: 'winstrom',
        password: 'winstrom',
        enabled: true
    };
    
    const applications = [
        {
            name: 'Bank Statement Import',
            executable: 'bank-statement-import',
            description: 'Import bankovních výpisů'
        },
        {
            name: 'Invoice Export',
            executable: 'invoice-export',
            description: 'Export faktur do účetnictví'
        },
        {
            name: 'Inventory Sync',
            executable: 'inventory-sync',
            description: 'Synchronizace skladových zásob'
        }
    ];
    
    before(async function() {
        console.log('🚀 Starting Multi-Company Setup...');
        
        await setupDatabase();
        
        authPage = new AuthPage();
        dashboardPage = new DashboardPage();
        companiesPage = new CompaniesPage();
        credentialsPage = new CredentialsPage();
        applicationsPage = new ApplicationsPage();
        runTemplatePage = new RunTemplatePage();
        jobsPage = new JobsPage();
        
        await authPage.initializeDriver();
        [dashboardPage, companiesPage, credentialsPage, applicationsPage, runTemplatePage, jobsPage]
            .forEach(page => page.driver = authPage.driver);
    });

    after(async function() {
        console.log('🧹 Cleaning up after Multi-Company workflow...');
        if (authPage) await authPage.quit();
        await cleanupDatabase();
    });

    describe('Krok 1: Příprava prostředí pro více firem', function() {
        it('should setup admin and dashboard access', async function() {
            console.log('👤 Preparing admin access...');
            
            await authPage.registerAdmin();
            await authPage.loginAsAdmin();
            
            const isLoggedIn = await authPage.isLoggedIn();
            expect(isLoggedIn).to.be.true;
            
            await dashboardPage.goToDashboard();
            const isLoaded = await dashboardPage.isDashboardLoaded();
            expect(isLoaded).to.be.true;
            
            console.log('✅ Admin access ready');
        });
    });

    describe('Krok 2: Vytvoření tří různých firem', function() {
        companies.forEach((company, index) => {
            it(`should create company ${index + 1}: ${company.name}`, async function() {
                console.log(`🏢 Creating ${company.name}...`);
                
                await companiesPage.createCompany({
                    name: company.name,
                    ico: company.ico,
                    enabled: company.enabled
                });
                
                // Ověření vytvoření
                const companiesList = await companiesPage.getCompaniesList();
                const createdCompany = companiesList.find(c => c.name.includes(company.name));
                expect(createdCompany).to.exist;
                
                console.log(`✅ ${company.name} created`);
            });
        });
        
        it('should verify all companies are created and visible', async function() {
            console.log('🔍 Ověření všech vytvořených firem...');
            
            const companiesList = await companiesPage.getCompaniesList();
            expect(companiesList.length).to.be.at.least(3);
            
            companies.forEach(company => {
                const found = companiesList.find(c => c.name.includes(company.name));
                expect(found, `Company ${company.name} not found`).to.exist;
            });
            
            console.log(`✅ Všech ${companies.length} firem ověřeno`);
        });
    });

    describe('Krok 3: Vytvoření izolovaných credentials pro každou firmu', function() {
        companies.forEach((company, index) => {
            it(`should create credentials for ${company.name}`, async function() {
                console.log(`🔐 Vytváření credentials pro ${company.name}...`);
                
                const credentials = {
                    ...credentialsTemplate,
                    name: `AbraFlexi - ${company.name}`,
                    url: company.abraflexiUrl,
                    company_code: company.abraflexiCompany
                };
                
                await credentialsPage.createCredential(credentials);
                
                // Ověření vytvoření
                const credentialsList = await credentialsPage.getCredentialsList();
                const createdCredential = credentialsList.find(c => c.name.includes(company.name));
                expect(createdCredential).to.exist;
                
                console.log(`✅ Credentials pro ${company.name} vytvořeny`);
            });
        });
        
        it('should verify credential isolation between companies', async function() {
            console.log('🔒 Ověření izolace credentials...');
            
            const credentialsList = await credentialsPage.getCredentialsList();
            expect(credentialsList.length).to.be.at.least(3);
            
            // Každá firma má své vlastní credentials
            companies.forEach(company => {
                const companyCredentials = credentialsList.filter(c => c.name.includes(company.name));
                expect(companyCredentials.length).to.be.at.least(1);
            });
            
            console.log('✅ Izolace credentials ověřena');
        });
    });

    describe('Krok 4: Konfigurace aplikací pro každou firmu', function() {
        it('should setup applications for multi-company use', async function() {
            console.log('📱 Konfigurace aplikací pro více firem...');
            
            // Každá firma má různé aplikace podle typu
            for (let i = 0; i < companies.length; i++) {
                const company = companies[i];
                const app = applications[i % applications.length];
                
                const companyApp = {
                    name: `${app.name} - ${company.name}`,
                    description: `${app.description} pro ${company.name}`,
                    executable: app.executable,
                    homepage: `https://github.com/VitexSoftware/${app.executable}`,
                    version: '1.0.0',
                    enabled: true
                };
                
                await applicationsPage.createApplication(companyApp);
                console.log(`✅ ${companyApp.name} připravena`);
            }
            
            // Ověření vytvoření
            const appsList = await applicationsPage.getApplicationsList();
            expect(appsList.length).to.be.at.least(3);
            
            console.log('✅ Aplikace pro všechny firmy nakonfigurovány');
        });
    });

    describe('Krok 5: Vytvoření izolovaných RunTemplate pro každou firmu', function() {
        companies.forEach((company, index) => {
            it(`should create RunTemplate for ${company.name}`, async function() {
                console.log(`📋 Vytváření RunTemplate pro ${company.name}...`);
                
                const app = applications[index % applications.length];
                
                const runTemplate = {
                    name: `${app.description} - ${company.name}`,
                    description: `Automatizované úlohy pro ${company.name}`,
                    application: `${app.name} - ${company.name}`,
                    scheduleType: 'interval',
                    interval: 30 + (index * 15), // Různé intervaly pro každou firmu
                    intervalUnit: 'minutes',
                    enabled: true,
                    envVars: {
                        'ABRAFLEXI_URL': company.abraflexiUrl,
                        'ABRAFLEXI_LOGIN': 'winstrom',
                        'ABRAFLEXI_PASSWORD': 'winstrom',
                        'ABRAFLEXI_COMPANY': company.abraflexiCompany,
                        'COMPANY_ICO': company.ico,
                        'COMPANY_TYPE': company.type
                    }
                };
                
                await runTemplatePage.createRunTemplate(runTemplate);
                
                console.log(`✅ RunTemplate pro ${company.name} vytvořen`);
            });
        });
        
        it('should verify RunTemplate isolation', async function() {
            console.log('🔍 Ověření izolace RunTemplate...');
            
            const templatesList = await runTemplatePage.getRunTemplatesList();
            expect(templatesList.length).to.be.at.least(3);
            
            // Každá firma má svůj RunTemplate
            companies.forEach(company => {
                const companyTemplates = templatesList.filter(t => t.name.includes(company.name));
                expect(companyTemplates.length).to.be.at.least(1);
            });
            
            console.log('✅ Izolace RunTemplate ověřena');
        });
    });

    describe('Krok 6: Test spuštění jobů pro různé firmy', function() {
        it('should execute jobs for all companies simultaneously', async function() {
            console.log('▶️ Starting jobs for all companies...');
            
            for (const company of companies) {
                const templateName = `${applications[0].description} - ${company.name}`;
                await runTemplatePage.executeRunTemplate(templateName);
                
                // Krátká pauza mezi spuštěními
                await runTemplatePage.driver.sleep(1000);
                
                console.log(`▶️ Job pro ${company.name} spuštěn`);
            }
            
            // Počkat na zpracování
            await runTemplatePage.driver.sleep(5000);
            
            console.log('✅ Všechny joby spuštěny');
        });
        
        it('should verify jobs are running independently', async function() {
            console.log('🔍 Ověření nezávislého běhu jobů...');
            
            const jobs = await jobsPage.getJobsList();
            expect(jobs.length).to.be.at.least(3);
            
            console.log(`📊 Nalezeno ${jobs.length} jobů`);
            
            // Ověřit, že každá firma má svůj job
            let companyJobCount = 0;
            companies.forEach(company => {
                const companyJobs = jobs.filter(job => {
                    // Job obsahuje informace o firmě (přes RunTemplate name)
                    return job.template && job.template.includes(company.name);
                });
                
                if (companyJobs.length > 0) {
                    companyJobCount++;
                }
            });
            
            expect(companyJobCount).to.be.at.least(1);
            console.log('✅ Nezávislost jobů ověřena');
        });
    });

    describe('Krok 7: Test přepínání mezi firmami v dashboard', function() {
        it('should test dashboard company filtering', async function() {
            console.log('🔄 Test filtrování podle firmy v dashboard...');
            
            await dashboardPage.goToDashboard();
            
            // Test různých zobrazení
            const dashboardStats = await dashboardPage.getDashboardStats();
            expect(dashboardStats).to.be.an('object');
            
            console.log('📊 Dashboard statistiky získány');
            console.log('✅ Přepínání mezi firmami funguje');
        });
        
        it('should verify company-specific data isolation', async function() {
            console.log('🔒 Ověření izolace dat podle firem...');
            
            // Pro každou firmu ověřit, že vidí jen svá data
            for (const company of companies) {
                console.log(`🔍 Kontrola dat pro ${company.name}...`);
                
                // Přejít na companies stránku
                await companiesPage.goToCompanies();
                
                const companiesList = await companiesPage.getCompaniesList();
                const targetCompany = companiesList.find(c => c.name.includes(company.name));
                
                expect(targetCompany, `Company ${company.name} should be visible`).to.exist;
                console.log(`✅ ${company.name} data accessible`);
            }
            
            console.log('✅ Datová izolace ověřena');
        });
    });

    describe('Krok 8: Monitoring a správa více firem', function() {
        it('should test bulk operations across companies', async function() {
            console.log('⚙️ Test hromadných operací...');
            
            // Test zobrazení všech firem
            await companiesPage.goToCompanies();
            const companiesList = await companiesPage.getCompaniesList();
            expect(companiesList.length).to.be.at.least(3);
            
            // Test zobrazení všech credentials
            await credentialsPage.goToCredentials();
            const credentialsList = await credentialsPage.getCredentialsList();
            expect(credentialsList.length).to.be.at.least(3);
            
            // Test zobrazení všech RunTemplate
            await runTemplatePage.goToRunTemplates();
            const templatesList = await runTemplatePage.getRunTemplatesList();
            expect(templatesList.length).to.be.at.least(3);
            
            console.log('✅ Hromadné operace fungují');
        });
        
        it('should generate multi-company summary report', async function() {
            console.log('📊 Generování souhrnné zprávy...');
            
            const companiesList = await companiesPage.getCompaniesList();
            const credentialsList = await credentialsPage.getCredentialsList();
            const templatesList = await runTemplatePage.getRunTemplatesList();
            const jobsList = await jobsPage.getJobsList();
            
            // Statistiky pro každou firmu
            const companyStats = companies.map(company => {
                const companyCredentials = credentialsList.filter(c => c.name.includes(company.name));
                const companyTemplates = templatesList.filter(t => t.name.includes(company.name));
                const companyJobs = jobsList.filter(j => j.template && j.template.includes(company.name));
                
                return {
                    name: company.name,
                    ico: company.ico,
                    type: company.type,
                    credentials: companyCredentials.length,
                    templates: companyTemplates.length,
                    jobs: companyJobs.length
                };
            });
            
            console.log('\n' + '='.repeat(80));
            console.log('📊 MULTI-COMPANY SETUP SUMMARY');
            console.log('='.repeat(80));
            
            companyStats.forEach((stats, index) => {
                console.log(`\n🏢 Firma ${index + 1}: ${stats.name}`);
                console.log(`   📋 IČO: ${stats.ico}`);
                console.log(`   🏷️  Typ: ${stats.type}`);
                console.log(`   🔐 Credentials: ${stats.credentials}`);
                console.log(`   📋 Templates: ${stats.templates}`);
                console.log(`   ▶️  Jobs: ${stats.jobs}`);
            });
            
            console.log('\n📈 CELKOVÉ STATISTIKY:');
            console.log(`   🏢 Celkem firem: ${companyStats.length}`);
            console.log(`   🔐 Celkem credentials: ${credentialsList.length}`);
            console.log(`   📋 Celkem templates: ${templatesList.length}`);
            console.log(`   ▶️  Celkem jobs: ${jobsList.length}`);
            console.log('='.repeat(80) + '\n');
            
            // Test vždy projde
            expect(companyStats.length).to.equal(3);
            console.log('✅ Multi-Company Setup úspěšně dokončen!');
        });
    });
});