const { expect } = require('chai');
const { setupDatabase, cleanupDatabase } = require('../scripts/setupDatabase');
const AuthPage = require('../src/AuthPage');
const ApplicationsPage = require('../src/ApplicationsPage');

/**
 * Tests for MultiFlexi Applications functionality
 */
describe('Applications Page Tests', function() {
    this.timeout(90000);
    
    let authPage;
    let applicationsPage;
    
    before(async function() {
        await setupDatabase();
        
        authPage = new AuthPage();
        applicationsPage = new ApplicationsPage();
        
        await authPage.initializeDriver();
        await applicationsPage.initializeDriver();
        
        // Login as admin
        await authPage.registerAdmin();
        await authPage.waitForRegistrationSuccess();
        await authPage.loginAsAdmin();
        await authPage.waitForLoginSuccess();
    });

    after(async function() {
        if (authPage) await authPage.quit();
        if (applicationsPage) await applicationsPage.quit();
        await cleanupDatabase();
    });

    describe('Applications List', function() {
        it('should load applications page', async function() {
            await applicationsPage.goToApplications();
            
            const title = await applicationsPage.getElementText(applicationsPage.selectors.pageTitle);
            expect(title).to.include('Application');
        });

        it('should display applications table', async function() {
            await applicationsPage.goToApplications();
            
            const hasTable = await applicationsPage.elementExists(applicationsPage.selectors.applicationsTable);
            expect(hasTable).to.be.true;
        });

        it('should get list of applications', async function() {
            const apps = await applicationsPage.getApplicationsList();
            
            expect(apps).to.be.an('array');
            // Should have at least the test app from database setup
            expect(apps.length).to.be.greaterThan(0);
        });
    });

    describe('Application Creation', function() {
        it('should navigate to new application form', async function() {
            await applicationsPage.goToNewApplication();
            
            const hasNameInput = await applicationsPage.elementExists(applicationsPage.selectors.nameInput);
            expect(hasNameInput).to.be.true;
        });

        it('should create new application successfully', async function() {
            const appData = {
                name: 'Test Selenium App',
                description: 'Application created by Selenium test',
                executable: 'echo',
                homepage: 'http://example.com',
                version: '1.0.0',
                enabled: true
            };
            
            await applicationsPage.createApplication(appData);
            
            // Verify creation by checking if we're redirected to app details
            const currentUrl = await applicationsPage.driver.getCurrentUrl();
            expect(currentUrl).to.include('app.php?id=');
        });

        it('should display created application in list', async function() {
            const apps = await applicationsPage.getApplicationsList();
            
            const testApp = apps.find(app => app.name.includes('Test Selenium App'));
            expect(testApp).to.exist;
        });
    });

    describe('Application Details', function() {
        it('should view specific application', async function() {
            // Use the test app ID (assuming it's created in database setup)
            await applicationsPage.goToApplication(1);
            
            const title = await applicationsPage.getElementText(applicationsPage.selectors.pageTitle);
            expect(title).to.include('Application');
        });

        it('should display application information', async function() {
            await applicationsPage.goToApplication(1);
            
            // Check if page has application-specific content
            const hasContent = await applicationsPage.elementExists(By.css('body'));
            expect(hasContent).to.be.true;
        });
    });

    describe('Application Search', function() {
        it('should search for applications', async function() {
            await applicationsPage.searchApplication('Test');
            
            // Verify search functionality doesn't cause errors
            const hasTable = await applicationsPage.elementExists(applicationsPage.selectors.applicationsTable);
            expect(hasTable).to.be.true;
        });
    });
});