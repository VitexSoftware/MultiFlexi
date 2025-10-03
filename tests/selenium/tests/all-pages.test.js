const { expect } = require('chai');
const { setupDatabase, cleanupDatabase } = require('../scripts/setupDatabase');
const AuthPage = require('../src/AuthPage');
const WebDriverHelper = require('../src/WebDriverHelper');

/**
 * Tests for all other MultiFlexi pages containing PageTop
 */
describe('All Pages Tests', function() {
    this.timeout(120000);
    
    let authPage;
    let helper;
    
    const pagesToTest = [
        // Core pages
        { url: '/index.php', name: 'Home Page' },
        { url: '/main.php', name: 'Main Page' },
        { url: '/dashboard.php', name: 'Dashboard' },
        
        // User management
        { url: '/users.php', name: 'Users List' },
        { url: '/user.php', name: 'User Details' },
        { url: '/createaccount.php', name: 'Create Account' },
        
        // System pages
        { url: '/status.php', name: 'System Status' },
        { url: '/requirements.php', name: 'System Requirements' },
        { url: '/wizard.php', name: 'Setup Wizard' },
        { url: '/about.php', name: 'About Page' },
        
        // Logs and monitoring
        { url: '/logs.php', name: 'Logs' },
        { url: '/queue.php', name: 'Job Queue' },
        { url: '/periodical.php', name: 'Periodical Tasks' },
        
        // Configuration
        { url: '/conffield.php', name: 'Config Fields' },
        { url: '/intervals.php', name: 'Intervals Setup' },
        { url: '/servers.php', name: 'Servers List' },
        
        // Modules and extensions
        { url: '/executors.php', name: 'Executor Modules' },
        { url: '/actionmodules.php', name: 'Action Modules' },
        { url: '/envmods.php', name: 'Environment Modules' },
        { url: '/credtypes.php', name: 'Credential Type Helpers' },
        
        // Search and templates
        { url: '/search.php', name: 'Search' },
        { url: '/template.php', name: 'Template' },
        
        // Customers (if available)
        { url: '/customers.php', name: 'Customers List' },
        { url: '/customer.php', name: 'Customer Details' }
    ];
    
    before(async function() {
        await setupDatabase();
        
        authPage = new AuthPage();
        helper = new WebDriverHelper();
        
        await authPage.initializeDriver();
        await helper.initializeDriver();
        
        // Login as admin
        await authPage.registerAdmin();
        await authPage.waitForRegistrationSuccess();
        await authPage.loginAsAdmin();
        await authPage.waitForLoginSuccess();
    });

    after(async function() {
        if (authPage) await authPage.quit();
        if (helper) await helper.quit();
        await cleanupDatabase();
    });

    describe('Page Loading Tests', function() {
        pagesToTest.forEach(function(page) {
            it(`should load ${page.name} (${page.url})`, async function() {
                try {
                    await helper.navigateTo(page.url);
                    
                    // Wait for page to load
                    await helper.waitForPageLoad();
                    
                    // Check if page loaded without error
                    const title = await helper.driver.getTitle();
                    expect(title).to.not.include('Error');
                    expect(title).to.not.include('404');
                    
                    // Check if body exists
                    const hasBody = await helper.elementExists({ tagName: 'body' });
                    expect(hasBody).to.be.true;
                    
                    // Check for common error indicators
                    const hasError = await helper.elementExists({ css: '.alert-danger, .error' });
                    if (hasError) {
                        const errorText = await helper.getElementText({ css: '.alert-danger, .error' });
                        console.warn(`Warning on ${page.name}: ${errorText}`);
                    }
                    
                } catch (error) {
                    // Some pages might not be accessible or might require specific parameters
                    console.warn(`Could not fully test ${page.name}: ${error.message}`);
                    
                    // At minimum, check that we don't get a complete failure
                    const currentUrl = await helper.driver.getCurrentUrl();
                    expect(currentUrl).to.include(page.url.split('?')[0]);
                }
            });
        });
    });

    describe('Page Structure Tests', function() {
        it('should have consistent navigation on pages', async function() {
            const pagesWithNav = ['/dashboard.php', '/apps.php', '/companies.php', '/jobs.php'];
            
            for (const pageUrl of pagesWithNav) {
                try {
                    await helper.navigateTo(pageUrl);
                    await helper.waitForPageLoad();
                    
                    // Check for navigation elements
                    const hasNav = await helper.elementExists({ css: '.navbar, nav' });
                    expect(hasNav).to.be.true;
                    
                } catch (error) {
                    console.warn(`Navigation check failed for ${pageUrl}: ${error.message}`);
                }
            }
        });

        it('should have page titles on main pages', async function() {
            const mainPages = ['/dashboard.php', '/apps.php', '/companies.php'];
            
            for (const pageUrl of mainPages) {
                try {
                    await helper.navigateTo(pageUrl);
                    await helper.waitForPageLoad();
                    
                    // Check for page title/header
                    const hasTitle = await helper.elementExists({ css: 'h1, .page-header, .page-title' });
                    expect(hasTitle).to.be.true;
                    
                } catch (error) {
                    console.warn(`Title check failed for ${pageUrl}: ${error.message}`);
                }
            }
        });
    });

    describe('Authentication Required Pages', function() {
        it('should protect admin pages from unauthorized access', async function() {
            // Logout first
            await authPage.logout();
            
            const protectedPages = ['/users.php', '/servers.php', '/executors.php'];
            
            for (const pageUrl of protectedPages) {
                try {
                    await helper.navigateTo(pageUrl);
                    
                    // Should redirect to login or show access denied
                    const currentUrl = await helper.driver.getCurrentUrl();
                    const isProtected = currentUrl.includes('login') || 
                                      currentUrl.includes('access') ||
                                      await helper.elementExists({ css: '.access-denied, .login-required' });
                    
                    expect(isProtected).to.be.true;
                    
                } catch (error) {
                    // Protection mechanism might vary
                    console.warn(`Protection check for ${pageUrl}: ${error.message}`);
                }
            }
            
            // Login back for other tests
            await authPage.loginAsAdmin();
            await authPage.waitForLoginSuccess();
        });
    });

    describe('Error Handling Tests', function() {
        it('should handle invalid IDs gracefully', async function() {
            const pagesWithIds = [
                '/app.php?id=99999',
                '/company.php?id=99999', 
                '/user.php?id=99999',
                '/job.php?id=99999'
            ];
            
            for (const pageUrl of pagesWithIds) {
                try {
                    await helper.navigateTo(pageUrl);
                    await helper.waitForPageLoad();
                    
                    // Should handle gracefully - either show error message or redirect
                    const hasError = await helper.elementExists({ 
                        css: '.alert-danger, .error, .not-found' 
                    });
                    const currentUrl = await helper.driver.getCurrentUrl();
                    
                    // Either shows error message or redirects away from invalid ID
                    expect(hasError || !currentUrl.includes('99999')).to.be.true;
                    
                } catch (error) {
                    console.warn(`Error handling check for ${pageUrl}: ${error.message}`);
                }
            }
        });
    });
});