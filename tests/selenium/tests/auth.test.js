const { expect } = require('chai');
const AuthPage = require('../src/AuthPage');

/**
 * Focused tests for authentication functionality
 */
describe('Authentication Tests', function() {
    this.timeout(60000);
    
    let authPage;
    
    beforeEach(async function() {
        authPage = new AuthPage();
        await authPage.initializeDriver();
    });

    afterEach(async function() {
        if (authPage) await authPage.quit();
    });

    describe('User Registration', function() {
        it('should register new user with valid data', async function() {
            const userData = {
                username: 'testuser',
                password: 'testpass123',
                email: 'test@example.com',
                firstName: 'Test',
                lastName: 'User'
            };
            
            await authPage.register(userData);
            await authPage.waitForRegistrationSuccess();
        });

        it('should show error for invalid email', async function() {
            const userData = {
                username: 'testuser2',
                password: 'testpass123',
                email: 'invalid-email',
                firstName: 'Test',
                lastName: 'User'
            };
            
            await authPage.register(userData);
            
            // Should show error message
            const hasError = await authPage.elementExists(authPage.selectors.errorMessage);
            expect(hasError).to.be.true;
        });

        it('should show error for password mismatch', async function() {
            await authPage.goToRegistration();
            
            await authPage.fillInput(authPage.selectors.regUsernameInput, 'testuser3');
            await authPage.fillInput(authPage.selectors.regPasswordInput, 'password123');
            await authPage.fillInput(authPage.selectors.regPasswordConfirmInput, 'differentpassword');
            await authPage.fillInput(authPage.selectors.regEmailInput, 'test3@example.com');
            
            await authPage.clickElement(authPage.selectors.registerButton);
            
            // Should show error message
            const hasError = await authPage.elementExists(authPage.selectors.errorMessage);
            expect(hasError).to.be.true;
        });
    });

    describe('User Login', function() {
        it('should login with valid credentials', async function() {
            // First register a user
            const userData = {
                username: 'logintest',
                password: 'loginpass123',
                email: 'logintest@example.com'
            };
            
            await authPage.register(userData);
            await authPage.waitForRegistrationSuccess();
            
            // Then login
            await authPage.login(userData.username, userData.password);
            await authPage.waitForLoginSuccess();
            
            const isLoggedIn = await authPage.isLoggedIn();
            expect(isLoggedIn).to.be.true;
        });

        it('should show error for invalid credentials', async function() {
            await authPage.login('nonexistent', 'wrongpassword');
            
            // Should show error message
            const hasError = await authPage.elementExists(authPage.selectors.errorMessage);
            expect(hasError).to.be.true;
        });

        it('should logout successfully', async function() {
            // Register and login first
            const userData = {
                username: 'logouttest',
                password: 'logoutpass123',
                email: 'logouttest@example.com'
            };
            
            await authPage.register(userData);
            await authPage.waitForRegistrationSuccess();
            await authPage.login(userData.username, userData.password);
            await authPage.waitForLoginSuccess();
            
            // Then logout
            await authPage.logout();
            
            const isLoggedIn = await authPage.isLoggedIn();
            expect(isLoggedIn).to.be.false;
        });
    });
});