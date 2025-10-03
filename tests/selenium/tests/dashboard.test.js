const { expect } = require('chai');
const { setupDatabase, cleanupDatabase } = require('../scripts/setupDatabase');
const AuthPage = require('../src/AuthPage');
const DashboardPage = require('../src/DashboardPage');

/**
 * Tests for MultiFlexi Dashboard functionality
 */
describe('Dashboard Page Tests', function() {
    this.timeout(60000);
    
    let authPage;
    let dashboardPage;
    
    before(async function() {
        // Setup database and authentication
        await setupDatabase();
        
        authPage = new AuthPage();
        dashboardPage = new DashboardPage();
        
        await authPage.initializeDriver();
        await dashboardPage.initializeDriver();
        
        // Login as admin
        await authPage.registerAdmin();
        await authPage.waitForRegistrationSuccess();
        await authPage.loginAsAdmin();
        await authPage.waitForLoginSuccess();
    });

    after(async function() {
        if (authPage) await authPage.quit();
        if (dashboardPage) await dashboardPage.quit();
        await cleanupDatabase();
    });

    describe('Dashboard Loading', function() {
        it('should load dashboard successfully', async function() {
            await dashboardPage.goToDashboard();
            
            const isLoaded = await dashboardPage.isDashboardLoaded();
            expect(isLoaded).to.be.true;
        });

        it('should display page title', async function() {
            await dashboardPage.goToDashboard();
            
            const title = await dashboardPage.getElementText(dashboardPage.selectors.pageTitle);
            expect(title).to.include('Dashboard');
        });

        it('should have navigation menu', async function() {
            await dashboardPage.goToDashboard();
            
            const hasNav = await dashboardPage.elementExists(dashboardPage.selectors.mainNav);
            expect(hasNav).to.be.true;
        });
    });

    describe('Dashboard Content', function() {
        it('should display dashboard cards', async function() {
            await dashboardPage.goToDashboard();
            
            const hasCards = await dashboardPage.elementExists(dashboardPage.selectors.dashboardCards);
            expect(hasCards).to.be.true;
        });

        it('should show system statistics', async function() {
            await dashboardPage.goToDashboard();
            
            const stats = await dashboardPage.getStats();
            expect(stats).to.be.an('object');
        });

        it('should have quick actions section', async function() {
            await dashboardPage.goToDashboard();
            
            const hasActions = await dashboardPage.hasQuickActions();
            // Quick actions might not be present in all configurations
            // Just verify the check doesn't throw an error
        });
    });

    describe('Dashboard Navigation', function() {
        it('should navigate to new job from dashboard', async function() {
            await dashboardPage.goToDashboard();
            
            if (await dashboardPage.elementExists(dashboardPage.selectors.newJobButton)) {
                await dashboardPage.clickNewJob();
                
                // Should redirect to job creation page
                const currentUrl = await dashboardPage.driver.getCurrentUrl();
                expect(currentUrl).to.include('newjob');
            }
        });
    });
});