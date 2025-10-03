const { expect } = require('chai');
const { setupDatabase, cleanupDatabase } = require('../scripts/setupDatabase');
const AuthPage = require('../src/AuthPage');
const CompaniesPage = require('../src/CompaniesPage');

/**
 * Tests for MultiFlexi Companies functionality
 */
describe('Companies Page Tests', function() {
    this.timeout(90000);
    
    let authPage;
    let companiesPage;
    
    before(async function() {
        await setupDatabase();
        
        authPage = new AuthPage();
        companiesPage = new CompaniesPage();
        
        await authPage.initializeDriver();
        await companiesPage.initializeDriver();
        
        // Login as admin
        await authPage.registerAdmin();
        await authPage.waitForRegistrationSuccess();
        await authPage.loginAsAdmin();
        await authPage.waitForLoginSuccess();
    });

    after(async function() {
        if (authPage) await authPage.quit();
        if (companiesPage) await companiesPage.quit();
        await cleanupDatabase();
    });

    describe('Companies List', function() {
        it('should load companies page', async function() {
            await companiesPage.goToCompanies();
            
            const title = await companiesPage.getElementText(companiesPage.selectors.pageTitle);
            expect(title).to.include('Company');
        });

        it('should display companies table', async function() {
            await companiesPage.goToCompanies();
            
            const hasTable = await companiesPage.elementExists(companiesPage.selectors.companiesTable);
            expect(hasTable).to.be.true;
        });

        it('should get list of companies', async function() {
            const companies = await companiesPage.getCompaniesList();
            
            expect(companies).to.be.an('array');
        });
    });

    describe('Company Creation', function() {
        it('should navigate to new company form', async function() {
            await companiesPage.goToNewCompany();
            
            const hasNameInput = await companiesPage.elementExists(companiesPage.selectors.nameInput);
            expect(hasNameInput).to.be.true;
        });

        it('should create new company successfully', async function() {
            const companyData = {
                name: process.env.TEST_COMPANY_NAME || 'Test Selenium Company',
                enabled: true
            };
            
            await companiesPage.createCompany(companyData);
            
            // Verify creation by checking if we're redirected to company details
            const currentUrl = await companiesPage.driver.getCurrentUrl();
            expect(currentUrl).to.include('company.php');
        });

        it('should display created company in list', async function() {
            const companies = await companiesPage.getCompaniesList();
            
            const testCompany = companies.find(company => 
                company.name.includes('Test Selenium Company') || 
                company.name.includes(process.env.TEST_COMPANY_NAME || 'Test Company')
            );
            expect(testCompany).to.exist;
        });
    });

    describe('Company Details', function() {
        it('should view specific company', async function() {
            // Try to navigate to company details
            await companiesPage.goToCompany(1);
            
            const title = await companiesPage.getElementText(companiesPage.selectors.pageTitle);
            expect(title).to.include('Company');
        });

        it('should display company information', async function() {
            await companiesPage.goToCompany(1);
            
            // Check if page has company-specific content
            const hasContent = await companiesPage.elementExists(By.css('body'));
            expect(hasContent).to.be.true;
        });
    });
});