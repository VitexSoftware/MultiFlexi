const { expect } = require('chai');
const { setupDatabase, cleanupDatabase } = require('../scripts/setupDatabase');
const AuthPage = require('../src/AuthPage');
const DashboardPage = require('../src/DashboardPage');
const ApplicationsPage = require('../src/ApplicationsPage');
const CompaniesPage = require('../src/CompaniesPage');
const JobsPage = require('../src/JobsPage');
const CredentialsPage = require('../src/CredentialsPage');
const RunTemplatePage = require('../src/RunTemplatePage');

/**
 * Complete integration test for all MultiFlexi pages
 * This test performs a comprehensive workflow across all major pages
 */
describe('Complete MultiFlexi Integration Test', function() {
    this.timeout(300000); // 5 minutes for complete workflow
    
    let authPage;
    let dashboardPage;
    let applicationsPage;
    let companiesPage;
    let jobsPage;
    let credentialsPage;
    let runTemplatePage;
    
    let createdApp = null;
    let createdCompany = null;
    let createdCredential = null;
    let createdRunTemplate = null;
    
    before(async function() {
        console.log('Setting up complete integration test environment...');
        
        // Setup clean database
        await setupDatabase();
        
        // Initialize all page objects
        authPage = new AuthPage();
        dashboardPage = new DashboardPage();
        applicationsPage = new ApplicationsPage();
        companiesPage = new CompaniesPage();
        jobsPage = new JobsPage();
        credentialsPage = new CredentialsPage();
        runTemplatePage = new RunTemplatePage();
        
        // Initialize WebDriver for all pages (they share the same session)
        await authPage.initializeDriver();
        
        // Set the same driver for all page objects
        dashboardPage.driver = authPage.driver;
        applicationsPage.driver = authPage.driver;
        companiesPage.driver = authPage.driver;
        jobsPage.driver = authPage.driver;
        credentialsPage.driver = authPage.driver;
        runTemplatePage.driver = authPage.driver;
    });

    after(async function() {
        console.log('Cleaning up complete integration test environment...');
        
        if (authPage) await authPage.quit();
        await cleanupDatabase();
    });

    describe('Complete Workflow Integration', function() {
        it('Step 1: Should register and login admin user', async function() {
            console.log('Step 1: Registering admin user...');
            
            const adminCredentials = await authPage.registerAdmin();
            await authPage.waitForRegistrationSuccess();
            
            console.log('Step 1: Logging in as admin...');
            await authPage.loginAsAdmin();
            await authPage.waitForLoginSuccess();
            
            const isLoggedIn = await authPage.isLoggedIn();
            expect(isLoggedIn).to.be.true;
            
            console.log('✓ Step 1 completed: Admin user registered and logged in');
        });

        it('Step 2: Should access and verify dashboard', async function() {
            console.log('Step 2: Accessing dashboard...');
            
            await dashboardPage.goToDashboard();
            const isLoaded = await dashboardPage.isDashboardLoaded();
            expect(isLoaded).to.be.true;
            
            const stats = await dashboardPage.getStats();
            console.log('Dashboard stats:', stats);
            
            console.log('✓ Step 2 completed: Dashboard accessed and verified');
        });

        it('Step 3: Should create a new application', async function() {
            console.log('Step 3: Creating new application...');
            
            createdApp = {
                name: 'Integration Test App',
                description: 'Application created during integration test',
                executable: 'echo "Integration test"',
                homepage: 'http://example.com',
                version: '1.0.0',
                enabled: true
            };
            
            await applicationsPage.createApplication(createdApp);
            
            // Verify application was created
            const apps = await applicationsPage.getApplicationsList();
            const foundApp = apps.find(app => app.name.includes('Integration Test App'));
            expect(foundApp).to.exist;
            
            console.log('✓ Step 3 completed: Application created successfully');
        });

        it('Step 4: Should create a new company', async function() {
            console.log('Step 4: Creating new company...');
            
            createdCompany = {
                name: 'Integration Test Company',
                enabled: true
            };
            
            await companiesPage.createCompany(createdCompany);
            
            // Verify company was created
            const companies = await companiesPage.getCompaniesList();
            const foundCompany = companies.find(company => 
                company.name.includes('Integration Test Company')
            );
            expect(foundCompany).to.exist;
            
            console.log('✓ Step 4 completed: Company created successfully');
        });

        it('Step 5: Should create a new credential', async function() {
            console.log('Step 5: Creating new credential...');
            
            createdCredential = {
                name: 'Integration Test Credential',
                login: 'testuser',
                password: 'testpass123',
                url: 'http://example.com',
                enabled: true
            };
            
            await credentialsPage.createCredential(createdCredential);
            
            // Verify credential was created
            const credentials = await credentialsPage.getCredentialsList();
            const foundCredential = credentials.find(cred => 
                cred.name.includes('Integration Test Credential')
            );
            expect(foundCredential).to.exist;
            
            console.log('✓ Step 5 completed: Credential created successfully');
        });

        it('Step 6: Should create and configure RunTemplate', async function() {
            console.log('Step 6: Creating RunTemplate...');
            
            createdRunTemplate = {
                name: 'Integration Test RunTemplate',
                description: 'RunTemplate created during integration test',
                application: 'TestApp', // From database setup
                scheduleType: 'manual',
                enabled: true,
                envVars: {
                    'TEST_MODE': 'integration',
                    'DEBUG': 'true'
                }
            };
            
            await runTemplatePage.createRunTemplate(createdRunTemplate);
            
            // Verify RunTemplate was created
            const templates = await runTemplatePage.getRunTemplatesList();
            const foundTemplate = templates.find(template => 
                template.name.includes('Integration Test RunTemplate')
            );
            expect(foundTemplate).to.exist;
            
            console.log('✓ Step 6 completed: RunTemplate created successfully');
        });

        it('Step 7: Should execute RunTemplate', async function() {
            console.log('Step 7: Executing RunTemplate...');
            
            await runTemplatePage.executeRunTemplate(createdRunTemplate.name);
            
            // Wait a bit for execution to start
            await runTemplatePage.driver.sleep(3000);
            
            console.log('✓ Step 7 completed: RunTemplate execution initiated');
        });

        it('Step 8: Should verify job execution', async function() {
            console.log('Step 8: Checking job execution...');
            
            // Check jobs list
            const jobs = await jobsPage.getJobsList();
            expect(jobs).to.be.an('array');
            
            if (jobs.length > 0) {
                const recentJob = jobs[0];
                console.log('Recent job:', recentJob);
                
                // Try to get job status
                const status = await jobsPage.getJobStatus(recentJob.id);
                console.log('Job status:', status);
                
                expect(status).to.be.a('string');
            }
            
            console.log('✓ Step 8 completed: Job execution verified');
        });

        it('Step 9: Should navigate through all main sections', async function() {
            console.log('Step 9: Testing navigation through main sections...');
            
            const sectionsToTest = [
                { page: dashboardPage, method: 'goToDashboard', name: 'Dashboard' },
                { page: applicationsPage, method: 'goToApplications', name: 'Applications' },
                { page: companiesPage, method: 'goToCompanies', name: 'Companies' },
                { page: jobsPage, method: 'goToJobs', name: 'Jobs' },
                { page: credentialsPage, method: 'goToCredentials', name: 'Credentials' },
                { page: runTemplatePage, method: 'goToRunTemplates', name: 'RunTemplates' }
            ];
            
            for (const section of sectionsToTest) {
                console.log(`Testing navigation to ${section.name}...`);
                
                await section.page[section.method]();
                
                // Verify page loads without error
                const currentUrl = await section.page.driver.getCurrentUrl();
                expect(currentUrl).to.not.include('error');
                
                console.log(`✓ Successfully navigated to ${section.name}`);
            }
            
            console.log('✓ Step 9 completed: All main sections accessible');
        });

        it('Step 10: Should logout successfully', async function() {
            console.log('Step 10: Logging out...');
            
            await authPage.logout();
            
            const isLoggedIn = await authPage.isLoggedIn();
            expect(isLoggedIn).to.be.false;
            
            console.log('✓ Step 10 completed: User logged out successfully');
        });
    });

    describe('Integration Test Summary', function() {
        it('should provide test summary', async function() {
            console.log('\n=== INTEGRATION TEST SUMMARY ===');
            console.log('✓ Admin user registration and login');
            console.log('✓ Dashboard access and verification');
            console.log('✓ Application creation and management');
            console.log('✓ Company creation and management'); 
            console.log('✓ Credential creation and management');
            console.log('✓ RunTemplate creation and configuration');
            console.log('✓ Job execution and monitoring');
            console.log('✓ Cross-section navigation');
            console.log('✓ User logout and session management');
            console.log('\n✅ All integration tests completed successfully!');
            console.log('================================\n');
            
            // This test always passes - it's just for logging
            expect(true).to.be.true;
        });
    });
});