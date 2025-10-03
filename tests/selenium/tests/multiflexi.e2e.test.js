const { expect } = require('chai');
const { setupDatabase, cleanupDatabase } = require('../scripts/setupDatabase');
const AuthPage = require('../src/AuthPage');
const RunTemplatePage = require('../src/RunTemplatePage');

/**
 * Complete end-to-end test suite for MultiFlexi web interface
 * 
 * Test flow:
 * 1. Setup clean database with migrations
 * 2. Register admin account
 * 3. Login as admin
 * 4. Create and configure RunTemplate
 * 5. Schedule and execute RunTemplate
 * 6. Verify execution results
 * 7. Cleanup
 */
describe('MultiFlexi E2E Tests', function() {
    this.timeout(120000); // 2 minutes timeout for entire suite
    
    let authPage;
    let runTemplatePage;
    let adminCredentials;
    
    before(async function() {
        console.log('Setting up test environment...');
        
        // Setup clean database
        await setupDatabase();
        
        // Initialize page objects
        authPage = new AuthPage();
        runTemplatePage = new RunTemplatePage();
        
        // Initialize WebDriver
        await authPage.initializeDriver();
        await runTemplatePage.initializeDriver();
    });

    after(async function() {
        console.log('Cleaning up test environment...');
        
        // Close WebDriver sessions
        if (authPage) await authPage.quit();
        if (runTemplatePage) await runTemplatePage.quit();
        
        // Cleanup test database
        await cleanupDatabase();
    });

    describe('User Authentication', function() {
        it('should register admin user successfully', async function() {
            adminCredentials = await authPage.registerAdmin();
            
            // Verify registration success
            await authPage.waitForRegistrationSuccess();
            expect(adminCredentials.username).to.equal(process.env.ADMIN_USERNAME || 'admin');
        });

        it('should login admin user successfully', async function() {
            await authPage.loginAsAdmin();
            
            // Verify login success
            await authPage.waitForLoginSuccess();
            
            const isLoggedIn = await authPage.isLoggedIn();
            expect(isLoggedIn).to.be.true;
        });
    });

    describe('RunTemplate Management', function() {
        let templateData;
        
        it('should navigate to RunTemplates section', async function() {
            await runTemplatePage.goToRunTemplates();
            
            // Verify we're on the RunTemplates page
            const currentUrl = await runTemplatePage.driver.getCurrentUrl();
            expect(currentUrl).to.include('runtemplates');
        });

        it('should create new RunTemplate successfully', async function() {
            templateData = await runTemplatePage.createDefaultRunTemplate();
            
            // Verify RunTemplate creation
            const templates = await runTemplatePage.getRunTemplatesList();
            const createdTemplate = templates.find(t => t.name === templateData.name);
            expect(createdTemplate).to.exist;
        });

        it('should execute RunTemplate successfully', async function() {
            await runTemplatePage.executeRunTemplate(templateData.name);
            
            // Wait for execution to complete
            try {
                await runTemplatePage.waitForExecutionComplete(templateData.name, 60000);
            } catch (error) {
                // Take screenshot for debugging
                await runTemplatePage.takeScreenshot('execution-failed');
                throw error;
            }
        });

        it('should verify RunTemplate execution results', async function() {
            // Navigate to jobs page to check execution results
            await runTemplatePage.navigateTo('/jobs.php');
            
            // Verify job was created and executed
            const jobExists = await runTemplatePage.elementExists(runTemplatePage.selectors.jobsList);
            expect(jobExists).to.be.true;
        });
    });

    describe('System Verification', function() {
        it('should verify system is functional after test operations', async function() {
            // Navigate to dashboard
            await authPage.navigateTo('/');
            
            // Verify dashboard loads correctly
            const dashboardLoaded = await authPage.elementExists({ tagName: 'body' });
            expect(dashboardLoaded).to.be.true;
        });

        it('should logout successfully', async function() {
            await authPage.logout();
            
            // Verify logout
            const isLoggedIn = await authPage.isLoggedIn();
            expect(isLoggedIn).to.be.false;
        });
    });
});

/**
 * Smoke tests for quick verification
 */
describe('MultiFlexi Smoke Tests', function() {
    this.timeout(30000);
    
    let authPage;
    
    before(async function() {
        authPage = new AuthPage();
        await authPage.initializeDriver();
    });

    after(async function() {
        if (authPage) await authPage.quit();
    });

    it('should load login page', async function() {
        await authPage.goToLogin();
        
        const loginFormExists = await authPage.elementExists(authPage.selectors.loginForm);
        expect(loginFormExists).to.be.true;
    });

    it('should load registration page', async function() {
        await authPage.goToRegistration();
        
        const registrationFormExists = await authPage.elementExists(authPage.selectors.registrationForm);
        expect(registrationFormExists).to.be.true;
    });
});