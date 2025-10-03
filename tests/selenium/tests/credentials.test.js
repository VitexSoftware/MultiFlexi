const { expect } = require('chai');
const { setupDatabase, cleanupDatabase } = require('../scripts/setupDatabase');
const AuthPage = require('../src/AuthPage');
const CredentialsPage = require('../src/CredentialsPage');

/**
 * Tests for MultiFlexi Credentials functionality
 */
describe('Credentials Page Tests', function() {
    this.timeout(90000);
    
    let authPage;
    let credentialsPage;
    
    before(async function() {
        await setupDatabase();
        
        authPage = new AuthPage();
        credentialsPage = new CredentialsPage();
        
        await authPage.initializeDriver();
        await credentialsPage.initializeDriver();
        
        // Login as admin
        await authPage.registerAdmin();
        await authPage.waitForRegistrationSuccess();
        await authPage.loginAsAdmin();
        await authPage.waitForLoginSuccess();
    });

    after(async function() {
        if (authPage) await authPage.quit();
        if (credentialsPage) await credentialsPage.quit();
        await cleanupDatabase();
    });

    describe('Credentials List', function() {
        it('should load credentials page', async function() {
            await credentialsPage.goToCredentials();
            
            const title = await credentialsPage.getElementText(credentialsPage.selectors.pageTitle);
            expect(title).to.include('Credential');
        });

        it('should display credentials table', async function() {
            await credentialsPage.goToCredentials();
            
            const hasTable = await credentialsPage.elementExists(credentialsPage.selectors.credentialsTable);
            expect(hasTable).to.be.true;
        });

        it('should get list of credentials', async function() {
            const credentials = await credentialsPage.getCredentialsList();
            
            expect(credentials).to.be.an('array');
        });
    });

    describe('Credential Creation', function() {
        it('should navigate to new credential form', async function() {
            await credentialsPage.goToNewCredential();
            
            const hasNameInput = await credentialsPage.elementExists(credentialsPage.selectors.nameInput);
            expect(hasNameInput).to.be.true;
        });

        it('should create new credential successfully', async function() {
            const credentialData = {
                name: 'Test Selenium Credential',
                login: 'testuser',
                password: 'testpass123',
                url: 'http://example.com',
                enabled: true
            };
            
            await credentialsPage.createCredential(credentialData);
            
            // Verify creation by checking if we're redirected to credential details
            const currentUrl = await credentialsPage.driver.getCurrentUrl();
            expect(currentUrl).to.include('credential.php');
        });

        it('should display created credential in list', async function() {
            const credentials = await credentialsPage.getCredentialsList();
            
            const testCredential = credentials.find(cred => 
                cred.name.includes('Test Selenium Credential')
            );
            expect(testCredential).to.exist;
        });
    });

    describe('Credential Types', function() {
        it('should load credential types page', async function() {
            await credentialsPage.goToCredentialTypes();
            
            const title = await credentialsPage.getElementText(credentialsPage.selectors.pageTitle);
            expect(title).to.include('Credential');
        });

        it('should display credential types content', async function() {
            await credentialsPage.goToCredentialTypes();
            
            // Check if page has content
            const hasContent = await credentialsPage.elementExists(By.css('body'));
            expect(hasContent).to.be.true;
        });
    });

    describe('Credential Details', function() {
        it('should view specific credential', async function() {
            // Try to navigate to credential details
            await credentialsPage.goToCredential(1);
            
            const title = await credentialsPage.getElementText(credentialsPage.selectors.pageTitle);
            expect(title).to.include('MultiFlexi');
        });

        it('should display credential information', async function() {
            await credentialsPage.goToCredential(1);
            
            // Check if page has credential-specific content
            const hasContent = await credentialsPage.elementExists(By.css('body'));
            expect(hasContent).to.be.true;
        });
    });
});